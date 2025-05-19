<?php
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    public $conn;

    public function __construct() {
        // Use environment variables in production, fallback to local config in development
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: 'Euqificap12.';
        $this->database = getenv('DB_NAME') ?: 'bookapp';

        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
