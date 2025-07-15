<?php
session_start();
require_once 'conexao.php';

if (!isset($_GET['id'])) {
    echo "Disco não encontrado.";
    exit;
}

$disco_id = intval($_GET['id']);
$fonte = $_GET['fonte'] ?? 'discogs';

if ($fonte === 'local') {
    $stmt = $pdo->prepare("SELECT * FROM discos WHERE id = ?");
    $stmt->execute([$disco_id]);
    $disco = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$disco) {
        echo "Disco local não encontrado.";
        exit;
    }

    // Dados do banco local
    $id = $disco['id'];
    $titulo = $disco['titulo'];
    $artista = $disco['artista'];
    $imagem_url = $disco['imagem_url'];
    $genero = $disco['genero'] ?? 'Não informado';
    $ano = $disco['ano'] ?? 'Não informado';
    $estilo = $disco['estilo'] ?? 'Não informado';
    $descricao = $disco['descricao'] ?? 'Sem descrição';
    $preco = $disco['preco'];
    $gravadora = $disco['gravadora'] ?? 'Não informada';
    $pais = $disco['pais'] ?? 'Não informado';
    $estoque = $disco['estoque'] ?? 0; // ← lê o estoque

} else {
    // API Discogs
    $key = "CdGODaeIwjOSStGpHleL";
    $secret = "vSgbqlAacxUTOCXAKXKgaBuTUEoEXBRs";
    $url = "https://api.discogs.com/releases/$disco_id?key=$key&secret=$secret";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'LetsRockDiscoStore/1.0');
    $response = curl_exec($ch);
    curl_close($ch);

    $disco = json_decode($response, true);

    if (!$disco || isset($disco['message'])) {
        echo "Erro ao carregar detalhes do disco.";
        exit;
    }

    // Dados da API
    $id = $disco['id'];
    $titulo = $disco['title'] ?? 'Sem título';
    $artista = $disco['artists'][0]['name'] ?? 'Desconhecido';
    $imagem_url = $disco['images'][0]['uri'] ?? 'imgs/sem-capa.jpg';
    $genero = isset($disco['genres']) ? implode(', ', $disco['genres']) : 'Desconhecido';
    $ano = $disco['year'] ?? 'Desconhecido';
    $estilo = isset($disco['styles']) ? implode(', ', $disco['styles']) : 'Não informado';
    $descricao = $disco['notes'] ?? 'Sem descrição';
    $gravadora = $disco['labels'][0]['name'] ?? 'Não informada';
    $pais = $disco['country'] ?? 'Não informado';
    $preco = rand(8000, 20000) / 100; // preço aleatório
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo); ?> - Detalhes</title>
    <link rel="stylesheet" href="css/prod.css">
</head>
<body>
<header class="topo">
        <div class="logo">
        <img src="imgs/logo.png" alt="Logo da página" title="Logo da página"> <!-- logo da let's rock-->
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

</div>
    </header>

  <div class="detalhes-container">
    <h1><?php echo htmlspecialchars($titulo); ?></h1>
    <img src="<?php echo $imagem_url; ?>" alt="Capa do disco" style="max-width: 300px;">
    <p class="preco"><strong>Preço:</strong> R$ <?php echo number_format($preco, 2, ',', '.'); ?></p>
    <p><strong>Artista:</strong> <?php echo htmlspecialchars($artista); ?></p>
    <p><strong>Gênero:</strong> <?php echo htmlspecialchars($genero); ?></p>
    <p><strong>Ano:</strong> <?php echo htmlspecialchars($ano); ?></p>
    <p><strong>Estilo:</strong> <?php echo htmlspecialchars($estilo); ?></p>
    <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($descricao)); ?></p>

    <?php if ($fonte === 'local' && $estoque == 0): ?>
      
      <p class="mensagem-estoque-esgotado">
    Produto esgotado no estoque!
      </p>


<?php else: ?>
    <form action="adicionar_carrinho.php" method="post" style="text-align: center; margin-top: 30px;">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
        <input type="hidden" name="preco" value="<?php echo $preco; ?>">
        <input type="hidden" name="imagem_url" value="<?php echo $imagem_url; ?>">
        <button type="submit" class="botao-carrinho">Adicionar ao Carrinho</button>
    </form>
<?php endif; ?>


  </div>
  

  <footer>
      <div class="footer-container">
        <!-- link - redes sociais -->
        <div class="footer-section">
          <a href="https://instagram.com/letsrock_discostore" target="_blank">
            <img src="imgs/instagram.png" alt="Instagram" class="icon">
          </a>
          <a href="https://x.com/letsrock_ds" target="_blank">
            <img src="imgs/twitter.png" alt="X (Twitter)" class="icon">
          </a>
          <p>SIGA-NOS</p>
        </div>
    
        <!-- logo enorme no meio -->
        <div class=".logo">
          <a href="dashboard.php" target="_blank">
          <img src="imgs/logo.png" alt="Logo Let's Rock Disco Store" class="logo">
          </a>
        </div>
    
        <!-- nossos contatos -->
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
    </footer> <!-- fim do footer/cabeçalho -->
</body>
</html>

