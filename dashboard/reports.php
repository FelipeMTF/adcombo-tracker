<?php
// Verificar se a sessão já está ativa antes de iniciá-la
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de configuração
require_once('../config.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Obter dados do usuário
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Usuário';

// Definir período de relatório
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$offerId = $_GET['offer_id'] ?? null;

// Obter lista de ofertas para o filtro
try {
    $stmt = $pdo->prepare("SELECT id, offer_id, name FROM offers WHERE user_id = ? ORDER BY name");
    $stmt->execute([$userId]);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $offers = [];
}

// Obter estatísticas de cliques
try {
    $params = [$userId, $startDate . ' 00:00:00', $endDate . ' 23:59:59'];
    $offerFilter = '';
    
    if ($offerId) {
        $offerFilter = "AND c.offer_id = ?";
        $params[] = $offerId;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE(c.created_at) as date,
            COUNT(*) as clicks
        FROM clicks c
        JOIN offers o ON c.offer_id = o.offer_id
        WHERE o.user_id = ? 
        AND c.created_at BETWEEN ? AND ?
        $offerFilter
        GROUP BY DATE(c.created_at)
        ORDER BY date
    ");
    
    $stmt->execute($params);
    $clicksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar dados para o gráfico
    $dates = [];
    $clickCounts = [];
    
    foreach ($clicksData as $data) {
        $dates[] = date('d/m', strtotime($data['date']));
        $clickCounts[] = $data['clicks'];
    }
    
} catch (PDOException $e) {
    $clicksData = [];
    $dates = [];
    $clickCounts = [];
}

// Obter estatísticas de conversões
try {
    $params = [$userId, $startDate . ' 00:00:00', $endDate . ' 23:59:59'];
    $offerFilter = '';
    
    if ($offerId) {
        $offerFilter = "AND c.offer_id = ?";
        $params[] = $offerId;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE(c.created_at) as date,
            COUNT(*) as conversions,
            SUM(c.payout) as revenue
        FROM conversions c
        JOIN offers o ON c.offer_id = o.offer_id
        WHERE o.user_id = ? 
        AND c.created_at BETWEEN ? AND ?
        AND c.status = 'approve'
        $offerFilter
        GROUP BY DATE(c.created_at)
        ORDER BY date
    ");
    
    $stmt->execute($params);
    $conversionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar dados para o gráfico
    $conversionCounts = [];
    $revenueCounts = [];
    
    // Inicializar arrays com zeros
    foreach ($dates as $date) {
        $conversionCounts[$date] = 0;
        $revenueCounts[$date] = 0;
    }
    
    foreach ($conversionData as $data) {
        $date = date('d/m', strtotime($data['date']));
        $conversionCounts[$date] = $data['conversions'];
        $revenueCounts[$date] = $data['revenue'];
    }
    
    // Converter para arrays simples para o gráfico
    $conversionCounts = array_values($conversionCounts);
    $revenueCounts = array_values($revenueCounts);
    
} catch (PDOException $e) {
    $conversionData = [];
    $conversionCounts = [];
    $revenueCounts = [];
}

// Obter resumo geral
try {
    $params = [$userId, $startDate . ' 00:00:00', $endDate . ' 23:59:59'];
    $offerFilter = '';
    
    if ($offerId) {
        $offerFilter = "AND c.offer_id = ?";
        $params[] = $offerId;
    }
    
    // Total de cliques
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM clicks c
        JOIN offers o ON c.offer_id = o.offer_id
        WHERE o.user_id = ? 
        AND c.created_at BETWEEN ? AND ?
        $offerFilter
    ");
    
    $stmt->execute($params);
    $totalClicks = $stmt->fetchColumn();
    
    // Total de conversões
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM conversions c
        JOIN offers o ON c.offer_id = o.offer_id
        WHERE o.user_id = ? 
        AND c.created_at BETWEEN ? AND ?
        AND c.status = 'approve'
        $offerFilter
    ");
    
    $stmt->execute($params);
    $totalConversions = $stmt->fetchColumn();
    
    // Taxa de conversão
    $conversionRate = ($totalClicks > 0) ? round(($totalConversions / $totalClicks) * 100, 2) : 0;
    
    // Receita total
    $stmt = $pdo->prepare("
        SELECT SUM(c.payout) as total
        FROM conversions c
        JOIN offers o ON c.offer_id = o.offer_id
        WHERE o.user_id = ? 
        AND c.created_at BETWEEN ? AND ?
        AND c.status = 'approve'
        $offerFilter
    ");
    
    $stmt->execute($params);
    $totalRevenue = $stmt->fetchColumn() ?: 0;
    
} catch (PDOException $e) {
    $totalClicks = 0;
    $totalConversions = 0;
    $conversionRate = 0;
    $totalRevenue = 0;
}

// Obter últimas conversões
try {
    $params = [$userId];
    $offerFilter = '';
    
    if ($offerId) {
        $offerFilter = "AND c.offer_id = ?";
        $params[] = $offerId;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            o.name as offer_name
        FROM conversions c
        JOIN offers o ON c.offer_id = o.offer_id
        WHERE o.user_id = ?
        $offerFilter
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute($params);
    $recentConversions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $recentConversions = [];
}

// Obter desempenho por oferta
try {
    $params = [$userId, $startDate . ' 00:00:00', $endDate . ' 23:59:59'];
    $offerFilter = '';
    
    if ($offerId) {
        $offerFilter = "AND o.offer_id = ?";
        $params[] = $offerId;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            o.offer_id,
            o.name as offer_name,
            COUNT(DISTINCT cl.id) as clicks,
            COUNT(DISTINCT CASE WHEN cv.status = 'approve' THEN cv.id END) as conversions,
            SUM(CASE WHEN cv.status = 'approve' THEN cv.payout ELSE 0 END) as revenue
        FROM offers o
        LEFT JOIN clicks cl ON o.offer_id = cl.offer_id AND cl.created_at BETWEEN ? AND ?
        LEFT JOIN conversions cv ON o.offer_id = cv.offer_id AND cv.created_at BETWEEN ? AND ?
        WHERE o.user_id = ?
        $offerFilter
        GROUP BY o.offer_id, o.name
        ORDER BY revenue DESC
    ");
    
    $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59', $startDate . ' 00:00:00', $endDate . ' 23:59:59', $userId]);
    $offerPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $offerPerformance = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - AdStrax</title>
    <link rel="icon" type="image/png" href="/public/assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .navbar {
            background-color: var(--primary-color);
            padding: 0.5rem 1rem;
        }
        .stats-card {
            border-radius: 0.35rem;
            border-left: 0.25rem solid;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        .stats-card .card-body {
            padding: 1.25rem;
        }
        .stats-card .value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .stats-card .label {
            font-size: 0.875rem;
            color: var(--secondary-color);
            text-transform: uppercase;
            font-weight: 600;
        }
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.35rem;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
        }
        .dropdown-menu {
            font-size: 0.85rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
        }
        .dropdown-item {
            padding: 0.5rem 1rem;
        }
        .dropdown-item i {
            margin-right: 0.5rem;
            width: 1rem;
            text-align: center;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <img src="/public/assets/images/logo.png" alt="AdStrax" height="40" onerror="this.src='https://via.placeholder.com/120x40?text=AdStrax'">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/offers.php"><i class="fas fa-tag"></i> Ofertas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/dashboard/reports.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/dashboard/profile.php"><i class="fas fa-user-cog"></i> Perfil</a></li>
                            <li><a class="dropdown-item" href="/dashboard/settings.php"><i class="fas fa-cog"></i> Configurações</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-gray-800">Relatórios</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filtros</h5>
            </div>
            <div class="card-body">
                <form method="get" action="/dashboard/reports.php" class="row g-3">
                    <div class="col-md-4">
                        <label for="date_range" class="form-label">Período</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="date_range" name="date_range" value="<?php echo $startDate; ?> - <?php echo $endDate; ?>" readonly>
                            <input type="hidden" name="start_date" id="start_date" value="<?php echo $startDate; ?>">
                            <input type="hidden" name="end_date" id="end_date" value="<?php echo $endDate; ?>">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="offer_id" class="form-label">Oferta</label>
                        <select class="form-select" id="offer_id" name="offer_id">
                            <option value="">Todas as Ofertas</option>
                            <?php foreach ($offers as $offer): ?>
                                <option value="<?php echo htmlspecialchars($offer['offer_id']); ?>" <?php echo ($offerId === $offer['offer_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($offer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i> Filtrar
                        </button>
                        <a href="/dashboard/reports.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo me-2"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card" style="border-left-color: var(--primary-color);">
                    <div class="card-body">
                        <i class="fas fa-mouse-pointer fa-2x text-primary mb-2"></i>
                        <div class="value"><?php echo number_format($totalClicks); ?></div>
                        <div class="label">Cliques</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card" style="border-left-color: var(--success-color);">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                        <div class="value"><?php echo number_format($totalConversions); ?></div>
                        <div class="label">Conversões</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card" style="border-left-color: var(--info-color);">
                    <div class="card-body">
                        <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                        <div class="value"><?php echo $conversionRate; ?>%</div>
                        <div class="label">Taxa de Conversão</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card" style="border-left-color: var(--warning-color);">
                    <div class="card-body">
                        <i class="fas fa-dollar-sign fa-2x text-warning mb-2"></i>
                        <div class="value">R$ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></div>
                        <div class="label">Receita</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Desempenho no Período</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="300"></canvas>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Desempenho por Oferta</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($offerPerformance)): ?>
                            <div class="text-center py-4">
                                <p class="mb-0">Nenhum dado disponível para o período selecionado.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Oferta</th>
                                            <th>Cliques</th>
                                            <th>Conversões</th>
                                            <th>Taxa</th>
                                            <th>Receita</th>
                                            <th>CPC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offerPerformance as $offer): ?>
                                            <?php 
                                                $convRate = ($offer['clicks'] > 0) ? round(($offer['conversions'] / $offer['clicks']) * 100, 2) : 0;
                                                $cpc = ($offer['clicks'] > 0) ? round($offer['revenue'] / $offer['clicks'], 2) : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($offer['offer_name']); ?></td>
                                                <td><?php echo number_format($offer['clicks']); ?></td>
                                                <td><?php echo number_format($offer['conversions']); ?></td>
                                                <td><?php echo $convRate; ?>%</td>
                                                <td>R$ <?php echo number_format($offer['revenue'], 2, ',', '.'); ?></td>
                                                <td>R$ <?php echo number_format($cpc, 2, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Últimas Conversões</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentConversions)): ?>
                            <div class="text-center py-4">
                                <p class="mb-0">Nenhuma conversão registrada.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recentConversions as $conversion): ?>
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($conversion['offer_name']); ?></h6>
                                            <small><?php echo date('d/m/Y H:i', strtotime($conversion['created_at'])); ?></small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">ID: <?
