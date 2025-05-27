<?php
// Configurações do banco de dados
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'adcombo_tracker';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

// Configurações da API AdCombo
$api_key = getenv('ADCOMBO_API_KEY') ?: '12b04b4803215aa842838b0a5dc0caeb';
$offer_id = getenv('ADCOMBO_OFFER_ID') ?: '';

// Configurações do tracker
$base_url = getenv('BASE_URL') ?: 'http://localhost';

// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
