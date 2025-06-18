<?php
// Environment Detection and Configuration
class Config {
    private static $instance = null;
    private $isLocalhost = false;
    private $dbConfig = [];
    
    private function __construct() {
        $this->detectEnvironment();
        $this->setDatabaseConfig();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function detectEnvironment() {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        
        // Check if running on localhost
        $this->isLocalhost = (
            $serverName === 'localhost' ||
            $httpHost === 'localhost' ||
            strpos($serverName, '127.0.0.1') !== false ||
            strpos($httpHost, '127.0.0.1') !== false ||
            strpos($serverName, 'localhost:') !== false ||
            strpos($httpHost, 'localhost:') !== false
        );
    }
    
    private function setDatabaseConfig() {
        if ($this->isLocalhost) {
            // Localhost configuration
            $this->dbConfig = [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '', // Default XAMPP MySQL password is empty
                'name' => 'ndmc_voting_system',
                'timezone' => '+08:00',
                'charset' => 'utf8'
            ];
        } else {
            // InfinityFree configuration
            // IMPORTANT: Update these credentials from your InfinityFree Control Panel > MySQL Databases
            // If your site stopped working after an InfinityFree update, check these values:
            $this->dbConfig = [
                'host' => 'sql113.infinityfree.com',     // Check: might be sql201, sql301, etc.
                'user' => 'if0_39055303',                // Your database username
                'pass' => 'wSxSKEffbM4G7ka',             // Your database password  
                'name' => 'if0_39055303_ovs',            // Your database name
                'timezone' => '+08:00',
                'charset' => 'utf8'
            ];
        }
    }
    
    public function isLocalhost() {
        return $this->isLocalhost;
    }
    
    public function getDatabaseConfig() {
        return $this->dbConfig;
    }
    
    public function getEnvironmentName() {
        return $this->isLocalhost ? 'localhost' : 'production';
    }
    
    public function isProduction() {
        return !$this->isLocalhost;
    }
    
    // Error reporting settings based on environment
    public function configureErrorReporting() {
        if ($this->isLocalhost) {
            // Show all errors in localhost for debugging
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
        } else {
            // Hide errors in production but log them
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        }
    }
    
    // Set execution time based on environment
    public function configureExecutionTime() {
        if ($this->isLocalhost) {
            set_time_limit(60); // More time for localhost development
        } else {
            set_time_limit(30); // Conservative time for production
        }
    }
}

// Initialize configuration
$config = Config::getInstance();
$config->configureErrorReporting();
$config->configureExecutionTime();

// Export configuration for easy access
$environment = $config->getEnvironmentName();
$isLocalhost = $config->isLocalhost();
$isProduction = $config->isProduction();
$dbConfig = $config->getDatabaseConfig();

// Legacy variables for backward compatibility
$db_host = $dbConfig['host'];
$db_user = $dbConfig['user'];
$db_pass = $dbConfig['pass'];
$db_name = $dbConfig['name'];
?> 