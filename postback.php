<?php
include 'config.php';

// Obter parâmetros da URL
$action = $_GET['action'] ?? '';
$offer_id = $_GET['offer_id'] ?? '';
$click_id = $_GET['click_id'] ?? '';
$status = $_GET['status'] ?? '';
$reason = $_GET['reason'] ?? '';

// Registrar em log para depuração
file_put_contents('postback_log.txt', date('Y-m-d H:i:s') . " - " . json_encode($_GET) . "\n", FILE_APPEND);

// Verificar parâmetros obrigatórios
if (empty($action) || empty($offer_id) || empty($click_id)) {
    http_response_code(400);
    echo "Parâmetros inválidos";
    exit;
}

// Verificar se o clique existe
$stmt = $pdo->prepare("SELECT * FROM clicks WHERE click_id = ? AND offer_id = ?");
$stmt->execute([$click_id, $offer_id]);
$click = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$click) {
    http_response_code(404);
    echo "Clique não encontrado";
    exit;
}

// Verificar se já existe uma conversão para este clique
$stmt = $pdo->prepare("SELECT * FROM conversions WHERE click_id = ? AND status = ?");
$stmt->execute([$click_id, $action]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // Já existe uma conversão deste tipo para este clique
    echo "Conversão já registrada";
    exit;
}

// Processar com base na ação
switch ($action) {
    case 'lead':
        // Registrar lead
        $stmt = $pdo->prepare("INSERT INTO conversions (click_id, offer_id, status, sub_status, timestamp) VALUES (?, ?, 'lead', ?, NOW())");
        $stmt->execute([$click_id, $offer_id, $status]);
        break;
        
    case 'hold':
        // Registrar hold
        $stmt = $pdo->prepare("INSERT INTO conversions (click_id, offer_id, status, timestamp) VALUES (?, ?, 'hold', NOW())");
        $stmt->execute([$click_id, $offer_id]);
        break;
        
    case 'reject':
        // Registrar rejeição
        $stmt = $pdo->prepare("INSERT INTO conversions (click_id, offer_id, status, sub_status, timestamp) VALUES (?, ?, 'reject', ?, NOW())");
        $stmt->execute([$click_id, $offer_id, $reason]);
        break;
        
    case 'sale':
        // Registrar venda
        $stmt = $pdo->prepare("INSERT INTO conversions (click_id, offer_id, status, timestamp) VALUES (?, ?, 'sale', NOW())");
        $stmt->execute([$click_id, $offer_id]);
        break;
        
    default:
        http_response_code(400);
        echo "Ação desconhecida";
        exit;
}

// Responder com sucesso
echo "OK";
?>
