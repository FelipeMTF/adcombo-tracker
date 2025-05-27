<?php
require_once 'config.php';

// Capturar parâmetros da URL
$click_id = isset($_GET['clickid']) ? $_GET['clickid'] : uniqid();
$gclid = isset($_GET['gclid']) ? $_GET['gclid'] : '';
$ad_id = isset($_GET['adId']) ? $_GET['adId'] : '';
$site_id = isset($_GET['siteId']) ? $_GET['siteId'] : '';
$traffic_source = isset($_GET['source']) ? $_GET['source'] : 'google';
$utm_source = isset($_GET['utm_source']) ? $_GET['utm_source'] : 'google';
$utm_medium = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : 'cpc';
$utm_campaign = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : '';
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$timestamp = date('Y-m-d H:i:s');

// Inserir clique no banco
$stmt = $pdo->prepare("INSERT INTO clicks (click_id, gclid, ad_id, site_id, traffic_source, utm_source, utm_medium, utm_campaign, timestamp, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$click_id, $gclid, $ad_id, $site_id, $traffic_source, $utm_source, $utm_medium, $utm_campaign, $timestamp, $ip_address, $user_agent]);

// Redirecionar para a landing page com os parâmetros
$landing_url = getenv('LANDING_URL') ?: 'https://sua-landing-page.com';
$redirect_url = $landing_url . "?clickid=$click_id&gclid=$gclid&utm_source=$utm_source&utm_medium=$utm_medium&utm_campaign=$utm_campaign";
header("Location: $redirect_url");
exit;
?>
