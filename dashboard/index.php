<?php
// Verificar se a sessão já está ativa antes de iniciá-la
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Resto do código permanece o mesmo
?>

// Obter estatísticas
$userId = $_SESSION['user_id'];

// Total de campanhas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE user_id = ?");
$stmt->execute([$userId]);
$totalCampaigns = $stmt->fetchColumn();

// Total de ofertas
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM offers o
    JOIN campaigns c ON o.campaign_id = c.id
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$totalOffers = $stmt->fetchColumn();

// Total de cliques (últimos 30 dias)
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clicks cl
    JOIN offers o ON cl.offer_id = o.offer_id
    JOIN campaigns c ON o.campaign_id = c.id
    WHERE c.user_id = ? AND cl.timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$stmt->execute([$userId]);
$totalClicks = $stmt->fetchColumn();

// Total de conversões (últimos 30 dias)
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM conversions cv
    JOIN offers o ON cv.offer_id = o.offer_id
    JOIN campaigns c ON o.campaign_id = c.id
    WHERE c.user_id = ? AND cv.timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$stmt->execute([$userId]);
$totalConversions = $stmt->fetchColumn();

// Taxa de conversão
$conversionRate = ($totalClicks > 0) ? round(($totalConversions / $totalClicks) * 100, 2) : 0;

// Receita total (últimos 30 dias)
$stmt = $pdo->prepare("
    SELECT SUM(cv.payout) FROM conversions cv
    JOIN offers o ON cv.offer_id = o.offer_id
    JOIN campaigns c ON o.campaign_id = c.id
    WHERE c.user_id = ? AND cv.status = 'sale' AND cv.timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$stmt->execute([$userId]);
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Últimas conversões
$stmt = $pdo->prepare("
    SELECT cv.*, o.name as offer_name FROM conversions cv
    JOIN offers o ON cv.offer_id = o.offer_id
    JOIN campaigns c ON o.campaign_id = c.id
    WHERE c.user_id = ?
    ORDER BY cv.timestamp DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$recentConversions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dados para gráfico de cliques por dia (últimos 7 dias)
$stmt = $pdo->prepare("
    SELECT DATE(cl.timestamp) as date, COUNT(*) as count
    FROM clicks cl
    JOIN offers o ON cl.offer_id = o.offer_id
    JOIN campaigns c ON o.campaign_id = c.id
    WHERE c.user_id = ? AND cl.timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(cl.timestamp)
    ORDER BY date
");
$stmt->execute([$userId]);
$clicksData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatar dados para o gráfico
$dates = [];
$clickCounts = [];
foreach ($clicksData as $data) {
    $dates[] = date('d/m', strtotime($data['date']));
    $clickCounts[] = $data['count'];
}
$chartData = [
    'labels' => $dates,
    'data' => $clickCounts
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AdStrax</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <img src="/public/assets/images/logo.png" alt="AdStrax" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/campaigns.php"><i class="fas fa-bullhorn"></i> Campanhas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/offers.php"><i class="fas fa-tag"></i> Ofertas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/reports.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/dashboard/profile.php"><i class="fas fa-user-cog"></i> Perfil</a></li>
                            <li><a class="dropdown-item" href="/dashboard/settings.php"><i class="fas fa-cog"></i> Configurações</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth.php?logout=1"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <i class="fas fa-mouse-pointer fa-2x text-primary mb-2"></i>
                        <div class="value"><?php echo number_format($totalClicks); ?></div>
                        <div class="label">Cliques (30 dias)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                        <div class="value"><?php echo number_format($totalConversions); ?></div>
                        <div class="label">Conversões (30 dias)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                        <div class="value"><?php echo $conversionRate; ?>%</div>
                        <div class="label">Taxa de Conversão</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <i class="fas fa-dollar-sign fa-2x text-warning mb-2"></i>
                        <div class="value">R$ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></div>
                        <div class="label">Receita (30 dias)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Cliques nos Últimos 7 Dias</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="clicksChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Resumo</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div>Campanhas Ativas</div>
                            <div><strong><?php echo $totalCampaigns; ?></strong></div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <div>Ofertas Ativas</div>
                            <div><strong><?php echo $totalOffers; ?></strong></div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <div>Cliques Hoje</div>
                            <div><strong>
                                <?php 
                                    $todayClicks = isset($clickCounts[count($clickCounts)-1]) ? $clickCounts[count($clickCounts)-1] : 0;
                                    echo $todayClicks;
                                ?>
                            </strong></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>Conversões Hoje</div>
                            <div><strong>
                                <?php 
                                    $stmt = $pdo->prepare("
                                        SELECT COUNT(*) FROM conversions cv
                                        JOIN offers o ON cv.offer_id = o.offer_id
                                        JOIN campaigns c ON o.campaign_id = c.id
                                        WHERE c.user_id = ? AND DATE(cv.timestamp) = CURDATE()
                                    ");
                                    $stmt->execute([$userId]);
                                    echo $stmt->fetchColumn();
                                ?>
                            </strong></div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Links Rápidos</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="/dashboard/campaigns.php?action=new" class="list-group-item list-group-item-action">
                                <i class="fas fa-plus-circle me-2"></i> Nova Campanha
                            </a>
                            <a href="/dashboard/offers.php?action=new" class="list-group-item list-group-item-action">
                                <i class="fas fa-plus-circle me-2"></i> Nova Oferta
                            </a>
                            <a href="/dashboard/reports.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-line me-2"></i> Ver Relatórios
                            </a>
                            <a href="/dashboard/settings.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-cog me-2"></i> Configurações
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Últimas Conversões</h5>
                        <a href="/dashboard/reports.php" class="btn btn-sm btn-primary">Ver Todas</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Oferta</th>
                                        <th>Status</th>
                                        <th>Sub-Status</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentConversions)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Nenhuma conversão registrada ainda.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentConversions as $conversion): ?>
                                            <tr>
                                                <td><?php echo $conversion['id']; ?></td>
                                                <td><?php echo htmlspecialchars($conversion['offer_name']); ?></td>
                                                <td>
                                                    <?php 
                                                        $statusClass = '';
                                                        switch ($conversion['status']) {
                                                            case 'lead': $statusClass = 'primary'; break;
                                                            case 'hold': $statusClass = 'warning'; break;
                                                            case 'reject': $statusClass = 'danger'; break;
                                                            case 'sale': $statusClass = 'success'; break;
                                                        }
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                                        <?php echo ucfirst($conversion['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($conversion['sub_status'] ?: '-'); ?></td>
                                                <td>R$ <?php echo number_format($conversion['payout'] ?: 0, 2, ',', '.'); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($conversion['timestamp'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-3">
        <div class="container text-center">
            <span class="text-muted">&copy; 2025 AdStrax. Todos os direitos reservados.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de cliques
        const ctx = document.getElementById('clicksChart').getContext('2d');
        const clicksChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartData['labels']); ?>,
                datasets: [{
                    label: 'Cliques',
                    data: <?php echo json_encode($chartData['data']); ?>,
                    backgroundColor: 'rgba(0, 102, 179, 0.1)',
                    borderColor: 'rgba(0, 102, 179, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(0, 102, 179, 1)',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
