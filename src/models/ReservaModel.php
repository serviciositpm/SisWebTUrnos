<?php
require_once '../config/Database.php';

class ReservaModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Obtener camaroneras activas para el usuario
    public function getCamaroneras($codUsuario = null) {
        $sql = "Select	CamaCod,
                        Replace(Replace(CamaNomCom, 'ñ', 'N'), 'Ñ', 'N') As CamaNomCom
                From		AGSUCS suc 
                Inner Join	COCAMA cama
                On			suc.SucsRefCamaCd = cama.CamaCod";
        $params = array();

        if (!empty($codUsuario)) {
            $sql .= " AND sucsjer = ?";
            $params[] = $codUsuario;
        }

        $stmt = sqlsrv_query($this->db, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception("Error en la consulta: " . print_r(sqlsrv_errors(), true));
        }

        $camaroneras = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Asegurar que los nombres de campos coincidan con lo esperado
            $camaroneras[] = array(
                'CamaCod' => $row['CamaCod'] ?? '',
                'CamaNomCom' => $row['CamaNomCom'] ?? ''
            );
        }

        sqlsrv_free_stmt($stmt);
        return $camaroneras;
    }

    // Obtener programas de pesca
    public function getProgramasPesca($camaCod, $fecha)
    {
        $sql = "SELECT PescFec, PescNo, PescCanRea FROM COPESC WHERE PescSta='C' AND PescFec>=? AND CamaCod=?";
        $params = array($fecha, $camaCod);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $programas = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Convertir DateTime a string para JSON
            $row['PescFec'] = $row['PescFec']->format('Y-m-d');
            $programas[] = $row;
        }

        return $programas;
    }
}
?>