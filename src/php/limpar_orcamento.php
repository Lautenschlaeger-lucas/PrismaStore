<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o array de orçamento existe e o destrói
if (isset($_SESSION['orcamento'])) {
    unset($_SESSION['orcamento']);
    $mensagem = "✅ Seu orçamento foi limpo com sucesso.";
} else {
    $mensagem = "⚠️ O orçamento já estava vazio.";
}

// Redireciona para a página de orçamento (ou Paineis.php)
header("Location: orcamento.php?msg=" . urlencode($mensagem));
exit;

// Opcional: Se quiser redirecionar para a página de produtos:
// header("Location: Paineis.php?msg=" . urlencode($mensagem));
// exit;
?>