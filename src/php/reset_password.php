<?php
session_start();
require_once 'conexao.php';

$token = $_GET['token'] ?? '';
$error_message = '';
$success_message = '';
$token_valid = false;

// Verifica se o token é válido
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("
            SELECT pr.user_id, pr.expires_at, u.email, u.name 
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? AND pr.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reset) {
            $token_valid = true;
        } else {
            $error_message = "❌ Link inválido ou expirado. Solicite um novo link de recuperação.";
        }
    } catch (PDOException $e) {
        $error_message = "❌ Erro ao verificar token.";
    }
} else {
    $error_message = "❌ Token não fornecido.";
}

// Processa o formulário de redefinição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "❌ Preencha todos os campos.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "❌ A senha deve ter no mínimo 6 caracteres.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "❌ As senhas não coincidem.";
    } else {
        try {
            // Atualiza a senha
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $reset['user_id']]);

            // Remove o token usado
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);

            $success_message = "✅ Senha redefinida com sucesso! Você já pode fazer login.";
            $token_valid = false; // Desabilita o formulário
        } catch (PDOException $e) {
            $error_message = "❌ Erro ao redefinir senha.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Redefinir Senha - PrismaStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            max-width: 150px;
        }

        h1 {
            color: #333;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #666;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo">
            <img src="../images/logo.png" alt="PrismaStore">
        </div>

        <h1>🔐 Redefinir Senha</h1>
        <p class="subtitle">Digite sua nova senha</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= $error_message ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= $success_message ?></span>
            </div>
            <a href="login.php" class="btn-submit" style="text-decoration: none; display: block; text-align: center;">
                <i class="fas fa-sign-in-alt"></i> Ir para Login
            </a>
        <?php elseif ($token_valid): ?>
            <form method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Nova Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="new_password" name="new_password" 
                               placeholder="••••••••" required minlength="6"
                               oninput="checkPasswordStrength()">
                    </div>
                    <div id="password-strength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label>Confirmar Nova Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="••••••••" required minlength="6"
                               oninput="checkPasswordMatch()">
                    </div>
                    <div id="password-match" class="password-strength"></div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-key"></i> Redefinir Senha
                </button>
            </form>
        <?php else: ?>
            <a href="login.php" class="btn-submit" style="text-decoration: none; display: block; text-align: center;">
                <i class="fas fa-arrow-left"></i> Voltar para Login
            </a>
        <?php endif; ?>

        <a href="login.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Voltar para o login
        </a>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            if (password.length < 6) {
                strengthDiv.textContent = '❌ Muito curta (mínimo 6 caracteres)';
                strengthDiv.style.color = '#dc3545';
            } else if (password.length < 8) {
                strengthDiv.textContent = '⚠️ Fraca';
                strengthDiv.style.color = '#ffc107';
            } else if (password.length < 12) {
                strengthDiv.textContent = '✅ Boa';
                strengthDiv.style.color = '#28a745';
            } else {
                strengthDiv.textContent = '✅ Forte';
                strengthDiv.style.color = '#155724';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirm.length === 0) {
                matchDiv.textContent = '';
                return;
            }
            
            if (password === confirm) {
                matchDiv.textContent = '✅ As senhas coincidem';
                matchDiv.style.color = '#28a745';
            } else {
                matchDiv.textContent = '❌ As senhas não coincidem';
                matchDiv.style.color = '#dc3545';
            }
        }

        function validateForm() {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                alert('A senha deve ter no mínimo 6 caracteres');
                return false;
            }
            
            if (password !== confirm) {
                alert('As senhas não coincidem');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>