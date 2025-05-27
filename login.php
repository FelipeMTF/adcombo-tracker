<?php
require_once 'config.php';

// Iniciar sessão
session_start();

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    // Validar campos
    if (empty($email)) {
        $errors[] = "Email é obrigatório";
    }
    
    if (empty($password)) {
        $errors[] = "Senha é obrigatória";
    }
    
    // Se não houver erros, verificar credenciais
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login bem-sucedido, criar sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirecionar para o dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Email ou senha incorretos";
        }
    }
}

// Verificar se o usuário acabou de se registrar
$registered = isset($_GET['registered']) && $_GET['registered'] == 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AdStrax</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="/public/assets/images/logo.png" alt="AdStrax" class="logo mb-4" style="max-height: 80px;">
                            <h2>Acesse sua conta</h2>
                        </div>
                        
                        <?php if ($registered): ?>
                            <div class="alert alert-success">
                                Conta criada com sucesso! Faça login para continuar.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email ?? ''; ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p><a href="forgot-password.php">Esqueceu sua senha?</a></p>
                            <p>Não tem uma conta? <a href="register.php">Registre-se</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
