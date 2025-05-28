<?php
include 'config.php';

// Função para registrar logs específicos de postback
function logPostback($message, $data = []) {
    $logDir = __DIR__ . '/logs';
    
    // Criar diretório de logs se não existir
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/postback_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp]: $message - " . json_encode($data) . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Obter parâmetros do postback
$clickId = $_GET['transaction_id'] ?? '';
$status = strtolower($_GET['status'] ?? '');
$payout = floatval($_GET['payout'] ?? 0);

// Registrar todos os parâmetros recebidos
logPostback("Postback recebido", $_GET);

// Validar parâmetros
if (empty($clickId)) {
    http_response_code(400);
    logPostback("Erro: ID de transação não especificado", $_GET);
    die("Erro: ID de transação não especificado.");
}

if (empty($status)) {
    http_response_code(400);
    logPostback("Erro: Status não especificado", $_GET);
    die("Erro: Status não especificado.");
}

// Mapear status do AdCombo para nosso sistema
$mappedStatus = $status;
switch ($status) {
    case 'confirmed':
    case 'confirmed_for_initial':
        $mappedStatus = 'lead';
        break;
    case 'confirmed_for_complete':
    case 'confirmed_for_delivery':
        $mappedStatus = 'sale';
        break;
    case 'hold':
    case 'hold_for_complete':
    case 'hold_for_delivery':
        $mappedStatus = 'hold';
        break;
    case 'rejected':
    case 'rejected_for_complete':
    case 'rejected_for_delivery':
        $mappedStatus = 'reject';
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
        logPostback("Erro: Clique não encontrado", ['click_id' => $clickId]);
        die("Erro: Clique não encontrado.");
    }
    
    $offerId = $click['offer_id'];
    
    // Verificar se já existe uma conversão para este clique com o mesmo status
    $stmt = $pdo->prepare("SELECT * FROM conversions WHERE click_id = ? AND status = ?");
    $stmt->execute([$clickId, $mappedStatus]);
    $existingConversion = $stmt->fetch();
    
    if ($existingConversion) {
        // Atualizar conversão existente
        $stmt = $pdo->prepare("
            UPDATE conversions 
            SET payout = ?, updated_at = NOW()
            WHERE click_id = ? AND status = ?
        ");
        
        $stmt->execute([
            $payout,
            $clickId,
            $mappedStatus
        ]);
        
        logPostback("Conversão atualizada", [
            'click_id' => $clickId,
            'status' => $mappedStatus,
            'payout' => $payout
        ]);
        
        echo "OK";
    } else {
        // Inserir nova conversão
        $stmt = $pdo->prepare("
            INSERT INTO conversions 
            (click_id, offer_id, status, payout) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $clickId,
            $offerId,
            $mappedStatus,
            $payout
        ]);
        
        logPostback("Nova conversão registrada", [
            'click_id' => $clickId,
            'offer_id' => $offerId,
            'status' => $mappedStatus,
            'payout' => $payout
        ]);
        
        echo "OK";
    }
    
} catch (PDOException $e) {
    // Log do erro
    logPostback("Erro no postback", ['error' => $e->getMessage()]);
    
    http_response_code(500);
    die("Erro interno no servidor.");
}
?>
