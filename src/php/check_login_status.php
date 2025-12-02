<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    echo json_encode([
        'isLoggedIn' => true,
        'userName' => $_SESSION['user_name'] ?? 'Usuário', // Retorna o nome se existir
        'userId' => $_SESSION['user_id']
    ]);
} else {
    echo json_encode([
        'isLoggedIn' => false
    ]);
}
exit;
?>