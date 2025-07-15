<?php
session_start();
require_once 'conexao.php';

// Verificar se usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$mensagem = '';

// Exclusão de administrador
if (isset($_GET['excluir'])) {
    $idExcluir = intval($_GET['excluir']);

    // Evitar que admin exclua a si mesmo
    if ($idExcluir == $_SESSION['usuario_id']) {
        $mensagem = "Você não pode excluir seu próprio usuário.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND is_admin = 1");
        $stmt->bindParam(':id', $idExcluir);
        if ($stmt->execute()) {
            $mensagem = "Administrador excluído com sucesso!";
        } else {
            $mensagem = "Erro ao excluir administrador.";
        }
    }
}

// Buscar admins
$stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE is_admin = 1 ORDER BY nome");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Administradores</title>
    <link rel="stylesheet" href="css/admin_funcionarios.css">

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

<h1>Gerenciar Administradores</h1>

<?php if (!empty($mensagem)): ?>
    <p class="<?php echo (strpos($mensagem, 'Erro') !== false) ? 'erro' : 'mensagem'; ?>">
        <?php echo htmlspecialchars($mensagem); ?>
    </p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($admins) === 0): ?>
            <tr><td colspan="3">Nenhum administrador cadastrado.</td></tr>
        <?php else: ?>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?php echo htmlspecialchars($admin['nome']); ?></td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                    <td>
                        <a class="button" href="admin_editar_funcionario.php?id=<?php echo $admin['id']; ?>">Editar</a>
                        <a class="button" href="?excluir=<?php echo $admin['id']; ?>" onclick="return confirm('Excluir este administrador?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="admin_dashboard.php">Voltar ao Painel</a></p>

</body>
</html>
