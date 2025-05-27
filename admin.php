<?php
include 'config.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_offer') {
        // Adicionar nova oferta
        $offer_id = $_POST['offer_id'];
        $offer_name = $_POST['offer_name'];
        $landing_url = $_POST['landing_url'];
        
        $stmt = $pdo->prepare("INSERT INTO offers (offer_id, name, landing_url) VALUES (?, ?, ?)");
        $stmt->execute([$offer_id, $offer_name, $landing_url]);
        
        $success_message = "Oferta adicionada com sucesso!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_offer') {
        // Excluir oferta
        $offer_id = $_POST['offer_id'];
        
        $stmt = $pdo->prepare("DELETE FROM offers WHERE offer_id = ?");
        $stmt->execute([$offer_id]);
        
        $success_message = "Oferta excluída com sucesso!";
    }
}

// Buscar todas as ofertas
$stmt = $pdo->query("SELECT * FROM offers ORDER BY name");
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Ofertas - AdCombo Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Gerenciar Ofertas</h1>
        <p><a href="index.php" class="btn btn-secondary">Voltar para o Dashboard</a></p>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Adicionar Nova Oferta</h2>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="add_offer">
                    <div class="mb-3">
                        <label for="offer_id" class="form-label">ID da Oferta (AdCombo)</label>
                        <input type="text" class="form-control" id="offer_id" name="offer_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="offer_name" class="form-label">Nome da Oferta</label>
                        <input type="text" class="form-control" id="offer_name" name="offer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="landing_url" class="form-label">URL da Landing Page</label>
                        <input type="url" class="form-control" id="landing_url" name="landing_url" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Adicionar Oferta</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Ofertas Existentes</h2>
            </div>
            <div class="card-body">
                <?php if (count($offers) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID da Oferta</th>
                                    <th>Nome</th>
                                    <th>URL da Landing Page</th>
                                    <th>URL de Rastreamento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($offers as $offer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($offer['offer_id']); ?></td>
                                        <td><?php echo htmlspecialchars($offer['name']); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($offer['landing_url']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($offer['landing_url']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($base_url . '/track.php?offer_id=' . $offer['offer_id'] . '&sub_id={SUB_ID}'); ?>" readonly>
                                                <button class="btn btn-outline-secondary copy-btn" data-clipboard-text="<?php echo htmlspecialchars($base_url . '/track.php?offer_id=' . $offer['offer_id'] . '&sub_id={SUB_ID}'); ?>">Copiar</button>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir esta oferta?');">
                                                <input type="hidden" name="action" value="delete_offer">
                                                <input type="hidden" name="offer_id" value="<?php echo htmlspecialchars($offer['offer_id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Nenhuma oferta cadastrada.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Configuração de Postbacks no AdCombo</h2>
            </div>
            <div class="card-body">
                <p>Configure os seguintes URLs de postback na sua conta AdCombo:</p>
                
                <h3 class="h6">Para Leads:</h3>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($base_url . '/postback.php?action=lead&offer_id={offer_id}&click_id={click_id}&status={status}'); ?>" readonly>
                    <button class="btn btn-outline-secondary copy-btn" data-clipboard-text="<?php echo htmlspecialchars($base_url . '/postback.php?action=lead&offer_id={offer_id}&click_id={click_id}&status={status}'); ?>">Copiar</button>
                </div>
                
                <h3 class="h6">Para Holds:</h3>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($base_url . '/postback.php?action=hold&offer_id={offer_id}&click_id={click_id}'); ?>" readonly>
                    <button class="btn btn-outline-secondary copy-btn" data-clipboard-text="<?php echo htmlspecialchars($base_url . '/postback.php?action=hold&offer_id={offer_id}&click_id={click_id}'); ?>">Copiar</button>
                </div>
                
                <h3 class="h6">Para Rejeições:</h3>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($base_url . '/postback.php?action=reject&offer_id={offer_id}&click_id={click_id}&reason={reason}'); ?>" readonly>
                    <button class="btn btn-outline-secondary copy-btn" data-clipboard-text="<?php echo htmlspecialchars($base_url . '/postback.php?action=reject&offer_id={offer_id}&click_id={click_id}&reason={reason}'); ?>">Copiar</button>
                </div>
                
                <h3 class="h6">Para Vendas:</h3>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($base_url . '/postback.php?action=sale&offer_id={offer_id}&click_id={click_id}'); ?>" readonly>
                    <button class="btn btn-outline-secondary copy-btn" data-clipboard-text="<?php echo htmlspecialchars($base_url . '/postback.php?action=sale&offer_id={offer_id}&click_id={click_id}'); ?>">Copiar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var clipboard = new ClipboardJS('.copy-btn');
            
            clipboard.on('success', function(e) {
                var button = e.trigger;
                button.innerHTML = 'Copiado!';
                setTimeout(function() {
                    button.innerHTML = 'Copiar';
                }, 2000);
                e.clearSelection();
            });
        });
    </script>
</body>
</html>
