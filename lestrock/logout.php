<?php
session_start();
require_once 'conexao.php';

$mensagem = '';
$erro = '';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Se recebeu confirmação de logout, processar
if (isset($_GET['confirm']) && $_GET['confirm'] == '1') {
    // Destruir sessão
    session_unset();
    session_destroy();
    
    // Redirecionar imediatamente
    header('Location: login.php');
    exit();
}

$usuario_nome = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Let's Rock Disco Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/logout.css"/>
    <script src="js/logout.js" defer></script>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="main-title">SAIR DO SISTEMA</h1>
            <img src="imagens/logo.png" alt="Logo da página" title="Logo da página">
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 class="form-title">LOGOUT</h2>
            
            <div class="logout-info">
                <p class="user-greeting">Olá, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong>!</p>
                <p class="logout-question">Tem certeza que deseja sair do sistema?</p>
            </div>
            
            <div class="button-group">
                <a href="logout.php?confirm=1" class="submit-btn logout-btn" style="text-decoration: none; display: inline-block; text-align: center;">
                    SAIR
                </a>
                <button type="button" class="cancel-btn" onclick="history.back()">
                    CANCELAR
                </button>
            </div>
        </div>
    </div>
</body>
</html>