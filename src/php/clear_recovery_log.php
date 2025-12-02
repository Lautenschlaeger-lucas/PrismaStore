<?php
// Arquivo para limpar o histórico de links
$logFile = 'recovery_links.txt';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists($logFile)) {
        if (unlink($logFile)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
?>