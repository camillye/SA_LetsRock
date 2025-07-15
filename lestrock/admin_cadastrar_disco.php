<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_email'] !== 'admin@letsrock.com') {
    echo "<script>alert('Acesso restrito!');window.location.href='login.php';</script>";
    exit;
}

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $discogs_id = !empty($_POST['discogs_id']) ? intval($_POST['discogs_id']) : null;
    $titulo = trim($_POST['titulo']);
    $artista = trim($_POST['artista']);
    $ano = intval($_POST['ano']);
    $gravadora = trim($_POST['gravadora']);
    $genero = trim($_POST['genero']);
    $estilo = trim($_POST['estilo']);
    $preco = floatval($_POST['preco']);
    $descricao = trim($_POST['descricao']);
    $pais = trim($_POST['pais']);
    $estoque = intval($_POST['estoque']);

    // Upload da imagem
    $imagem_url = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $nomeTemp = $_FILES['imagem']['tmp_name'];
        $nomeFinal = 'imagens/' . uniqid() . '_' . basename($_FILES['imagem']['name']);

        if (move_uploaded_file($nomeTemp, $nomeFinal)) {
            $imagem_url = $nomeFinal;
        } else {
            $mensagem = "Erro ao fazer upload da imagem.";
        }
    }

    if ($titulo && $artista && $preco && $descricao) {
        try {
            $stmt = $pdo->prepare("INSERT INTO discos 
                (discogs_id, titulo, artista, ano, gravadora, genero, estilo, preco, descricao, pais, imagem_url, estoque) 
                VALUES 
                (:discogs_id, :titulo, :artista, :ano, :gravadora, :genero, :estilo, :preco, :descricao, :pais, :imagem_url, :estoque)");
            
            $stmt->execute([
                ':discogs_id' => $discogs_id,
                ':titulo' => $titulo,
                ':artista' => $artista,
                ':ano' => $ano,
                ':gravadora' => $gravadora,
                ':genero' => $genero,
                ':estilo' => $estilo,
                ':preco' => $preco,
                ':descricao' => $descricao,
                ':pais' => $pais,
                ':imagem_url' => $imagem_url,
                ':estoque' => $estoque
            ]);

            $mensagem = "Disco cadastrado com sucesso!";
        } catch (PDOException $e) {
            $mensagem = "Erro: " . ($e->errorInfo[1] == 1062 ? "Discogs ID duplicado." : $e->getMessage());
        }
    } else {
        $mensagem = "Preencha todos os campos obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Disco</title>
    <link rel="stylesheet" href="css/cadastro_disco.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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


    <h1>Cadastrar Novo Disco</h1>
    <a href="admin_dashboard.php"><button>← Voltar para o Painel</button></a><br><br>

    <?php if ($mensagem): ?>
        <p><strong><?= htmlspecialchars($mensagem) ?></strong></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" onsubmit="return validarFormulario();">
        <label>Título*: <input type="text" name="titulo" required></label><br><br>
        <label>Artista*: <input type="text" name="artista" required></label><br><br>
        <label>Ano: <input type="number" name="ano" maxlength="4"></label><br><br>
        <label>Gravadora: <input type="text" name="gravadora"></label><br><br>
        <label>Gênero: <input type="text" name="genero"></label><br><br>
        <label>Estilo: <input type="text" name="estilo"></label><br><br>
        <label>Preço (R$)*: <input type="text" id="preco" name="preco" required></label><br><br>
        <label>Descrição*: <input type="text" name="descricao" required></label><br><br>
        <label>País: <input type="text" name="pais"></label><br><br>
        <label>Imagem do Disco*: <input type="file" name="imagem" accept="image/*" required></label><br><br>
        <label>Estoque: <input type="number" name="estoque" value="1" min="0"></label><br><br>

        <button type="submit">Cadastrar Disco</button>
    </form>

    <script>
    function validarFormulario() {
        const preco = document.getElementById('preco').value;
        if (isNaN(preco.replace(',', '.'))) {
            alert("Preço inválido!");
            return false;
        }
        return true;
    }

    // Máscara simples de preço com jQuery
    $(document).ready(function(){
        $('#preco').on('input', function(){
            this.value = this.value.replace(/[^0-9,]/g, '');
        });
    });
    </script>
</body>
</html>
