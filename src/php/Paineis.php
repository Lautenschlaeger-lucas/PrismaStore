<?php
// 1. INICIALIZAÇÃO DA SESSÃO E INCLUSÃO DE CONFIGURAÇÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexao.php'; 

// Inicializa o array de orçamento na sessão se ele ainda não existir
if (!isset($_SESSION['orcamento'])) {
    $_SESSION['orcamento'] = [];
}

// Verifica se o usuário está logado e se é admin
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_admin = ($user && $user['role'] === 'admin');
    } catch (PDOException $e) {
        // Em caso de erro, mantém como não-admin
        $is_admin = false;
    }
}

$mensagem = '';
$produtos = []; 

// Exibe mensagem se houver uma no URL
if (isset($_GET['msg'])) {
    $mensagem = htmlspecialchars($_GET['msg']);
}

// ============================================
// LÓGICA PARA ADICIONAR AO ORÇAMENTO
// ============================================
if (isset($_GET['acao']) && $_GET['acao'] === 'adicionar_orcamento' && isset($_GET['id'])) {
    $id_painel = (int)$_GET['id'];
    
    if ($id_painel > 0) {
        if (array_key_exists($id_painel, $_SESSION['orcamento'])) {
            $_SESSION['orcamento'][$id_painel] += 1; 
            $mensagem = "✅ Mais uma unidade do Painel adicionada ao orçamento!";
        } else {
            $_SESSION['orcamento'][$id_painel] = 1;
            $mensagem = "✅ Painel adicionado ao orçamento!";
        }
    }

    header("Location: Paineis.php?msg=" . urlencode($mensagem)); 
    exit;
}

// ============================================
// LÓGICA DE EXCLUSÃO (APENAS ADMIN)
// ============================================
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    if (!$is_admin) {
        $mensagem = "❌ Acesso negado! Apenas administradores podem excluir painéis.";
        header("Location: Paineis.php?msg=" . urlencode($mensagem)); 
        exit;
    }
    
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM paineis WHERE id = ?"); 
        $stmt->execute([$id]);
        $mensagem = "✅ Painel excluído com sucesso!";
    } catch (PDOException $e) {
        $mensagem = "❌ Erro ao excluir: " . $e->getMessage();
    }
    header("Location: Paineis.php?msg=" . urlencode($mensagem)); 
    exit;
}

