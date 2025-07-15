<?php
session_start();
require_once 'conexao.php';

// Verifica se está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado.'); window.location='login.php';</script>";
    exit();
}

// Buscar todos os pedidos com nome do cliente
$sql = "SELECT 
            p.id AS pedido_id,
            p.total,
            p.status,
            p.data_pedido,
            u.nome AS nome_usuario,
            u.email
        FROM pedidos p
        JOIN usuarios u ON u.id = p.usuario_id
        ORDER BY p.data_pedido DESC";

$stmt = $pdo->query($sql);
$pedidos = $stmt->fetchAll();

// Função para buscar itens de um pedido
function buscarItensPedido($pdo, $pedido_id) {
    $sql = "SELECT 
                ip.quantidade,
                ip.preco_unitario,
                d.titulo,
                d.artista,
                d.imagem_url
            FROM itens_pedido ip
            JOIN discos d ON d.id = ip.disco_id
            WHERE ip.pedido_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pedido_id]);
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Pedidos - Admin</title>
    <link rel="stylesheet" href="css/admin_ver_itens_pedidos.css">
</head>
<body>
<div class="container">
    <h2>Todos os Pedidos</h2>

    <?php if (empty($pedidos)): ?>
        <p>Nenhum pedido encontrado.</p>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <div class="pedido-box">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#<?= $pedido['pedido_id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                            <td><?= htmlspecialchars($pedido['nome_usuario']) ?></td>
                            <td><?= htmlspecialchars($pedido['email']) ?></td>
                            <td><?= ucfirst($pedido['status']) ?></td>
                            <td>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php 
                $itens = buscarItensPedido($pdo, $pedido['pedido_id']);
                if (!empty($itens)):
                ?>
                    <div class="itens-title">Itens comprados:</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Capa</th>
                                <th>Título</th>
                                <th>Artista</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td><img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="Capa"></td>
                                    <td><?= htmlspecialchars($item['titulo']) ?></td>
                                    <td><?= htmlspecialchars($item['artista']) ?></td>
                                    <td><?= $item['quantidade'] ?></td>
                                    <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum item encontrado para este pedido.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
