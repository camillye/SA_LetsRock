<?php
session_start();
require_once 'conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    echo "Disco não encontrado.";
    exit;
}

$disco_id = intval($_GET['id']);

// Buscar disco no banco local
try {
    $stmt = $pdo->prepare("SELECT * FROM discos WHERE id = :id");
    $stmt->bindParam(':id', $disco_id);
    $stmt->execute();
    $disco = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$disco) {
        echo "Disco não encontrado.";
        exit;
    }
} catch (PDOException $e) {
    echo "Erro ao buscar disco: " . $e->getMessage();
    exit;
}

// Dados do disco
$id = $disco['id'];
$titulo = $disco['titulo'];
$artista = $disco['artista'];
$imagem_url = $disco['imagem_url'];
$genero = $disco['genero'] ?? 'Não informado';
$ano = $disco['ano'] ?? 'Não informado';
$estilo = $disco['estilo'] ?? 'Não informado';
$descricao = $disco['descricao'] ?? 'Sem descrição disponível';
$preco = $disco['preco'];
$gravadora = $disco['gravadora'] ?? 'Não informada';
$pais = $disco['pais'] ?? 'Não informado';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo); ?> - Detalhes</title>
    <link rel="stylesheet" href="css/discoslocal.css">
</head>
<body>
<header class="topo">
    <div class="logo">
        <img src="imgs/logo.png" alt="Logo da página" title="Logo da página">
    </div>

    <div id="form-busca">
        <input type="text" id="busca" placeholder="Digite o nome do artista ou álbum...">
        <button id="botao-buscar">Buscar</button>

        <a href="config.php" id="botao-configuracoes" title="Configurações">
            <img src="imgs/config.png" alt="Configurações" />
        </a>

        <a href="carrinho.php" id="botao-carrinho" title="Carrinho">
            <img src="imgs/carrinho.png" alt="Carrinho" />
        </a>

        <a href="logout.php" id="botao-logout" title="Logout">
            <img src="imgs/logout.png" alt="Logout"/>
        </a>

        <a href="meus_pedidos.php" id="botao-meuspedidos" title="Meus Pedidos">
            <img src="imgs/box.png" alt="Meus pedidos"/>
        </a>
    </div>
</header>

<div class="detalhes-container">
    <h1><?php echo htmlspecialchars($titulo); ?></h1>
    <div style="text-align: center;">
        <img src="<?php echo htmlspecialchars($imagem_url); ?>" alt="Capa do disco" style="max-width: 300px;" onerror="this.src='imgs/sem-capa.jpg'">
    </div>
    
    <div class="badge-local" style="background: #28a745; color: white; padding: 5px 10px; border-radius: 15px; display: inline-block; margin: 10px 0; font-size: 12px;">
        📦 Produto da Loja
    </div>
    
    <p class="preco"><strong>Preço:</strong> R$ <?php echo number_format($preco, 2, ',', '.'); ?></p>
    <p><strong>Artista:</strong> <?php echo htmlspecialchars($artista); ?></p>
    <p><strong>Gênero:</strong> <?php echo htmlspecialchars($genero); ?></p>
    <p><strong>Ano:</strong> <?php echo htmlspecialchars($ano); ?></p>
    <?php if ($estilo && $estilo !== 'Não informado'): ?>
        <p><strong>Estilo:</strong> <?php echo htmlspecialchars($estilo); ?></p>
    <?php endif; ?>
    <?php if ($gravadora && $gravadora !== 'Não informada'): ?>
        <p><strong>Gravadora:</strong> <?php echo htmlspecialchars($gravadora); ?></p>
    <?php endif; ?>
    <?php if ($pais && $pais !== 'Não informado'): ?>
        <p><strong>País:</strong> <?php echo htmlspecialchars($pais); ?></p>
    <?php endif; ?>
    <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($descricao)); ?></p>

    <form action="adicionar_carrinho.php" method="post" style="text-align: center; margin-top: 30px;">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
        <input type="hidden" name="preco" value="<?php echo $preco; ?>">
        <input type="hidden" name="imagem_url" value="<?php echo htmlspecialchars($imagem_url); ?>">
        <button type="submit" class="botao-carrinho">Adicionar ao Carrinho</button>
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <a href="dashboard.php" style="text-decoration: none; background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px;">← Voltar para o catálogo</a>
    </div>
</div>

<footer>
    <div class="footer-container">
        <div class="footer-section">
            <a href="https://instagram.com/letsrock_discostore" target="_blank">
                <img src="imgs/instagram.png" alt="Instagram" class="icon">
            </a>
            <a href="https://x.com/letsrock_ds" target="_blank">
                <img src="imgs/twitter.png" alt="X (Twitter)" class="icon">
            </a>
            <p>SIGA-NOS</p>
        </div>

        <div class=".logo">
            <a href="dashboard.php">
                <img src="imgs/logo.png" alt="Logo Let's Rock Disco Store" class="logo">
            </a>
        </div>

        <div class="footer-section">
            <a href="mailto:contato@letsrock.com">
                <img src="imgs/gmail.png" alt="Email" class="icon">
            </a>
            <a href="https://wa.me/5547999169146" target="_blank">
                <img src="imgs/whatsapp.png" alt="WhatsApp" class="icon">
            </a>
            <p>CONTATE-NOS</p>
        </div>
    </div>
</footer>
</body>
</html>