<?php
session_start();

// Se já estiver logado, redireciona
if (isset($_SESSION['user_id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'Paineis.php';
    header("Location: " . $redirect);
    exit;
}

$redirect_url = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : 'Paineis.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>PrismaStore - Login</title>
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

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            min-height: 500px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-left img {
            max-width: 200px;
            margin-bottom: 30px;
            filter: brightness(0) invert(1);
        }

        .login-left h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
        }

        .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            font-weight: 600;
        }

        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
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

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 40px 20px;
            }

            .login-right {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <img src="../images/logo.png" alt="PrismaStore Logo">
            <p>Sua plataforma completa para orçamentos de energia solar. Cadastre-se ou faça login para começar!</p>
        </div>

        <div class="login-right">
            <div class="form-header">
                <h2>Bem-vindo!</h2>
                <p>Entre na sua conta ou crie uma nova</p>
            </div>

            <div class="tabs">
                <div class="tab active" onclick="showTab('login')">Login</div>
                <div class="tab" onclick="showTab('signup')">Cadastrar</div>
                <div class="tab" onclick="showTab('forgot')">Esqueci a Senha</div>
            </div>

            <div id="message" class="message"></div>

            <!-- FORMULÁRIO DE LOGIN -->
            <div id="login-form" class="form-container active">
                <form onsubmit="handleLogin(event)">
                    <div class="form-group">
                        <label>E-mail</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="login-email" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Senha</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="login-password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </form>
            </div>

            <!-- FORMULÁRIO DE CADASTRO -->
            <div id="signup-form" class="form-container">
                <form onsubmit="handleSignup(event)">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="signup-name" placeholder="Seu nome" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>E-mail</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="signup-email" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="signup-phone" placeholder="(00) 00000-0000" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Senha</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="signup-password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="btn-signup">
                        <i class="fas fa-user-plus"></i> Criar Conta
                    </button>
                </form>
            </div>

            <!-- FORMULÁRIO DE RECUPERAÇÃO DE SENHA -->
            <div id="forgot-form" class="form-container">
                <form onsubmit="handleForgotPassword(event)">
                    <p style="color: #666; margin-bottom: 20px;">Digite seu e-mail cadastrado e enviaremos um link para redefinir sua senha.</p>
                    
                    <div class="form-group">
                        <label>E-mail</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="forgot-email" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="btn-forgot">
                        <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const redirectUrl = '<?= $redirect_url ?>';

        function showTab(tab) {
            // Prevenir comportamento padrão
            if (event) {
                event.preventDefault();
            }

            // Atualizar abas
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            if (event && event.target) {
                event.target.classList.add('active');
            }

            // Mostrar formulário correspondente
            document.querySelectorAll('.form-container').forEach(f => f.classList.remove('active'));
            const formToShow = document.getElementById(tab + '-form');
            if (formToShow) {
                formToShow.classList.add('active');
            }

            // Limpar mensagem
            hideMessage();
        }

        function showMessage(text, type) {
            const msgEl = document.getElementById('message');
            msgEl.textContent = text;
            msgEl.className = 'message ' + type;
            msgEl.style.display = 'block';
        }

        function hideMessage() {
            document.getElementById('message').style.display = 'none';
        }

        async function handleLogin(e) {
            e.preventDefault();

            const emailInput = document.getElementById('login-email');
            const passwordInput = document.getElementById('login-password');
            const btnLogin = document.getElementById('btn-login');

            if (!emailInput || !passwordInput || !btnLogin) {
                console.error('Elementos do formulário não encontrados');
                return;
            }

            const email = emailInput.value;
            const password = passwordInput.value;

            btnLogin.disabled = true;
            btnLogin.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';

            try {
                const response = await fetch('api_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('✅ Login realizado! Redirecionando...', 'success');
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1000);
                } else {
                    showMessage('❌ ' + data.message, 'error');
                    btnLogin.disabled = false;
                    btnLogin.innerHTML = '<i class="fas fa-sign-in-alt"></i> Entrar';
                }
            } catch (error) {
                showMessage('❌ Erro ao fazer login. Verifique sua conexão.', 'error');
                console.error(error);
                btnLogin.disabled = false;
                btnLogin.innerHTML = '<i class="fas fa-sign-in-alt"></i> Entrar';
            }
        }

        async function handleSignup(e) {
            e.preventDefault();

            const name = document.getElementById('signup-name').value;
            const email = document.getElementById('signup-email').value;
            const phone = document.getElementById('signup-phone').value;
            const password = document.getElementById('signup-password').value;
            const btnSignup = document.getElementById('btn-signup');

            btnSignup.disabled = true;
            btnSignup.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cadastrando...';

            try {
                const response = await fetch('api_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'signup',
                        name: name,
                        email: email,
                        phone: phone,
                        password: password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('✅ ' + data.message + ' Faça login agora!', 'success');
                    // Limpar formulário
                    e.target.reset();
                    btnSignup.disabled = false;
                    btnSignup.innerHTML = '<i class="fas fa-user-plus"></i> Criar Conta';
                    
                    // Mudar para aba de login após 2 segundos
                    setTimeout(() => {
                        document.querySelectorAll('.tab')[0].click();
                    }, 2000);
                } else {
                    showMessage('❌ ' + data.message, 'error');
                    btnSignup.disabled = false;
                    btnSignup.innerHTML = '<i class="fas fa-user-plus"></i> Criar Conta';
                }
            } catch (error) {
                showMessage('❌ Erro ao cadastrar. Verifique sua conexão.', 'error');
                console.error(error);
                btnSignup.disabled = false;
                btnSignup.innerHTML = '<i class="fas fa-user-plus"></i> Criar Conta';
            }
        }

        async function handleForgotPassword(e) {
            e.preventDefault();

            const email = document.getElementById('forgot-email').value;
            const btnForgot = document.getElementById('btn-forgot');

            btnForgot.disabled = true;
            btnForgot.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

            try {
                const response = await fetch('api_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'forgot_password',
                        email: email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('✅ ' + data.message, 'success');
                    e.target.reset();
                } else {
                    showMessage('❌ ' + data.message, 'error');
                }
                
                btnForgot.disabled = false;
                btnForgot.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Link de Recuperação';
            } catch (error) {
                showMessage('❌ Erro ao enviar email. Verifique sua conexão.', 'error');
                console.error(error);
                btnForgot.disabled = false;
                btnForgot.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Link de Recuperação';
            }
        }
    </script>
</body>
</html>