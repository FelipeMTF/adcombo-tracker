<?php
// Configurações do banco de dados
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_port = getenv('MYSQLPORT') ?: '3306';
$db_name = getenv('MYSQL_DATABASE') ?: 'railway';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQL_ROOT_PASSWORD') ?: '';

// Configurações da API AdCombo
$api_key = getenv('ADCOMBO_API_KEY') ?: '12b04b4803215aa842838b0a5dc0caeb';

// Configurações do tracker
$base_url = getenv('BASE_URL') ?: 'http://localhost';

// Conexão com o banco de dados
try {
    $host = $db_host;
    // Se o host contém variáveis de substituição, use o domínio público
    if (strpos($host, '${{') !== false) {
        $host = getenv('RAILWAY_TCP_PROXY_DOMAIN') ?: 'localhost';
        $db_port = getenv('RAILWAY_TCP_PROXY_PORT') ?: $db_port;
    }
    
    $dsn = "mysql:host=$host;port=$db_port;dbname=$db_name";
    
    // Adicionar opções SSL para conexões remotas
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
