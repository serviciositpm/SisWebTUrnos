<?php
require_once __DIR__ . '/../config/Database.php';

class User
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();

        // Verificar conexión
        if ($this->db === false) {
            die("Error de conexión a la base de datos: " . print_r(sqlsrv_errors(), true));
        }
    }


    public function authenticate($username, $password)
    {
        $sql = "SELECT * FROM SEUSUA WHERE usuanomred = ? AND UsuaClave = ?";
        $params = array($username, $password);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            die("Error en la consulta SQL: " . print_r(sqlsrv_errors(), true));
        }

        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $user;
    }


    private function getUserProfiles($userId)
    {
        $sql = "SELECT p.* FROM SEUSUAPERF up
                JOIN SEPERF p ON up.perfcod = p.perfcod
                WHERE up.usuacod = ?";
        $params = array($userId);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        $profiles = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $profiles[] = $row;
        }

        return $profiles;
    }

    public function getUserMenu($userId)
    {
        // Obtener todas las aplicaciones del usuario ordenadas jerárquicamente
        $sql = "WITH MenuCTE AS (
            SELECT a.*, 0 AS level 
            FROM PerfilAplicacionSis pa
            JOIN SeAplicacionSis a ON pa.SeAplCodigo = a.SeAplCodigo
            JOIN SEUSUAPERF up ON pa.perfcod = up.perfcod
            WHERE up.usuacod = ? AND a.SeAplEstado = 'A' AND a.SeAplCodigoSt IS NULL
            
            UNION ALL
            
            SELECT a.*, m.level + 1
            FROM SeAplicacionSis a
            JOIN MenuCTE m ON a.SeAplCodigoSt = m.SeAplCodigo
            WHERE a.SeAplEstado = 'A'
        )
        SELECT * FROM MenuCTE
        ORDER BY level, SeAplOrden";

        $params = array($userId);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        $menu = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if ($row['level'] == 0) {
                // Menú principal (nivel 1)
                $menu[$row['SeAplCodigo']] = [
                    'main' => $row,
                    'submenu' => []
                ];
            } else if ($row['level'] == 1) {
                // Submenú (nivel 2)
                $menu[$row['SeAplCodigoSt']]['submenu'][$row['SeAplCodigo']] = [
                    'main' => $row,
                    'applications' => []
                ];
            } else if ($row['level'] == 2) {
                // Aplicación (nivel 3)
                foreach ($menu as &$mainItem) {
                    if (isset($mainItem['submenu'][$row['SeAplCodigoSt']])) {
                        $mainItem['submenu'][$row['SeAplCodigoSt']]['applications'][] = $row;
                        break;
                    }
                }
            }
        }

        return $menu;
    }
}
?>