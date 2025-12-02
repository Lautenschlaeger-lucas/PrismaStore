<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redireciona se não estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'conexao.php'; 

$itens_orcamento = [];
$total_orcamento = 0;
$mensagem_status = '';
$sucesso_processamento = false;

// Buscar dados do usuário logado
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Erro: Usuário não encontrado.");
}

// Função para buscar itens do orçamento
function buscarItensOrcamento($pdo) {
    global $itens_orcamento, $total_orcamento;
    
    if (isset($_SESSION['orcamento']) && !empty($_SESSION['orcamento'])) {
        $ids_para_buscar = array_keys($_SESSION['orcamento']);
        $ids_placeholders = implode(',', array_fill(0, count($ids_para_buscar), '?'));

        try {
            $stmt = $pdo->prepare("SELECT id, name, price FROM paineis WHERE id IN ($ids_placeholders)");
            $stmt->execute($ids_para_buscar);
            $paineis_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($paineis_db as $painel) {
                $id = $painel['id'];
                $quantidade = $_SESSION['orcamento'][$id];
                $subtotal = $painel['price'] * $quantidade;
                $total_orcamento += $subtotal;

                $itens_orcamento[] = [
                    'id' => $id,
                    'name' => $painel['name'],
                    'price' => $painel['price'],
                    'quantidade' => $quantidade,
                    'subtotal' => $subtotal
                ];
            }
        } catch (PDOException $e) {
            return "Erro ao carregar orçamento: " . $e->getMessage();
        }
    }
    return null;
}

$erro_db = buscarItensOrcamento($pdo);

