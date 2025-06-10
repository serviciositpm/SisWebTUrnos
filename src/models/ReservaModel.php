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
    public function obtenerReservasFiltradas($filtros = [])
    {
        $sql = "SELECT 
                    c.GeReCodigo,
                    d.GeReSecuencia,
                    c.GeReFecha,
                    d.GeReHora, 
                    d.GeReKilos, 
                    d.GeReObservaciones,
                    d.CamaCod,
                    cm.CamaNomCom,
                    d.GeRePescNo,
                    p.PescFec as PescFecha,
                    d.GeReEstadoDet,
                    ps.PiscNo,
                    p.PescFecPla as fechaLlegadaPlanta
                FROM GetReservasDet d
                INNER JOIN GetReservasCab c ON d.GeReCodigo = c.GeReCodigo
                LEFT JOIN COCAMA cm ON d.CamaCod = cm.CamaCod
                LEFT JOIN COPESC p ON d.GeRePescNo = p.PescNo AND d.CamaCod = p.CamaCod
                LEFT JOIN COPISC ps ON p.PiscCod = ps.PiscCod AND p.CamaCod = ps.CamaCod
                WHERE 1=1";

        $params = [];

        // Aplicar filtros
        if (!empty($filtros['fecha'])) {
            $sql .= " AND CONVERT(DATE, c.GeReFecha) = ?";
            $params[] = $filtros['fecha'];
        }

        if (!empty($filtros['hora'])) {
            $sql .= " AND CONVERT(TIME, d.GeReHora) = ?";
            $params[] = $filtros['hora'];
        }

        if (!empty($filtros['camaCod'])) {
            $sql .= " AND d.CamaCod = ?";
            $params[] = $filtros['camaCod'];
        }

        if (!empty($filtros['pescNo'])) {
            $sql .= " AND d.GeRePescNo LIKE ?";
            $params[] = '%' . $filtros['pescNo'] . '%'; // Búsqueda parcial
        }
        if (!empty($filtros['piscina'])) {
            $sql .= " AND ps.PiscNo = ?";
            $params[] = $filtros['piscina'];
        }


        if (!empty($filtros['estado'])) {
            $sql .= " AND d.GeReEstadoDet = ?";
            $params[] = $filtros['estado'];
        }

        $sql .= " ORDER BY c.GeReFecha DESC, d.GeReHora ASC";

        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Error al obtener reservas: " . print_r(sqlsrv_errors(), true));
        }

        $reservas = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Formatear fechas y horas
            if ($row['GeReFecha'] instanceof DateTime) {
                $row['GeReFecha'] = $row['GeReFecha']->format('Y-m-d');
            }
            if ($row['fechaLlegadaPlanta'] instanceof DateTime) {
                $row['fechaLlegadaPlanta'] = $row['fechaLlegadaPlanta']->format('Y-m-d');
            }
            if ($row['GeReHora'] instanceof DateTime) {
                $row['GeReHora'] = $row['GeReHora']->format('H:i');
            }
            if (isset($row['PescFecha']) && $row['PescFecha'] instanceof DateTime) {
                $row['PescFecha'] = $row['PescFecha']->format('Y-m-d');
            }
            $reservas[] = $row;
        }

        sqlsrv_free_stmt($stmt);
        return $reservas;
    }
    public function obtenerTotalKilosPrograma($pescNo, $camaCod)
    {
        $sql = "SELECT PescCanRea 
            FROM COPESC 
            WHERE PescNo = ? AND CamaCod = ?";

        $params = array($pescNo, $camaCod);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Error al obtener total del programa: " . print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);

        return $row ? (float) $row['PescCanRea'] : 0;
    }

    public function obtenerTotalKilosReservados($pescNo, $camaCod, $excluirReservaId = null)
    {
        $sql = "SELECT SUM(d.GeReKilos) as total
            FROM GetReservasDet d
            INNER JOIN GetReservasCab c ON d.GeReCodigo = c.GeReCodigo
            WHERE d.GeRePescNo = ? 
            AND d.CamaCod = ?
            AND d.GeReEstadoDet = 'A'";

        $params = array($pescNo, $camaCod);

        if ($excluirReservaId) {
            $sql .= " AND d.GeReCodigo != ?";
            $params[] = $excluirReservaId;
        }

        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Error al obtener total reservado: " . print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);

        return $row ? (float) $row['total'] : 0;
    }
    public function cambiarEstadoReserva($codigo, $secuencia, $nuevoEstado, $usuario)
    {
        $sql = "UPDATE GetReservasDet 
                SET GeReEstadoDet = ?, 
                    GeReUsrModificacionReserva = ?, 
                    GeReFecModificacionReserva = GETDATE()
                WHERE GeReCodigo = ? AND GeReSecuencia = ?";

        $params = [$nuevoEstado, $usuario, $codigo, $secuencia];
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Error al cambiar estado: " . print_r(sqlsrv_errors(), true));
        }

        return sqlsrv_rows_affected($stmt) > 0;
    }

    // Obtener detalles de una reserva
    public function obtenerDetalleReserva($codigo, $secuencia)
    {
        $sql = "SELECT 
                    c.GeReCodigo,
                    d.GeReSecuencia,
                    c.GeReFecha,
                    d.GeReHora, 
                    d.GeReKilos, 
                    d.GeReObservaciones,
                    d.CamaCod,
                    cm.CamaNomCom,
                    d.GeRePescNo,
                    ps.PiscNo,
                    p.PescFec as PescFecha,
                    p.PescFecPla as fechaLlegadaPlanta,
                    d.GeReEstadoDet
                From		GetReservasCab	c
                Inner Join	GetReservasDet	d
                On			c.GeReCodigo	=	d.GeReCodigo
                Join		cocama cm
                On			cm.CamaCod		=	d.CamaCod
                Join		COPESC p
                On			p.PescNo		=	d.GeRePescNo
                And			p.CamaCod		=	d.CamaCod
                Join		COPISC	ps
                On			ps.PiscCod		=	p.PiscCod
                And			ps.CamaCod		=	p.CamaCod
                WHERE d.GeReCodigo = ? AND d.GeReSecuencia = ?";

        $params = [$codigo, $secuencia];
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Error al obtener detalle de reserva: " . print_r(sqlsrv_errors(), true));
        }

        $reserva = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($reserva) {
            // Formatear fechas y horas
            if ($reserva['GeReFecha'] instanceof DateTime) {
                $reserva['GeReFecha'] = $reserva['GeReFecha']->format('Y-m-d');
            }
            if ($reserva['GeReHora'] instanceof DateTime) {
                $reserva['GeReHora'] = $reserva['GeReHora']->format('H:i');
            }
            if (isset($reserva['PescFecha']) && $reserva['PescFecha'] instanceof DateTime) {
                $reserva['PescFecha'] = $reserva['PescFecha']->format('Y-m-d');
            }
        }

        sqlsrv_free_stmt($stmt);
        return $reserva;
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
        $sql = "SELECT TOP 5 
                    pesc.PescFec, 
                    pesc.PescNo, 
                    pesc.PescCanRea,
                    pisc.PiscNo,
                    cama.CamaNomCom,
                    pesc.PescFecPla as fechaLlegadaPlanta
                FROM COPESC pesc
                INNER JOIN COPISC pisc ON pesc.CamaCod = pisc.CamaCod AND pesc.PiscCod = pisc.PiscCod
                JOIN COCAMA cama ON pesc.CamaCod = cama.CamaCod
                WHERE PescSta='C' 
                AND PescFec<=? 
                AND pesc.CamaCod=? 
                ORDER BY PescFec DESC";

        $params = array($fecha, $camaCod);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $programas = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Convertir DateTime a string para JSON
            $row['PescFec'] = $row['PescFec']->format('Y-m-d');
            if ($row['fechaLlegadaPlanta'] instanceof DateTime) {
                $row['fechaLlegadaPlanta'] = $row['fechaLlegadaPlanta']->format('Y-m-d');
            }
            $programas[] = $row;
        }

        return $programas;
    }
    public function obtenerReservasPorFiltros($fecha)
    {
        $query = "  Select 
                            c.GeReCodigo,
                            d.GeReSecuencia,
                            c.GeReFecha,
                            d.GeReHora, 
                            d.GeReKilos, 
                            d.GeReObservaciones,
                            d.CamaCod,
                            cm.CamaNomCom,
                            d.GeRePescNo,
                            ps.PiscNo,
                            p.PescFecPla as fechaLlegadaPlanta,
                            d.GeReEstadoDet
                    From		GetReservasCab	c
                    Inner Join	GetReservasDet	d
                    On			c.GeReCodigo	=	d.GeReCodigo
                    Join		cocama cm
                    On			cm.CamaCod		=	d.CamaCod
                    Join		COPESC p
                    On			p.PescNo		=	d.GeRePescNo
                    And			p.CamaCod		=	d.CamaCod
                    Join		COPISC	ps
                    On			ps.PiscCod		=	p.PiscCod
                    And			ps.CamaCod		=	p.CamaCod
                    WHERE		CONVERT(DATE, c.GeReFecha) = ?
                    AND			d.GeReEstadoDet = 'A'
                    ORDER BY	d.GeReHora ASC";

        $params = array($fecha);
        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            error_log("Error en la consulta SQL: " . print_r($errors, true));
            throw new Exception("Error al obtener reservas. Detalles en el log.");
        }

        $result = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Convertir objetos DateTime a strings si es necesario
            if ($row['GeReFecha'] instanceof DateTime) {
                $row['GeReFecha'] = $row['GeReFecha']->format('Y-m-d');
            }
            if ($row['fechaLlegadaPlanta'] instanceof DateTime) {
                $row['fechaLlegadaPlanta'] = $row['fechaLlegadaPlanta']->format('Y-m-d');
            }
            if ($row['GeReHora'] instanceof DateTime) {
                $row['GeReHora'] = $row['GeReHora']->format('H:i:s');
            }
            $result[] = $row;
        }

        sqlsrv_free_stmt($stmt);
        return $result;
    }
    public function obtenerHorariosBloqueados()
    {
        $sql = "SELECT AmGeDetHoraInicio
                FROM AGAMGE cab
                INNER JOIN AgAmgeDetHorarios det
                ON cab.AmGecod = det.AmGecod
                WHERE amgesta = '17'
                AND AmGeDetEstadoHor = 'A'";
        
        $stmt = sqlsrv_query($this->db, $sql);
        
        if ($stmt === false) {
            throw new Exception("Error al obtener horarios bloqueados: " . print_r(sqlsrv_errors(), true));
        }
        
        $horariosBloqueados = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if ($row['AmGeDetHoraInicio'] instanceof DateTime) {
                $horariosBloqueados[] = $row['AmGeDetHoraInicio']->format('H:i');
            } else if (is_string($row['AmGeDetHoraInicio'])) {
                // Si ya viene como string, asegurarnos que tenga formato HH:MM
                $horariosBloqueados[] = substr($row['AmGeDetHoraInicio'], 0, 5);
            }
        }
        
        sqlsrv_free_stmt($stmt);
        return $horariosBloqueados;
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
    public function editarReserva($id, $secuencia, $camaCod, $pescNo, $fecha, $hora, $kilos, $observaciones, $usuario)
    {
        try {
            // Validar datos de entrada
            if (empty($id) || empty($secuencia) || empty($camaCod) || empty($pescNo) || empty($fecha) || empty($hora) || empty($kilos)) {
                throw new Exception("Todos los campos son requeridos");
            }

            // Convertir hora a formato SQL
            $horaFormateada = date('H:i:s', strtotime($hora));

            $sql = "UPDATE GetReservasDet SET 
                    CamaCod = ?, 
                    GeRePescNo = ?, 
                    GeReHora = ?, 
                    GeReKilos = ?, 
                    GeReObservaciones = ?, 
                    GeReUsrModificacionReserva = ?, 
                    GeReFecModificacionReserva = GETDATE()
                    WHERE GeReCodigo = ? AND GeReSecuencia = ?";

            $params = array(
                $camaCod,
                $pescNo,
                $horaFormateada,
                $kilos,
                $observaciones,
                $usuario,
                $id,
                $secuencia
            );

            $stmt = sqlsrv_query($this->db, $sql, $params);

            if ($stmt === false) {
                $errors = sqlsrv_errors();
                throw new Exception("Error al actualizar reserva: " . print_r($errors, true));
            }

            return sqlsrv_rows_affected($stmt) > 0;

        } catch (Exception $e) {
            error_log("Error en editarReserva: " . $e->getMessage());
            throw $e;
        }
    }
    public function obtenerReservaPorId($id)
    {
        try {
            $sql = "SELECT r.*, c.CamaNomCom 
                    FROM GesReserva r
                    LEFT JOIN Camaroneras c ON r.CamaCod = c.CamaCod
                    WHERE r.GeReCodigo = ?";

            $params = array($id);
            $stmt = sqlsrv_query($this->db, $sql, $params);

            if ($stmt === false) {
                error_log("Error en obtenerReservaPorId: " . print_r(sqlsrv_errors(), true));
                return null;
            }

            $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($stmt);
            return $result;
        } catch (Exception $e) {
            error_log("Error en obtenerReservaPorId: " . $e->getMessage());
            return null;
        }
    }
    public function validarDisponibilidad($camaCod, $fecha, $hora, $excluirReservaId = null)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM GetReservasDet d
                INNER JOIN GetReservasCab c ON d.GeReCodigo = c.GeReCodigo
                WHERE d.CamaCod = ? 
                AND CONVERT(DATE, c.GeReFecha) = ?
                AND CONVERT(TIME, d.GeReHora) = ?
                AND d.GeReEstadoDet = 'A'";

        $params = array($camaCod, $fecha, $hora);

        if ($excluirReservaId) {
            $sql .= " AND d.GeReCodigo != ?";
            $params[] = $excluirReservaId;
        }

        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Error al validar disponibilidad");
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['total'] == 0;
    }
}
?>