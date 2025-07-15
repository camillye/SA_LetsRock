<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado.");
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem = "";

// --- TRATAMENTO DO FORMULÁRIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Excluir conta
    if (isset($_POST['excluir'])) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        session_destroy();
        header("Location: dashboard.php");
        exit;
    }

    // Atualização dos dados
    if (isset($_POST['salvar'])) {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $endereco = $_POST['endereco'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        if (!empty($senha) && $senha !== $confirmar) {
            $mensagem = "As senhas não coincidem.";
        } else {
            // Atualizar tabela usuarios
            if (!empty($senha)) {
                $sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?, senha_hash = SHA2(?, 256) WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $telefone, $senha, $usuario_id]);
            } else {
                $sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $telefone, $usuario_id]);
            }

            // Atualizar ou inserir cliente
            $sqlVerifica = "SELECT id FROM clientes WHERE usuario_id = ?";
            $stmtVerifica = $pdo->prepare($sqlVerifica);
            $stmtVerifica->execute([$usuario_id]);

            if ($stmtVerifica->fetch()) {
                $sqlCliente = "UPDATE clientes SET endereco = ? WHERE usuario_id = ?";
                $stmtCliente = $pdo->prepare($sqlCliente);
                $stmtCliente->execute([$endereco, $usuario_id]);
            } else {
                $sqlCliente = "INSERT INTO clientes (usuario_id, nome, telefone, endereco) VALUES (?, ?, ?, ?)";
                $stmtCliente = $pdo->prepare($sqlCliente);
                $stmtCliente->execute([$usuario_id, $nome, $telefone, $endereco]);
            }

            $mensagem = "Configurações atualizadas com sucesso.";
        }
    }
}

// --- CARREGAR DADOS PARA EXIBIÇÃO ---
$sqlUsuario = "SELECT nome, email, telefone FROM usuarios WHERE id = ?";
$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute([$usuario_id]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

$sqlCliente = "SELECT endereco FROM clientes WHERE usuario_id = ?";
$stmtCliente = $pdo->prepare($sqlCliente);
$stmtCliente->execute([$usuario_id]);
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Configurações da Conta</title>
  <link rel="stylesheet" href="css/configg.css">
</head>
<body>
<header class="topo">
  <div class="logo">
    <a href="dashboard.php">
      <img src="imgs/voltar.png" alt="Voltar" class="logo">
    </a>
  </div>
</header>

<div class="container">
  <h1>Configurações da Conta</h1>

  <?php if (!empty($mensagem)): ?>
    <p style="color: green;"><?= htmlspecialchars($mensagem) ?></p>
  <?php endif; ?>

  <form method="POST">
    <!-- Dados Pessoais -->
    <section class="config-section">
      <h2>Dados Pessoais</h2>
      <label for="nome">Nome:</label>
      <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

      <label for="email">Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

      <label for="telefone">Telefone:</label>
      <input type="tel" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>">
    </section>

    <!-- Endereço -->
    <section class="config-section">
      <h2>Endereço</h2>
      <label for="endereco">CEP:</label>
      <input type="text" id="endereco" name="endereco" maxlength="9" placeholder="00000-000" value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>" required>

      <label for="logradouro">Logradouro:</label>
      <input type="text" id="logradouro" name="logradouro" readonly>

      <label for="bairro">Bairro:</label>
      <input type="text" id="bairro" name="bairro" readonly>

      <label for="cidade">Cidade:</label>
      <input type="text" id="cidade" name="cidade" readonly>

      <label for="estado">Estado:</label>
      <input type="text" id="estado" name="estado" readonly>

      <label for="numero">Número:</label>
      <input type="text" id="numero" name="numero" placeholder="Digite o número da casa" required>
    </section>

    <!-- Segurança -->
    <section class="config-section">
      <h2>Segurança</h2>
      <label for="senha">Nova Senha:</label>
      <input type="password" name="senha">

      <label for="confirmar_senha">Confirmar Senha:</label>
      <input type="password" name="confirmar_senha">
    </section>

    <div class="actions">
      <button type="submit" name="salvar">Salvar</button>
      <button type="submit" name="excluir" onclick="return confirm('Tem certeza que deseja excluir sua conta?');" class="excluir">Excluir conta</button>
    </div>
  </form>
</div>

<script src="js/cep.js"></script>
</body>
</html>
