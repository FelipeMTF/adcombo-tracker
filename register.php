<?php
session_start();
include 'config.php';

// Verificar se já está logado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Processar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verificar se as senhas coincidem
    if ($password !== $confirm_password) {
        $error_message = "As senhas não coincidem.";
    } else {
        // Verificar se o email já está em uso
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_user) {
            $error_message = "Este email já está em uso.";
        } else {
            // Criar novo usuário
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password]);
            
            // Redirecionar para a página de login
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - AdCombo Tracker</title>
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
        .register-container {
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
    <div class="register-container">
        <div class="logo">
            <h1>AdCombo Tracker</h1>
        </div>
        
        <h2 class="text-center mb-4">Criar uma conta</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <p class="text-center mb-4">Preencha os campos abaixo para criar sua conta.</p>
        
        <form method="post">
            <div class="mb-3">
                <input type="text" class="form-control" name="name" placeholder="Nome completo" required>
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Senha" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirmar senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Criar conta</button>
        </form>
        
        <div class="login-link">
            <a href="login.php">Já tem uma conta? Faça login</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
