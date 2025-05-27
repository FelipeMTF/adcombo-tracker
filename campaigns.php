<?php
include 'auth.php';
include 'config.php';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Adicionar nova campanha
        if ($_POST['action'] === 'add_campaign') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $status = $_POST['status'];
            
            $stmt = $pdo->prepare("INSERT INTO campaigns (name, description, status) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $status]);
            
            $success_message = "Campanha adicionada com sucesso!";
        }
        
        // Atualizar campanha
        elseif ($_POST['action'] === 'update_campaign') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $status = $_POST['status'];
            
            $stmt = $pdo->prepare("UPDATE campaigns SET name = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $status, $id]);
            
            $success_message = "Campanha atualizada com sucesso!";
        }
        
        // Excluir campanha
        elseif ($_POST['action'] === 'delete_campaign') {
            $id = $_POST['id'];
            
            // Atualizar ofertas para remover a referência à campanha
            $stmt = $pdo->prepare("UPDATE offers SET campaign_id = NULL WHERE campaign_id = ?");
            $stmt->execute([$id]);
            
            // Excluir a campanha
            $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
            $stmt->execute([$id]);
            
            $success_message = "Campanha excluída com sucesso!";
        }
    }
}

// Verificar se é uma ação específica
$action = isset($_GET['action']) ? $_GET['action'] : '';
$campaign_id = isset($_GET['id']) ? $_GET['id'] : '';

// Buscar campanha específica para edição
$campaign = null;
if ($action === 'edit' && $campaign_id) {
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->execute([$campaign_id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$campaign) {
        header('Location: campaigns.php');
        exit;
    }
}

// Buscar todas as campanhas
$stmt = $pdo->query("SELECT c.*, 
                     (SELECT COUNT(*) FROM offers o WHERE o.campaign_id = c.id) AS offer_count,
                     (SELECT COUNT(*) FROM clicks cl JOIN offers o ON cl.offer_id = o.offer_id WHERE o.campaign_id = c.id) AS click_count,
                     (SELECT COUNT(*) FROM conversions cv JOIN offers o ON cv.offer_id = o.offer_id WHERE o.campaign_id = c.id AND cv.action_type = 'lead') AS lead_count,
                     (SELECT COUNT(*) FROM conversions cv JOIN offers o ON cv.offer_id = o.offer_id WHERE o.campaign_id = c.id AND cv.action_type = 'sale') AS sale_count,
                     (SELECT COALESCE(SUM(cv.revenue), 0) FROM conversions cv JOIN offers o ON cv.offer_id = o.offer_id WHERE o.campaign_id = c.id AND cv.action_type = 'sale') AS total_revenue
                     FROM campaigns c
                     ORDER BY c.name");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Campanhas - AdCombo Tracker</title>
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
        .campaign-card {
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
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-paused {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #d6d8db;
            color: #383d41;
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
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="campaigns.php">Campanhas</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gerenciar Campanhas</h1>
            <a href="campaigns.php?action=new" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Campanha
            </a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($action === 'new' || $action === 'edit'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <?php echo $action === 'new' ? 'Nova Campanha' : 'Editar Campanha'; ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action === 'new' ? 'add_campaign' : 'update_campaign'; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome da Campanha</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $action === 'edit' ? htmlspecialchars($campaign['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $action === 'edit' ? htmlspecialchars($campaign['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo $action === 'edit' && $campaign['status'] === 'active' ? 'selected' : ''; ?>>Ativa</option>
                                <option value="paused" <?php echo $action === 'edit' && $campaign['status'] === 'paused' ? 'selected' : ''; ?>>Pausada</option>
                                <option value="completed" <?php echo $action === 'edit' && $campaign['status'] === 'completed' ? 'selected' : ''; ?>>Concluída</option>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="campaigns.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $action === 'new' ? 'Criar Campanha' : 'Atualizar Campanha'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($campaigns as $campaign): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card campaign-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($campaign['name']); ?></h5>
                                    <span class="status-badge status-<?php echo $campaign['status']; ?>">
                                        <?php 
                                        if ($campaign['status'] === 'active') echo 'Ativa';
                                        elseif ($campaign['status'] === 'paused') echo 'Pausada';
                                        elseif ($campaign['status'] === 'completed') echo 'Concluída';
                                        ?>
                                    </span>
                                </div>
                                
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($campaign['description']); ?></p>
                                
                                <div class="campaign-stats">
                                    <div class="campaign-stat">
                                        <div class="value"><?php echo number_format($campaign['offer_count']); ?></div>
                                        <div class="label">Ofertas</div>
                                    </div>
                                    <div class="campaign-stat">
                                        <div class="value"><?php echo number_format($campaign['click_count']); ?></div>
                                        <div class="label">Cliques</div>
                                    </div>
                                    <div class="campaign-stat">
                                        <div class="value"><?php echo number_format($campaign['lead_count']); ?></div>
                                        <div class="label">Leads</div>
                                    </div>
                                    <div class="campaign-stat">
                                        <div class="value">R$ <?php echo number_format($campaign['total_revenue'], 0, ',', '.'); ?></div>
                                        <div class="label">Receita</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-3">
                                    <a href="campaign_details.php?id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-graph-up"></i> Detalhes
                                    </a>
                                    <div>
                                        <a href="campaigns.php?action=edit&id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $campaign['id']; ?>">
                                            <i class="bi bi-trash"></i> Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal de confirmação de exclusão -->
                        <div class="modal fade" id="deleteModal<?php echo $campaign['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmar Exclusão</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Tem certeza que deseja excluir a campanha <strong><?php echo htmlspecialchars($campaign['name']); ?></strong>?</p>
                                        <p class="text-danger">Esta ação não pode ser desfeita. As ofertas associadas a esta campanha serão desvinculadas, mas não excluídas.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <form method="post">
                                            <input type="hidden" name="action" value="delete_campaign">
                                            <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($campaigns) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <p>Nenhuma campanha encontrada.</p>
                            <a href="campaigns.php?action=new" class="btn btn-primary mt-2">Criar Nova Campanha</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
