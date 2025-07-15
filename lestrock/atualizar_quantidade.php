<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado para atualizar o carrinho.'); window.location='login.php';</script>";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Verifica se os dados vieram pelo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $nova_quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 0;

    if ($item_id <= 0 || $nova_quantidade <= 0) {
        echo "<script>alert('Dados inválidos.'); window.history.back();</script>";
        exit;
    }

    // Busca o item no carrinho (verifica se pertence ao usuário e está em carrinho ativo)
    $sql = "
        SELECT ic.disco_id, d.estoque
        FROM itens_carrinho ic
        JOIN carrinhos c ON ic.carrinho_id = c.id
        JOIN discos d ON ic.disco_id = d.id
        WHERE ic.id = ? AND c.usuario_id = ? AND c.finalizado = 0
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id, $usuario_id]);
    $item = $stmt->fetch();

    if (!$item) {
        echo "<script>alert('Item não encontrado ou não pertence ao seu carrinho.'); window.history.back();</script>";
        exit;
    }

    if ($nova_quantidade > $item['estoque']) {
        echo "<script>alert('Estoque insuficiente. Disponível: {$item['estoque']} unidades.'); window.history.back();</script>";
        exit;
    }

    // Atualiza a quantidade
    $sql = "UPDATE itens_carrinho SET quantidade = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$nova_quantidade, $item_id])) {
        echo "<script>alert('Quantidade atualizada com sucesso!'); window.location='carrinho.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar a quantidade.'); window.history.back();</script>";
    }
}
?>
