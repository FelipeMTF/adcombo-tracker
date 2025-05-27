<?php
require_once 'config.php';

// Criar tabela de cliques
$pdo->exec("CREATE TABLE IF NOT EXISTS clicks (
    click_id VARCHAR(255) PRIMARY KEY,
    gclid VARCHAR(255),
    ad_id VARCHAR(255),
    site_id VARCHAR(255),
    traffic_source VARCHAR(255),
    utm_source VARCHAR(255),
    utm_medium VARCHAR(255),
    utm_campaign VARCHAR(255),
    timestamp DATETIME,
    ip_address VARCHAR(45),
    user_agent TEXT
)");

// Criar tabela de conversões
$pdo->exec("CREATE TABLE IF NOT EXISTS conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    click_id VARCHAR(255),
    trans_id VARCHAR(255),
    order_id VARCHAR(255),
    revenue DECIMAL(10,2),
    status VARCHAR(50),
    timestamp DATETIME,
    FOREIGN KEY (click_id) REFERENCES clicks(click_id)
)");

echo "Tabelas criadas com sucesso!";
?>
