<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão com o Banco de Dados</h1>";

// Usar a URL pública do MySQL
$mysql_url = getenv('MYSQL_PUBLIC_URL') ?: 'mysql://root:VJKFHyGJyaAAJEXoMxwDghkmzLJVebKP@tramway.proxy.rlwy.net:33459/railway';
echo "<p><strong>MySQL URL:</strong> " . $mysql_url . "</p>";

// Parse da URL de conexão
$url_parts = parse_url($mysql_url);
$db_host = $url_parts['host'];
$db_port = $url_parts['port'];
$db_user = $url_parts['user'];
$db_pass = $url_parts['pass'];
$db_name = ltrim($url_parts['path'], '/');

echo "<p><strong>Variáveis de Conexão:</strong></p>";
echo "<ul>";
echo "<li>Host: $db_host</li>";
echo "<li>Port: $db_port</li>";
echo "<li>Database: $db_name</li>";
echo "<li>User: $db_user</li>";
echo "</ul>";

try {
    echo "<p>Tentando conectar ao banco de dados...</p>";
    
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    echo "<p>DSN: $dsn</p>";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
    echo "<p style='color:green;font-weight:bold;'>✅ Conexão bem-sucedida!</p>";
    
    // Verificar se as tabelas existem
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Tabelas no banco de dados:</strong></p>";
    echo "<ul>";
    if (empty($tables)) {
        echo "<li>Nenhuma tabela encontrada. Execute o setup.php para criar as tabelas.</li>";
    } else {
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
    }
    echo "</ul>";
    
    echo "<p><a href='setup.php' style='display:inline-block; background-color:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Executar Setup</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
    
    echo "<h2>Possíveis soluções:</h2>";
    echo "<ol>";
    echo "<li>Verifique se a URL do MySQL está correta</li>";
    echo "<li>Confirme se o banco de dados MySQL no Railway está em execução</li>";
    echo "<li>Verifique se o IP do Render está na lista de permissões do Railway (se aplicável)</li>";
    echo "<li>Tente reiniciar o serviço no Render</li>";
    echo "</ol>";
}
?>
