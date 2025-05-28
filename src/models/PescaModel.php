<?php
require_once '../config/Database.php';

class PescaModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getProgramasPesca($fecha, $camaCod) {
        $query = "SELECT PescFec, PescNo, PescCanRea FROM COPESC 
                 WHERE PescSta = 'C' AND PescFec >= ? AND CamaCod = ?";
        $params = [$fecha, $camaCod];

        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }
}
?>