// PROCESSAR FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($itens_orcamento)) {
        $mensagem_status = "❌ O orçamento está vazio! Adicione itens antes de finalizar.";
    } else {
        $estado = htmlspecialchars(trim($_POST['estado'] ?? ''));
        $cidade = htmlspecialchars(trim($_POST['cidade'] ?? ''));
        
        if (empty($estado) || empty($cidade)) {
            $mensagem_status = "⚠️ Por favor, preencha o estado e a cidade.";
        } else {
            // Calcular prazo de entrega
            $prazo_entrega = (strtoupper($estado) === 'SP') ? '5 dias úteis' : '10 dias úteis';
            
            // Preparar dados do pedido
            $nome = $usuario['name'];
            $email = $usuario['email'];
            $telefone = $usuario['phone'];
            
            // Gerar HTML dos itens do pedido
            $itens_html = '';
            foreach ($itens_orcamento as $item) {
                $itens_html .= "
                <tr>
                    <td style='padding: 12px; border-bottom: 1px solid #e0e0e0;'>{$item['name']}</td>
                    <td style='padding: 12px; border-bottom: 1px solid #e0e0e0; text-align: center;'>{$item['quantidade']}</td>
                    <td style='padding: 12px; border-bottom: 1px solid #e0e0e0; text-align: right;'>R$ " . number_format($item['price'], 2, ',', '.') . "</td>
                    <td style='padding: 12px; border-bottom: 1px solid #e0e0e0; text-align: right; font-weight: bold;'>R$ " . number_format($item['subtotal'], 2, ',', '.') . "</td>
                </tr>";
            }
            
            // Configurar email
            $para = $email;
            $assunto = "PrismaStore - Confirmação de Pedido #" . time();
            $mensagem_email = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
                    .header h1 { margin: 0; font-size: 28px; }
                    .content { padding: 30px 20px; }
                    .success-badge { background: #10b981; color: white; display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; margin: 10px 0; }
                    .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
                    .info-row { display: flex; justify-content: space-between; padding: 8px 0; }
                    .info-label { font-weight: bold; color: #666; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: bold; color: #333; }
                    .total-row { background: #f8f9fa; font-size: 18px; font-weight: bold; }
                    .delivery-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin: 20px 0; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
                    .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='header'>
                        <h1>🌞 PrismaStore</h1>
                        <p style='margin: 5px 0; opacity: 0.9;'>Energia Solar Sustentável</p>
                    </div>
                    
                    <div class='content'>
                        <div class='success-badge'>✅ Pedido Confirmado!</div>
                        
                        <h2 style='color: #333; margin-top: 20px;'>Olá, {$nome}!</h2>
                        <p style='color: #666; line-height: 1.6;'>Seu pedido foi recebido com sucesso! Nossa equipe está preparando seu orçamento detalhado e entrará em contato em breve.</p>
                        
                        <div class='info-box'>
                            <h3 style='margin-top: 0; color: #667eea;'>📋 Informações do Pedido</h3>
                            <div class='info-row'>
                                <span class='info-label'>Cliente:</span>
                                <span>{$nome}</span>
                            </div>
                            <div class='info-row'>
                                <span class='info-label'>E-mail:</span>
                                <span>{$email}</span>
                            </div>
                            <div class='info-row'>
                                <span class='info-label'>Telefone:</span>
                                <span>{$telefone}</span>
                            </div>
                            <div class='info-row'>
                                <span class='info-label'>Local:</span>
                                <span>{$cidade} - {$estado}</span>
                            </div>
                        </div>
                        
                        <h3 style='color: #333; margin-top: 30px;'>🛒 Itens do Pedido</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th style='text-align: center;'>Qtd</th>
                                    <th style='text-align: right;'>Preço Unit.</th>
                                    <th style='text-align: right;'>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$itens_html}
                                <tr class='total-row'>
                                    <td colspan='3' style='padding: 15px; text-align: right;'>TOTAL ESTIMADO:</td>
                                    <td style='padding: 15px; text-align: right; color: #667eea;'>R$ " . number_format($total_orcamento, 2, ',', '.') . "</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class='delivery-box'>
                            <strong>📦 Prazo de Entrega:</strong> {$prazo_entrega}
                            <p style='margin: 10px 0 0 0; font-size: 14px;'>*Prazo estimado a partir da confirmação do pedido e pagamento.</p>
                        </div>
                        
                        <h3 style='color: #333; margin-top: 30px;'>📞 Próximos Passos</h3>
                        <ol style='color: #666; line-height: 1.8;'>
                            <li>Nossa equipe técnica analisará seu pedido</li>
                            <li>Você receberá um orçamento detalhado via WhatsApp/E-mail</li>
                            <li>Após aprovação, agendaremos a instalação</li>
                            <li>Instalação profissional no prazo informado</li>
                        </ol>
                        
                        <center>
                            <a href='https://api.whatsapp.com/send?phone=5519999999999&text=Olá! Gostaria de saber mais sobre meu pedido.' class='button'>💬 Falar com Consultor</a>
                        </center>
                    </div>
                    
                    <div class='footer'>
                        <p><strong>PrismaStore - Energia Solar</strong></p>
                        <p>📧 contato@prismastore.com | 📱 (19) 99999-9999</p>
                        <p style='margin-top: 15px; font-size: 12px;'>Este é um e-mail automático, não responda esta mensagem.</p>
                        <p style='margin-top: 10px;'>&copy; 2025 PrismaStore. Todos os direitos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Headers do email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: PrismaStore <noreply@prismastore.com>" . "\r\n";
            $headers .= "Reply-To: contato@prismastore.com" . "\r\n";
            
            // Enviar email
            if (mail($para, $assunto, $mensagem_email, $headers)) {
                // Limpar carrinho após sucesso
                unset($_SESSION['orcamento']);
                $sucesso_processamento = true;
                $mensagem_status = "🎉 Pedido finalizado com sucesso! Enviamos um e-mail de confirmação para {$email}. Nossa equipe entrará em contato em breve. Prazo de entrega: {$prazo_entrega}.";
            } else {
                $mensagem_status = "⚠️ Pedido processado, mas houve um erro ao enviar o e-mail de confirmação. Entre em contato conosco pelo WhatsApp.";
            }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../styles/user-menu.css">
    <title>Finalizar Orçamento | PrismaStore</title>
    <style>
        :root {
            --primary-color: #3504FD;
            --primary-dark: #2a03d1;
            --primary-light: #5a3bff;
            --background: #c2cdff;
            --white: #ffffff;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --radius: 16px;
            --shadow-large: 0 16px 64px rgba(0, 0, 0, 0.16);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--background);
            line-height: 1.6;
            color: var(--gray-800);
            padding: 100px 20px 40px;
            min-height: 100vh;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        #navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 8%;
            max-width: 1400px;
            margin: 0 auto;
        }

        #logo {
            height: 50px;
        }

        .process-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-large);
            border-top: 5px solid var(--primary-color);
        }

        h1 {
            color: var(--primary-color);
            font-size: 2.2em;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            background: var(--gray-100);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .user-info h3 {
            color: var(--gray-700);
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .info-label {
            font-weight: bold;
            color: var(--gray-600);
        }

        .summary-card {
            background: #f0f9ff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed var(--gray-300);
        }

        .summary-total {
            font-size: 1.8em;
            font-weight: bold;
            color: var(--primary-color);
            text-align: right;
            margin-top: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            color: var(--gray-700);
            margin-bottom: 8px;
        }

        select, input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1em;
            transition: var(--transition);
        }

        select:focus, input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(53, 4, 253, 0.1);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(53, 4, 253, 0.3);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-600);
            font-weight: 600;
            margin-top: 15px;
            text-decoration: none;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }

        .status-message {
            padding: 18px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
        }

        .status-success {
            background: #d1fae5;
            color: var(--success);
            border: 2px solid var(--success);
        }

        .status-error {
            background: #fee2e2;
            color: var(--error);
            border: 2px solid var(--error);
        }

        @media (max-width: 768px) {
            .process-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav id="navbar">
            <a href="../../PainelStore.php">
                <img id="logo" src="../images/logo.png" alt="PrismaStore">
            </a>
            <div id="user_menu_container">
                <div id="user_icon" class="logged-in">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div id="dropdown_menu" class="dropdown-hidden">
                    <a href="orcamento.php" class="dropdown-item">
                        <i class="fa-solid fa-shopping-cart"></i>
                        <span>Carrinho</span>
                    </a>
                    <a href="dashboard.php" class="dropdown-item">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="logout.php" class="dropdown-item logout">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="process-container">
        <h1><i class="fas fa-paper-plane"></i> Finalizar Pedido</h1>
        
        <?php if (!empty($mensagem_status)): ?>
            <div class="status-message <?= $sucesso_processamento ? 'status-success' : 'status-error' ?>">
                <i class="fas <?= $sucesso_processamento ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <?= $mensagem_status ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso_processamento): ?>
            <a href="../../PainelStore.php" class="btn-back" style="color: var(--success);"><i class="fas fa-home"></i> Voltar à Página Inicial</a>
        <?php elseif (empty($itens_orcamento)): ?>
            <div class="status-message status-error">
                <i class="fas fa-box-open"></i> Seu orçamento está vazio.
            </div>
            <a href="Paineis.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar aos produtos</a>
        <?php else: ?>
            
            <div class="user-info">
                <h3>👤 Seus Dados</h3>
                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span><?= htmlspecialchars($usuario['name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">E-mail:</span>
                    <span><?= htmlspecialchars($usuario['email']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telefone:</span>
                    <span><?= htmlspecialchars($usuario['phone']) ?></span>
                </div>
            </div>

            <div class="summary-card">
                <h2 style="margin-bottom: 15px;">📦 Resumo do Pedido</h2>
                <?php foreach ($itens_orcamento as $item): ?>
                    <div class="summary-item">
                        <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantidade'] ?>)</span>
                        <span>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-total">
                    Total: R$ <?= number_format($total_orcamento, 2, ',', '.') ?>
                </div>
            </div>

            <form method="POST">
                <h3 style="margin-bottom: 20px; color: var(--gray-700);">📍 Local de Entrega</h3>
                
                <div class="form-group">
                    <label for="estado">Estado *</label>
                    <select id="estado" name="estado" required>
                        <option value="">Selecione o estado</option>
                        <option value="SP">São Paulo</option>
                        <option value="AC">Acre</option>
                        <option value="AL">Alagoas</option>
                        <option value="AP">Amapá</option>
                        <option value="AM">Amazonas</option>
                        <option value="BA">Bahia</option>
                        <option value="CE">Ceará</option>
                        <option value="DF">Distrito Federal</option>
                        <option value="ES">Espírito Santo</option>
                        <option value="GO">Goiás</option>
                        <option value="MA">Maranhão</option>
                        <option value="MT">Mato Grosso</option>
                        <option value="MS">Mato Grosso do Sul</option>
                        <option value="MG">Minas Gerais</option>
                        <option value="PA">Pará</option>
                        <option value="PB">Paraíba</option>
                        <option value="PR">Paraná</option>
                        <option value="PE">Pernambuco</option>
                        <option value="PI">Piauí</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="RN">Rio Grande do Norte</option>
                        <option value="RS">Rio Grande do Sul</option>
                        <option value="RO">Rondônia</option>
                        <option value="RR">Roraima</option>
                        <option value="SC">Santa Catarina</option>
                        <option value="SE">Sergipe</option>
                        <option value="TO">Tocantins</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade *</label>
                    <input type="text" id="cidade" name="cidade" required placeholder="Digite sua cidade">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> Confirmar Pedido
                </button>
            </form>
            
            <a href="orcamento.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Carrinho</a>
        <?php endif; ?>
    </div>

    <script src="../js/user-menu.js"></script>
</body>
</html>