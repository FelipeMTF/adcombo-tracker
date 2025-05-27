<?php
include 'config.php';

// Obter parâmetros do postback
$clickId = $_GET['transaction_id'] ?? '';
$status = strtolower($_GET['status'] ?? '');
$subStatus = $_GET['sub_status'] ?? '';
$payout = floatval($_GET['payout'] ?? 0);

// Validar parâmetros
if (empty($clickId)) {
    http_response_code(400);
    die("Erro: ID de transação não especificado.");
}

if (empty($status)) {
    http_response_code(400);
    die("Erro: Status não especificado.");
}

// Mapear status do AdCombo para nosso sistema
switch ($status) {
    case 'confirmed':
    case 'confirmed_for_initial':
        $status = 'lead';
        break;
    case 'confirmed_for_complete':
    case 'confirmed_for_delivery':
        $status = 'sale';
        break;
    case 'hold':
    case 'hold_for_complete':
    case 'hold_for_delivery':
        $status = 'hold';
        break;
    case 'rejected':
    case 'rejected_for_complete':
    case 'rejected_for_delivery':
        $status = 'reject';
        break;
    default:
        // Status desconhecido, usar como está
        break;
}

try {
    // Verificar se o clique existe
    $stmt = $pdo->prepare("SELECT * FROM clicks WHERE click_id = ?");
    $stmt->execute([$clickId]);
    $click = $stmt->fetch();
    
    if (!$click) {
        http_response_code(404);
        die("Erro: Clique não encontrado.");
    }
    
    $offerId = $click['offer_id'];
    
    // Verificar se já existe uma conversão para este clique
    $stmt = $pdo->prepare("SELECT * FROM conversions WHERE click_id = ?");
    $stmt->execute([$clickId]);
    $existingConversion = $stmt->fetch();
    
    if ($existingConversion) {
        // Atualizar conversão existente
        $stmt = $pdo->prepare("
            UPDATE conversions 
            SET status = ?, sub_status = ?, payout = ?, timestamp = NOW()
            WHERE click_id = ?
        ");
        
        $stmt->execute([
            $status,
            $subStatus,
            $payout,
            $clickId
        ]);
        
        echo "Conversão atualizada com sucesso.";
    } else {
        // Inserir nova conversão
        $stmt = $pdo->prepare("
            INSERT INTO conversions 
            (click_id, offer_id, status, sub_status, payout) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $clickId,
            $offerId,
            $status,
            $subStatus,
            $payout
        ]);
        
        echo "Conversão registrada com sucesso.";
    }
    
} catch (PDOException $e) {
    // Log do erro
    error_log("Erro no postback: " . $e->getMessage());
    
    http_response_code(500);
    die("Erro interno no servidor.");
}
?>
