<?php
function requireAuth()
{
    session_start();
    if (!isset($_SESSION['user'])) {
        $_SESSION['login_error'] = "Por favor inicie sesión primero";
        header('Location: ../views/auth/login.php');
        exit();
    }
}

function getCurrentUser()
{
    return $_SESSION['user'] ?? null;
}

function getUserMenu()
{
    return $_SESSION['menu'] ?? array();
}
?>