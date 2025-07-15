<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado para acessar o carrinho.'); window.location='login.php';</script>";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar o carrinho ativo do usuário
$sql = "SELECT id FROM carrinhos WHERE usuario_id = ? AND finalizado = 0 LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$carrinho = $stmt->fetch();

$total = 0;
$itens = [];

if ($carrinho) {
    $carrinho_id = $carrinho['id'];

    $sql = "SELECT 
                ic.id AS item_id, 
                ic.disco_id, 
                d.titulo, 
                d.artista, 
                d.preco, 
                d.imagem_url, 
                ic.quantidade
            FROM itens_carrinho ic
            JOIN discos d ON ic.disco_id = d.id
            WHERE ic.carrinho_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$carrinho_id]);
    $itens = $stmt->fetchAll();

    foreach ($itens as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho - Let's Rock Disco Store</title>
    <link rel="stylesheet" href="css/carrinho.css">
    <script src="js/carrinho.js" defer></script>
</head>
<body>
<header class="topo">
  <div class="logo">
    <a href="dashboard.php">
      <img src="imgs/voltar.png" alt="Voltar" class="logo">
    </a>
  </div>
</header>
    <div class="carrinho-container">
        <h2>Seu Carrinho</h2>

        <?php if (empty($itens)): ?>
            <div class="vazio">Seu carrinho está vazio.</div>
        <?php else: ?>
            <?php foreach ($itens as $item): ?>
                <div class="item">
                    <img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>">
                    <div class="item-info">
                        <p><strong><?= htmlspecialchars($item['titulo']) ?></strong> - <?= htmlspecialchars($item['artista']) ?></p>
                        <p>Preço: R$ <?= number_format($item['preco'], 2, ',', '.') ?></p>
                        <form class="quantidade-form" action="atualizar_quantidade.php" method="POST">
                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                            <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1">
                            <button type="submit">Atualizar</button>
                        </form>
                        <form action="remover_item.php" method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                            <button type="submit">Remover</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total">
                Total: R$ <?= number_format($total, 2, ',', '.') ?>
            </div>

            <form action="pagamento.php" method="POST">
                <button class="finalizar-btn" type="submit">Ir para o pagamento</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>


