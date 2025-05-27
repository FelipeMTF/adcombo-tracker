<?php
include 'config.php';

// Buscar estatísticas
$stmt = $pdo->query("SELECT COUNT(*) as total_clicks FROM clicks");
$clicks = $stmt->fetch(PDO::FETCH_ASSOC)['total_clicks'];

$stmt = $pdo->query("SELECT COUNT(*) as total_conversions FROM conversions WHERE status = 'sale'");
$conversions = $stmt->fetch(PDO::FETCH_ASSOC)['total_conversions'];

$conversion_rate = $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0;

// Buscar estatísticas por oferta
$stmt = $pdo->query("
    SELECT 
        o.offer_id,
        o.name,
        COUNT(DISTINCT c.click_id) as clicks,
        COUNT(DISTINCT CASE WHEN cv.status = 'lead' THEN cv.click_id END) as leads,
        COUNT(DISTINCT CASE WHEN cv.status = 'hold' THEN cv.click_id END) as holds,
        COUNT(DISTINCT CASE WHEN cv.status = 'reject' THEN cv.click_id END) as rejects,
        COUNT(DISTINCT CASE WHEN cv.status = 'sale' THEN cv.click_id END) as sales
    FROM 
        offers o
    LEFT JOIN 
        clicks c ON o.offer_id = c.offer_id
    LEFT JOIN 
        conversions cv ON c.click_id = cv.click_id
    GROUP BY 
        o.offer_id, o.name
    ORDER BY 
        o.name
");
$offer_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdCombo Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>AdCombo Tracker</h1>
        <p>Sistema de rastreamento para campanhas de afiliados AdCombo.</p>
        
        <div class="mb-3">
            <a href="admin.php" class="btn btn-primary">Gerenciar Ofertas</a>
            <a href="reports.php" class="btn btn-success">Relatórios Detalhados</a>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Estatísticas Gerais</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="h5">Cliques</h3>
                                <p class="display-4"><?php echo $clicks; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="h5">Vendas</h3>
                                <p class="display-4"><?php echo $conversions; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="h5">Taxa de Conversão</h3>
                                <p class="display-4"><?php echo $conversion_rate; ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Estatísticas por Oferta</h2>
            </div>
            <div class="card-body">
                <?php if (count($offer_stats) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Oferta</th>
                                    <th>Cliques</th>
                                    <th>Leads</th>
                                    <th>Holds</th>
                                    <th>Rejeições</th>
                                    <th>Vendas</th>
                                    <th>Taxa de Conversão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($offer_stats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['name']); ?></td>
                                        <td><?php echo $stat['clicks']; ?></td>
                                        <td><?php echo $stat['leads']; ?></td>
                                        <td><?php echo $stat['holds']; ?></td>
                                        <td><?php echo $stat['rejects']; ?></td>
                                        <td><?php echo $stat['sales']; ?></td>
                                        <td>
                                            <?php 
                                            $offer_cr = $stat['clicks'] > 0 ? round(($stat['sales'] / $stat['clicks']) * 100, 2) : 0;
                                            echo $offer_cr . '%'; 
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Nenhuma oferta cadastrada. <a href="admin.php">Adicione ofertas</a> para começar a rastrear.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
