<?php
require_once '../config/Database.php';
class ReservasModel {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getCamaroneras($codUsuario) {
        $sql = "SELECT CamaCod, CamaNomCom, CamaSta FROM COCAMA WHERE CamaSta='A' "; //AND codUsuario = ?
        $params = array($codUsuario);
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception("Error al obtener camaroneras: " . print_r(sqlsrv_errors(), true));
        }
        
        $camaroneras = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $camaroneras[] = $row;
        }
        
        return $camaroneras;
    }
    
    public function getProgramasPesca($CamaCod, $PescFec) {
        $sql = "SELECT PescFec, PescNo, PescCanRea FROM COPESC WHERE PescSta='P' AND PescFec >= ? AND CamaCod = ?";
        $params = array($PescFec, $CamaCod);
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception("Error al obtener programas de pesca: " . print_r(sqlsrv_errors(), true));
        }
        
        $programas = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $programas[] = $row;
        }
        
        return $programas;
    }
    
    public function getHorariosDisponibles($CamaCod, $fecha, $PescNo) {
        // Todas las horas posibles
        $todasHoras = array();
        for ($i = 0; $i < 24; $i++) {
            $todasHoras[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }
        
        // Horas ya reservadas por este usuario para esta fecha y camaronera
        $horasReservadas = $this->getHorasReservadas($CamaCod, $fecha, true);
        
        // Otras reservas (de otros usuarios)
        $otrasReservas = $this->getHorasReservadas($CamaCod, $fecha, false);
        
        // Horas disponibles son las que no están en ninguna de las dos listas anteriores
        $horasDisponibles = array_diff($todasHoras, $horasReservadas, $otrasReservas);
        
        return [
            'horasDisponibles' => array_values($horasDisponibles),
            'horasReservadas' => $horasReservadas,
            'otrasReservas' => $otrasReservas
        ];
    }
    
    private function getHorasReservadas($CamaCod, $fecha, $propias = true) {
        $sql = "SELECT d.GeReHora 
                FROM GetReservasCab c
                JOIN GetReservasDet d ON c.GeReCodigo = d.GeReCodigo
                WHERE c.GeReFecha = ? 
                AND d.CamaCod = ? 
                AND d.GeReEstadoDet = 'A'";
        
        if ($propias) {
            $sql .= " AND c.GeReUsrCreacion = ?";
            $params = array($fecha, $CamaCod, $_POST['codUsuario'] ?? '');
        } else {
            $sql .= " AND c.GeReUsrCreacion != ?";
            $params = array($fecha, $CamaCod, $_POST['codUsuario'] ?? '');
        }
        
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception("Error al obtener horas reservadas: " . print_r(sqlsrv_errors(), true));
        }
        
        $horas = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $hora = date('H:i', strtotime($row['GeReHora']));
            $horas[] = $hora;
        }
        
        return $horas;
    }
    
    public function getResumenReservas($CamaCod) {
        $sql = "SELECT c.GeReFecha, d.GeReKilos, 
                STRING_AGG(FORMAT(d.GeReHora, 'HH:mm'), ', ') AS horas
                FROM GetReservasCab c
                JOIN GetReservasDet d ON c.GeReCodigo = d.GeReCodigo
                WHERE d.CamaCod = ? 
                AND c.GeReEstado = 'A'
                AND d.GeReEstadoDet = 'A'
                AND c.GeReUsrCreacion = ?
                GROUP BY c.GeReFecha, d.GeReKilos
                ORDER BY c.GeReFecha DESC";
        
        $params = array($CamaCod, $_POST['codUsuario'] ?? '');
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception("Error al obtener resumen de reservas: " . print_r(sqlsrv_errors(), true));
        }
        
        $reservas = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $row['horas'] = explode(', ', $row['horas']);
            $reservas[] = $row;
        }
        
        return $reservas;
    }
    
    public function guardarReserva($data) {
        // Iniciar transacción
        sqlsrv_begin_transaction($this->conn);
        
        try {
            // Insertar cabecera de reserva
            $sqlCab = "INSERT INTO GetReservasCab 
                      (GeReFecha, GeReEstado, GeReUsrCreacion, GeReFecCreacion)
                      VALUES (?, 'A', ?, GETDATE())";
            
            $paramsCab = array($data['fecha'], $data['codUsuario']);
            $stmtCab = sqlsrv_query($this->conn, $sqlCab, $paramsCab);
            
            if ($stmtCab === false) {
                throw new Exception("Error al insertar cabecera de reserva: " . print_r(sqlsrv_errors(), true));
            }
            
            // Obtener ID de la reserva insertada
            $sqlId = "SELECT SCOPE_IDENTITY() AS id";
            $stmtId = sqlsrv_query($this->conn, $sqlId);
            
            if ($stmtId === false) {
                throw new Exception("Error al obtener ID de reserva: " . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmtId, SQLSRV_FETCH_ASSOC);
            $reservaId = $row['id'];
            
            // Insertar detalles de reserva (horas seleccionadas)
            foreach ($data['horas'] as $index => $hora) {
                $sqlDet = "INSERT INTO GetReservasDet
                          (GeReCodigo, GeReSecuencia, GeReHora, GeReKilos, GeReEstadoDet,
                           CamaCod, GeRePescNo, GeReObservaciones, GeReMailEnviado,
                           GeReUsrCreacionReserva, GeReFecCreacionReserva)
                          VALUES (?, ?, ?, ?, 'A', ?, ?, ?, 0, ?, GETDATE())";
                
                $horaCompleta = $data['fecha'] . ' ' . $hora;
                $paramsDet = array(
                    $reservaId,
                    $index + 1,
                    $horaCompleta,
                    $data['kilos'],
                    $data['CamaCod'],
                    $data['PescNo'],
                    $data['comentarios'],
                    $data['codUsuario']
                );
                
                $stmtDet = sqlsrv_query($this->conn, $sqlDet, $paramsDet);
                
                if ($stmtDet === false) {
                    throw new Exception("Error al insertar detalle de reserva: " . print_r(sqlsrv_errors(), true));
                }
            }
            
            // Confirmar transacción
            sqlsrv_commit($this->conn);
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            sqlsrv_rollback($this->conn);
            throw $e;
        }
    }
}
?>