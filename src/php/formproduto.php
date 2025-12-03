<?php
// Iniciar sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexao.php'; 

// Verificar se o usuário está logado e é admin
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_admin = ($user && $user['role'] === 'admin');
    } catch (PDOException $e) {
        $is_admin = false;
    }
}

// Se não for admin, redireciona para a página de painéis
if (!$is_admin) {
    header("Location: Paineis.php?msg=" . urlencode("❌ Acesso negado! Apenas administradores podem gerenciar painéis."));
    exit;
}

$upload_dir = __DIR__ . '/../images/';

$id = $_GET['id'] ?? null;
$produto = [
    'name' => '', 
    'price' => '', 
    'images' => '',
    'marca' => '',
    'potencia' => '',
    'dimensoes' => '',
    'eficiencia' => '',
    'garantia' => '',
    'beneficios' => ''
]; 
$mensagem = '';
$form_titulo = 'Novo Painel';

// ============================================
// LÓGICA DE EDIÇÃO (Update - U)
// ============================================
if ($id) {
    $form_titulo = 'Editar Painel';
    try {
        $stmt = $pdo->prepare("SELECT id, name, price, images, marca, potencia, dimensoes, eficiencia, garantia, beneficios FROM paineis WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            die("Painel não encontrado.");
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao buscar painel: " . $e->getMessage();
    }
}

// ============================================
// LÓGICA DE INSERÇÃO/ATUALIZAÇÃO (Create/Update - C/U)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $marca = sanitize($_POST['marca'] ?? '');
    $potencia = sanitize($_POST['potencia'] ?? '');
    $dimensoes = sanitize($_POST['dimensoes'] ?? '');
    $eficiencia = sanitize($_POST['eficiencia'] ?? '');
    $garantia = sanitize($_POST['garantia'] ?? '');
    $beneficios = sanitize($_POST['beneficios'] ?? '');
    
    $images = $produto['images'];
    $upload_ok = true;
    
    // 1. TRATAMENTO DO UPLOAD DE IMAGEM
    if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['images']['tmp_name'];
        $file_name = $_FILES['images']['name'];
        $file_size = $_FILES['images']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $new_file_name = uniqid() . '.' . $file_ext;
        $dest_path_fisico = $upload_dir . $new_file_name;
        $dest_path_db = 'src/images/' . $new_file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_types)) {
            $mensagem = "❌ Tipo de arquivo não permitido. Apenas JPG, JPEG, PNG e GIF.";
            $upload_ok = false;
        }

        if ($upload_ok && !move_uploaded_file($file_tmp_path, $dest_path_fisico)) {
            $mensagem = "❌ Erro ao mover o arquivo de upload. Verifique permissões da pasta: " . $upload_dir;
            $upload_ok = false;
        }
        
        if ($upload_ok) {
            if ($id && !empty($produto['images'])) {
                @unlink(__DIR__ . '/../../' . $produto['images']);
            }
            $images = $dest_path_db;
        }
    }

    // 2. SALVAR NO BANCO DE DADOS
    if ($upload_ok && !empty($name) && $price !== false) {
        try {
            if ($id) {
                // Atualizar (Update)
                $stmt = $pdo->prepare("UPDATE paineis SET name = ?, price = ?, images = ?, marca = ?, potencia = ?, dimensoes = ?, eficiencia = ?, garantia = ?, beneficios = ? WHERE id = ?");
                $stmt->execute([$name, $price, $images, $marca, $potencia, $dimensoes, $eficiencia, $garantia, $beneficios, $id]);
                $mensagem = "✅ Painel atualizado com sucesso!";
            } else {
                // Inserir (Create)
                $stmt = $pdo->prepare("INSERT INTO paineis (name, price, images, marca, potencia, dimensoes, eficiencia, garantia, beneficios) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $price, $images, $marca, $potencia, $dimensoes, $eficiencia, $garantia, $beneficios]);
                $mensagem = "✅ Painel inserido com sucesso!";
                $id = $pdo->lastInsertId();
            }
            $produto['images'] = $images;
            
        } catch (PDOException $e) {
            $mensagem = "❌ Erro no banco de dados: " . $e->getMessage();
        }
    } elseif ($upload_ok) {
        $mensagem = "❌ Preencha todos os campos obrigatórios (Nome e Preço).";
    }
    
    // Mantém os dados no formulário após o POST
    $produto['name'] = $name;
    $produto['price'] = $_POST['price'];
    $produto['marca'] = $marca;
    $produto['potencia'] = $potencia;
    $produto['dimensoes'] = $dimensoes;
    $produto['eficiencia'] = $eficiencia;
    $produto['garantia'] = $garantia;
    $produto['beneficios'] = $beneficios;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/Paineis.css"> 
    <title>Gerenciar Painel | PrismaStore</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f7f6; 
            color: #333; 
            padding: 20px; 
        }
        .container { 
            max-width: 700px; 
            margin: 0 auto; 
            background: #fff; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        h1 { 
            color: #2C5282; 
            margin-bottom: 20px; 
        }
        .section-title {
            background-color: #E6F3FF;
            padding: 10px;
            margin-top: 25px;
            margin-bottom: 15px;
            border-left: 4px solid #2C5282;
            font-weight: bold;
            color: #2C5282;
        }
        label { 
            display: block; 
            margin-top: 15px; 
            font-weight: bold; 
        }
        input[type="text"], 
        input[type="number"], 
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        .current-image { 
            margin-top: 10px; 
            border: 1px solid #ddd; 
            padding: 10px; 
            border-radius: 4px; 
            text-align: center; 
        }
        .current-image img { 
            max-width: 150px; 
            height: auto; 
            display: block; 
            margin: 0 auto 10px; 
        }
        button[type="submit"] {
            background-color: #38A169; 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
            margin-top: 25px; 
            transition: background-color 0.3s ease;
            width: 100%;
        }
        button[type="submit"]:hover { 
            background-color: #2F855A; 
        }
        .message-success { 
            color: #38A169; 
            font-weight: bold; 
            margin-bottom: 15px; 
            padding: 10px;
            background-color: #E6FFED;
            border-radius: 4px;
        }
        .message-error { 
            color: #E53E3E; 
            font-weight: bold; 
            margin-bottom: 15px; 
            padding: 10px;
            background-color: #FFF5F5;
            border-radius: 4px;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #2C5282;
            text-decoration: none;
            margin-right: 15px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        small {
            color: #666;
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $form_titulo ?></h1>
        <div class="nav-links">
            <a href="produtos.php">← Voltar para a Lista</a>
            <a href="Paineis.php">← Voltar para os Orçamentos</a>
        </div>

        <?php if (!empty($mensagem)): ?>
            <p class="<?= strpos($mensagem, '✅') !== false ? 'message-success' : 'message-error' ?>">
                <?= $mensagem ?>
            </p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="section-title">Informações Básicas</div>
            
            <label for="name">Nome do Painel: *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($produto['name']) ?>" required>

            <label for="price">Preço (R$): *</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($produto['price']) ?>" required>
            
            <label for="images">Imagem do Painel: <?= $id ? '' : '*' ?></label>
            <?php if (!empty($produto['images'])): ?>
                <div class="current-image">
                    <strong>Imagem Atual:</strong><br>
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($produto['images']) ?>" alt="Imagem atual">
                    <p>Substituir Imagem:</p>
                </div>
            <?php endif; ?>
            <input type="file" id="images" name="images" accept="image/*" <?= $id ? '' : 'required' ?>>
            <small>Tamanho máximo: 2MB. Apenas JPG, PNG ou GIF.</small>

            <div class="section-title">Especificações Técnicas</div>

            <label for="marca">Marca:</label>
            <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($produto['marca']) ?>" placeholder="Ex: Marca Padrão">

            <label for="potencia">Potência:</label>
            <input type="text" id="potencia" name="potencia" value="<?= htmlspecialchars($produto['potencia']) ?>" placeholder="Ex: 450W">

            <label for="dimensoes">Dimensões:</label>
            <input type="text" id="dimensoes" name="dimensoes" value="<?= htmlspecialchars($produto['dimensoes']) ?>" placeholder="Ex: 2.0m x 1.0m">

            <label for="eficiencia">Eficiência:</label>
            <input type="text" id="eficiencia" name="eficiencia" value="<?= htmlspecialchars($produto['eficiencia']) ?>" placeholder="Ex: 20.0%">

            <label for="garantia">Garantia:</label>
            <input type="text" id="garantia" name="garantia" value="<?= htmlspecialchars($produto['garantia']) ?>" placeholder="Ex: 25 anos">

            <div class="section-title">Benefícios</div>

            <label for="beneficios">Benefícios do Produto:</label>
            <textarea id="beneficios" name="beneficios" placeholder="Digite cada benefício em uma linha separada&#10;Exemplo:&#10;Alta eficiência energética&#10;Resistente a condições climáticas extremas&#10;Garantia estendida"><?= htmlspecialchars($produto['beneficios']) ?></textarea>
            <small>Digite cada benefício em uma linha separada. Cada linha será exibida como um item da lista.</small>

            <button type="submit"><?= $id ? '💾 Salvar Edição' : '➕ Cadastrar Painel' ?></button>
        </form>
    </div>
</body>
</html>