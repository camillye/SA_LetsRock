<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_email'] !== 'admin@letsrock.com') {
    echo "<script>alert('Acesso restrito!');window.location.href='login.php';</script>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "Disco não especificado.";
    exit;
}

$id = intval($_GET['id']);

// Buscar disco
$stmt = $pdo->prepare("SELECT * FROM discos WHERE id = :id");
$stmt->execute([':id' => $id]);
$disco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$disco) {
    echo "Disco não encontrado.";
    exit;
}

$mensagem = '';

// Atualizar dados
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
    $imagem_url = $disco['imagem_url'];

    // Se nova imagem foi enviada
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $nomeTemp = $_FILES['imagem']['tmp_name'];
        $nomeFinal = 'imagens/' . uniqid() . '_' . basename($_FILES['imagem']['name']);
        if (move_uploaded_file($nomeTemp, $nomeFinal)) {
            $imagem_url = $nomeFinal;
        } else {
            $mensagem = "Erro ao fazer upload da imagem.";
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE discos SET 
            discogs_id = :discogs_id,
            titulo = :titulo,
            artista = :artista,
            ano = :ano,
            gravadora = :gravadora,
            genero = :genero,
            estilo = :estilo,
            preco = :preco,
            descricao = :descricao,
            pais = :pais,
            imagem_url = :imagem_url,
            estoque = :estoque
            WHERE id = :id");
        
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
            ':estoque' => $estoque,
            ':id' => $id
        ]);

        $mensagem = "Disco atualizado com sucesso!";
    } catch (PDOException $e) {
        $mensagem = "Erro ao atualizar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Disco</title>
    <link rel="stylesheet" href="css/cadastro_disco.css">
</head>
<body>
    <h1>Editar Disco</h1>
    <a href="admin_dashboard.php"><button>← Voltar para o Painel</button></a><br><br>


    <?php if ($mensagem): ?>
        <p><strong><?= htmlspecialchars($mensagem) ?></strong></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Discogs ID: <input type="number" name="discogs_id" value="<?= htmlspecialchars($disco['discogs_id']) ?>"></label>
        <label>Título: <input type="text" name="titulo" required value="<?= htmlspecialchars($disco['titulo']) ?>"></label>
        <label>Artista: <input type="text" name="artista" required value="<?= htmlspecialchars($disco['artista']) ?>"></label>
        <label>Ano: <input type="number" name="ano" value="<?= htmlspecialchars($disco['ano']) ?>"></label>
        <label>Gravadora: <input type="text" name="gravadora" value="<?= htmlspecialchars($disco['gravadora']) ?>"></label>
        <label>Gênero: <input type="text" name="genero" value="<?= htmlspecialchars($disco['genero']) ?>"></label>
        <label>Estilo: <input type="text" name="estilo" value="<?= htmlspecialchars($disco['estilo']) ?>"></label>
        <label>Preço (R$): <input type="text" name="preco" value="<?= number_format($disco['preco'], 2, ',', '.') ?>"></label>
        <label>Descrição: <input type="text" name="descricao" required value="<?= htmlspecialchars($disco['descricao']) ?>"></label>
        <label>País: <input type="text" name="pais" value="<?= htmlspecialchars($disco['pais']) ?>"></label>
        <label>Imagem Atual:</label>
        <?php if ($disco['imagem_url']): ?>
            <img src="<?= htmlspecialchars($disco['imagem_url']) ?>" width="100"><br>
        <?php endif; ?>
        <label>Nova Imagem (opcional): <input type="file" name="imagem" accept="image/*"></label>
        <label>Estoque: <input type="number" name="estoque" value="<?= htmlspecialchars($disco['estoque']) ?>"></label>

        <button type="submit">Salvar Alterações</button>
        <a href="admin_estoque.php"><button type="button">Voltar</button></a>
    </form>
</body>
</html>
