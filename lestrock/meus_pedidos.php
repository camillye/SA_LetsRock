<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado para ver seus pedidos.'); window.location='login.php';</script>";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar pedidos do usuário com informações de pagamento
$sql = "SELECT 
            p.id AS pedido_id,
            p.total,
            p.status AS status_pedido,
            p.data_pedido,
            pg.status_pagamento,
            pg.forma_pagamento,
            pg.valor_pago
        FROM pedidos p
        LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
        WHERE p.usuario_id = ?
        ORDER BY p.data_pedido DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos - Let's Rock Disco Store</title>
    <link rel="stylesheet" href="css/pedidos.css">
</head>
<body>
    <header class="topo">
        <div class="logo">
    <a href="dashboard.php">
      <img src="imgs/voltar.png" alt="Voltar" class="logo">
    </a>
        </div>
    </header>
    <div class="container-pedidos">
        <h2>Meus Pedidos</h2>

        <?php if (empty($pedidos)): ?>
            <p>Você ainda não fez nenhum pedido.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID do Pedido</th>
                        <th>Data</th>
                        <th>Status do Pedido</th>
                        <th>Pagamento</th>
                        <th>Forma</th>
                        <th>Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?= $pedido['pedido_id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                            <td><?= ucfirst($pedido['status_pedido']) ?></td>
                            <td><?= ucfirst($pedido['status_pagamento'] ?? '---') ?></td>
                            <td><?= $pedido['forma_pagamento'] ?? '---' ?></td>
                            <td>R$ <?= number_format($pedido['valor_pago'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($pedido['status_pagamento'] === 'pago'): ?>
                                    <button class="btn-comprovante" onclick="gerarComprovante(<?= $pedido['pedido_id'] ?>)">
                                        Gerar Comprovante
                                    </button>
                                <?php else: ?>
                                    <span class="sem-comprovante">---</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Modal para exibir o comprovante -->
    <div id="modalComprovante" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="comprovanteContent"></div>
            <div class="modal-actions">
                <button id="btnImprimir" class="btn-imprimir">Imprimir</button>
            </div>
        </div>
    </div>

    <script src="js/comprovante.js"></script>
</body>
</html>