<?php
require_once '../models/ReservasModel.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Acción no válida'];

try {
    $db = new Database();
    $model = new ReservasModel();
    
    switch ($action) {
        case 'getCamaroneras':
            $codUsuario = $_POST['codUsuario'] ?? '';
            $data = $model->getCamaroneras($codUsuario);
            $response = ['success' => true, 'data' => $data];
            break;
            
        case 'getProgramasPesca':
            $CamaCod = $_POST['CamaCod'] ?? '';
            $PescFec = $_POST['PescFec'] ?? '';
            $data = $model->getProgramasPesca($CamaCod, $PescFec);
            $response = ['success' => true, 'data' => $data];
            break;
            
        case 'getHorariosDisponibles':
            $CamaCod = $_POST['CamaCod'] ?? '';
            $fecha = $_POST['fecha'] ?? '';
            $PescNo = $_POST['PescNo'] ?? '';
            $data = $model->getHorariosDisponibles($CamaCod, $fecha, $PescNo);
            $response = ['success' => true] + $data;
            break;
            
        case 'getResumenReservas':
            $CamaCod = $_POST['CamaCod'] ?? '';
            $data = $model->getResumenReservas($CamaCod);
            $response = ['success' => true, 'data' => $data];
            break;
            
        case 'guardarReserva':
            $data = [
                'CamaCod' => $_POST['CamaCod'] ?? '',
                'fecha' => $_POST['fecha'] ?? '',
                'horas' => $_POST['horas'] ?? [],
                'PescNo' => $_POST['PescNo'] ?? '',
                'kilos' => $_POST['kilos'] ?? 0,
                'comentarios' => $_POST['comentarios'] ?? '',
                'codUsuario' => $_POST['codUsuario'] ?? ''
            ];
            
            $result = $model->guardarReserva($data);
            $response = ['success' => $result, 'message' => $result ? 'Reserva guardada' : 'Error al guardar'];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Acción no reconocida'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
} finally {
    if (isset($db)) {
        $db->closeConnection();
    }
}

echo json_encode($response);
?>