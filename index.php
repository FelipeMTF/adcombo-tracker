<?php
include 'auth.php';
include 'config.php';

// Buscar estatísticas gerais
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$this_month = date('Y-m-01');
$last_month = date('Y-m-01', strtotime('-1 month'));

// Cliques hoje
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clicks WHERE DATE(timestamp) = ?");
$stmt->execute([$today]);
$clicks_today = $stmt->fetchColumn();

// Cliques ontem
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clicks WHERE DATE(timestamp) = ?");
$stmt->execute([$yesterday]);
$clicks_yesterday = $stmt->fetchColumn();

// Leads hoje
$stmt = $pdo->prepare("SELECT COUNT(*) FROM conversions WHERE action_type = 'lead' AND DATE(timestamp) = ?");
$stmt->execute([$today]);
$leads_today = $stmt->fetchColumn();

// Leads ontem
$stmt = $pdo->prepare("SELECT COUNT(*) FROM conversions WHERE action_type = 'lead' AND DATE(timestamp) = ?");
$stmt->execute([$yesterday]);
$leads_yesterday = $stmt->fetchColumn();

// Vendas hoje
$stmt = $pdo->prepare("SELECT COUNT(*) FROM conversions WHERE action_type = 'sale' AND DATE(timestamp) = ?");
$stmt->execute([$today]);
$sales_today = $stmt->fetchColumn();

// Vendas ontem
$stmt = $pdo->prepare("SELECT COUNT(*) FROM conversions WHERE action_type = 'sale' AND DATE(timestamp) = ?");
$stmt->execute([$yesterday]);
$sales_yesterday = $stmt->fetchColumn();

// Receita hoje
$stmt = $pdo->prepare("SELECT COALESCE(SUM(revenue), 0) FROM conversions WHERE action_type = 'sale' AND DATE(timestamp) = ?");
$stmt->execute([$today]);
$revenue_today = $stmt->fetchColumn();

// Receita ontem
$stmt = $pdo->prepare("SELECT COALESCE(SUM(revenue), 0) FROM conversions WHERE action_type = 'sale' AND DATE(timestamp) = ?");
$stmt->execute([$yesterday]);
$revenue_yesterday = $stmt->fetchColumn();

