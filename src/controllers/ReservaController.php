<?php
require_once '../models/ReservaModel.php';

class ReservaController
{
    private $model;

    public function __construct()
    {
        session_start(); // Iniciar sesión AL INICIO del controlador
        $this->model = new ReservaModel();
    }

    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_REQUEST['action'] ?? null; // Usa $_REQUEST que funciona para GET y POST

        if (!$action) {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(["error" => "Parámetro 'action' faltante"]);
            return;
        }

        // Acciones que deben ser GET
        $getActions = ['getCamaroneras', 'getProgramas', 'getReservasExistentes'];

        // Acciones que deben ser POST
        $postActions = ['guardarReserva','editarReserva'];

        if (in_array($action, $getActions)) {
            if ($method !== 'GET') {
                header("HTTP/1.1 405 Method Not Allowed");
                echo json_encode(["error" => "Esta acción requiere método GET"]);
                return;
            }
        } elseif (in_array($action, $postActions)) {
            if ($method !== 'POST') {
                header("HTTP/1.1 405 Method Not Allowed");
                echo json_encode(["error" => "Esta acción requiere método POST"]);
                return;
            }
        }

        switch ($action) {
            case 'getCamaroneras':
                $this->getCamaroneras();
                break;
            case 'getProgramas':
                $this->getProgramasPesca();
                break;
            case 'getReservasExistentes':
                $this->getReservasExistentes();
                break;
            case 'guardarReserva':
                $this->guardarReserva();
                break;
            case 'editarReserva':
                $this->editarReserva();
                break;
            default:
                header("HTTP/1.1 400 Bad Request, Method Not Found");
                echo json_encode(["error" => "Metodo  no válida"]);
        }
    }

    private function getCamaroneras()
    {
        try {
            // Limpiar buffer
            if (ob_get_length()) {
                ob_clean();
            }

            // Establecer encabezados con charset UTF-8
            header('Content-Type: application/json; charset=utf-8');

            $codUsuario = null;
            $camaroneras = $this->model->getCamaroneras($codUsuario);

            // Verificar y convertir caracteres si es necesario
            array_walk_recursive($camaroneras, function (&$item) {
                if (is_string($item)) {
                    // Convertir a UTF-8 si no lo está
                    if (!mb_detect_encoding($item, 'UTF-8', true)) {
                        $item = utf8_encode($item);
                    }
                }
            });

            if (empty($camaroneras)) {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(["error" => "No se encontraron camaroneras activas"], JSON_UNESCAPED_UNICODE);
                exit;
            }

            echo json_encode($camaroneras, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(["error" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    private function getProgramasPesca()
    {
        try {
            $camaCod = $_GET['camaCod'] ?? '';
            $fecha = $_GET['fecha'] ?? date('Y-m-d');

            $programas = $this->model->getProgramasPesca($camaCod, $fecha);

            if (!is_array($programas)) {
                throw new Exception("Datos de programas no válidos");
            }

            header('Content-Type: application/json');
            echo json_encode($programas);
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    public function getReservasExistentes() {
        try {
            $fecha = $_GET['fecha'] ?? null;
            
            if (!$fecha) {
                throw new Exception("El parámetro fecha es requerido");
            }

            $reservas = $this->model->obtenerReservasPorFiltros($fecha);
            
            echo json_encode($reservas);
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(['error' => $e->getMessage()]);
        }
    }                                       
    public function guardarReserva(){
        try {
            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("HTTP/1.1 405 Method Not Allowed");
                echo json_encode(["success" => false, "message" => "Método no permitido"]);
                return;
            }
            
            // Obtener y validar datos
            $data = [
                'camaCod' => $_POST['camaCod'] ?? null,
                'pescNo' => $_POST['pescNo'] ?? null,
                'fecha' => $_POST['fecha'] ?? null,
                'hora' => $_POST['hora'] ?? null,
                'kilos' => $_POST['kilos'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null,
                'usuario' => $_POST['usuario'] ?? null
            ];

            // Validaciones
            foreach ($data as $key => $value) {
                if (empty($value) && $key !== 'observaciones') {
                    throw new Exception("El campo $key es requerido");
                }
            }

            // Procesar reserva
            $result = $this->model->crearReservaCompleta($data);

            if ($result === false) {
                throw new Exception("Error al guardar la reserva en la base de datos");
            }

            echo json_encode([
                'success' => true,
                'message' => 'Reserva guardada exitosamente',
                'data' => $result // Opcional: devolver datos de la reserva creada
            ]);
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function editarReserva() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }

            // Obtener datos
            $data = [
                'reservaId' => $_POST['reservaId'] ?? null,
                'secuencia' => $_POST['secuencia'] ?? null, // Nuevo campo
                'camaCod' => $_POST['camaCod'] ?? null,
                'pescNo' => $_POST['pescNo'] ?? null,
                'fecha' => $_POST['fecha'] ?? null,
                'hora' => $_POST['hora'] ?? null,
                'kilos' => $_POST['kilos'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null,
                'usuario' => $_POST['usuario'] ?? null
            ];

            // Validar datos requeridos
            $requiredFields = ['reservaId', 'secuencia', 'camaCod', 'pescNo', 'fecha', 'hora', 'kilos', 'usuario'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            // Ejecutar la actualización
            $result = $this->model->editarReserva(
                $data['reservaId'],
                $data['secuencia'],
                $data['camaCod'],
                $data['pescNo'],
                $data['fecha'],
                $data['hora'],
                $data['kilos'],
                $data['observaciones'],
                $data['usuario']
            );

            if ($result === false) {
                throw new Exception("No se pudo actualizar la reserva");
            }

            echo json_encode([
                'success' => true,
                'message' => 'Reserva actualizada correctamente',
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function obtenerReservaPorId($id) {
        try {
            $reserva = $this->model->obtenerReservaPorId($id);
            
            if ($reserva) {
                return $reserva;
            } else {
                throw new Exception("Reserva no encontrada");
            }
        } catch (Exception $e) {
            error_log("Error en obtenerReservaPorId: " . $e->getMessage());
            return null;
        }
    }
}

$controller = new ReservaController();
$controller->handleRequest();
?>