// ============================================
// LÓGICA PARA LISTAR (Read) todos os painéis COM TODAS AS COLUNAS
// ============================================
try {
    $stmt = $pdo->query("
        SELECT 
            id, 
            name, 
            price, 
            images, 
            marca, 
            potencia, 
            dimensoes, 
            eficiencia, 
            garantia, 
            beneficios 
        FROM paineis 
        ORDER BY id DESC
    "); 
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem = "❌ Erro ao buscar painéis: " . $e->getMessage();
    $produtos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../styles/Paineis.css"> 
    <title>PrismaStore - Orçamentos</title>
</head>
<body>
    <header>
        <nav id="navbar">
            <a href="../../PainelStore.html">
                <img id="logo" src="../images/logo.png" alt="PrismaStore Logo">
            </a>
            <ul id="nav_list">
                <li class="nav_item">
                    <a href="../../PainelStore.php">Início</a>
                </li>
                <?php if ($is_admin): ?>
                <li class="nav_item">
                    <a href="formproduto.php">Cadastrar Painel</a>
                </li>
                <?php endif; ?>
                <li class="nav_item">
                    <a href="#" class="active">Orçamentos</a>
                </li>
                <li class="nav_item">
                    <a href="../../simulador.html">Simulador</a>
                </li>
            </ul>
            <div id="login_icon">
                <a href="orcamento.php" title="Ver Orçamento (<?= count($_SESSION['orcamento']) ?>)">
                    <i class="fa-solid fa-cart-shopping"></i>
                </a>
            </div>
        </nav>
    </header>

    <main>
        <div class="page-header">
            <h1 class="page-title">Nossos Orçamentos</h1>
            <p class="page-subtitle">Escolha a melhor opção para sua necessidade energética</p>
            
            <?php if (!empty($mensagem)): ?>
                <p class="alerta" style="color: <?= strpos($mensagem, '✅') !== false ? 'green' : 'red' ?>; font-weight: bold; margin-top: 10px;"><?= $mensagem ?></p>
            <?php endif; ?>
        </div>

        <div class="panels-grid">
    <?php if (count($produtos) > 0): ?>
        <?php foreach ($produtos as $produto): 
            // DADOS BÁSICOS DO PRODUTO (DO BANCO DE DADOS)
            $nome = htmlspecialchars($produto['name']);
            $preco_formatado = 'R$ ' . number_format($produto['price'], 2, ',', '.');
            $id = htmlspecialchars($produto['id']);
            
            // CAMINHO DA IMAGEM REAL (DO DB)
            $caminho_imagem_db = $produto['images'] ?? null; 
            
            if (!empty($caminho_imagem_db)) {
                $imagem_src = BASE_URL . '/' . htmlspecialchars($caminho_imagem_db);
            } else {
                $imagem_src = BASE_URL . '/src/images/Painel_Padrao.png'; 
            }

            // DADOS COMPLEMENTARES (AGORA VINDOS DO BANCO DE DADOS)
            $marca = !empty($produto['marca']) ? htmlspecialchars($produto['marca']) : 'Marca Padrão';
            $potencia = !empty($produto['potencia']) ? htmlspecialchars($produto['potencia']) : '450W';
            $dimensoes = !empty($produto['dimensoes']) ? htmlspecialchars($produto['dimensoes']) : '2.0m x 1.0m';
            $eficiencia = !empty($produto['eficiencia']) ? htmlspecialchars($produto['eficiencia']) : '20.0%';
            $garantia = !empty($produto['garantia']) ? htmlspecialchars($produto['garantia']) : '25 anos';
            
            // BENEFÍCIOS - Converte o texto do banco em array (cada linha = um benefício)
            $beneficios_texto = $produto['beneficios'] ?? '';
            if (!empty(trim($beneficios_texto))) {
                $beneficios = array_filter(array_map('trim', explode("\n", $beneficios_texto)));
            } else {
                $beneficios = [
                    'Geração de energia otimizada',
                    'Durabilidade superior',
                    'Baixo custo de manutenção'
                ];
            }
            
            $etiqueta = 'NOVO';
        ?>
        
        <div class="panel-card">
            
            <?php if (!empty($etiqueta)): ?>
                <div class="<?= strtolower(str_replace(' ', '-', $etiqueta)) ?>"><?= $etiqueta ?></div>
            <?php endif; ?>

            <div class="panel-image">
                <img src="<?= $imagem_src ?>" alt="<?= $nome ?>">
                <div class="panel-brand"><?= $marca ?></div>
            </div>
            
            <div class="panel-info">
                <h3><?= $nome ?></h3>
                
                <div class="panel-specs">
                    <div class="spec-item">
                        <span class="spec-label">Potência:</span>
                        <span class="spec-value"><?= $potencia ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Dimensões:</span>
                        <span class="spec-value"><?= $dimensoes ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Eficiência:</span>
                        <span class="spec-value"><?= $eficiencia ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Garantia:</span>
                        <span class="spec-value"><?= $garantia ?></span>
                    </div>
                </div>
                
                <div class="panel-price">
                    <div class="price-value"><?= $preco_formatado ?></div>
                    <div class="price-unit">por unidade</div>
                </div>
                
                <div class="panel-benefits">
                    <div class="benefits-title">Principais benefícios:</div>
                    <?php foreach ($beneficios as $beneficio): ?>
                        <div class="benefit-item">
                            <i class="fas fa-check"></i>
                            <span><?= htmlspecialchars($beneficio) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($is_admin): ?>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <a href="formproduto.php?id=<?= $id ?>" class="btn-primary" style="background-color: #3182CE; text-align: center; padding: 10px; border-radius: 5px; flex-grow: 1; text-decoration: none; color: white;">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="Paineis.php?acao=excluir&id=<?= $id ?>" onclick="return confirm('Excluir <?= $nome ?>?');" class="btn-primary" style="background-color: #E53E3E; text-align: center; padding: 10px; border-radius: 5px; flex-grow: 1; text-decoration: none; color: white;">
                        <i class="fas fa-trash-alt"></i> Excluir
                    </a>
                </div>
                <?php endif; ?>

                <a href="Paineis.php?acao=adicionar_orcamento&id=<?= $id ?>" class="btn-primary" style="margin-top: 10px; width: 100%; text-align: center; padding: 10px; border-radius: 5px; text-decoration: none; display: block; background-color: #38A169; color: white;">
                    <i class="fas fa-cart-plus" style="margin-right: 0.5rem;"></i>
                    Adicionar ao Orçamento
                </a>

            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum painel encontrado. 
            <?php if ($is_admin): ?>
                Cadastre um painel em <a href="formproduto.php">Novo Painel</a>.
            <?php endif; ?>
        </p>
    <?php endif; ?>
    </div>
    </main>

    <footer>
        <div id="footer_items">
            <span id="copyright">
                &copy; 2025 PrismaStore
            </span>
            <div class="social-media-buttons">
                <a href="https://www.whatsapp.com/?lang=pt_BR">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>
                <a href="https://www.instagram.com/">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://www.facebook.com/login/?locale=pt_BR">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
            </div>
        </div>
    </footer>
</body>
</html>