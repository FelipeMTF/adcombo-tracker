<?php
// Configurações do banco de dados
$db_host = 'tramway.proxy.rlwy.net';
$db_port = '33459';
$db_name = 'railway';
$db_user = 'root';
$db_pass = 'VJKFHyGJyaAAJEXoMxwDghkmzLJVebKP';

// Configurações da API AdCombo
$api_key = getenv('ADCOMBO_API_KEY') ?: '12b04b4803215aa842838b0a5dc0caeb';

// Configurações do tracker
$base_url = getenv('BASE_URL') ?: 'https://ads-trax.onrender.com';

// Conexão com o banco de dados
try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name";
    
    // Adicionar opções SSL para conexões remotas
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage() . " (Host: $db_host, Port: $db_port, DB: $db_name, User: $db_user)");
}
?>
