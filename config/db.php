<?php
class Database {
    private $host     = "127.0.0.1";  // TCP loopback
    private $port     = 3306;         // MySQL’s default port
    private $db_name  = "bookapp";
    private $username = "root";
    private $password = "";           // Empty, because we used --initialize-insecure
    public  $conn;

    public function __construct() {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
            $this->port
        );
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
