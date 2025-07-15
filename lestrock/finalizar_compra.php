<?php
session_start();
require_once 'conexao.php';

$forma_pagamento = $_POST['forma_pagamento'] ?? null;
if (!$forma_pagamento) {
    echo "<script>alert('Selecione uma forma de pagamento.'); window.history.back();</script>";
    exit();
}

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado para finalizar a compra.'); window.location='login.php';</script>";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar carrinho ativo
$sql = "SELECT id FROM carrinhos WHERE usuario_id = ? AND finalizado = 0 LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$carrinho = $stmt->fetch();

if (!$carrinho) {
    echo "<script>alert('Nenhum carrinho ativo encontrado.'); window.location='carrinho.php';</script>";
    exit();
}

$carrinho_id = $carrinho['id'];

// Buscar itens do carrinho
$sql = "SELECT disco_id, quantidade FROM itens_carrinho WHERE carrinho_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$carrinho_id]);
$itens = $stmt->fetchAll();

if (empty($itens)) {
    echo "<script>alert('Carrinho vazio.'); window.location='carrinho.php';</script>";
    exit();
}

// Calcular total e validar estoque
$total = 0;
$itens_validados = [];

foreach ($itens as $item) {
    $sqlPreco = "SELECT preco, estoque FROM discos WHERE id = ?";
    $stmtPreco = $pdo->prepare($sqlPreco);
    $stmtPreco->execute([$item['disco_id']]);
    $disco = $stmtPreco->fetch();

    if (!$disco || $disco['estoque'] < $item['quantidade']) {
        echo "<script>alert('Estoque insuficiente para um dos itens.'); window.location='carrinho.php';</script>";
        exit();
    }

    $subtotal = $disco['preco'] * $item['quantidade'];
    $total += $subtotal;
    
    // Armazenar dados validados para usar depois
    $itens_validados[] = [
        'disco_id' => $item['disco_id'],
        'quantidade' => $item['quantidade'],
        'preco_unitario' => $disco['preco']
    ];
}

try {
    // Iniciar transação
    $pdo->beginTransaction();

    // Criar pedido com status 'pago'
    $sql = "INSERT INTO pedidos (usuario_id, carrinho_id, total, data_pedido, status) VALUES (?, ?, ?, NOW(), 'pago')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $carrinho_id, $total]);
    $pedido_id = $pdo->lastInsertId();

    // Criar registros na tabela itens_pedido
    foreach ($itens_validados as $item) {
        $sqlItem = "INSERT INTO itens_pedido (pedido_id, disco_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
        $stmtItem = $pdo->prepare($sqlItem);
        $stmtItem->execute([$pedido_id, $item['disco_id'], $item['quantidade'], $item['preco_unitario']]);
    }

    // Criar pagamento com status_pagamento 'pago'
    $sql = "INSERT INTO pagamentos (
      pedido_id,
      usuario_id,
      forma_pagamento,
      valor_pago,
      status_pagamento,
      data_pagamento
    ) VALUES (?, ?, ?, ?, 'pago', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pedido_id, $usuario_id, $forma_pagamento, $total]);

    // Atualizar estoque
    foreach ($itens_validados as $item) {
        $sql = "UPDATE discos SET estoque = estoque - ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$item['quantidade'], $item['disco_id']]);
    }

    // Finalizar carrinho
    $sql = "UPDATE carrinhos SET finalizado = 1 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$carrinho_id]);

    // Confirmar transação
    $pdo->commit();

    // Redirecionar para o comprovante
    echo "<script>
        alert('Compra finalizada com sucesso!');
        window.location='comprovante.php?pedido_id=" . $pedido_id . "';
    </script>";
    exit();

} catch (Exception $e) {
    // Desfazer transação em caso de erro
    $pdo->rollBack();
    echo "<script>alert('Erro ao processar compra: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    exit();
}
?>