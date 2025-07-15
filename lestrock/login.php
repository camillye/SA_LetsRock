<?php
session_start();
require_once 'conexao.php';

$mensagem = '';
$erro = '';

// Para debug - remover em produção
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar se usuário já está logado
if (isset($_SESSION['usuario_id'])) {
    if (!empty($_SESSION['is_admin'])) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

// Verificar mensagem do cadastro
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
}

// Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro = 'Email e senha são obrigatórios!';
    } else {
        try {
            // Consulta usuário
            $sql = "SELECT id, nome, email, senha_hash, is_admin FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                $login_valido = false;

                // Verifica com password_hash
                if (password_verify($senha, $usuario['senha_hash'])) {
                    $login_valido = true;
                } else {
                    // Verifica com SHA256 (compatibilidade antiga)
                    $senhaHashSHA256 = hash('sha256', $senha);
                    if ($senhaHashSHA256 === $usuario['senha_hash']) {
                        $login_valido = true;

                        // Migra para password_hash
                        try {
                            $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                            $sql_update = "UPDATE usuarios SET senha_hash = :novo_hash WHERE id = :id";
                            $stmt_update = $pdo->prepare($sql_update);
                            $stmt_update->bindParam(':novo_hash', $novo_hash);
                            $stmt_update->bindParam(':id', $usuario['id']);
                            $stmt_update->execute();
                        } catch (Exception $e) {
                            error_log("Erro ao atualizar hash: " . $e->getMessage());
                        }
                    }
                }

                if ($login_valido) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['is_admin'] = !empty($usuario['is_admin']);

                    echo "<script>
                        alert('Login realizado com sucesso!');
                        window.location.href = '" . (!empty($usuario['is_admin']) ? 'admin_dashboard.php' : 'dashboard.php') . "';
                    </script>";
                    exit;
                } else {
                    $erro = 'Email ou senha incorretos!';
                }
            } else {
                $erro = 'Email ou senha incorretos!';
            }

        } catch (PDOException $e) {
            $erro = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Let's Rock Disco Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/login.css"/>
    <script src="js/login.js" defer></script>
</head>
<body>
    <div class="particles" id="particles"></div>

    <header class="header">
        <div class="header-content">
            <h1 class="main-title">FAÇA SEU LOGIN</h1>
            <img src="imagens/logo.png" alt="Logo da página" title="Logo da página">
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 class="form-title">LOGIN</h2>

            <?php if (!empty($mensagem)): ?>
                <div class="mensagem sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="mensagem erro"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="E-mail"
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <input type="password" name="senha" class="form-input" placeholder="Senha" required>
                </div>
                <div class="form-group">
                    <div class="checkbox-container">
                        <input type="checkbox" id="lembrar" name="lembrar" class="checkbox-input">
                        <label for="lembrar" class="checkbox-label">Lembrar de mim</label>
                    </div>
                </div>
                <button type="submit" class="submit-btn">ENTRAR</button>
            </form>

            <div class="login-links">
                <a href="cadastro.php" class="link">Não tem conta? Cadastre-se</a>
            </div>
        </div>
    </div>
</body>
</html>
