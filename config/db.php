<?php
class Database {
    private $host     = "localhost";  // or "127.0.0.1" / service name if in compose
    private $db_name  = "bookapp";
    private $username = "root";
    private $password = "";           // <— empty password
    public  $conn;

    public function __construct() {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name
        );
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}
?>
