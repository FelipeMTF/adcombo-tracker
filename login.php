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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            max-width: 180px;
            height: auto;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .btn-primary {
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #ff8e53, #ff6b6b);
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(255, 107, 107, 0.5);
        }
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        .forgot-password a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .forgot-password a:hover {
            color: #fff;
            text-decoration: underline;
        }
        h1 {
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
        }
        h2 {
            font-weight: 600;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .login-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <!-- Substitua pelo caminho do seu logo -->
            <img src="assets/images/logo.png" alt="AdCombo Tracker Logo">
        </div>
        
        <h2 class="text-center mb-4">Acesse sua conta</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        
        <div class="forgot-password">
            <a href="forgot_password.php">Esqueceu sua senha?</a>
        </div>
        
        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> AdCombo Tracker - Todos os direitos reservados
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
