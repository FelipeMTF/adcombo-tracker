<?php
include 'config.php';

// Obter parâmetros de filtro
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$offer_id = $_GET['offer_id'] ?? '';
$sub_id = $_GET['sub_id'] ?? '';
$source = $_GET['source'] ?? '';

// Construir consulta SQL com filtros
$sql_conditions = [];
$sql_params = [];

if (!empty($start_date)) {
    $sql_conditions[] = "c.timestamp >= ?";
    $sql_params[] = $start_date . ' 00:00:00';
}

if (!empty($end_date)) {
    $sql_conditions[] = "c.timestamp <= ?";
    $sql_params[] = $end_date . ' 23:59:59';
}

if (!empty($offer_id)) {
    $sql_conditions[] = "c.offer_id = ?";
    $sql_params[] = $offer_id;
}

if (!empty($sub_id)) {
    $sql_conditions[] = "c.sub_id = ?";
    $sql_params[] = $sub_id;
}

if (!empty($source)) {
    $sql_conditions[] = "c.source = ?";
    $sql_params[] = $source;
}

$where_clause = count($sql_conditions) > 0 ? "WHERE " . implode(" AND ", $sql_conditions) : "";

// Buscar ofertas para o filtro
$stmt = $pdo->query("SELECT offer_id, name FROM offers ORDER BY name");
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar fontes para o filtro
$stmt = $pdo->query("SELECT DISTINCT source FROM clicks WHERE source != '' ORDER BY source");
$sources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar sub_ids para o filtro
$stmt = $pdo->query("SELECT DISTINCT sub_id FROM clicks WHERE sub_id != '' ORDER BY sub_id");
$sub_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar dados detalhados
$sql = "
    SELECT 
        c.click_id,
        c.timestamp as click_time,
        o.name as offer_name,
        c.sub_id,
        c.source,
        c.campaign,
        c.keyword,
        c.ip,
        cv.status,
        cv.sub_status,
        cv.timestamp as conversion_time
    FROM 
        clicks c
    LEFT JOIN 
        offers o ON c.offer_id = o.offer_id
    LEFT JOIN 
        conversions cv ON c.click_id = cv.click_id
    $where_clause
    ORDER BY 
        c.timestamp DESC
    LIMIT 1000
";

