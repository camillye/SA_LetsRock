<?php
session_start();
require_once 'conexao.php';

// Verificar permissão de admin
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$erro = '';
$mensagem = '';

if (!isset($_GET['id'])) {
    die("ID do administrador não informado.");
}

$id = intval($_GET['id']);

// Buscar dados do admin
$stmt = $pdo->prepare("SELECT nome, email, is_admin FROM usuarios WHERE id = :id AND is_admin = 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Administrador não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha']; // pode ser vazio para não alterar
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if (empty($nome) || empty($email)) {
        $erro = "Nome e email são obrigatórios.";
    } else {
        try {
            // Verifica se email está em uso por outro usuário
            $verifica = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
            $verifica->bindParam(':email', $email);
            $verifica->bindParam(':id', $id);
            $verifica->execute();

            if ($verifica->rowCount() > 0) {
                $erro = "Este email já está em uso por outro usuário.";
            } else {
                if (!empty($senha)) {
                    // Atualiza com nova senha
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $sql = "UPDATE usuarios SET nome = :nome, email = :email, senha_hash = :senha_hash, is_admin = :is_admin WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':senha_hash', $senha_hash);
                } else {
                    // Atualiza sem alterar senha
                    $sql = "UPDATE usuarios SET nome = :nome, email = :email, is_admin = :is_admin WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                }

                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                $mensagem = "Administrador atualizado com sucesso!";
                // Atualizar variáveis para preencher formulário
                $admin['nome'] = $nome;
                $admin['email'] = $email;
                $admin['is_admin'] = $is_admin;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Administrador</title>
    <link rel="stylesheet" href="css/editar_funcionario.css">
</head>
<body>

<h1>Editar Administrador</h1>

<?php if (!empty($mensagem)): ?>
    <p class="mensagem"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>

<?php if (!empty($erro)): ?>
    <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Nome:</label><br>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($admin['nome']); ?>" required><br><br>

    <label>E-mail:</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required><br><br>

    <label>Senha (deixe em branco para não alterar):</label><br>
    <input type="password" name="senha"><br><br>

    <label><input type="checkbox" name="is_admin" <?php echo ($admin['is_admin'] == 1) ? 'checked' : ''; ?>> Administrador</label><br><br>

    <button type="submit">Salvar Alterações</button>
</form>

<p><a href="admin_funcionarios.php">Voltar para Gerenciar Administradores</a></p>

</body>
</html>
