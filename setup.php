<?php
include 'config.php';

try {
    // Criar tabela de usuários
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            api_key VARCHAR(100),
            reset_token VARCHAR(64),
            reset_expires DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Criar tabela de campanhas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            status ENUM('active', 'paused', 'archived') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    
    // Atualizar tabela de ofertas para incluir relação com campanhas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS offers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campaign_id INT,
            offer_id VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            landing_url TEXT NOT NULL,
            payout DECIMAL(10,2) DEFAULT 0,
            status ENUM('active', 'paused', 'archived') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
        )
    ");
    
    // Criar tabela de cliques
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clicks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            click_id VARCHAR(50) NOT NULL UNIQUE,
            offer_id VARCHAR(50) NOT NULL,
            sub_id VARCHAR(255),
            source VARCHAR(255),
            campaign VARCHAR(255),
            keyword VARCHAR(255),
            ip VARCHAR(45),
            user_agent TEXT,
            referrer TEXT,
            country VARCHAR(2),
            city VARCHAR(100),
            device VARCHAR(50),
            os VARCHAR(50),
            browser VARCHAR(50),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (offer_id),
            INDEX (sub_id),
            INDEX (source)
        )
    ");
    
    // Criar tabela de conversões
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS conversions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            click_id VARCHAR(50) NOT NULL,
            offer_id VARCHAR(50) NOT NULL,
            transaction_id VARCHAR(100),
            payout DECIMAL(10,2) DEFAULT 0,
            status ENUM('lead', 'hold', 'reject', 'sale') NOT NULL,
            sub_status VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (click_id),
            INDEX (offer_id),
            INDEX (status)
        )
    ");
    
    // Criar usuário admin padrão se não existir
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        $username = 'admin';
        $email = 'admin@adstrax.com.br';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $api_key = bin2hex(random_bytes(16));
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, api_key) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $api_key]);
        
        echo "<p>Usuário admin criado com sucesso!</p>";
        echo "<p>Username: admin</p>";
        echo "<p>Senha: admin123</p>";
        echo "<p>Lembre-se de alterar a senha após o primeiro login.</p>";
    }
    
    echo "<p>Tabelas criadas com sucesso!</p>";
    echo "<p><a href='login.php'>Ir para a página de login</a></p>";
} catch (PDOException $e) {
    die("Erro ao criar tabelas: " . $e->getMessage());
}
?>
