<?php
require_once 'config.php';

// Capturar parâmetros do postback
$click_id = isset($_GET['clickid']) ? $_GET['clickid'] : '';
$trans_id = isset($_GET['trans_id']) ? $_GET['trans_id'] : '';
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
$revenue = isset($_GET['revenue']) ? $_GET['revenue'] : 0.00;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$timestamp = date('Y-m-d H:i:s');

// Verificar se o click_id existe
$stmt = $pdo->prepare("SELECT click_id FROM clicks WHERE click_id = ?");
$stmt->execute([$click_id]);
if ($stmt->fetch()) {
    // Inserir conversão
    $stmt = $pdo->prepare("INSERT INTO conversions (click_id, trans_id, order_id, revenue, status, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$click_id, $trans_id, $order_id, $revenue, $status, $timestamp]);
    echo "Postback registrado com sucesso";
} else {
    echo "Click ID não encontrado";
}
?>
