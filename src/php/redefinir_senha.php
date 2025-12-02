<?php
session_start();

$token = isset($_GET['token']) ? $_GET['token'] : '';
$mensagem = '';
$token_valido = false;

if (!empty($token)) {
    // Conectar no banco
    $conexao = mysqli_connect('localhost', 'root', '', 'prismastore');
    
    if ($conexao) {
        $token_escaped = mysqli_real_escape_string($conexao, $token);
        
        // Verificar se o token existe e não expirou
        $sql = "SELECT id, name, email FROM users 
                WHERE reset_token='$token_escaped' 
                AND reset_expira > NOW()";
        
        $resultado = mysqli_query($conexao, $sql);
        
        if (mysqli_num_rows($resultado) > 0) {
            $token_valido = true;
            $usuario = mysqli_fetch_assoc($resultado);
        } else {
            $mensagem = 'Link inválido ou expirado. Solicite uma nova recuperação de senha.';
        }
        
        mysqli_close($conexao);
    }
} else {
    $mensagem = 'Token não fornecido.';
}

// Processar redefinição de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nova_senha'])) {
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];
    $token_post = $_POST['token'];
    
    if ($nova_senha !== $confirma_senha) {
        $mensagem = 'As senhas não coincidem!';
    } elseif (strlen($nova_senha) < 6) {
        $mensagem = 'A senha deve ter no mínimo 6 caracteres!';
    } else {
        $conexao = mysqli_connect('localhost', 'root', '', 'prismastore');
        
        if ($conexao) {
            $token_escaped = mysqli_real_escape_string($conexao, $token_post);
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            
            // Atualizar senha e limpar token
            $sql = "UPDATE users 
                    SET password='$senha_hash', reset_token=NULL, reset_expira=NULL 
                    WHERE reset_token='$token_escaped'";
            
            if (mysqli_query($conexao, $sql)) {
                $mensagem = 'success';
            } else {
                $mensagem = 'Erro ao redefinir senha. Tente novamente.';
            }
            
            mysqli_close($conexao);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>PrismaStore - Redefinir Senha</title>
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
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            padding: 60px 40px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 150px;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
            text-align: center;
        }

        p {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
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

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .success-icon {
            text-align: center;
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo-container">
            <img src="../images/logo.png" alt="PrismaStore Logo">
        </div>

        <?php if ($mensagem === 'success'): ?>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Senha Redefinida!</h1>
            <p>Sua senha foi alterada com sucesso.</p>
            <div class="back-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Voltar para o Login
                </a>
            </div>
        <?php elseif ($token_valido): ?>
            <h1>Redefinir Senha</h1>
            <p>Olá, <?= htmlspecialchars($usuario['name']) ?>! Digite sua nova senha.</p>

            <?php if (!empty($mensagem) && $mensagem !== 'success'): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="form-group">
                    <label>Nova Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="nova_senha" placeholder="••••••••" required minlength="6">
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirmar Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirma_senha" placeholder="••••••••" required minlength="6">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-key"></i> Redefinir Senha
                </button>
            </form>

            <div class="back-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Voltar para o Login
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?= $mensagem ?>
            </div>
            <div class="back-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Voltar para o Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>