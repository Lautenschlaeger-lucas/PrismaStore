<?php
/**
 * Configuração - PrismaStore
 * Localização: src/php/config.php
 */

// Impede acesso direto
if (!defined('ALLOW_CONFIG')) {
    define('ALLOW_CONFIG', true);
}

// ============================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prismastore');

// ============================================
// CONFIGURAÇÕES DO SITE
// ============================================
define('BASE_URL', 'http://localhost');
define('SITE_NAME', 'PrismaStore');
define('DEV_MODE', true);

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('America/Sao_Paulo');

// ============================================
// EXIBIR ERROS (desenvolvimento)
// ============================================
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// ============================================
// INICIAR SESSÃO (se ainda não iniciada)
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CONEXÃO COM O BANCO DE DADOS
// ============================================
$pdo = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Testa a conexão
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    // Log do erro
    error_log("Erro PDO: " . $e->getMessage());
    
    // Em desenvolvimento, mostra erro detalhado
    if (DEV_MODE) {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Erro de Conexão</title>
            <style>
                body { font-family: Arial; padding: 40px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
                h1 { color: #e53e3e; margin: 0 0 20px 0; }
                .error-details { background: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; }
                .checklist { background: #f7fafc; padding: 20px; border-radius: 5px; margin: 20px 0; }
                code { background: #edf2f7; padding: 2px 6px; border-radius: 3px; font-size: 14px; }
                .ok { color: #38a169; }
                .error { color: #e53e3e; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>❌ Erro de Conexão com Banco de Dados</h1>
                
                <div class='error-details'>
                    <strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "<br>
                    <strong>Código:</strong> " . $e->getCode() . "
                </div>
                
                <div class='checklist'>
                    <h3>📋 Checklist de Verificação:</h3>
                    <ol>
                        <li>XAMPP/WAMP está rodando? <code>Apache</code> e <code>MySQL</code> devem estar verdes</li>
                        <li>Banco existe? Verifique em <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>
                        <li>Configurações:
                            <ul>
                                <li>Host: <code>" . DB_HOST . "</code></li>
                                <li>Banco: <code>" . DB_NAME . "</code></li>
                                <li>Usuário: <code>" . DB_USER . "</code></li>
                                <li>Senha: " . (empty(DB_PASS) ? '<code>vazia</code>' : '<code>configurada</code>') . "</li>
                            </ul>
                        </li>
                    </ol>
                </div>
                
                <p><strong>Solução Rápida:</strong></p>
                <ol>
                    <li>Inicie o MySQL no XAMPP/WAMP</li>
                    <li>Acesse <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a></li>
                    <li>Verifique se o banco '<strong>prismastore</strong>' existe</li>
                    <li>Recarregue esta página</li>
                </ol>
            </div>
        </body>
        </html>
        ");
    } else {
        die("Erro ao conectar ao banco de dados.");
    }
}

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitize($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ============================================
// VERIFICAÇÃO (apenas em modo desenvolvimento)
// ============================================
if (DEV_MODE && isset($pdo)) {
    // Verifica se as tabelas existem
    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('users', $tables)) {
            error_log("⚠️ Tabela 'usuarios' não encontrada");
        }
        
        if (!in_array('password_resets', $tables)) {
            error_log("⚠️ Tabela 'password_resets' não encontrada");
        }
    } catch(PDOException $e) {
        error_log("Erro ao verificar tabelas: " . $e->getMessage());
    }
}
?>