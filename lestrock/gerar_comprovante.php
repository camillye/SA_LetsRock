<?php
session_start();
require_once 'conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

// Verificar se o pedido_id foi fornecido
if (!isset($_GET['pedido_id']) || empty($_GET['pedido_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do pedido não fornecido']);
    exit();
}

$pedido_id = $_GET['pedido_id'];
$usuario_id = $_SESSION['usuario_id'];

try {
    // Buscar dados do pedido e pagamento
    $sql = "SELECT 
                p.id AS pedido_id,
                p.total,
                p.status AS status_pedido,
                p.data_pedido,
                pg.status_pagamento,
                pg.forma_pagamento,
                pg.valor_pago,
                pg.codigo_transacao,
                pg.data_pagamento,
                u.nome AS nome_usuario,
                u.email
            FROM pedidos p
            LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
            LEFT JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.id = ? AND p.usuario_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pedido_id, $usuario_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
        exit();
    }
    
    // Verificar se o pagamento foi realizado
    if ($pedido['status_pagamento'] !== 'pago') {
        echo json_encode(['success' => false, 'message' => 'Comprovante disponível apenas para pedidos pagos']);
        exit();
    }
    
    // Buscar itens do pedido
    $sql_itens = "SELECT 
                    ip.quantidade,
                    ip.preco_unitario,
                    d.titulo,
                    d.artista
                  FROM itens_pedido ip
                  JOIN discos d ON d.id = ip.disco_id
                  WHERE ip.pedido_id = ?";
    
    $stmt_itens = $pdo->prepare($sql_itens);
    $stmt_itens->execute([$pedido_id]);
    $itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);
    
    // Adicionar itens ao pedido
    $pedido['itens'] = $itens;
    
    // Retornar dados em JSON
    echo json_encode([
        'success' => true,
        'pedido' => $pedido
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?>