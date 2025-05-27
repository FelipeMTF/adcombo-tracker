<?php
session_start();
include 'config.php';
include 'auth.php';

// Se o usuário já estiver logado, redirecionar para o dashboard
if (isLoggedIn()) {
    header('Location: dashboard/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdStrax - Sistema de Tracking para Afiliados</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 80px 0;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .logo {
            max-width: 300px;
            margin-bottom: 20px;
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .cta-section {
            background-color: var(--light-bg);
            padding: 60px 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
            <img src="/public/assets/images/logo.png" alt="AdStrax" class="logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Recursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Sobre</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light ms-2" href="/login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="container text-center">
            <img src="/public/assets/images/logo.png" alt="AdStrax" class="logo">
            <h1>Sistema de Tracking para Afiliados</h1>
            <p class="lead">Monitore cliques, leads, holds e vendas em um único lugar</p>
            <div class="mt-4">
                <a href="/login.php" class="btn btn-light btn-lg me-2">Login</a>
                <a href="/register.php" class="btn btn-secondary btn-lg">Registrar</a>
            </div>
        </div>
    </div>

    <div class="container py-5" id="features">
        <h2 class="text-center mb-5">Recursos Principais</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Tracking Completo</h3>
                        <p>Acompanhe todo o funil de conversão: cliques, leads, holds, rejeições e vendas.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h3>Dashboard Intuitivo</h3>
                        <p>Visualize suas métricas importantes em tempo real com gráficos e relatórios detalhados.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3>Integração com AdCombo</h3>
                        <p>Sincronização automática com a plataforma AdCombo para dados precisos e atualizados.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3>Gerenciamento de Campanhas</h3>
                        <p>Organize suas ofertas em campanhas para melhor controle e análise de desempenho.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>Relatórios Detalhados</h3>
                        <p>Gere relatórios personalizados por campanha, oferta, período e muito mais.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Design Responsivo</h3>
                        <p>Acesse suas estatísticas de qualquer dispositivo, com interface adaptada para mobile.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="cta-section" id="about">
        <div class="container text-center">
            <h2 class="mb-4">Potencialize seus Resultados como Afiliado</h2>
            <p class="lead mb-4">O AdStrax foi desenvolvido especialmente para afiliados do AdCombo que desejam ter controle total sobre suas campanhas e maximizar seus resultados.</p>
            <a href="/register.php" class="btn btn-primary btn-lg">Comece Agora</a>
        </div>
    </div>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <img src="/public/assets/images/logo.png" alt="AdStrax" height="40" class="mb-3">
                    <p>Sistema de tracking personalizado para afiliados do AdCombo.</p>
                </div>
                <div class="col-md-3">
                    <h5>Links Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="/login.php" class="text-white">Login</a></li>
                        <li><a href="/register.php" class="text-white">Registrar</a></li>
                        <li><a href="#features" class="text-white">Recursos</a></li>
                        <li><a href="#about" class="text-white">Sobre</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contato</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> contato@adstrax.com.br</li>
                        <li><i class="fas fa-globe me-2"></i> www.adstrax.com.br</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-3 bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 AdStrax. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
