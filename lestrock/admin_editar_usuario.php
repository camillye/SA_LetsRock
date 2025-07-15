<?php
session_start();
require_once 'conexao.php';

// Verificação de admin
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
    echo "<script>alert('Acesso restrito!');window.location.href='login.php';</script>";
    exit;
}

// Captura o ID do usuário
if (isset($_POST['editar_id'])) {
    $usuario_id = $_POST['editar_id'];

    // Buscar dados do usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "<script>alert('Usuário não encontrado.');window.location.href='admin_usuarios.php';</script>";
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    // Atualizar dados
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $nascimento = $_POST['nascimento'];

    if (empty($nome) || empty($email)) {
        $erro = "Nome e email são obrigatórios.";
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone, nascimento = :nascimento WHERE id = :id");
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'nascimento' => $nascimento,
            'id' => $id
        ]);

        echo "<script>alert('Usuário atualizado com sucesso!'); window.location.href='admin_usuarios.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Acesso inválido.'); window.location.href='admin_usuarios.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="css/admin_editar_usuario.css">
</head>
<body>
    <div class="container">
        <h1> Editar Usuário</h1>

        <?php if (!empty($erro)): ?>
            <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <label>Nome:</label><br>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required><br><br>

            <label>Telefone:</label><br>
            <input type="text" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>"><br><br>

            <label>Data de Nascimento:</label><br>
            <input type="date" name="nascimento" value="<?= $usuario['nascimento'] ?>"><br><br>

            <button type="submit" name="salvar" class="btn-editar">Salvar Alterações</button>
            <a href="admin_usuarios.php" class="voltar-btn">Cancelar</a>
        </form>
    </div>
</body>
</html>
