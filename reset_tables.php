<?php
include 'config.php';

try {
    // Excluir tabelas existentes
    $pdo->exec("DROP TABLE IF EXISTS conversions");
    $pdo->exec("DROP TABLE IF EXISTS clicks");
    $pdo->exec("DROP TABLE IF EXISTS offers");
    
    // Criar tabela de ofertas
    $pdo->exec("
        CREATE TABLE offers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            offer_id VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            landing_url TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Criar tabela de cliques
    $pdo->exec("
        CREATE TABLE clicks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            click_id VARCHAR(50) NOT NULL UNIQUE,
            offer_id VARCHAR(50) NOT NULL,
            sub_id VARCHAR(255),
            source VARCHAR(255),
            campaign VARCHAR(255),
            keyword VARCHAR(255),
            ip VARCHAR(45),
            user_agent TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (offer_id),
            INDEX (sub_id),
            INDEX (source)
        )
    ");
    
    // Criar tabela de conversões
    $pdo->exec("
        CREATE TABLE conversions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            click_id VARCHAR(50) NOT NULL,
            offer_id VARCHAR(50) NOT NULL,
            status ENUM('lead', 'hold', 'reject', 'sale') NOT NULL,
            sub_status VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (click_id),
            INDEX (offer_id),
            INDEX (status)
        )
    ");
    
    echo "Tabelas recriadas com sucesso!";
} catch (PDOException $e) {
    die("Erro ao recriar tabelas: " . $e->getMessage());
}
?>
