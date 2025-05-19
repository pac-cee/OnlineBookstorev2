<?php
class Database {
    private $host     = "127.0.0.1";       // TCP to MySQL in same container
    private $port     = 3306;
    private $db_name  = "bookapp";
    private $username = "appuser";
    private $password = "Euqificap12.";    // Password we set in entrypoint
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
