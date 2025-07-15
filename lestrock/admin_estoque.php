<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_email'] !== 'admin@letsrock.com') {
    echo "<script>alert('Acesso restrito!');window.location.href='login.php';</script>";
    exit;
}

// Exclusão de disco
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $stmt = $pdo->prepare("DELETE FROM discos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: admin_estoque.php");
    exit;
}

// Consulta
$discos = [];

if (isset($_GET['busca']) && !empty(trim($_GET['busca']))) {
    $busca = trim($_GET['busca']);
    $stmt = $pdo->prepare("SELECT * FROM discos WHERE titulo LIKE :busca OR artista LIKE :busca OR ano LIKE :busca ORDER BY id DESC");
    $stmt->execute([':busca' => "%$busca%"]);
    $discos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM discos ORDER BY id DESC");
    $discos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Discos</title>
    <link rel="stylesheet" href="css/estoque.css">
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
    <h2>Discos Cadastrados</h2>

    <a href="admin_dashboard.php"><button>← Voltar para o Painel</button></a><br><br>

    <div class="container-busca">
    <form method="get" class="form-pesquisa">
        <input type="text" name="busca" placeholder="Buscar por título, artista ou ano..." value="<?= isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '' ?>">
        <button type="submit">Buscar</button>
    </form>
    </div>


    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Capa</th>
                <th>Título</th>
                <th>Artista</th>
                <th>Ano</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($discos as $disco): ?>
                <tr>
                    <td><?= $disco['id'] ?></td>
                    <td>
                        <?php if ($disco['imagem_url']): ?>
                            <img src="<?= htmlspecialchars($disco['imagem_url']) ?>" width="50">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($disco['titulo']) ?></td>
                    <td><?= htmlspecialchars($disco['artista']) ?></td>
                    <td><?= htmlspecialchars($disco['ano']) ?></td>
                    <td>R$ <?= number_format($disco['preco'], 2, ',', '.') ?></td>
                    <td><?= $disco['estoque'] ?></td>
                    <td>
                        <a href="admin_editar_disco.php?id=<?= $disco['id'] ?>">Editar</a> |
                        <a href="?excluir=<?= $disco['id'] ?>" onclick="return confirm('Deseja excluir este disco?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div>
</body>
</html>
