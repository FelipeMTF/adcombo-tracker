<?php
include 'config.php';

// Obter parâmetros da URL
$offer_id = $_GET['offer_id'] ?? null;
$sub_id = $_GET['sub_id'] ?? '';
$source = $_GET['source'] ?? '';
$campaign = $_GET['campaign'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// Verificar se a oferta existe
$stmt = $pdo->prepare("SELECT * FROM offers WHERE offer_id = ?");
$stmt->execute([$offer_id]);
$offer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$offer) {
    die("Oferta não encontrada");
}

// Gerar ID de clique único
$click_id = uniqid();

// Registrar clique
$stmt = $pdo->prepare("INSERT INTO clicks (click_id, offer_id, sub_id, source, campaign, keyword, ip, user_agent, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->execute([
    $click_id,
    $offer_id,
    $sub_id,
    $source,
    $campaign,
    $keyword,
    $_SERVER['REMOTE_ADDR'],
    $_SERVER['HTTP_USER_AGENT']
]);

// Construir URL de redirecionamento
$redirect_url = $offer['landing_url'];
$redirect_url .= (strpos($redirect_url, '?') !== false) ? '&' : '?';
$redirect_url .= "click_id=$click_id";

// Redirecionar para a landing page
header("Location: $redirect_url");
exit;
?>
