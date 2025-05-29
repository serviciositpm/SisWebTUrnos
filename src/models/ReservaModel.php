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
    public function getCamaroneras($codUsuario = null)
    {
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
    public function obtenerReservasPorFiltros($fecha) {
        $query = "SELECT 
                    d.GeReHora, 
                    d.GeReKilos, 
                    d.GeReObservaciones,
                    d.CamaCod,
                    d.GeRePescNo
                FROM GetReservasDet d
                INNER JOIN GetReservasCab c ON d.GeReCodigo = c.GeReCodigo
                WHERE CONVERT(DATE, d.GeReHora) = ?
                AND d.GeReEstadoDet = 'A'
                ORDER BY d.GeReHora";

        $params = array($fecha);
        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            throw new Exception("Error en la consulta: " . print_r(sqlsrv_errors(), true));
        }

        $result = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        return $result;
    }
    public function obtenerCodigoReservaPorFecha($fecha)
    {
        $query = "SELECT GeReCodigo 
                  FROM GetReservasCab 
                  WHERE CONVERT(DATE, GeReFecha) = ?
                  AND GeReEstado = 'A'";

        $params = array($fecha);
        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            throw new Exception("Error en la consulta: " . print_r(sqlsrv_errors(), true));
        }

        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        return $result ? $result['GeReCodigo'] : null;
    }
    public function crearReservaCompleta($data)
    {
        // Iniciar transacción
        if (sqlsrv_begin_transaction($this->db) === false) {
            throw new Exception("No se pudo iniciar transacción: " . print_r(sqlsrv_errors(), true));
        }

        try {
            // 1. Crear cabecera de reserva (si no existe)
            $geReCodigo = $this->obtenerCodigoReservaPorFecha($data['fecha']);

            if (!$geReCodigo) {
                $geReCodigo = $this->crearCabeceraReserva([
                    'fecha' => $data['fecha'],
                    'usuario' => $data['usuario']
                ]);
            }

            // 2. Crear detalle de reserva
            $this->crearDetalleReserva([
                'geReCodigo' => $geReCodigo,
                'camaCod' => $data['camaCod'],
                'pescNo' => $data['pescNo'],
                'hora' => $data['hora'],
                'kilos' => $data['kilos'],
                'observaciones' => $data['observaciones'],
                'usuario' => $data['usuario']
            ]);

            // Confirmar transacción
            sqlsrv_commit($this->db);
            return true;
        } catch (Exception $e) {
            // Revertir en caso de error
            sqlsrv_rollback($this->db);
            throw $e;
        }
    }
    public function crearCabeceraReserva($data)
    {
        $query = "INSERT INTO GetReservasCab (
                GeReFecha, 
                GeReEstado, 
                GeReUsrCreacion, 
                GeReFecCreacion
              ) OUTPUT INSERTED.GeReCodigo
              VALUES (
                ?, 
                'A', 
                ?, 
                GETDATE()
              )";

        $params = array($data['fecha'], $data['usuario']);
        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            throw new Exception("Error al insertar cabecera de reserva: " . print_r(sqlsrv_errors(), true));
        }

        // Obtener el ID generado directamente
        if (sqlsrv_fetch($stmt)) {
            $geReCodigo = sqlsrv_get_field($stmt, 0);
            sqlsrv_free_stmt($stmt);
            return $geReCodigo;
        } else {
            sqlsrv_free_stmt($stmt);
            throw new Exception("No se pudo obtener el ID de cabecera generado");
        }
    }

    public function crearDetalleReserva($data)
    {
        $secuencia = $this->obtenerSiguienteSecuencia($data['geReCodigo']);

        $query = "INSERT INTO GetReservasDet (
                GeReCodigo,
                GeReSecuencia,
                GeReHora,
                GeReKilos,
                GeReEstadoDet,
                CamaCod,
                GeRePescNo,
                GeReObservaciones,
                GeReMailEnviado,
                GeReUsrCreacionReserva,
                GeReFecCreacionReserva
              ) VALUES (
                ?, ?, ?, ?, 'A', ?, ?, ?, 0, ?, GETDATE()
              )";

        $params = array(
            $data['geReCodigo'],
            $secuencia,
            $data['hora'],
            $data['kilos'],
            $data['camaCod'],
            $data['pescNo'],
            $data['observaciones'],
            $data['usuario']
        );

        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            throw new Exception("Error al insertar detalle de reserva: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_free_stmt($stmt);
        return true;
    }

    private function obtenerSiguienteSecuencia($geReCodigo)
    {
        $query = "SELECT ISNULL(MAX(GeReSecuencia), 0) + 1 AS siguiente 
                  FROM GetReservasDet 
                  WHERE GeReCodigo = ?";

        $params = array($geReCodigo);
        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            throw new Exception("Error al obtener la siguiente secuencia: " . print_r(sqlsrv_errors(), true));
        }

        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        return $result['siguiente'];
    }
}
?>