<?php
include 'config.php';

// Obter parâmetros da URL
$offerId = $_GET['offer'] ?? '';
$subId = $_GET['sub_id'] ?? '';
$source = $_GET['source'] ?? '';
$campaign = $_GET['campaign'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// Validar oferta
if (empty($offerId)) {
    die("Erro: ID da oferta não especificado.");
}

try {
    // Verificar se a oferta existe
    $stmt = $pdo->prepare("SELECT * FROM offers WHERE offer_id = ?");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch();
    
    if (!$offer) {
        die("Erro: Oferta não encontrada.");
    }
    
    // Gerar ID de clique único
    $clickId = generateClickId();
    
    // Obter informações do cliente
    $ip = getClientIp();
    $userAgent = getUserAgent();
    $referrer = getReferrer();
    
    // Registrar clique no banco de dados
    $stmt = $pdo->prepare("
        INSERT INTO clicks 
        (click_id, offer_id, sub_id, source, campaign, keyword, ip, user_agent, referrer) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $clickId,
        $offerId,
        $subId,
        $source,
        $campaign,
        $keyword,
        $ip,
        $userAgent,
        $referrer
    ]);
    
    // Log do clique
    logMessage("Novo clique registrado: $clickId para oferta $offerId");
    
    // Construir URL de redirecionamento para a landing page
    $landingUrl = $offer['landing_url'];
    
    // Adicionar parâmetros à URL da landing page
    $separator = (strpos($landingUrl, '?') !== false) ? '&' : '?';
    $redirectUrl = $landingUrl . $separator . "transaction_id=" . urlencode($clickId);
    
    // Adicionar outros parâmetros se existirem
    if (!empty($subId)) {
        $redirectUrl .= "&sub_id=" . urlencode($subId);
    }
    
    if (!empty($source)) {
        $redirectUrl .= "&source=" . urlencode($source);
    }
    
    if (!empty($campaign)) {
        $redirectUrl .= "&campaign=" . urlencode($campaign);
    }
    
    if (!empty($keyword)) {
        $redirectUrl .= "&keyword=" . urlencode($keyword);
    }
    
    // Redirecionar para a landing page
    header("Location: $redirectUrl");
    exit;
    
} catch (PDOException $e) {
    // Log do erro
    logMessage("Erro no tracking: " . $e->getMessage(), 'error');
    
    // Redirecionar para a landing page mesmo com erro
    if (isset($offer) && isset($offer['landing_url'])) {
        header("Location: " . $offer['landing_url']);
        exit;
    } else {
        die("Erro no sistema de tracking. Por favor, tente novamente mais tarde.");
    }
}
?>
