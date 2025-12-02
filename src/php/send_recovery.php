<?php
header('Content-Type: application/json; charset=utf-8');
// Inclui o arquivo de configuração (mesma pasta)
require_once 'config.php';

// Define header JSON
header('Content-Type: application/json');

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

// Pega o email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

// Validação de email
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

try {
    // Verifica se o email existe no banco
    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Por segurança, retorna sucesso mesmo se o email não existir
        echo json_encode([
            'success' => true, 
            'message' => 'Se o email estiver cadastrado, você receberá o link de recuperação.'
        ]);
        exit;
    }
    
    // Gera token único e seguro
    $token = bin2hex(random_bytes(32));
    
    // Remove tokens antigos deste email
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);
    
    // Insere novo token no banco
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
    $stmt->execute([$email, $token]);
    
    // Monta o link de recuperação
    $resetLink = BASE_URL . "/src/php/reset_password.php?token=" . $token;
    
    // ===== MODO TESTE: Salva em arquivo ao invés de enviar email =====
    $logFile = 'recovery_links.txt';
    
    // Formata a mensagem do log
    $logMessage = str_repeat('=', 70) . "\n";
    $logMessage .= "DATA/HORA: " . date('d/m/Y H:i:s') . "\n";
    $logMessage .= "USUÁRIO: " . $user['email'] . "\n";
    $logMessage .= "EMAIL: " . $email . "\n";
    $logMessage .= "TOKEN: " . $token . "\n";
    $logMessage .= "LINK DE RECUPERAÇÃO:\n" . $resetLink . "\n";
    $logMessage .= "EXPIRA EM: 1 hora\n";
    $logMessage .= str_repeat('=', 70) . "\n\n";
    
    // Salva no arquivo
    if (file_put_contents($logFile, $logMessage, FILE_APPEND)) {
        echo json_encode([
            'success' => true, 
            'message' => '✅ Link de recuperação gerado com sucesso!<br><br>📄 <strong>MODO TESTE:</strong> O link foi salvo no arquivo <code>recovery_links.txt</code><br><br>🔗 <a href="' . $resetLink . '" target="_blank" style="color: #3504fd; font-weight: bold;">Clique aqui para abrir o link</a>',
            'development_link' => $resetLink // Remove em produção!
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao salvar o link. Verifique as permissões da pasta.'
        ]);
    }
    
} catch(PDOException $e) {
    error_log('Erro em send_recovery.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro PDO: ' . $e->getMessage()
    ]);

}
?>