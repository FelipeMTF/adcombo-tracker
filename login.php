<?php
// Verificar se a sessão já está ativa antes de iniciá-la
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    header("Location: /dashboard/index.php");
    exit;
}

// Função para sanitizar input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
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
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login bem-sucedido, criar sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirecionar para o dashboard
                header("Location: /dashboard/index.php");
                exit;
            } else {
                $errors[] = "Email ou senha incorretos";
            }
        } catch (PDOException $e) {
            $errors[] = "Erro ao fazer login: " . $e->getMessage();
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="/public/assets/images/favicon.ico" type="image/x-icon">
</head>
<body class="bg-primary">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-6">
                <div class="text-center mb-4 d-md-none">
                    <img src="/public/assets/images/logo.png" alt="AdStrax" class="img-fluid" style="max-width: 200px;">
                </div>
                <div class="card shadow">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="/public/assets/images/logo.png" alt="AdStrax" class="img-fluid mb-4" style="max-height: 60px;">
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
                <div class="text-center mt-3 text-white">
                    <p>© 2025 AdStrax - Todos os direitos reservados</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
