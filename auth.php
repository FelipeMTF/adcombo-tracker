<?php
session_start();

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função para redirecionar para login se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Função para fazer login
function login($username, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Login bem-sucedido
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['api_key'] = $user['api_key'];
        
        // Registrar login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return true;
    }
    
    return false;
}

// Função para fazer logout
function logout() {
    // Limpar todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir a sessão
    session_destroy();
    
    // Redirecionar para a página de login
    header('Location: /login.php');
    exit;
}

// Função para obter dados do usuário atual
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para atualizar senha
function updatePassword($userId, $newPassword, $pdo) {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashedPassword, $userId]);
}

// Função para gerar token de recuperação de senha
function generatePasswordResetToken($email, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        return $token;
    }
    
    return false;
}

// Função para verificar token de recuperação de senha
function verifyPasswordResetToken($token, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para resetar senha com token
function resetPasswordWithToken($token, $newPassword, $pdo) {
    $user = verifyPasswordResetToken($token, $pdo);
    
    if ($user) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        return $stmt->execute([$hashedPassword, $user['id']]);
    }
    
    return false;
}

// Processar logout se solicitado
if (isset($_GET['logout'])) {
    logout();
}
?>
