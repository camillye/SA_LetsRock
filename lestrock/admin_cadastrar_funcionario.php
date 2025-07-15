<?php
session_start();
require_once 'conexao.php';

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        try {
            // Verifica se o e-mail já está em uso
            $verifica = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $verifica->bindParam(':email', $email);
            $verifica->execute();

            if ($verifica->rowCount() > 0) {
                $erro = "Este e-mail já está cadastrado.";
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
                $sql = "INSERT INTO usuarios (nome, email, senha_hash, is_admin) 
                        VALUES (:nome, :email, :senha_hash, :is_admin)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha_hash', $senha_hash);
                $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
                $stmt->execute();
            
                if ($is_admin == 1) {
                    $mensagem = "Administrador cadastrado com sucesso!";
                } else {
                    $mensagem = "Usuário cadastrado com sucesso!";
                }
            }
            } catch (PDOException $e) {
            $erro = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Administrador</title>
    <link rel="stylesheet" href="css/cadastrar_funcionario.css">
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
    <h1>Cadastrar Novo Usuário/Administrador</h1>

    <?php if (!empty($mensagem)): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <label><input type="checkbox" name="is_admin"> Administrador</label><br><br>

        <button type="submit">Cadastrar</button>
    </form>

    <p><a href="login.php">Voltar ao painel</a></p>
</body>
</html>
