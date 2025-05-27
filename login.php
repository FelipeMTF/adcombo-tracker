<?php
session_start();
include 'config.php';

// Verificar se já está logado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Verificar credenciais
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Login bem-sucedido
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Redirecionar para o dashboard
        header('Location: index.php');
        exit;
    } else {
        $error_message = "Email ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AdCombo Tracker</title>
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
        .login-container {
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
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #ff6600;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>AdCombo Tracker</h1>
        </div>
        
        <h2 class="text-center mb-4">Acesse sua conta</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <p class="text-center mb-4">Se você já possui uma conta, preencha seus dados de acesso à plataforma.</p>
        
        <form method="post">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Acessar sua conta →</button>
        </form>
        
        <div class="forgot-password">
            <a href="forgot_password.php">Esqueceu sua senha?</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
