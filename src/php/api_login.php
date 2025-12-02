<?php
session_start();
require_once 'conexao.php';

// Importar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


// Configurar cabeçalhos para JSON
header('Content-Type: application/json');

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// ========================================
// FUNÇÃO PARA ENVIAR EMAIL COM PHPMAILER
// ========================================
function sendPasswordResetEmail($email, $token, $name) {
    $mail = new PHPMailer(true);
    
    try {
        // ========== CONFIGURAÇÕES DO SERVIDOR ==========
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'luckgamer954@gmail.com'; // SEU EMAIL
        $mail->Password = 'bgff yvfs zoas whij'; // SENHA DE APP (16 dígitos)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // ========== REMETENTE E DESTINATÁRIO ==========
        $mail->setFrom('seu_email@gmail.com', 'PrismaStore');
        $mail->addAddress($email, $name);
        
        // ========== CONTEÚDO DO EMAIL ==========
        $reset_link = BASE_URL . "/src/php/reset_password.php?token=" . $token;
        
        $mail->isHTML(true);
        $mail->Subject = '🔐 PrismaStore - Recuperação de Senha';
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0;'>🔐 Recuperação de Senha</h1>
            </div>
            
            <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2>Olá, {$name}!</h2>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta no PrismaStore.</p>
                <p>Clique no botão abaixo para criar uma nova senha:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$reset_link}' style='display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Redefinir Senha
                    </a>
                </div>
                
                <p>Ou copie e cole este link no seu navegador:</p>
                <p style='background: white; padding: 15px; border-radius: 5px; word-break: break-all; border: 1px solid #ddd;'>
                    {$reset_link}
                </p>
                
                <p style='color: #d9534f; font-weight: bold;'>⚠️ Este link expira em 1 hora.</p>
                <p style='color: #666;'>Se você não solicitou esta redefinição, ignore este email. Sua senha permanecerá inalterada.</p>
            </div>
            
            <div style='text-align: center; padding: 20px; color: #666; font-size: 12px;'>
                <p>&copy; 2025 PrismaStore - Energia Solar</p>
            </div>
        </div>
        ";
        
        // Versão texto (fallback)
        $mail->AltBody = "
Olá, {$name}!

Clique no link abaixo para redefinir sua senha:
{$reset_link}

Este link expira em 1 hora.

Se você não solicitou esta redefinição, ignore este email.

© 2025 PrismaStore
        ";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log do erro (para debug)
        error_log("Erro PHPMailer: {$mail->ErrorInfo}");
        return false;
    }
}

// ========================================
// PROCESSAR AÇÕES
// ========================================
try {
    // ============================================
    // LOGIN
    // ============================================
    if ($action === 'login') {
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Preencha todos os campos']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
        }
    }
    
    // ============================================
    // CADASTRO
    // ============================================
    elseif ($action === 'signup') {
        $name = trim($input['name'] ?? '');
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres']);
            exit;
        }

        // Verifica se o email já existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
            exit;
        }

        // Cadastra o novo usuário
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'cliente')");
        
        if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
            echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário']);
        }
    }
    
    // ============================================
    // RECUPERAÇÃO DE SENHA
    // ============================================
    elseif ($action === 'forgot_password') {
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Digite seu email']);
            exit;
        }

        // Verifica se o email existe
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Por segurança, não revela se o email existe ou não
            echo json_encode([
                'success' => true, 
                'message' => 'Se o email estiver cadastrado, você receberá um link de recuperação em instantes'
            ]);
            exit;
        }

        // Gera token único e seguro
        $token = bin2hex(random_bytes(32)); // 64 caracteres
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira em 1 hora

        // Salva o token no banco (substitui token anterior se existir)
        $stmt = $pdo->prepare("
            INSERT INTO password_resets (user_id, token, expires_at) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
        ");
        $stmt->execute([$user['id'], $token, $expires, $token, $expires]);

        // Envia o email
        $email_sent = sendPasswordResetEmail($email, $token, $user['name']);

        if ($email_sent) {
            echo json_encode([
                'success' => true, 
                'message' => '✅ Link de recuperação enviado! Verifique seu email (e a pasta de spam)'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => '❌ Erro ao enviar email. Verifique as configurações ou tente novamente mais tarde.'
            ]);
        }
    }
    
    else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }

} catch (PDOException $e) {
    error_log("Erro no banco de dados: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor. Tente novamente mais tarde.']);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro inesperado. Tente novamente.']);
}
?>