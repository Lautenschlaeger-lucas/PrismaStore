<?php
// dashboard.php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    // Caminho ajustado para o login.html
    header('Location: login.html');
    exit;
}

// Configuração do banco de dados
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'prismastore';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    // Adiciona o erro de conexão para facilitar a depuração
    die('Erro de conexão com o banco de dados: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

// Buscar dados do usuário
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // Se o usuário não for encontrado (ex: ID inválido na sessão)
    session_destroy();
    // Caminho ajustado para o login.html
    header('Location: login.html');
    exit;
}

$stmt->close();
$mysqli->close();

// Formatar data de criação
try {
    $created_date = new DateTime($user['created_at']);
    $formatted_date = $created_date->format('d/m/Y H:i');
} catch (Exception $e) {
    $formatted_date = 'Data Inválida';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="src/images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/dashboard.css"> <title>PrismaStore - Dashboard</title>
</head>
<body>
    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <header>
        <nav id="navbar">
            <a href="../../PainelStore.php">
                <img id="logo" src="../images/logo.png" alt="PrismaStore Logo">
            </a>
            <ul id="nav_list">
                <li class="nav_item">
                    <a href="../../PainelStore.php">Início</a> </li>
                <li class="nav_item">
                    <a href="Paineis.php">Painéis</a> </li>
                <li class="nav_item">
                    <a href="../../simulador.php">Simulador</a> </li>
            </ul>
            <div id="login_icon">
                <button id="logoutBtn" class="logout-button" onclick="logout()">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    Sair
                </button>
            </div>
        </nav>
    </header>

    <main class="dashboard-wrapper">
        <div class="dashboard-container">
            <div class="welcome-section">
                <div class="welcome-content">
                    <div class="welcome-greeting">
                        <h1>Bem-vindo, <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>! 👋</h1>
                        <p class="welcome-subtitle">Aqui estão seus dados de perfil e informações da conta</p>
                    </div>
                    <div class="welcome-icon">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                </div>
            </div>

            <div class="user-data-section">
                <h2 class="section-title">Dados da Sua Conta</h2>
                
                <div class="data-list-container">
                    <ul class="user-details-list">
                        <li>
                            <span class="detail-icon"><i class="fa-solid fa-user"></i></span>
                            <span class="detail-label">Nome Completo:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['name']); ?></span>
                        </li>
                        <li>
                            <span class="detail-icon"><i class="fa-solid fa-envelope"></i></span>
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </li>
                        <li>
                            <span class="detail-icon"><i class="fa-solid fa-phone"></i></span>
                            <span class="detail-label">Telefone:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                        </li>
                        <li>
                            <span class="detail-icon"><i class="fa-solid fa-calendar"></i></span>
                            <span class="detail-label">Membro Desde:</span>
                            <span class="detail-value"><?php echo $formatted_date; ?></span>
                        </li>
                        <li>
                            <span class="detail-icon"><i class="fa-solid fa-id-card"></i></span>
                            <span class="detail-label">ID de Usuário:</span>
                            <span class="detail-value">#<?php echo htmlspecialchars($user['id']); ?></span>
                        </li>
                        <li>
                            <span class="detail-icon"><i class="fa-solid fa-check-circle"></i></span>
                            <span class="detail-label">Status:</span>
                            <span class="detail-value status-active">
                                <i class="fa-solid fa-circle"></i> Ativo
                            </span>
                        </li>
                    </ul>
                    
                    <button class="edit-button-main" onclick="openEditModal()">
                        <i class="fa-solid fa-edit"></i>
                        Editar Dados Pessoais
                    </button>
                </div>
            </div>

            <div class="actions-section">
                <h2 class="section-title">Ações Rápidas</h2>
                
                <div class="actions-grid">
                    <a href="Paineis.php" class="action-button"> <div class="action-icon">
                            <i class="fa-solid fa-solar-panel"></i>
                        </div>
                        <div class="action-content">
                            <h3>Explorar Painéis</h3>
                            <p>Conheça nossos painéis solares</p>
                        </div>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="../../simulador.php" class="action-button"> <div class="action-icon">
                            <i class="fa-solid fa-calculator"></i>
                        </div>
                        <div class="action-content">
                            <h3>Simulador</h3>
                            <p>Simule seu sistema solar</p>
                        </div>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="../../PainelStore.php" class="action-button"> <div class="action-icon">
                            <i class="fa-solid fa-home"></i>
                        </div>
                        <div class="action-content">
                            <h3>Página Inicial</h3>
                            <p>Retornar à página inicial</p>
                        </div>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeEditModal()">&times;</span>
            <h2>Editar Dados Pessoais</h2>
            <form id="editForm">
                <div class="form-group">
                    <label for="edit_name">Nome Completo</label>
                    <input type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_phone">Telefone</label>
                    <input type="text" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <button type="submit" class="save-button">
                    <i class="fa-solid fa-save"></i> Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <div id="toast" class="toast">
        <div class="toast-content">
            <div class="toast-icon">
                <i class="fa-solid fa-info-circle"></i>
            </div>
            <div class="toast-message">
                Mensagem de notificação
            </div>
            <button class="toast-close" onclick="hideToast()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    </div>

    <footer>
        <div id="footer_items">
            <span id="copyright">
                &copy; 2025 PrismaStore
            </span>
            <div class="social-media-buttons">
                <a href="https://www.whatsapp.com/?lang=pt_BR" target="_blank" title="WhatsApp">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>
                <a href="https://www.instagram.com/" target="_blank" title="Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://www.facebook.com/login/?locale=pt_BR" target="_blank" title="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
            </div>
        </div>
    </footer>

    <script>
        function logout() {
            if (confirm('Tem certeza que deseja sair?')) {
                // Caminho para o script de logout.
                fetch('auth.php', { 
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'logout'
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }).then(() => {
                    showToast('Logout realizado com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = '../../login.html'; 
                    }, 1500);
                }).catch(error => {
                    console.error('Erro ao fazer logout:', error);
                    showToast('Erro ao realizar logout. Tente novamente.', 'error');
                });
            }
        }

        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const toastMessage = toast.querySelector('.toast-message');
            const toastIcon = toast.querySelector('.toast-icon i');
            
            toastMessage.textContent = message;
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            toastIcon.className = `fa-solid ${icons[type] || icons.info}`;
            
            // Remova todas as classes de tipo antes de adicionar a correta
            toast.classList.remove('toast-success', 'toast-error', 'toast-warning', 'toast-info');
            toast.className = `toast toast-${type} show`;
            
            setTimeout(() => {
                hideToast();
            }, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('show');
        }

        /* ===== FUNÇÕES DO MODAL DE EDIÇÃO (NOVO) ===== */
        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        document.getElementById('editForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.action = 'update_profile'; // Ação para o backend

            // Requisição para o backend para salvar os dados
            // ATENÇÃO: Você DEVE criar o arquivo update_profile.php!
            fetch('update_profile.php', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('Dados atualizados com sucesso!', 'success');
                    closeEditModal();
                    // Recarrega a página para mostrar os dados atualizados
                    setTimeout(() => window.location.reload(), 1000); 
                } else {
                    showToast('Erro ao atualizar: ' + result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro de rede:', error);
                showToast('Erro de rede. Tente novamente.', 'error');
            });
        });
    </script>
</body>
</html>