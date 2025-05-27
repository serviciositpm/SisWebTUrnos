<?php
require_once '../config/Database.php';

class Application {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getApplicationsByUser($userId) {
        $sql = "SELECT a.SeAplCodigo, a.SeAplDescripcion, a.SeAplFontIcon, 
                       a.SeAplNombreObjeto, a.SeAplCodigoSt, a.SeAplOrden
                FROM SeAplicacionSis a
                INNER JOIN PerfilAplicacionSis pa ON a.SeAplCodigo = pa.SeAplCodigo
                INNER JOIN SEUSUAPERF up ON pa.perfcod = up.perfcod
                INNER JOIN SEUSUA u ON up.usuacod = u.usuacod
                WHERE u.usuanomred = ?
                AND a.SeAplEstado = 'A'
                ORDER BY a.SeAplOrden";

        $params = array($userId);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $applications = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $applications[] = $row;
        }

        return $this->buildMenuTree($applications);
    }

    private function buildMenuTree($applications, $parentId = null) {
        $branch = array();
        
        foreach ($applications as $application) {
            if ($application['SeAplCodigoSt'] == $parentId) {
                $children = $this->buildMenuTree($applications, $application['SeAplCodigo']);
                if ($children) {
                    $application['children'] = $children;
                }
                $branch[] = $application;
            }
        }
        
        return $branch;
    }
}
?>