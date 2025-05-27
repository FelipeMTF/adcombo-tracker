<?php
session_start();
include 'config.php';

// Verificar se já está logado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Processar solicitação de recuperação de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // Verificar se o email existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Gerar token de recuperação
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Salvar token no banco de dados
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        // Enviar email (simulado)
        $reset_link = $base_url . "/reset_password.php?token=$token";
        
        // Exibir mensagem de sucesso
        $success_message = "Um link de recuperação foi enviado para o seu email.";
    } else {
        $error_message = "Email não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - AdCombo Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-container {
            background-color: #222;
            border-radius: 10px;
            padding: 30px;
            max-width: 450px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 200px;
        }
        .form-control {
            background-color: #333;
            border: none;
            color: #fff;
            padding: 12px;
        }
        .form-control:focus {
            background-color: #444;
            color: #fff;
            box-shadow: none;
        }
        .btn-primary {
            background-color: #ff6600;
            border: none;
            padding: 12px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #ff8533;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #ff6600;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="logo">
            <h1>AdCombo Tracker</h1>
        </div>
        
        <h2 class="text-center mb-4">Recuperar Senha</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
            <div class="login-link mt-3">
                <a href="login.php">Voltar para o login</a>
            </div>
        <?php else: ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <p class="text-center mb-4">Digite seu email para receber um link de recuperação de senha.</p>
            
            <form method="post">
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Enviar link de recuperação</button>
            </form>
            
            <div class="login-link">
                <a href="login.php">Voltar para o login</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