$stmt = $pdo->prepare($sql);
$stmt->execute($sql_params);
$clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar estatísticas resumidas
$sql = "
    SELECT 
        DATE(c.timestamp) as date,
        o.name as offer_name,
        c.sub_id,
        c.source,
        COUNT(DISTINCT c.click_id) as clicks,
        COUNT(DISTINCT CASE WHEN cv.status = 'lead' THEN cv.click_id END) as leads,
        COUNT(DISTINCT CASE WHEN cv.status = 'hold' THEN cv.click_id END) as holds,
        COUNT(DISTINCT CASE WHEN cv.status = 'reject' THEN cv.click_id END) as rejects,
        COUNT(DISTINCT CASE WHEN cv.status = 'sale' THEN cv.click_id END) as sales
    FROM 
        clicks c
    LEFT JOIN 
        offers o ON c.offer_id = o.offer_id
    LEFT JOIN 
        conversions cv ON c.click_id = cv.click_id
    $where_clause
    GROUP BY 
        DATE(c.timestamp), o.name, c.sub_id, c.source
    ORDER BY 
        DATE(c.timestamp) DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($sql_params);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios Detalhados - AdCombo Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Relatórios Detalhados</h1>
        <p><a href="index.php" class="btn btn-secondary">Voltar para o Dashboard</a></p>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Filtros</h2>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Data Inicial</label>
                        <input type="text" class="form-control datepicker" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Data Final</label>
                        <input type="text" class="form-control datepicker" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="offer_id" class="form-label">Oferta</label>
                        <select class="form-select" id="offer_id" name="offer_id">
                            <option value="">Todas</option>
                            <?php foreach ($offers as $offer): ?>
                                <option value="<?php echo htmlspecialchars($offer['offer_id']); ?>" <?php echo $offer_id === $offer['offer_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($offer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sub_id" class="form-label">Sub ID</label>
                        <select class="form-select" id="sub_id" name="sub_id">
                            <option value="">Todos</option>
                            <?php foreach ($sub_ids as $sid): ?>
                                <option value="<?php echo htmlspecialchars($sid['sub_id']); ?>" <?php echo $sub_id === $sid['sub_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sid['sub_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="source" class="form-label">Fonte</label>
                        <select class="form-select" id="source" name="source">
                            <option value="">Todas</option>
                            <?php foreach ($sources as $src): ?>
                                <option value="<?php echo htmlspecialchars($src['source']); ?>" <?php echo $source === $src['source'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($src['source']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="reports.php" class="btn btn-outline-secondary">Limpar Filtros</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Estatísticas Resumidas</h2>
            </div>
            <div class="card-body">
                <?php if (count($stats) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Oferta</th>
                                    <th>Sub ID</th>
                                    <th>Fonte</th>
                                    <th>Cliques</th>
                                    <th>Leads</th>
                                    <th>Holds</th>
                                    <th>Rejeições</th>
                                    <th>Vendas</th>
                                    <th>Taxa de Conversão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['date']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['offer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['sub_id']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['source']); ?></td>
                                        <td><?php echo $stat['clicks']; ?></td>
                                        <td><?php echo $stat['leads']; ?></td>
                                        <td><?php echo $stat['holds']; ?></td>
                                        <td><?php echo $stat['rejects']; ?></td>
                                        <td><?php echo $stat['sales']; ?></td>
                                        <td>
                                            <?php 
                                            $cr = $stat['clicks'] > 0 ? round(($stat['sales'] / $stat['clicks']) * 100, 2) : 0;
                                            echo $cr . '%'; 
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Nenhum dado encontrado para os filtros selecionados.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Dados Detalhados</h2>
            </div>
            <div class="card-body">
                <?php if (count($clicks) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID do Clique</th>
                                    <th>Data/Hora do Clique</th>
                                    <th>Oferta</th>
                                    <th>Sub ID</th>
                                    <th>Fonte</th>
                                    <th>Campanha</th>
                                    <th>Palavra-chave</th>
                                    <th>IP</th>
                                    <th>Status</th>
                                    <th>Sub-status</th>
                                    <th>Data/Hora da Conversão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clicks as $click): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($click['click_id']); ?></td>
                                        <td><?php echo htmlspecialchars($click['click_time']); ?></td>
                                        <td><?php echo htmlspecialchars($click['offer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($click['sub_id']); ?></td>
                                        <td><?php echo htmlspecialchars($click['source']); ?></td>
                                        <td><?php echo htmlspecialchars($click['campaign']); ?></td>
                                        <td><?php echo htmlspecialchars($click['keyword']); ?></td>
                                        <td><?php echo htmlspecialchars($click['ip']); ?></td>
                                        <td>
                                            <?php 
                                            if ($click['status']) {
                                                $status_class = '';
                                                switch ($click['status']) {
                                                    case 'lead': $status_class = 'text-primary'; break;
                                                    case 'hold': $status_class = 'text-warning'; break;
                                                    case 'reject': $status_class = 'text-danger'; break;
                                                    case 'sale': $status_class = 'text-success'; break;
                                                }
                                                echo '<span class="' . $status_class . '">' . htmlspecialchars(ucfirst($click['status'])) . '</span>';
                                            } else {
                                                echo '<span class="text-muted">Sem conversão</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($click['sub_status'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($click['conversion_time'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($clicks) >= 1000): ?>
                        <div class="alert alert-info mt-3">
                            Mostrando os 1000 registros mais recentes. Use os filtros para refinar os resultados.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Nenhum dado encontrado para os filtros selecionados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d"
            });
        });
    </script>
</body>
</html>
