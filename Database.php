<?php
<<<<<<< HEAD
// Database.php (Singleton pattern)
class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $this->connection = new PDO("mysql:host=localhost;dbname=go_puppy_go", "username", "password");
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
=======
require_once __DIR__ . '/bootstrap.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $_ENV['DB_HOST'],
                $_ENV['DB_NAME'],
                $_ENV['DB_CHARSET']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $_ENV['DB_CHARSET']
            ];

            $this->connection = new PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $options
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #dc3545;'>🚫 Database Connection Error</h2>
                    <p><strong>The application cannot connect to the database.</strong></p>
                    <p>Please check your <code>.env</code> settings.</p>
                </div>
            ");
        }
>>>>>>> fe6b50c7bdffa84d47878f7363fb5aa959e52208
    }


    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function __clone() {
        throw new Exception("Cannot clone singleton Database instance");
    }

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton Database instance");
    }
}
