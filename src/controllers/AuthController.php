<?php
// Asegurarnos de que el controlador se ejecute correctamente
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        // Habilitar visualización de errores para desarrollo
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['pass'] ?? '';
            
            // Validar que los campos no estén vacíos
            if (empty($username) || empty($password)) {
                $this->handleLoginError("Usuario y contraseña son requeridos");
            }

            $user = $this->userModel->authenticate($username, $password);

            if ($user) {
                session_start();
                $_SESSION['user'] = $user;
                $_SESSION['menu'] = $this->userModel->getUserMenu($user['usuacod']);
                
                // Redirección absoluta para evitar problemas
                $base_url = $this->getBaseUrl();
                header('Location: ' . $base_url . '/SisWebTurnos/src/views/home.php');
                exit();
            } else {
                $this->handleLoginError("Credenciales inválidas");
            }
        } else {
            // Si no es POST, mostrar el formulario de login
            $this->showLoginForm();
        }
    }

    private function handleLoginError($errorMessage) {
        session_start();
        $_SESSION['login_error'] = $errorMessage;
        
        $base_url = $this->getBaseUrl();
        header('Location: ' . $base_url . '/SisWebTurnos/src/views/auth/login.php');
        exit();
    }

    private function showLoginForm() {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host";
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        $base_url = $this->getBaseUrl();
    // Forzar la redirección en la ventana principal, no en el iframe
        echo "<script>window.top.location.href = '" . $base_url . "/SisWebTurnos/src/views/auth/login.php';</script>";
        exit();
        /* $base_url = $this->getBaseUrl();
        header('Location: ' . $base_url . '/SisWebTurnos/src/views/auth/login.php');
        exit(); */
    }
}

// Ejecutar el controlador
$action = $_GET['action'] ?? 'login';
$controller = new AuthController();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Acción no encontrada";
}
?>