<?php
// Asegurarnos de que el controlador se ejecute correctamente
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login()
{
    header('Content-Type: application/json');
    
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Método no permitido', 405);
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['pass'] ?? '');

        // Validaciones
        if (empty($username)) {
            throw new Exception('El usuario es requerido', 400);
        }

        if (empty($password)) {
            throw new Exception('La contraseña es requerida', 400);
        }

        $user = $this->userModel->authenticate($username, $password);

        if (!$user) {
            throw new Exception('Credenciales inválidas', 401);
        }

        // Iniciar sesión
        session_start();
        $_SESSION['user'] = $user;
        $_SESSION['menu'] = $this->userModel->getUserMenu($user['usuacod']);

        echo json_encode([
            'success' => true,
            'redirectUrl' => $this->getBaseUrl() . '/SisWebTurnos/src/views/home.php'
        ]);
        exit();

    } catch (Exception $e) {
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

    private function handleLoginError($errorMessage)
    {
        session_start();
        $_SESSION['login_error'] = $errorMessage;

        $base_url = $this->getBaseUrl();
        header('Location: ' . $base_url . '/SisWebTurnos/src/views/auth/login.php');
        exit();
    }

    private function showLoginForm()
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host";
    }

    public function logout()
    {
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

    public function showChangePasswordForm()
    {
        require_once __DIR__ . '/../views/auth/change_password.php';
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit();
        }

        $username = $_POST['username'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validaciones
        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'El usuario es requerido']);
            exit();
        }

        if (empty($currentPassword)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es requerida']);
            exit();
        }

        if (empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña es requerida']);
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
            exit();
        }

        // Verificar credenciales actuales
        $user = $this->userModel->authenticate($username, $currentPassword);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario o contraseña actual incorrectos']);
            exit();
        }

        // Actualizar contraseña
        $success = $this->userModel->updatePassword($user['usuacod'], $newPassword);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada exitosamente']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
            exit();
        }
    }

    
}

// Ejecutar el controlador
$action = $_GET['action'] ?? 'login';
$controller = new AuthController();
$allowedActions = ['login', 'logout', 'showChangePasswordForm', 'changePassword'];
if (in_array($action, $allowedActions) && method_exists($controller, $action)) {
    $controller->$action();
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Acción no encontrada";
}
