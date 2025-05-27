<?php
include 'config.php';

try {
    // Remover tabelas existentes
    $pdo->exec("DROP TABLE IF EXISTS conversions");
    $pdo->exec("DROP TABLE IF EXISTS clicks");
    $pdo->exec("DROP TABLE IF EXISTS offers");
    $pdo->exec("DROP TABLE IF EXISTS domains");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("DROP TABLE IF EXISTS campaigns");
    
    // Criar tabela de usuários
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        reset_token VARCHAR(64),
        reset_expires DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Criar usuário padrão (senha: admin123)
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (name, email, password) VALUES ('Admin', 'admin@example.com', '$default_password')");
    
    // Criar tabela de campanhas
    $pdo->exec("CREATE TABLE campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('active', 'paused', 'completed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Criar tabela de ofertas
    $pdo->exec("CREATE TABLE offers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        offer_id VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        landing_url TEXT NOT NULL,
        campaign_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL
    )");
    
    // Criar tabela de cliques
    $pdo->exec("CREATE TABLE clicks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        click_id VARCHAR(50) NOT NULL UNIQUE,
        offer_id VARCHAR(50) NOT NULL,
        sub_id VARCHAR(255),
        utm_source VARCHAR(255),
        utm_medium VARCHAR(255),
        utm_campaign VARCHAR(255),
        utm_content VARCHAR(255),
        utm_term VARCHAR(255),
        ip_address VARCHAR(45),
        user_agent TEXT,
        country VARCHAR(2),
        device_type ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (offer_id) REFERENCES offers(offer_id)
    )");
    
    // Criar tabela de conversões
    $pdo->exec("CREATE TABLE conversions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        click_id VARCHAR(50) NOT NULL,
        offer_id VARCHAR(50) NOT NULL,
        action_type ENUM('lead', 'hold', 'reject', 'sale') NOT NULL,
        status VARCHAR(50),
        reason VARCHAR(50),
        revenue DECIMAL(10,2) DEFAULT 0,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (click_id) REFERENCES clicks(click_id),
        FOREIGN KEY (offer_id) REFERENCES offers(offer_id)
    )");
    
    // Criar tabela de domínios
    $pdo->exec("CREATE TABLE domains (
        id INT AUTO_INCREMENT PRIMARY KEY,
        domain_name VARCHAR(255) NOT NULL UNIQUE,
        domain_type ENUM('gate', 'prelanding', 'landing', 'iframe') NOT NULL,
        cname_value VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Criar campanha de exemplo
    $pdo->exec("INSERT INTO campaigns (name, description, status) VALUES 
        ('Campanha Google Ads', 'Campanha principal no Google Ads', 'active'),
        ('Campanha Facebook', 'Campanha de teste no Facebook', 'active')");
    
    echo "Tabelas recriadas com sucesso!";
} catch (PDOException $e) {
    die("Erro ao recriar tabelas: " . $e->getMessage());
}
