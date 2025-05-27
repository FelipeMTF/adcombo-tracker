<!DOCTYPE html>
<html>
<head>
    <title>AdCombo Tracker</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>AdCombo Tracker</h1>
        <p>Sistema de rastreamento para campanhas de afiliados AdCombo.</p>
        
        <div class="card mt-4">
            <div class="card-header">
                Estatísticas
            </div>
            <div class="card-body">
                <?php
                require_once 'config.php';
                
                // Contar cliques
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM clicks");
                $clicks = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                
                // Contar conversões
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM conversions");
                $conversions = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                
                // Calcular taxa de conversão
                $conversion_rate = $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0;
                ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Cliques</h5>
                                <p class="card-text display-4"><?php echo $clicks; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Conversões</h5>
                                <p class="card-text display-4"><?php echo $conversions; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Taxa de Conversão</h5>
                                <p class="card-text display-4"><?php echo $conversion_rate; ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
