<?php
require_once 'config.php';

// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Configuração do Banco de Dados</h1>";

try {
    // Criar tabela de ofertas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS offers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            offer_id VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            landing_url VARCHAR(255) NOT NULL,
            payout DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY (offer_id)
        )
    ");
    echo "<p>✅ Tabela 'offers' criada com sucesso!</p>";

    // Criar tabela de redefinição de senha
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(100) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (email)
        )
    ");
    echo "<p>✅ Tabela 'password_resets' criada com sucesso!</p>";
    
    // Criar tabela de usuários
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (email)
        )
    ");
    echo "<p>✅ Tabela 'users' criada com sucesso!</p>";
    
    // Criar tabela de cliques
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clicks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            click_id VARCHAR(50) NOT NULL,
            offer_id VARCHAR(50) NOT NULL,
            sub_id VARCHAR(100),
            source VARCHAR(100),
            campaign VARCHAR(100),
            keyword VARCHAR(100),
            ip VARCHAR(45) NOT NULL,
            user_agent TEXT,
            referrer TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (click_id)
        )
    ");
    echo "<p>✅ Tabela 'clicks' criada com sucesso!</p>";
    
    // Criar tabela de conversões
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS conversions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            click_id VARCHAR(50) NOT NULL,
            offer_id VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL,
            payout DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY (click_id, status)
        )
    ");
    echo "<p>✅ Tabela 'conversions' criada com sucesso!</p>";
    
    // Criar tabela de configurações
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL,
            setting_value TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY (setting_key)
        )
    ");
    echo "<p>✅ Tabela 'settings' criada com sucesso!</p>";
    
    // Inserir configurações padrão
    $stmt = $pdo->prepare("
        INSERT INTO settings (setting_key, setting_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    
    $defaultSettings = [
        ['adcombo_api_key', $adcombo_api_key],
        ['base_url', $base_url],
        ['app_name', $app_name],
        ['app_version', $app_version],
        ['app_email', $app_email]
    ];
    
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }
    
    echo "<p>✅ Configurações padrão inseridas com sucesso!</p>";
    
    echo "<p style='color:green;font-weight:bold;'>✅ Configuração do banco de dados concluída com sucesso!</p>";
    
    echo "<p><a href='index.php' style='display:inline-block; background-color:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Ir para a página inicial</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>❌ Erro na configuração do banco de dados: " . $e->getMessage() . "</p>";
}
?>
