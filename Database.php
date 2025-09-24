<?php
// Database.php (Singleton pattern)
class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuration 
    private const DB_HOST = 'localhost:3307';
    private const DB_NAME = 'gopuppygo';
    private const DB_USER = 'root';      
    private const DB_PASS = 'whatdoyouthink';          
    private const DB_CHARSET = 'utf8mb4';

    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                self::DB_HOST,
                self::DB_NAME,
                self::DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . self::DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Database connection failed: " . $e->getMessage());
            
            // Show user-friendly error
            die("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #dc3545;'>🚫 Database Connection Error</h2>
                    <p><strong>The application cannot connect to the database.</strong></p>
                    <p>Please check the following:</p>
                    <ol>
                        <li><strong>MySQL Server:</strong> Make sure MySQL/XAMPP is running</li>
                        <li><strong>Database:</strong> Create database 'gopuppygo'</li>
                        <li><strong>Credentials:</strong> Update Database.php with correct username/password</li>
                    </ol>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <strong>Quick Setup:</strong><br>
                        1. Start XAMPP/MySQL<br>
                        2. Open phpMyAdmin<br>
                        3. Create database: <code>gopuppygo</code><br>
                        4. Update Database.php credentials if needed
                    </div>
                    <p style='margin-top: 20px;'>
                        <a href='javascript:history.back()' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>← Go Back</a>
                    </p>
                </div>
            ");
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning of the instance
    public function __clone() {
        throw new Exception("Cannot clone singleton Database instance");
    }
    
    // Prevent unserialization of the instance
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton Database instance");
    }
}
