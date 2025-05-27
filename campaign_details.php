<?php
include 'auth.php';
include 'config.php';

// Verificar se o ID da campanha foi fornecido
if (!isset($_GET['id'])) {
    header('Location: campaigns.php');
    exit;
}

$campaign_id = $_GET['id'];

// Buscar detalhes da campanha
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt->execute([$campaign_id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaign) {
    header('Location: campaigns.php');
    exit;
}

// Buscar estatísticas da campanha
$stmt = $pdo->prepare("SELECT 
                      (SELECT COUNT(*) FROM offers WHERE campaign_id = ?) AS offer_count,
                      (SELECT COUNT(*) FROM clicks cl JOIN offers o ON cl.offer_id = o.offer_id WHERE o.campaign_id = ?) AS click_count,
                      (SELECT COUNT(*) FROM conversions cv JOIN offers o ON cv.offer_id = o.offer_id WHERE o.campaign_id = ? AND cv.action_type = 'lead') AS lead_count,
                      (SELECT COUNT(*) FROM conversions cv JOIN
