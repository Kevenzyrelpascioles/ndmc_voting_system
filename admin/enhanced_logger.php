<?php
/**
 * Enhanced Logger Class for NDMC Voting System
 * Provides comprehensive logging for all admin activities and database changes
 */

class EnhancedLogger {
    private $conn;
    private $user_id;
    private $user_name;
    private $user_type;
    private $ip_address;
    private $user_agent;
    private $session_id;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
        $this->ip_address = $this->getClientIP();
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $this->session_id = session_id();
        
        // Get current user info from session with better fallback handling
        if (isset($_SESSION['id'])) {
            $this->user_id = $_SESSION['id'];
            
            // Prioritize session User_Type over database lookup for simplicity
            if (isset($_SESSION['User_Type']) && !empty($_SESSION['User_Type'])) {
                $this->user_type = $_SESSION['User_Type'];
                $this->user_name = $_SESSION['User_Type'];
            } else {
                // Fallback: try to get user info from database if session User_Type is not available
                $user_query = mysqli_query($this->conn, "SELECT User_Type, FirstName, LastName FROM users WHERE User_id = '{$_SESSION['id']}'");
                if ($user_query && mysqli_num_rows($user_query) > 0) {
                    $user_row = mysqli_fetch_array($user_query);
                    $this->user_type = $user_row['User_Type'];
                    $this->user_name = $user_row['User_Type'];
                } else {
                    $this->user_name = 'Admin (ID: ' . $_SESSION['id'] . ')';
                    $this->user_type = 'admin';
                }
            }
        } else {
            // Fallback for system operations
            $this->user_id = null;
            $this->user_name = 'System';
            $this->user_type = 'system';
        }
        
