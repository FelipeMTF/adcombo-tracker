<?php
// Usar a URL pública do MySQL
$mysql_url = getenv('MYSQL_PUBLIC_URL') ?: 'mysql://root:VJKFHyGJyaAAJEXoMxwDghkmzLJVebKP@tramway.proxy.rlwy.net:33459/railway';

// Parse da URL de conexão
$url_parts = parse_url($mysql_url);
$db_host = $url_parts['host'];
$db_port = $url_parts['port'];
$db_user = $url_parts['user'];
$db_pass = $url_parts['pass'];
$db_name = ltrim($url_parts['path'], '/');

// Configurações do AdCombo
$adcombo_api_key = getenv('ADCOMBO_API_KEY') ?: '';
$adcombo_api_url = 'https://api.adcombo.com/api/v2/';

// URL base do site
$base_url = getenv('BASE_URL') ?: 'https://adstrax.com.br';

// Configurações gerais
$app_name = 'AdStrax';
$app_version = '1.0.0';
$app_email = 'contato@adstrax.com.br';

// Conexão com o banco de dados
try {
    // Use a string de conexão completa com a porta
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    
    // Opções adicionais para a conexão
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // Aumentei o timeout para 5 segundos
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
} catch (PDOException $e) {
    // Em produção, não exibir a mensagem de erro detalhada
    if (getenv('APP_ENV') === 'production') {
        die("Erro na conexão com o banco de dados. Por favor, tente novamente mais tarde.");
    } else {
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
}

// Funções utilitárias
function generateClickId() {
    return uniqid('clk_', true);
}

function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

function getReferrer() {
    return $_SERVER['HTTP_REFERER'] ?? '';
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateTrackingUrl($offerId, $subId = '', $source = '', $campaign = '', $keyword = '') {
    global $base_url;
    
    $url = "$base_url/track.php?offer=$offerId";
    
    if (!empty($subId)) {
        $url .= "&sub_id=" . urlencode($subId);
    }
    
    if (!empty($source)) {
        $url .= "&source=" . urlencode($source);
    }
    
    if (!empty($campaign)) {
        $url .= "&campaign=" . urlencode($campaign);
    }
    
    if (!empty($keyword)) {
        $url .= "&keyword=" . urlencode($keyword);
    }
    
    return $url;
}

function getAdComboOfferUrl($offerId, $clickId) {
    global $adcombo_api_key;
    
    return "https://adcombo.com/api/v2/order.confirmed?api_key=$adcombo_api_key&offer_id=$offerId&transaction_id=$clickId";
}

function callAdComboApi($endpoint, $params = []) {
    global $adcombo_api_key, $adcombo_api_url;
    
    $params['api_key'] = $adcombo_api_key;
    $url = $adcombo_api_url . $endpoint . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
?>
