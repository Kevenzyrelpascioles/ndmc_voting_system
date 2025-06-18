<?php
// Database Synchronization System
// This syncs data changes from localhost to InfinityFree

class DatabaseSync {
    private $localConn;
    private $remoteConn;
    private $isLocalhost;
    
    public function __construct() {
        require_once 'config.php';
        $this->isLocalhost = $isLocalhost;
        
        if ($this->isLocalhost) {
            $this->setupConnections();
        }
    }
    
    private function setupConnections() {
        // Local connection (localhost)
        $this->localConn = mysqli_connect('localhost', 'root', '', 'ndmc_voting_system');
        
        // Remote connection (InfinityFree)
        $this->remoteConn = mysqli_connect(
            'sql113.infinityfree.com',
            'if0_39055303',
            'wSxSKEffbM4G7ka',
            'if0_39055303_ovs'
        );
        
        if (!$this->localConn || !$this->remoteConn) {
            throw new Exception("Failed to establish sync connections");
        }
        
        // Set charset for both connections
        mysqli_set_charset($this->localConn, 'utf8');
        mysqli_set_charset($this->remoteConn, 'utf8');
    }
    
    // Sync a new voter to InfinityFree
    public function syncVoter($voterData) {
        if (!$this->isLocalhost || !$this->remoteConn) return false;
        
        $stmt = mysqli_prepare($this->remoteConn, 
            "INSERT INTO voters (FirstName, LastName, MiddleName, Username, Password, Year, Status) 
             VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        mysqli_stmt_bind_param($stmt, "sssssss", 
            $voterData['FirstName'],
            $voterData['LastName'],
            $voterData['MiddleName'],
            $voterData['Username'],
            $voterData['Password'],
            $voterData['Year'],
            $voterData['Status']
        );
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // Sync a new candidate to InfinityFree
    public function syncCandidate($candidateData) {
        if (!$this->isLocalhost || !$this->remoteConn) return false;
        
        $stmt = mysqli_prepare($this->remoteConn,
            "INSERT INTO candidate (abc, Position, Party, FirstName, LastName, MiddleName, Gender, Year, Photo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        mysqli_stmt_bind_param($stmt, "sssssssss",
            $candidateData['abc'],
            $candidateData['Position'],
            $candidateData['Party'],
            $candidateData['FirstName'],
            $candidateData['LastName'],
            $candidateData['MiddleName'],
            $candidateData['Gender'],
            $candidateData['Year'],
            $candidateData['Photo']
        );
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // Sync a vote to InfinityFree
    public function syncVote($voteData) {
        if (!$this->isLocalhost || !$this->remoteConn) return false;
        
        $stmt = mysqli_prepare($this->remoteConn,
            "INSERT INTO votes (CandidateID, VoterID, Position) VALUES (?, ?, ?)");
        
        mysqli_stmt_bind_param($stmt, "iis",
            $voteData['CandidateID'],
            $voteData['VoterID'],
            $voteData['Position']
        );
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // Update voter status (when they vote)
    public function syncVoterStatus($voterID, $status) {
        if (!$this->isLocalhost || !$this->remoteConn) return false;
        
        $stmt = mysqli_prepare($this->remoteConn,
            "UPDATE voters SET Status = ? WHERE VoterID = ?");
        
        mysqli_stmt_bind_param($stmt, "si", $status, $voterID);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // Delete record from InfinityFree
    public function syncDelete($table, $idField, $id) {
        if (!$this->isLocalhost || !$this->remoteConn) return false;
        
        $allowedTables = ['voters', 'candidate', 'users'];
        if (!in_array($table, $allowedTables)) return false;
        
        $stmt = mysqli_prepare($this->remoteConn,
            "DELETE FROM {$table} WHERE {$idField} = ?");
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // Update record in InfinityFree
    public function syncUpdate($table, $data, $idField, $id) {
        if (!$this->isLocalhost || !$this->remoteConn) return false;
        
        $allowedTables = ['voters', 'candidate', 'users'];
        if (!in_array($table, $allowedTables)) return false;
        
        $setParts = [];
        $values = [];
        $types = '';
        
        foreach ($data as $field => $value) {
            $setParts[] = "{$field} = ?";
            $values[] = $value;
            $types .= 's';
        }
        
        $values[] = $id;
        $types .= 'i';
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$idField} = ?";
        $stmt = mysqli_prepare($this->remoteConn, $sql);
        
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // Sync history/audit log
    public function syncHistory($data, $action, $user) {
        if (!$this->isLocalhost || !$this->remoteConn) return false;
        
        $stmt = mysqli_prepare($this->remoteConn,
            "INSERT INTO history (data, action, date, user) VALUES (?, ?, ?, ?)");
        
        $date = date('Y-m-d H:i:s');
        mysqli_stmt_bind_param($stmt, "ssss", $data, $action, $date, $user);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // Test sync connection
    public function testSync() {
        if (!$this->isLocalhost) {
            return ['status' => 'info', 'message' => 'Sync only works from localhost'];
        }
        
        if (!$this->remoteConn) {
            return ['status' => 'error', 'message' => 'Cannot connect to InfinityFree database'];
        }
        
        // Try a simple query
        $result = mysqli_query($this->remoteConn, "SELECT 1");
        if ($result) {
            return ['status' => 'success', 'message' => 'Sync connection successful'];
        } else {
            return ['status' => 'error', 'message' => 'Sync test failed: ' . mysqli_error($this->remoteConn)];
        }
    }
    
    // Get sync status
    public function getSyncStatus() {
        if (!$this->isLocalhost) {
            return ['enabled' => false, 'reason' => 'Not on localhost'];
        }
        
        return [
            'enabled' => ($this->localConn && $this->remoteConn),
            'local_connected' => (bool)$this->localConn,
            'remote_connected' => (bool)$this->remoteConn
        ];
    }
    
    public function __destruct() {
        if ($this->localConn) mysqli_close($this->localConn);
        if ($this->remoteConn) mysqli_close($this->remoteConn);
    }
}

// Global sync instance
$sync = new DatabaseSync();
?> 