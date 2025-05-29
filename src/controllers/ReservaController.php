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
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'getCamaroneras':
                        $this->getCamaroneras();
                        break;
                    case 'getProgramas':
                        $this->getProgramasPesca();
                        break;
                    default:
                        header("HTTP/1.1 400 Bad Request");
                        echo json_encode(["error" => "Acción no válida"]);
                        break;
                }
            } else {
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(["error" => "Parámetro 'action' faltante"]);
            }
        } else {
            header("HTTP/1.1 405 Method Not Allowed");
            echo json_encode(["error" => "Método no permitido"]);
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
}

$controller = new ReservaController();
$controller->handleRequest();
?>