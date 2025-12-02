<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Certifique-se de que o caminho para 'conexao.php' está correto.
require_once 'conexao.php'; 

$itens_orcamento = [];
$total_orcamento = 0;

// Verifica se há itens na sessão
if (isset($_SESSION['orcamento']) && !empty($_SESSION['orcamento'])) {
    // 1. Pega apenas os IDs que estão na sessão
    $ids_para_buscar = array_keys($_SESSION['orcamento']);
    $ids_placeholders = implode(',', array_fill(0, count($ids_para_buscar), '?'));

    try {
        // 2. Busca os detalhes dos painéis no banco de dados
        $stmt = $pdo->prepare("SELECT id, name, price FROM paineis WHERE id IN ($ids_placeholders)");
        // O bindParam é mais seguro, mas o execute com array funciona bem com PDO para IN
        $stmt->execute($ids_para_buscar); 
        $paineis_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Junta os dados do DB com as quantidades da sessão
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
        // Tratar erro do DB
        $mensagem_erro = "Erro ao carregar orçamento: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Orçamento | PrismaStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    
    <style>
        /* ===== VARIÁVEIS CSS (Base fornecida por você) ===== */
        :root {
            --primary-color: #3504FD;
            --primary-dark: #2a03d1;
            --primary-light: #5a3bff;
            --background: #c2cdff;
            --background-secondary: #e8f0fe;
            --white: #ffffff;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            
            --radius: 16px;
            --radius-small: 8px;
            --radius-large: 24px;
            
            --shadow-small: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 32px rgba(0, 0, 0, 0.12);
            --shadow-large: 0 16px 64px rgba(0, 0, 0, 0.16);
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== RESET E BASE ===== */
        * {
            font-family: "League Spartan", sans-serif;
            font-weight: 500; /* Peso padrão mais leve */
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--background);
            line-height: 1.6;
            color: var(--gray-800);
            padding: 40px 20px;
        }
        
        a {
            text-decoration: none;
            transition: var(--transition-fast);
        }

        /* === CONTAINER PRINCIPAL === */
        .orcamento-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--white);
            padding: 35px 50px;
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-large);
            border-top: 5px solid var(--primary-color);
        }

        h1 {
            color: var(--primary-color); 
            font-size: 2.5em;
            font-weight: 800;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
        }
        h1 i {
            margin-right: 15px;
            color: var(--primary-light);
        }

        /* === TABELA DE ITENS (ORÇAMENTO) === */
        .orcamento-table {
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0;
            margin-bottom: 30px;
            border-radius: var(--radius-small);
            overflow: hidden; 
            box-shadow: var(--shadow-small);
        }

        .orcamento-table thead th {
            background-color: var(--primary-color); /* Azul principal */
            color: var(--white);
            padding: 15px 25px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        
        /* Arredondamento dos cantos do cabeçalho */
        .orcamento-table thead th:first-child { border-top-left-radius: var(--radius-small); }
        .orcamento-table thead th:last-child { border-top-right-radius: var(--radius-small); }

        .orcamento-table tbody tr {
            transition: var(--transition-fast);
            border-bottom: 1px solid var(--gray-200);
        }
        .orcamento-table tbody tr:last-child {
            border-bottom: none;
        }
        .orcamento-table tbody tr:hover {
            background-color: var(--background-secondary);
        }

        .orcamento-table td { 
            padding: 18px 25px; 
            vertical-align: middle;
            color: var(--gray-700);
        }

        /* Estilos de colunas específicas */
        .orcamento-table td:nth-child(1) { /* Nome do Painel */
            font-weight: 600;
            color: var(--primary-dark);
        }
        .orcamento-table td:nth-child(2) { /* Preço Unitário */
            color: var(--gray-600);
            font-weight: 500;
        }
        .orcamento-table td:nth-child(3) { /* Quantidade */
            text-align: center;
            font-weight: 600;
        }
        .orcamento-table td:nth-child(4) { /* Subtotal */
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.1em;
        }

        /* === RODAPÉ DA TABELA (TOTAL) === */
        .orcamento-table tfoot td {
            background-color: var(--gray-100); 
            padding: 25px;
            font-size: 1.1em;
            font-weight: 600;
        }

        .orcamento-table tfoot td:last-child {
            /* Destaque para o Valor Total */
            background: linear-gradient(90deg, var(--primary-dark), var(--primary-color));
            color: var(--white);
            font-size: 1.5em;
            font-weight: 800;
            text-align: right;
            border-bottom-right-radius: var(--radius-small);
        }
        .orcamento-table tfoot td:nth-child(3) {
            text-align: right;
            font-weight: 700;
            color: var(--gray-700);
        }
        .orcamento-table tfoot td:first-child {
            border-bottom-left-radius: var(--radius-small);
        }

        /* === BOTÕES DE AÇÃO === */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            gap: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05em;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
            min-width: 220px;
            justify-content: center;
        }

        /* Botão Principal: Finalizar */
        .btn-finish {
            background: linear-gradient(135deg, var(--success), #059669); /* Verde para sucesso/conclusão */
            color: var(--white);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4);
        }
        .btn-finish:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.6);
        }

        /* Botões Secundários: Continuar/Limpar */
        .btn-secondary {
            background-color: var(--white);
            color: var(--primary-color);
            border-color: var(--primary-color);
            box-shadow: var(--shadow-small);
        }
        .btn-secondary:hover {
            background-color: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(53, 4, 253, 0.2);
        }

        .btn i {
            margin-right: 10px;
        }

        /* === Mensagem Vazia === */
        .empty-message {
            text-align: center;
            padding: 60px;
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius);
            margin-top: 30px;
            color: var(--gray-600);
            background-color: var(--gray-100);
        }
        .empty-message a {
            color: var(--primary-color);
            font-weight: 700;
        }

        /* === RESPONSIVIDADE === */
        @media (max-width: 850px) {
            .orcamento-container {
                padding: 30px;
            }
            h1 {
                font-size: 2em;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                min-width: 100%;
                margin-bottom: 15px;
            }
            .orcamento-table tfoot td:last-child {
                font-size: 1.3em;
            }
        }
    </style>

</head>
<body>
    <div class="orcamento-container">
        <h1><i class="fas fa-file-invoice-dollar"></i> Resumo do Seu Orçamento</h1>
        
        <?php if (isset($mensagem_erro)): ?>
            <p style="color: var(--error); padding: 15px; background: var(--gray-100); border-radius: var(--radius-small); margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?= $mensagem_erro ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($itens_orcamento)): ?>
            <table class="orcamento-table">
                <thead>
                    <tr>
                        <th>Painel Solar</th>
                        <th>Preço Unitário</th>
                        <th style="text-align: center;">Quantidade</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens_orcamento as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; color: var(--gray-700);">TOTAL ESTIMADO:</td>
                        <td style="text-align: right;">R$ <?= number_format($total_orcamento, 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="action-buttons">
                <a href="Paineis.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Continuar Escolhendo
                </a>

                <a href="limpar_orcamento.php" class="btn btn-secondary">
                    <i class="fas fa-trash-alt"></i> Limpar Orçamento
                </a>

                <a href="processar_orcamento.php" class="btn btn-finish">
                    <i class="fas fa-paper-plane"></i> Finalizar e Pedir Contato
                </a>
            </div>

        <?php else: ?>
            <p class="empty-message">
                <i class="fas fa-box-open fa-3x" style="margin-bottom: 15px; display: block;"></i>
                Seu orçamento está vazio. Adicione painéis na <a href="Paineis.php">página de produtos</a>.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>