<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado para ver o comprovante.'); window.location='login.php';</script>";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$pedido_id = $_GET['pedido_id'] ?? null;

if (!$pedido_id) {
    echo "<script>alert('Pedido inválido.'); window.location='meus_pedidos.php';</script>";
    exit();
}

// Buscar pedido + pagamento
$sql = "SELECT 
            p.id AS pedido_id,
            p.data_pedido,
            p.total,
            p.status AS status_pedido,
            pg.forma_pagamento,
            pg.valor_pago,
            pg.status_pagamento,
            u.nome AS nome_usuario
        FROM pedidos p
        INNER JOIN pagamentos pg ON pg.pedido_id = p.id
        INNER JOIN usuarios u ON u.id = p.usuario_id
        WHERE p.id = ? AND p.usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido_id, $usuario_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    echo "<script>alert('Pedido não encontrado.'); window.location='meus_pedidos.php';</script>";
    exit();
}

// Buscar itens do pedido
$sqlItens = "SELECT ip.quantidade, ip.preco_unitario, d.titulo, d.artista 
             FROM itens_pedido ip 
             INNER JOIN discos d ON d.id = ip.disco_id
             WHERE ip.pedido_id = ?";
$stmtItens = $pdo->prepare($sqlItens);
$stmtItens->execute([$pedido_id]);
$itens = $stmtItens->fetchAll();

// Função para formatar o status
function formatarStatus($status) {
    $statusFormatado = ucfirst($status);
    $classe = 'status-' . $status;
    return "<span class='status-badge $classe'>$statusFormatado</span>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pagamento - Pedido #<?= htmlspecialchars($pedido['pedido_id']) ?></title>
    <link rel="stylesheet" href="css/comprovante.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="comprovante-container">
        <h1>
            <img src="imgs/logo.png" alt="Logo Let's Rock" style="height: 150px; vertical-align: middle; margin-right: 10px;">
            Let's Rock Disco Store
        </h1>
        <h2><i class="fas fa-receipt"></i> Comprovante de Pagamento</h2>
        <div class="info">
            <p><strong><i class="fas fa-hashtag"></i> Pedido:</strong> #<?= htmlspecialchars($pedido['pedido_id']) ?></p>
            <p><strong><i class="fas fa-calendar-alt"></i> Data do Pedido:</strong> <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></p>
            <p><strong><i class="fas fa-user"></i> Cliente:</strong> <?= htmlspecialchars($pedido['nome_usuario']) ?></p>
            <p><strong><i class="fas fa-info-circle"></i> Status do Pedido:</strong> <?= formatarStatus($pedido['status_pedido']) ?></p>
            <p><strong><i class="fas fa-credit-card"></i> Status do Pagamento:</strong> <?= formatarStatus($pedido['status_pagamento']) ?></p>
            <p><strong><i class="fas fa-money-bill-wave"></i> Forma de Pagamento:</strong> <?= htmlspecialchars($pedido['forma_pagamento']) ?></p>
        </div>

        <?php if (!empty($itens)): ?>
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-compact-disc"></i> Disco</th>
                    <th><i class="fas fa-microphone"></i> Artista</th>
                    <th><i class="fas fa-sort-numeric-up"></i> Quantidade</th>
                    <th><i class="fas fa-tag"></i> Preço Unitário</th>
                    <th><i class="fas fa-calculator"></i> Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['titulo']) ?></td>
                        <td><?= htmlspecialchars($item['artista']) ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="total">
            <i class="fas fa-money-check-alt"></i> 
            Total Pago: R$ <?= number_format($pedido['total'], 2, ',', '.') ?>
        </div>

        <div class="comprovante-footer">
            <p><strong>Obrigado por comprar conosco!</strong></p>
            <p>Este comprovante é válido como documento fiscal.</p>
            <p>Comprovante gerado em: <?= date('d/m/Y H:i:s') ?></p>
        </div>

        <div class="btn-print">
            <button onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir Comprovante
            </button>
        </div>
    </div>

    <script>
        // Script para imprimir automaticamente se necessário
        // Descomente a linha abaixo se quiser que imprima automaticamente
        // window.print();
        
        // Adicionar funcionalidade de voltar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location = 'meus_pedidos.php';
            }
        });
    </script>
</body>
</html>