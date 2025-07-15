<?php
session_start();

// Verificação de acesso
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
    echo "<script>alert('Acesso restrito!'); window.location.href='login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador - Let's Rock</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container">
        <h1> Painel do Administrador</h1>
        <p>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</p>

        <div class="admin-cards">
            <a href="admin_estoque.php" class="card">
                <h2>📦 Gerenciar Estoque</h2>
                <p>Atualize a quantidade dos discos disponíveis.</p>
            </a>

            <a href="admin_pedidos.php" class="card">
                <h2>🧾 Ver Pedidos</h2>
                <p>Acompanhe os pedidos realizados pelos clientes.</p>
            </a>

            <a href="admin_usuarios.php" class="card">
                <h2>👤 Gerenciar Usuários</h2>
                <p>Visualize ou edite dados de usuários cadastrados.</p>
            </a>

            <a href="admin_cadastrar_disco.php" class="card">
                <h2>💿 Cadastrar Disco</h2>
                <p>Cadastre novos discos.</p>
            </a>

            <a href="admin_cadastrar_funcionario.php" class="card">
                <h2>🧑‍💻 Cadastrar Usuário/Funcionário</h2>
                <p>Cadastre novos usuários ou funcionários.</p>
            </a>

            <a href="admin_funcionarios.php" class="card">
                <h2>🧑‍💻 Gerenciar Funcionários</h2>
                <p>Visualize ou edite dados de funcionários cadastrados.</p>
            </a>

        </div>

        <a href="logout.php" class="logout-btn">Sair</a>
    </div>
</body>
</html>