        // Ensure user_name is never null
        if (empty($this->user_name)) {
            $this->user_name = 'Anonymous';
        }
    }
    
    /**
     * Determine if an activity should be logged based on importance
     */
    private function shouldLogActivity($action, $data) {
        $action_lower = strtolower($action);
        
        // Skip repetitive page access logs
        if (strpos($action_lower, 'page access') !== false) {
            return false;
        }
        
        // Skip button clicks and navigation
        if (strpos($action_lower, 'button click') !== false || 
            strpos($action_lower, 'navigation') !== false ||
            strpos($action_lower, 'refresh') !== false) {
            return false;
        }
        
        // Always log important security and database activities
        $important_keywords = [
            'login', 'logout', 'password', 'reset', 'delete', 'add', 'edit', 'update',
            'create', 'remove', 'candidate', 'voter', 'user', 'database', 'backup',
            'restore', 'export', 'import', 'system', 'security', 'admin', 'permission'
        ];
        
        foreach ($important_keywords as $keyword) {
            if (strpos($action_lower, $keyword) !== false || strpos(strtolower($data), $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Main logging function that logs to the existing history table with enhanced data
     */
    public function logActivity($action, $data, $table_name = null, $record_id = null, $severity = 'MEDIUM', $old_data = null, $new_data = null) {
        // Check if this activity should be logged
        if (!$this->shouldLogActivity($action, $data)) {
            return; // Skip logging this activity
        }
        
        // Create detailed description
        $details = $this->createDetailedDescription($action, $data, $table_name, $record_id);
        
        // Enhanced data with more context
        $enhanced_data = $data;
        if ($table_name && $record_id) {
            $enhanced_data .= " (Table: $table_name, ID: $record_id)";
        }
        
        // Ensure user_name is not null
        $user_name = $this->user_name ?? 'System';
        if (empty($user_name)) {
            $user_name = 'Anonymous';
        }
        
        // Insert into existing history table structure (compatible with current schema)
        $query = "INSERT INTO history (
            data, 
            action, 
            date, 
            user
        ) VALUES (?, ?, NOW(), ?)";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", 
                $enhanced_data, 
                $action, 
                $user_name
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Also log to system activity if it's a significant action
        if (in_array($severity, ['HIGH', 'CRITICAL'])) {
            $this->logSystemActivity($action, $details, $table_name);
        }
    }
    
    /**
     * Log database changes with before/after data
     */
    public function logDatabaseChange($table_name, $operation, $record_id, $old_data = null, $new_data = null, $field_name = null) {
        $action = ucfirst(strtolower($operation)) . " " . ucfirst($table_name);
        $data = "Record ID: $record_id";
        
        if ($old_data && $new_data) {
            $data .= " | Changed: " . $this->formatDataChanges($old_data, $new_data);
        }
        
        $severity = $this->determineSeverity($operation);
        $this->logActivity($action, $data, $table_name, $record_id, $severity, $old_data, $new_data);
    }
    
    /**
     * Log user authentication events
     */
    public function logAuthentication($username, $success, $failure_reason = null) {
        $action = $success ? 'Login' : 'Failed Login';
        $data = $username;
        if (!$success && $failure_reason) {
            $data .= " - Reason: $failure_reason";
        }
        
        $severity = $success ? 'LOW' : 'MEDIUM';
        $this->logActivity($action, $data, 'users', null, $severity);
        
        // Also log to login attempts tracking
        $this->logLoginAttempt($username, $success, $failure_reason);
    }
    
    /**
     * Log system operations (resets, backups, etc.)
     */
    public function logSystemOperation($operation, $details, $affected_count = 1) {
        $action = "System: $operation";
        $data = $details;
        if ($affected_count > 1) {
            $data .= " (Affected: $affected_count records)";
        }
        
        $severity = 'HIGH';
        $this->logActivity($action, $data, null, null, $severity);
    }
    
    /**
     * Log page access and queries for admin monitoring
     */
    public function logPageAccess($page_name, $query_executed = null, $execution_time = null) {
        $action = "Page Access";
        $data = "Accessed: $page_name";
        
        if ($query_executed) {
            $data .= " | Query: " . substr($query_executed, 0, 100) . "...";
        }
        
        if ($execution_time) {
            $data .= " | Time: {$execution_time}ms";
        }
        
        $this->logActivity($action, $data, null, null, 'LOW');
    }
    
    /**
     * Log search activities
     */
    public function logSearch($search_term, $table_searched, $results_count) {
        $action = "Search";
        $data = "Searched '$search_term' in $table_searched (Results: $results_count)";
        $this->logActivity($action, $data, $table_searched, null, 'LOW');
    }
    
    /**
     * Log export/download activities
     */
    public function logExport($export_type, $table_name, $record_count) {
        $action = "Export";
        $data = "Exported $export_type from $table_name ($record_count records)";
        $this->logActivity($action, $data, $table_name, null, 'MEDIUM');
    }
    
    /**
     * Log candidate management activities
     */
    public function logCandidateActivity($action, $candidate_name, $position = null, $candidate_id = null) {
        $data = "Candidate: $candidate_name";
        if ($position) {
            $data .= " | Position: $position";
        }
        $this->logActivity($action, $data, 'candidates', $candidate_id, 'MEDIUM');
    }
    
    /**
     * Log voter management activities
     */
    public function logVoterActivity($action, $voter_name, $voter_id = null, $additional_info = null) {
        $data = "Voter: $voter_name";
        if ($additional_info) {
            $data .= " | $additional_info";
        }
        $this->logActivity($action, $data, 'voters', $voter_id, 'MEDIUM');
    }
    
    /**
     * Log password reset activities
     */
    public function logPasswordReset($target_user, $target_type = 'voter', $target_id = null) {
        $action = "Password Reset";
        $data = "Reset password for $target_type: $target_user";
        $severity = 'HIGH'; // Password resets are high priority for security
        $this->logActivity($action, $data, $target_type === 'voter' ? 'voters' : 'users', $target_id, $severity);
    }
    
    /**
     * Log administrative actions
     */
    public function logAdminAction($action, $details, $affected_table = null, $record_id = null) {
        $this->logActivity($action, $details, $affected_table, $record_id, 'HIGH');
    }
    
    /**
     * Private helper methods
     */
    private function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    private function determineCategory($action) {
        $action_lower = strtolower($action);
        
        if (strpos($action_lower, 'login') !== false || strpos($action_lower, 'logout') !== false) {
            return 'Authentication';
        } elseif (strpos($action_lower, 'add') !== false || strpos($action_lower, 'create') !== false) {
            return 'Creation';
        } elseif (strpos($action_lower, 'edit') !== false || strpos($action_lower, 'update') !== false) {
            return 'Modification';
        } elseif (strpos($action_lower, 'delete') !== false || strpos($action_lower, 'remove') !== false) {
            return 'Deletion';
        } elseif (strpos($action_lower, 'reset') !== false || strpos($action_lower, 'system') !== false) {
            return 'System';
        } elseif (strpos($action_lower, 'search') !== false) {
            return 'Search';
        } elseif (strpos($action_lower, 'export') !== false || strpos($action_lower, 'download') !== false) {
            return 'Export';
        } else {
            return 'General';
        }
    }
    
    private function determineSeverity($operation) {
        switch (strtoupper($operation)) {
            case 'DELETE':
            case 'DROP':
                return 'HIGH';
            case 'UPDATE':
            case 'ALTER':
                return 'MEDIUM';
            case 'INSERT':
            case 'CREATE':
            default:
                return 'LOW';
        }
    }
    
    private function createDetailedDescription($action, $data, $table_name, $record_id) {
        $details = "Action: $action | Data: $data";
        
        if ($table_name) {
            $details .= " | Table: $table_name";
        }
        
        if ($record_id) {
            $details .= " | Record ID: $record_id";
        }
        
        $details .= " | IP: {$this->ip_address} | Time: " . date('Y-m-d H:i:s');
        
        return $details;
    }
    
    private function formatDataChanges($old_data, $new_data) {
        if (is_array($old_data) && is_array($new_data)) {
            $changes = [];
            foreach ($new_data as $key => $new_value) {
                if (isset($old_data[$key]) && $old_data[$key] != $new_value) {
                    $changes[] = "$key: '{$old_data[$key]}' → '$new_value'";
                }
            }
            return implode(', ', $changes);
        }
        
        return "Old: $old_data | New: $new_data";
    }
    
    private function logSystemActivity($action, $description, $table_name = null) {
        // Create system_activity table if it doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS system_activity (
            activity_id INT AUTO_INCREMENT PRIMARY KEY,
            activity_type VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            user_name VARCHAR(100),
            ip_address VARCHAR(45),
            page_accessed VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            session_id VARCHAR(255)
        )";
        mysqli_query($this->conn, $create_table);
        
        $page_accessed = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        
        $query = "INSERT INTO system_activity (
            activity_type, 
            description, 
            user_name, 
            ip_address, 
            page_accessed, 
            session_id
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssss", 
                $action, 
                $description, 
                $this->user_name, 
                $this->ip_address, 
                $page_accessed, 
                $this->session_id
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    private function logLoginAttempt($username, $success, $failure_reason) {
        // Create login_attempts table if it doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS login_attempts (
            attempt_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success TINYINT(1) DEFAULT 0,
            failure_reason VARCHAR(255),
            session_id VARCHAR(255)
        )";
        mysqli_query($this->conn, $create_table);
        
        $query = "INSERT INTO login_attempts (
            username, 
            ip_address, 
            user_agent, 
            success, 
            failure_reason, 
            session_id
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if ($stmt) {
            $success_int = $success ? 1 : 0;
            mysqli_stmt_bind_param($stmt, "sssiss", 
                $username, 
                $this->ip_address, 
                $this->user_agent, 
                $success_int, 
                $failure_reason, 
                $this->session_id
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// Global function to get logger instance
function getLogger($conn) {
    static $logger = null;
    if ($logger === null) {
        $logger = new EnhancedLogger($conn);
    }
    return $logger;
}

// Quick logging functions for easy use
function logActivity($conn, $action, $data, $table_name = null, $record_id = null, $severity = 'MEDIUM') {
    $logger = getLogger($conn);
    $logger->logActivity($action, $data, $table_name, $record_id, $severity);
}

function logDatabaseChange($conn, $table_name, $operation, $record_id, $old_data = null, $new_data = null) {
    $logger = getLogger($conn);
    $logger->logDatabaseChange($table_name, $operation, $record_id, $old_data, $new_data);
}

function logAuthentication($conn, $username, $success, $failure_reason = null) {
    $logger = getLogger($conn);
    $logger->logAuthentication($username, $success, $failure_reason);
}

function logSystemOperation($conn, $operation, $details, $affected_count = 1) {
    $logger = getLogger($conn);
    $logger->logSystemOperation($operation, $details, $affected_count);
}

function logPageAccess($conn, $page_name, $query_executed = null, $execution_time = null) {
    $logger = getLogger($conn);
    $logger->logPageAccess($page_name, $query_executed, $execution_time);
}

function logSearch($conn, $search_term, $table_searched, $results_count) {
    $logger = getLogger($conn);
    $logger->logSearch($search_term, $table_searched, $results_count);
}

function logExport($conn, $export_type, $table_name, $record_count) {
    $logger = getLogger($conn);
    $logger->logExport($export_type, $table_name, $record_count);
}

// Specific logging functions for important activities
function logCandidateActivity($conn, $action, $candidate_name, $position = null, $candidate_id = null) {
    $logger = getLogger($conn);
    $logger->logCandidateActivity($action, $candidate_name, $position, $candidate_id);
}

function logVoterActivity($conn, $action, $voter_name, $voter_id = null, $additional_info = null) {
    $logger = getLogger($conn);
    $logger->logVoterActivity($action, $voter_name, $voter_id, $additional_info);
}

function logPasswordReset($conn, $target_user, $target_type = 'voter', $target_id = null) {
    $logger = getLogger($conn);
    $logger->logPasswordReset($target_user, $target_type, $target_id);
}

function logAdminAction($conn, $action, $details, $affected_table = null, $record_id = null) {
    $logger = getLogger($conn);
    $logger->logAdminAction($action, $details, $affected_table, $record_id);
}
?> 