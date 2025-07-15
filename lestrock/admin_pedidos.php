<?php
session_start();
require_once 'conexao.php';

// Verificação de admin
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
    echo "<script>alert('Acesso restrito!'); window.location.href='login.php';</script>";
    exit;
}

// Buscar todos os pedidos com dados do usuário e pagamento
$sql = "
    SELECT p.id AS pedido_id, u.nome AS cliente, u.email, p.total, p.data_pedido, p.status,
           pg.forma_pagamento, pg.status_pagamento
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
    ORDER BY p.data_pedido DESC
";
$pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin - Pedidos</title>
    <link rel="stylesheet" href="css/admin_pedidos.css">
</head>
<body>
<nav class="topbar">
    <ul class="nav-links">
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_pedidos.php">Pedidos</a></li>
        <li><a href="admin_usuarios.php">Usuários</a></li>
        <li><a href="admin_funcionarios.php">Funcionários</a></li>
        <li><a href="admin_cadastrar_disco.php">Novo Disco</a></li>
        <li><a href="admin_cadastrar_funcionario.php">Novo Funcionário</a></li>
        <li><a href="admin_estoque.php">Estoque</a></li>
    </ul>
</nav>
<div class="container" style="margin-top: 80px;">
    <div class="container">
        <h1> Pedidos Realizados</h1>
        <a href="admin_dashboard.php" class="voltar-btn">← Voltar ao Painel</a>


        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Pagamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td>#<?= $pedido['pedido_id'] ?></td>
                    <td><?= htmlspecialchars($pedido['cliente']) ?></td>
                    <td><?= htmlspecialchars($pedido['email']) ?></td>
                    <td>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                    <td><?= ucfirst($pedido['status']) ?></td>
                    <td><?= ucfirst($pedido['forma_pagamento']) ?> (<?= $pedido['status_pagamento'] ?>)</td>
                    <td>
                        <form method="post" action="admin_ver_itens_pedido.php">
                            <input type="hidden" name="pedido_id" value="<?= $pedido['pedido_id'] ?>">
                            <button type="submit" class="btn-detalhes">Ver Itens</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
