<?php
// Configurações do banco de dados
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'adstrax';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

// Configurações da aplicação
$base_url = getenv('BASE_URL') ?: 'https://adstrax.com.br';
$app_name = 'AdStrax';
$app_version = '1.0.0';
$app_email = 'contato@adstrax.com.br';
$adcombo_api_key = getenv('ADCOMBO_API_KEY') ?: '';

// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Funções auxiliares

// Gerar ID de clique único
function generateClickId() {
    return 'clk_' . uniqid() . '_' . bin2hex(random_bytes(4));
}

// Obter IP do cliente
function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    return $ip;
}

// Obter User Agent
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

// Obter Referrer
function getReferrer() {
    return $_SERVER['HTTP_REFERER'] ?? '';
}

// Função para registrar logs
function logMessage($message, $type = 'info') {
    $logDir = __DIR__ . '/logs';
    
    // Criar diretório de logs se não existir
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type]: $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Iniciar sessão se ainda não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
