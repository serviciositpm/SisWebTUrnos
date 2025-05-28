<?php
require_once '../models/AplicacionesModel.php';

class AplicacionesController
{
    private $model;

    public function __construct()
    {
        $this->model = new AplicacionesModel();
    }

    public function index()
    {
        // Esta acción ahora solo muestra la vista principal
        require_once '../views/aplicaciones/index.php';
    }

    // En AplicacionesController.php modificamos el método form():

    public function form()
    {
        $id = $_GET['id'] ?? null;
        $aplicacion = $id ? $this->model->getAplicacion($id) : null;

        // Obtenemos los datos para los combos
        $menusPadre = $this->model->getMenusPadre();
        $sistemas = $this->model->getSistemas();

        // Si es AJAX, pasamos los datos a la vista
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            ob_start();
            require_once '../views/aplicaciones/form.php';
            $formulario = ob_get_clean();
            echo $formulario;
            exit;
        }

        require_once '../views/aplicaciones/form.php';
    }

    public function save()
    {
        try {
            $id = $_POST['SeAplCodigo'] ?? null;
            // Validar y limpiar el código de usuario
            $userCode = $_POST['SeAplUserCreacion'] ?? '01005';
            $userCode = preg_replace('/[^0-9]/', '', $userCode); // Solo números
            $userCode = substr($userCode, 0, 10); // Asegurar longitud máxima
            $data = [
                'SeAplDescripcion' => substr($_POST['SeAplDescripcion'], 0, 100), // Ejemplo: limitar a 100 caracteres
                'SeAplFontIcon' => substr($_POST['SeAplFontIcon'], 0, 50),
                'SeAplTipo' => substr($_POST['SeAplTipo'], 0, 3), // MEN, SUB, APL
                'SeAplEstado' => substr($_POST['SeAplEstado'], 0, 1), // A o I
                'SeAplCodigoSt' => !empty($_POST['SeAplCodigoSt']) ? substr($_POST['SeAplCodigoSt'], 0, 10) : NULL,
                'sistcod' => substr($_POST['sistcod'], 0, 10),
                'SeAplUserCreacion' => $userCode,
                'SeAplUserModificacion' => $userCode, // Mismo usuario para modificación
                'SeAplNombreObjeto' => substr($_POST['SeAplNombreObjeto'], 0, 255),
                'SeAplOrden' => (int)$_POST['SeAplOrden']
            ];
           
            // Validación adicional
            if (empty($data['SeAplUserCreacion'])) {
                throw new Exception("El código de usuario es requerido");
            }

            if ($id) {
                $this->model->actualizarAplicacion($id, $data);
                $mensaje = "Aplicación actualizada correctamente";
            } else {
                $this->model->crearAplicacion($data);
                $mensaje = "Aplicación creada correctamente";
            }


            // Respuesta JSON para AJAX
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['success' => true, 'message' => $mensaje]);
                exit;
            }

            $_SESSION['message'] = $mensaje;
            header('Location: index.php?action=index');

        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }

            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?action=index');
        }
    }

    public function cambiarEstado()
    {
        try {
            $id = $_GET['id'];
            $estado = $_GET['estado'];
            $usuario = $_GET['usuario'];

            $this->model->cambiarEstado($id, $estado, $usuario);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado cambiado correctamente'
                ]);
                exit;
            }

            header('Location: index.php?action=index');

        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }

            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?action=index');
        }
    }

    public function obtenerAplicaciones()
    {
        $filtros = [
            'descripcion' => $_GET['descripcion'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'tipo' => $_GET['tipo'] ?? ''
        ];

        $aplicaciones = $this->model->getAplicaciones($filtros);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $aplicaciones
        ]);
        exit;
    }
}

// Procesar la acción
$action = $_GET['action'] ?? 'index';
$controller = new AplicacionesController();

// Manejar acciones AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($action)) {
    $controller->$action();
    exit;
}

// Manejar acciones normales
switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'form':
        $controller->form();
        break;
    case 'save':
        $controller->save();
        break;
    case 'cambiarEstado':
        $controller->cambiarEstado();
        break;
    case 'obtenerAplicaciones':
        $controller->obtenerAplicaciones();
        break;
    default:
        $controller->index();
        break;
}