// Buscar campanhas ativas
$stmt = $pdo->query("SELECT c.*, 
                     (SELECT COUNT(*) FROM offers o WHERE o.campaign_id = c.id) AS offer_count,
                     (SELECT COUNT(*) FROM clicks cl JOIN offers o ON cl.offer_id = o.offer_id WHERE o.campaign_id = c.id) AS click_count,
                     (SELECT COUNT(*) FROM conversions cv JOIN offers o ON cv.offer_id = o.offer_id WHERE o.campaign_id = c.id AND cv.action_type = 'lead') AS lead_count,
                     (SELECT COUNT(*) FROM conversions cv JOIN offers o ON cv.offer_id = o.offer_id WHERE o.campaign_id = c.id AND cv.action_type = 'sale') AS sale_count,
                     (SELECT COALESCE(SUM(cv.revenue), 0) FROM conversions cv JOIN offers o ON cv.offer_id = o.offer_id WHERE o.campaign_id = c.id AND cv.action_type = 'sale') AS total_revenue
                     FROM campaigns c
                     WHERE c.status = 'active'
                     ORDER BY c.name");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar ofertas recentes
$stmt = $pdo->query("SELECT o.*, 
                     (SELECT COUNT(*) FROM clicks WHERE offer_id = o.offer_id) AS click_count,
                     (SELECT COUNT(*) FROM conversions WHERE offer_id = o.offer_id AND action_type = 'lead') AS lead_count,
                     (SELECT COUNT(*) FROM conversions WHERE offer_id = o.offer_id AND action_type = 'sale') AS sale_count,
                     (SELECT COALESCE(SUM(revenue), 0) FROM conversions WHERE offer_id = o.offer_id AND action_type = 'sale') AS total_revenue
                     FROM offers o
                     ORDER BY o.created_at DESC
                     LIMIT 5");
$recent_offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar conversões recentes
$stmt = $pdo->query("SELECT c.*, o.name AS offer_name, 
                     (SELECT sub_id FROM clicks WHERE click_id = c.click_id) AS sub_id
                     FROM conversions c
                     JOIN offers o ON c.offer_id = o.offer_id
                     ORDER BY c.timestamp DESC
                     LIMIT 10");
$recent_conversions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <div class="sidebar">
        <div class="sidebar-brand">
            <a href="/dashboard">
                <img src="/assets/images/logo.png" alt="AdStrax" class="sidebar-logo">
            </a>
        </div>
        
        <!-- Resto do menu lateral -->
        <ul class="sidebar-nav">
            <li><a href="/dashboard">Dashboard</a></li>
            <!-- Outros itens do menu -->
        </ul>
    </div>
    
    <div class="content">
        <!-- Conteúdo principal -->
    </div>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AdCombo Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: bold;
        }
        .stats-card {
            text-align: center;
            padding: 15px;
        }
        .stats-card .value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stats-card .label {
            color: #6c757d;
            font-size: 14px;
        }
        .stats-card .change {
            font-size: 12px;
            padding: 3px 6px;
            border-radius: 10px;
            display: inline-block;
        }
        .change.positive {
            background-color: #d4edda;
            color: #155724;
        }
        .change.negative {
            background-color: #f8d7da;
            color: #721c24;
        }
        .campaign-card {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
        }
        .campaign-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .campaign-stat {
            text-align: center;
        }
        .campaign-stat .value {
            font-weight: bold;
            font-size: 18px;
        }
        .campaign-stat .label {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">AdCombo Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="campaigns.php">Campanhas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Gerenciar Ofertas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="api.php">Importar Ofertas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="domains.php">Gerenciar Domínios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Relatórios Detalhados</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Meu Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="label">Cliques Hoje</div>
                    <div class="value"><?php echo number_format($clicks_today); ?></div>
                    <?php if ($clicks_yesterday > 0): ?>
                        <?php $change_percent = round(($clicks_today - $clicks_yesterday) / $clicks_yesterday * 100, 1); ?>
                        <div class="change <?php echo $change_percent >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $change_percent >= 0 ? '+' : ''; ?><?php echo $change_percent; ?>% vs ontem
                        </div>
                    <?php else: ?>
                        <div class="change positive">Novo</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="label">Leads Hoje</div>
                    <div class="value"><?php echo number_format($leads_today); ?></div>
                    <?php if ($leads_yesterday > 0): ?>
                        <?php $change_percent = round(($leads_today - $leads_yesterday) / $leads_yesterday * 100, 1); ?>
                        <div class="change <?php echo $change_percent >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $change_percent >= 0 ? '+' : ''; ?><?php echo $change_percent; ?>% vs ontem
                        </div>
                    <?php else: ?>
                        <div class="change positive">Novo</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="label">Vendas Hoje</div>
                    <div class="value"><?php echo number_format($sales_today); ?></div>
                    <?php if ($sales_yesterday > 0): ?>
                        <?php $change_percent = round(($sales_today - $sales_yesterday) / $sales_yesterday * 100, 1); ?>
                        <div class="change <?php echo $change_percent >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $change_percent >= 0 ? '+' : ''; ?><?php echo $change_percent; ?>% vs ontem
                        </div>
                    <?php else: ?>
                        <div class="change positive">Novo</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="label">Receita Hoje</div>
                    <div class="value">R$ <?php echo number_format($revenue_today, 2, ',', '.'); ?></div>
                    <?php if ($revenue_yesterday > 0): ?>
                        <?php $change_percent = round(($revenue_today - $revenue_yesterday) / $revenue_yesterday * 100, 1); ?>
                        <div class="change <?php echo $change_percent >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $change_percent >= 0 ? '+' : ''; ?><?php echo $change_percent; ?>% vs ontem
                        </div>
                    <?php else: ?>
                        <div class="change positive">Novo</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Campanhas Ativas</span>
                        <a href="campaigns.php" class="btn btn-sm btn-primary">Ver Todas</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($campaigns as $campaign): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card campaign-card" onclick="window.location.href='campaign_details.php?id=<?php echo $campaign['id']; ?>'">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($campaign['name']); ?></h5>
                                            <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($campaign['description'], 0, 60)); ?><?php echo strlen($campaign['description']) > 60 ? '...' : ''; ?></p>
                                            <div class="campaign-stats">
                                                <div class="campaign-stat">
                                                    <div class="value"><?php echo number_format($campaign['click_count']); ?></div>
                                                    <div class="label">Cliques</div>
                                                </div>
                                                <div class="campaign-stat">
                                                    <div class="value"><?php echo number_format($campaign['lead_count']); ?></div>
                                                    <div class="label">Leads</div>
                                                </div>
                                                <div class="campaign-stat">
                                                    <div class="value"><?php echo number_format($campaign['sale_count']); ?></div>
                                                    <div class="label">Vendas</div>
                                                </div>
                                                <div class="campaign-stat">
                                                    <div class="value">R$ <?php echo number_format($campaign['total_revenue'], 0, ',', '.'); ?></div>
                                                    <div class="label">Receita</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($campaigns) === 0): ?>
                                <div class="col-12">
                                    <p class="text-center text-muted">Nenhuma campanha ativa encontrada.</p>
                                    <p class="text-center">
                                        <a href="campaigns.php?action=new" class="btn btn-primary">Criar Nova Campanha</a>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Conversões Recentes</span>
                        <a href="reports.php" class="btn btn-sm btn-primary">Ver Todas</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>Oferta</th>
                                        <th>Sub ID</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_conversions as $conversion): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($conversion['timestamp'])); ?></td>
                                            <td><?php echo htmlspecialchars($conversion['offer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($conversion['sub_id']); ?></td>
                                            <td>
                                                <?php if ($conversion['action_type'] === 'lead'): ?>
                                                    <span class="badge bg-primary">Lead</span>
                                                <?php elseif ($conversion['action_type'] === 'sale'): ?>
                                                    <span class="badge bg-success">Venda</span>
                                                <?php elseif ($conversion['action_type'] === 'hold'): ?>
                                                    <span class="badge bg-warning text-dark">Hold</span>
                                                <?php elseif ($conversion['action_type'] === 'reject'): ?>
                                                    <span class="badge bg-danger">Rejeição</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($conversion['status']): ?>
                                                    <?php echo htmlspecialchars($conversion['status']); ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($conversion['action_type'] === 'sale'): ?>
                                                    R$ <?php echo number_format($conversion['revenue'], 2, ',', '.'); ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($recent_conversions) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Nenhuma conversão registrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Ofertas Recentes</span>
                        <a href="admin.php" class="btn btn-sm btn-primary">Gerenciar</a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($recent_offers as $offer): ?>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($offer['name']); ?></h6>
                                    <small class="text-muted">ID: <?php echo htmlspecialchars($offer['offer_id']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="small fw-bold">
                                        <?php echo number_format($offer['click_count']); ?> cliques
                                    </div>
                                    <div class="small text-muted">
                                        <?php echo number_format($offer['lead_count']); ?> leads / <?php echo number_format($offer['sale_count']); ?> vendas
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($recent_offers) === 0): ?>
                            <p class="text-center text-muted">Nenhuma oferta cadastrada.</p>
                            <p class="text-center">
                                <a href="admin.php" class="btn btn-primary">Adicionar Oferta</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">Links Rápidos</div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="admin.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-tag-fill me-2"></i> Gerenciar Ofertas
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="campaigns.php?action=new" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-megaphone-fill me-2"></i> Nova Campanha
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="reports.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-graph-up me-2"></i> Relatórios Detalhados
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="domains.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-globe me-2"></i> Gerenciar Domínios
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="api.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-cloud-download me-2"></i> Importar Ofertas
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
