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
        $getActions =[ 'getCamaroneras', 
                        'getProgramas', 
                        'getReservasExistentes', 
                        'obtenerDetalleReserva', 
                        'obtenerReservas',
                        'validarKilosDisponibles',
                        'getHorariosBloqueados',
                        'obtenerDatosGrafico'
                    ];

        // Acciones que deben ser POST
        $postActions = ['guardarReserva', 'editarReserva','cambiarEstado','cambiarEstadoDetalle'];

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
            case 'obtenerDetalleReserva':
                $this->obtenerDetalleReserva();
                break;
            case 'obtenerReservas':
                $this->obtenerReservas();
                break;
            case 'cambiarEstado':
                $this->cambiarEstado();
                break;    
            case 'validarKilosDisponibles':
                $this->validarKilosDisponibles();
                break;
            case 'getHorariosBloqueados':
                $this->getHorariosBloqueados();
                break;
            case 'cambiarEstadoDetalle':
                $this->cambiarEstadoDetalle();  
                break;
            case 'obtenerDatosGrafico':
                $this->obtenerDatosGrafico();
                break;
            default:
                header("HTTP/1.1 400 Bad Request, Method Not Found");
                echo json_encode(["error" => "Metodo  no válida"]);
        }
    }
    private function validarHorarioPermitido($hora)
    {
        $horariosBloqueados = $this->model->obtenerHorariosBloqueados();
        $horaFormateada = date('H:i', strtotime($hora));
        
        if (in_array($horaFormateada, $horariosBloqueados)) {
            throw new Exception("No se pueden realizar reservas en el horario $horaFormateada (horario bloqueado)");
        }
        
        return true;
    }

    public function getHorariosBloqueados()
    {
        try {
            $horariosBloqueados = $this->model->obtenerHorariosBloqueados();
            
            echo json_encode([
                'success' => true,
                'horariosBloqueados' => $horariosBloqueados
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function obtenerReservas()
    {
        try {
            // Limpiar buffer y establecer cabeceras con UTF-8
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');

            $filtros = [
                'fecha' => $_GET['fecha'] ?? null,
                'hora' => $_GET['hora'] ?? null,
                'camaCod' => $_GET['camaCod'] ?? null,
                'pescNo' => $_GET['pescNo'] ?? null,
                'piscina' => $_GET['piscina'] ?? null,
                'estado' => $_GET['estado'] ?? null
            ];

            $reservas = $this->model->obtenerReservasFiltradas($filtros);
             // Verificar y convertir caracteres si es necesario
            array_walk_recursive($reservas, function (&$item) {
                if (is_string($item)) {
                    // Convertir a UTF-8 si no lo está
                    if (!mb_detect_encoding($item, 'UTF-8', true)) {
                        $item = utf8_encode($item);
                    }
                }
            });
            echo json_encode([
                'success' => true,
                'data' => $reservas,
                'total' => count($reservas)
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit(); // Asegurar que no se envía nada más
    }
    public function validarKilosDisponibles()
    {
        try {
            $pescNo = $_GET['pescNo'] ?? null;
            $camaCod = $_GET['camaCod'] ?? null;
            $reservaId = $_GET['reservaId'] ?? null;

            if (!$pescNo || !$camaCod) {
                throw new Exception("Datos incompletos para la validación");
            }

            // Obtener total del programa
            $totalPrograma = $this->model->obtenerTotalKilosPrograma($pescNo, $camaCod);
            
            // Obtener total reservado (excluyendo la reserva actual si es edición)
            $totalReservado = $this->model->obtenerTotalKilosReservados($pescNo, $camaCod, $reservaId);
            
            $disponible = $totalPrograma - $totalReservado;

            echo json_encode([
                'success' => true,
                'totalPrograma' => $totalPrograma,
                'totalReservado' => $totalReservado,
                'disponible' => $disponible
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function cambiarEstado()
    {
        try {
            $codigo = $_POST['codigo'];
            $secuencia = $_POST['secuencia'];
            $nuevoEstado = $_POST['nuevoEstado'];
            $usuario = $_POST['usuario'] ?? '01005'; // Usuario por defecto si no se envía

            $resultado = $this->model->cambiarEstadoReserva($codigo, $secuencia, $nuevoEstado, $usuario);

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Estado de la reserva cambiado correctamente' : 'No se pudo actualizar el estado'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function cambiarEstadoDetalle ()
    {
        try {
            $codigo = $_POST['codigo'];
            $secuencia = $_POST['secuencia'];
            $nuevoEstado = $_POST['nuevoEstado'];
            $observacion = $_POST['observacion'] ?? null;
            $campoObservacion = $_POST['campoObservacion'] ?? null;
            $usuario = $_POST['usuario'] ?? '01005'; // Usuario por defecto si no se envía

            $resultado = $this->model->cambiarEstadoReservaDetalle(
                $codigo, 
                $secuencia, 
                $nuevoEstado, 
                $usuario,
                $observacion,
                $campoObservacion
            );

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Estado de la reserva cambiado correctamente' : 'No se pudo actualizar el estado'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function obtenerDetalleReserva()
    {
        try {
            $codigo = $_GET['codigo'];
            $secuencia = $_GET['secuencia'];

            $reserva = $this->model->obtenerDetalleReserva($codigo, $secuencia);

            echo json_encode([
                'success' => true,
                'data' => $reserva
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function getCamaroneras()
    {
        try {

            // Iniciar sesión si no está iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Obtener código de usuario de la sesión
            $codUsuario = $_SESSION['user']['usuacod'] ?? null;

            // Verificar si el usuario está logueado
            if (!$codUsuario) {
                header("HTTP/1.1 401 Unauthorized");
                echo json_encode(["error" => "Usuario no autenticado"], JSON_UNESCAPED_UNICODE);
                exit;
            }
            // Limpiar buffer
            /* if (ob_get_length()) {
                ob_clean();
            } */

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
    public function obtenerDatosGrafico()
    {
        try {
            $fecha = $_GET['fecha'] ?? date('Y-m-d');
            
            $model = new ReservaModel();
            $reservas = $model->obtenerReservasPorFiltros($fecha);
            
            // Procesar datos para el gráfico
            $datosGrafico = $this->procesarDatosParaGrafico($reservas);
            
            echo json_encode([
                'success' => true,
                'data' => $datosGrafico,
                'fecha' => $fecha
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function procesarDatosParaGrafico($reservas)
    {
        $datosPorHora = [];
        
        foreach ($reservas as $reserva) {
            $hora = $reserva['GeReHora'];
            $kilos = (float)$reserva['GeReKilos'];
            $toneladas = $kilos / 1000; // Convertir a toneladas
            
            if (!isset($datosPorHora[$hora])) {
                $datosPorHora[$hora] = 0;
            }
            
            $datosPorHora[$hora] += $toneladas;
        }
        
        // Ordenar por hora
        ksort($datosPorHora);
        
        return [
            'horas' => array_keys($datosPorHora),
            'toneladas' => array_values($datosPorHora)
        ];
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

    public function getReservasExistentes()
    {
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
    public function guardarReserva()
    {
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

            $this->validarHorarioPermitido($data['hora']);

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

    public function editarReserva()
    {
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

    public function obtenerReservaPorId($id)
    {
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