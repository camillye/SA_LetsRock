    <?php
    session_start();
    require_once 'conexao.php';

    // Verificar se usuário está logado
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];

    // Verificar se disco_id foi enviado e não está vazio
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo "<script>alert('Erro: ID do disco não foi fornecido.'); window.history.back();</script>";
        exit();
    }
    $disco_id = (int)$_POST['id'];
    $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;

    // Validar se disco_id é válido
    if ($disco_id <= 0) {
        echo "<script>alert('Erro: ID do disco inválido.'); window.history.back();</script>";
        exit();
    }

    // Validar quantidade
    if ($quantidade <= 0) {
        $quantidade = 1;
    }

    try {
        // Verificar se o disco existe no banco
        $sql = "SELECT id, titulo, estoque FROM discos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$disco_id]);
        $disco = $stmt->fetch();

        if (!$disco) {
            echo "<script>alert('Erro: Disco não encontrado.'); window.history.back();</script>";
            exit();
        }

        // Verificar estoque
        if ($disco['estoque'] < $quantidade) {
            echo "<script>alert('Erro: Estoque insuficiente. Disponível: {$disco['estoque']}'); window.history.back();</script>";
            exit();
        }

        // Busca ou cria o carrinho do usuário
        $sql = "SELECT id FROM carrinhos WHERE usuario_id = ? AND finalizado = 0 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $carrinho = $stmt->fetch();

        if (!$carrinho) {
            $pdo->prepare("INSERT INTO carrinhos (usuario_id) VALUES (?)")->execute([$usuario_id]);
            $carrinho_id = $pdo->lastInsertId();
        } else {
            $carrinho_id = $carrinho['id'];
        }

        // Verifica se o item já está no carrinho
        $sql = "SELECT id, quantidade FROM itens_carrinho WHERE carrinho_id = ? AND disco_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$carrinho_id, $disco_id]);
        $item = $stmt->fetch();

        if ($item) {
            // Se já existe, atualiza a quantidade
            $nova_quantidade = $item['quantidade'] + $quantidade;
            $pdo->prepare("UPDATE itens_carrinho SET quantidade = ? WHERE id = ?")
                ->execute([$nova_quantidade, $item['id']]);
            
            echo "<script>alert('Quantidade atualizada no carrinho!'); window.location='carrinho.php';</script>";
        } else {
            // Se não existe, adiciona novo item
            $pdo->prepare("INSERT INTO itens_carrinho (carrinho_id, disco_id, quantidade) VALUES (?, ?, ?)")
                ->execute([$carrinho_id, $disco_id, $quantidade]);
            
            echo "<script>alert('Disco adicionado ao carrinho!'); window.location='carrinho.php';</script>";
        }

    } catch (PDOException $e) {
        // Tratamento de erro
        echo "<script>alert('Erro ao adicionar ao carrinho: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
    ?>

