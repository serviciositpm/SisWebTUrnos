<?php
require_once '../config/Database.php';

class CamaronerasModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getCamaronerasActivas($codUsuario) {
        $query = "SELECT CamaCod, CamaNomCom, CamaSta FROM COCAMA 
                 WHERE CamaSta = 'A' ";
                 //AND codUsuario = ?";
        $params = [$codUsuario];

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