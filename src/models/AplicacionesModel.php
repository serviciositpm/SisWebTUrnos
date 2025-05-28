<?php
require_once '../config/Database.php';

class AplicacionesModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAplicaciones($filtros = [])
    {
        $sql = "SELECT * FROM SeAplicacionSis WHERE 1=1";
        $params = [];

        if (!empty($filtros['descripcion'])) {
            $sql .= " AND SeAplDescripcion LIKE ?";
            $params[] = '%' . $filtros['descripcion'] . '%';
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND SeAplEstado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND SeAplTipo = ?";
            $params[] = $filtros['tipo'];
        }

        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Error al obtener aplicaciones: " . print_r(sqlsrv_errors(), true));
        }

        $aplicaciones = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $aplicaciones[] = $row;
        }

        return $aplicaciones;
    }

    public function getAplicacion($id)
    {
        $query = "SELECT * FROM SeAplicacionSis WHERE SeAplCodigo = ?";
        $params = [$id];

        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function crearAplicacion($data)
    {
        // Verificar longitudes máximas (ajusta según tu esquema de base de datos)
        $maxLengths = [
            'SeAplDescripcion' => 100,
            'SeAplFontIcon' => 50,
            'SeAplTipo' => 3,
            'SeAplEstado' => 1,
            'sistcod' => 10,
            'SeAplUserCreacion' => 10,
            'SeAplUserModificacion' => 10,
            'SeAplNombreObjeto' => 255,
            'SeAplCodigoSt' => 10
        ];

        foreach ($maxLengths as $field => $max) {
            if (isset($data[$field]) && is_string($data[$field]) && strlen($data[$field]) > $max) {
                throw new Exception("El campo $field excede la longitud máxima de $max caracteres");
            }
        }

        $query = "INSERT INTO SeAplicacionSis (
        SeAplDescripcion, 
        SeAplFontIcon, 
        SeAplTipo, 
        SeAplEstado, 
        SeAplCodigoSt,
        sistcod, 
        SeAplUserCreacion, 
        SeAplFecCreacion, 
        SeAplUserModificacion,
        SeAplFecModficiacion,
        SeAplNombreObjeto, 
        SeAplOrden
    ) VALUES (?, ?, ?, ?, ?, ?, ?,GETDATE(), ?, GETDATE() , ?, ?)";

        $params = [
            $data['SeAplDescripcion'],
            $data['SeAplFontIcon'],
            $data['SeAplTipo'],
            $data['SeAplEstado'],
            !empty($data['SeAplCodigoSt']) ? $data['SeAplCodigoSt'] : NULL,
            $data['sistcod'],
            $data['SeAplUserCreacion'],
            $data['SeAplUserModificacion'],
            $data['SeAplNombreObjeto'],
            $data['SeAplOrden']
        ];

        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            $errorMsg = "Error al crear aplicación: ";
            foreach ($errors as $error) {
                $errorMsg .= "SQLSTATE: " . $error['SQLSTATE'] . " Code: " . $error['code'] . " Message: " . $error['message'];
            }
            throw new Exception($errorMsg);
        }

        return true;
    }


    public function actualizarAplicacion($id, $data)
    {
        $query = "UPDATE dbo.SeAplicacionSis SET 
        SeAplDescripcion = ?, 
        SeAplFontIcon = ?, 
        SeAplTipo = ?, 
        SeAplEstado = ?, 
        sistcod = ?, 
        SeAplUserModificacion = ?, 
        SeAplFecModficiacion = GETDATE(), 
        SeAplNombreObjeto = ?, 
        SeAplOrden = ?,
        SeAplCodigoSt = ?
    WHERE SeAplCodigo = ?";

        $params = [
            $data['SeAplDescripcion'],
            $data['SeAplFontIcon'],
            $data['SeAplTipo'],
            $data['SeAplEstado'],
            $data['sistcod'],
            $data['SeAplUserModificacion'], // Asegúrate que este valor no sea NULL
            $data['SeAplNombreObjeto'],
            $data['SeAplOrden'],
            !empty($data['SeAplCodigoSt']) ? $data['SeAplCodigoSt'] : NULL,
            $id
        ];

        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            throw new Exception("Error al actualizar aplicación: " . print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    public function cambiarEstado($id, $estado, $usuario)
    {
        $query = "UPDATE SeAplicacionSis SET 
            SeAplEstado = ?, 
            SeAplUserModificacion = ?, 
            SeAplFecModficiacion = GETDATE()
        WHERE SeAplCodigo = ?";

        $params = [$estado, $usuario, $id];

        $stmt = sqlsrv_query($this->db, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // En AplicacionesModel.php agregamos estos métodos:

    public function getMenusPadre()
    {
        $sql = "SELECT SeAplCodigo, SeAplDescripcion 
            FROM SeAplicacionSis 
            WHERE SeAplTipo = 'MEN' 
            ORDER BY SeAplDescripcion";

        $stmt = sqlsrv_query($this->db, $sql);

        if ($stmt === false) {
            throw new Exception("Error al obtener menús padre: " . print_r(sqlsrv_errors(), true));
        }

        $menus = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $menus[] = $row;
        }

        return $menus;
    }

    public function getSistemas()
    {
        $sql = "SELECT sistcod, sistnom FROM dbo.SESIST ORDER BY sistnom";

        $stmt = sqlsrv_query($this->db, $sql);

        if ($stmt === false) {
            throw new Exception("Error al obtener sistemas: " . print_r(sqlsrv_errors(), true));
        }

        $sistemas = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $sistemas[] = $row;
        }

        return $sistemas;
    }
}
?>