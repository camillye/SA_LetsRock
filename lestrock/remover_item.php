<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Você precisa estar logado para realizar essa ação.'); window.location='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    $usuario_id = $_SESSION['usuario_id'];

    try {
        // Verificar se o disco pertence a um carrinho do usuário logado
        $sql = "SELECT ic.id FROM itens_carrinho ic
                JOIN carrinhos c ON ic.carrinho_id = c.id
                WHERE ic.id = :item_id AND c.usuario_id = :usuario_id AND c.finalizado = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':item_id' => $item_id,
            ':usuario_id' => $usuario_id
        ]);
        $item = $stmt->fetch();

        if ($item) {
            // Remover o disco do carrinho
            $sql = "DELETE FROM itens_carrinho WHERE id = :item_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':item_id' => $item_id]);

            header('Location: carrinho.php');
            exit();
        } else {
            echo "<script>alert('Item não encontrado ou você não tem permissão para removê-lo.'); window.location='carrinho.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "Erro ao remover item: " . $e->getMessage();
    }
} else {
    echo "<script>alert('Requisição inválida.'); window.location='carrinho.php';</script>";
    exit();
}
