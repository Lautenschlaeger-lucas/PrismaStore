<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action']) && $data['action'] === 'logout') {
    $_SESSION = array();
    session_destroy();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso.']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
    http_response_code(400);
    exit;
}
?>