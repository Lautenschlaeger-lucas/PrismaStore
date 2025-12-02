<?php
// Inclui o arquivo de configuração/conexão para usar $pdo
require_once 'conexao.php'; 

// Lembrete: Garanta que BASE_URL está definido como 'http://localhost' no conexao.php

$mensagem = '';

// Lógica para Exclusão (Delete)
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // CORREÇÃO: Tabela 'produtos' mudada para 'paineis'
        $stmt = $pdo->prepare("DELETE FROM paineis WHERE id = ?"); 
        $stmt->execute([$id]);
        $mensagem = "✅ Painel excluído com sucesso!"; 
    } catch (PDOException $e) {
        $mensagem = "❌ Erro ao excluir: " . $e->getMessage();
    }
    // Redireciona para evitar re-exclusão ao recarregar
    header("Location: produtos.php?msg=" . urlencode($mensagem));
    exit;
}

// Exibe mensagem se houver uma no URL
if (isset($_GET['msg'])) {
    $mensagem = htmlspecialchars($_GET['msg']);
}

// Lógica para Listar (Read) todos os painéis
try {
    // CORREÇÕES NA QUERY: 
    $stmt = $pdo->query("SELECT id, name, price FROM paineis ORDER BY id DESC");
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
    <title>Lista de Painéis | PrismaStore (Admin)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* === RESET BÁSICO E TIPOGRAFIA === */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2C5282; /* Azul escuro da PrismaStore */
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 10px;
            margin-top: 0;
        }

        /* === BOTÕES E LINKS DE AÇÃO SUPERIOR === */
        .header-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background-color: #38A169; /* Verde */
            color: white;
        }
        .btn-primary:hover {
            background-color: #2F855A;
        }

        .btn-secondary {
            background-color: #F7FAFC;
            color: #2C5282;
            border: 1px solid #E2E8F0;
        }
        .btn-secondary:hover {
            background-color: #EDF2F7;
        }

        .btn i {
            margin-right: 8px;
        }

        /* === MENSAGENS (Feedback) === */
        .message {
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .message-success {
            background-color: #D6F6D6; /* Verde claro */
            color: #2F855A; /* Verde escuro */
            border-left: 5px solid #38A169;
        }
        .message-error {
            background-color: #FED7D7; /* Vermelho claro */
            color: #C53030; /* Vermelho escuro */
            border-left: 5px solid #E53E3E;
        }

        /* === ESTILIZAÇÃO DA TABELA === */
        table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 6px;
            overflow: hidden; /* Garante que o border-radius funcione */
        }
        
        thead th {
            background-color: #2C5282; /* Cor principal */
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }

        tbody tr {
            background-color: #ffffff;
            transition: background-color 0.3s;
        }
        tbody tr:nth-child(even) {
            background-color: #F7FAFC; /* Linhas zebradas */
        }
        tbody tr:hover {
            background-color: #EDF2F7;
        }

        td { 
            padding: 12px 15px; 
            border-bottom: 1px solid #E2E8F0; 
        }

        /* Colunas específicas */
        td:nth-child(1) { width: 5%; font-weight: bold; color: #2C5282; } /* ID */
        td:nth-child(3) { width: 15%; font-weight: 600; color: #38A169; } /* Preço */
        td:nth-child(4) { width: 25%; text-align: center; } /* Ações */


        /* Links de Ação da Tabela */
        .action-links a {
            color: #3182CE;
            text-decoration: none;
            font-weight: 500;
            margin: 0 5px;
            transition: color 0.2s;
        }

        .action-links a:hover {
            text-decoration: underline;
            color: #2C5282;
        }
        
        .action-links .delete-link {
            color: #E53E3E; /* Vermelho para exclusão */
        }
        .action-links .delete-link:hover {
            color: #C53030;
        }
        
        /* Se nenhum produto */
        .no-records {
            padding: 20px;
            text-align: center;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gerenciamento de Painéis Solares</h1>
        
        <div class="header-actions">
            <a href="formproduto.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Cadastrar Novo Painel
            </a>
            <a href="Paineis.php" class="btn btn-secondary">
                <i class="fas fa-eye"></i> Ver Loja (Visualização)
            </a>
        </div>

        <?php if (!empty($mensagem)): ?>
            <p class="message <?= strpos($mensagem, '✅') !== false ? 'message-success' : 'message-error' ?>">
                <?= $mensagem ?>
            </p>
        <?php endif; ?>

        <?php if (count($produtos) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Painel</th>
                    <th>Preço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= htmlspecialchars($produto['id']) ?></td>
                        <td><?= htmlspecialchars($produto['name']) ?></td>
                        <td>R$ <?= number_format($produto['price'], 2, ',', '.') ?></td>
                        <td class="action-links">
                            <a href="formproduto.php?id=<?= $produto['id'] ?>">
                                <i class="fas fa-edit"></i> Editar
                            </a> | 
                            <a href="produtos.php?acao=excluir&id=<?= $produto['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir o painel ID <?= $produto['id'] ?>?');" class="delete-link">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="no-records">Nenhum painel cadastrado. Utilize o botão "Cadastrar Novo Painel" acima.</p> 
        <?php endif; ?>
    </div>
</body>
</html>