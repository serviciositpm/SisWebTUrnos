<?php
class Database {
    private $serverName = "10.100.120.8"; // o la IP de tu servidor SQL Server
    private $connectionOptions = array(
        "Database" => "pSipe2", // nombre de tu base de datos
        "Uid" => "sa",
        "PWD" => "84+-blaster32"
    );
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $this->conn = sqlsrv_connect($this->serverName, $this->connectionOptions);
            
            if ($this->conn === false) {
                die(print_r(sqlsrv_errors(), true));
            }
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        sqlsrv_close($this->conn);
    }
}
?>