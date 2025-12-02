<?php
// update_profile.php
session_start();

header('Content-Type: application/json');

// 1. Verificação de Autenticação
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

// 2. Receber e Decodificar os Dados JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

// 3. Limpeza e Validação dos Dados
$user_id = $_SESSION['user_id'];
$name = trim($data['name']);
$email = trim($data['email']);
$phone = trim($data['phone']); // O telefone pode ser opcional ou exigir validação específica

if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Nome ou Email inválido.']);
    exit;
}

// 4. Configuração e Conexão com o Banco de Dados
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'prismastore';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    exit;
}

$mysqli->set_charset('utf8mb4');

// 5. Query de Atualização
$stmt = $mysqli->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
$stmt->bind_param('sssi', $name, $email, $phone, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();
?>