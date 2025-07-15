<?php
require_once 'conexao.php';
header('Content-Type: application/json');

$query = $_GET['busca'] ?? '';

try {
    if (empty($query)) {
        // Se não há busca, retorna alguns discos aleatórios
        $stmt = $pdo->prepare("SELECT id, titulo AS title, ano AS year, imagem_url AS cover_image, 
                              artista, genero, preco, estilo, descricao 
                              FROM discos 
                              ORDER BY id DESC 
                              LIMIT 20");
        $stmt->execute();
    } else {
        // Se há busca, filtra por título ou artista
        $stmt = $pdo->prepare("SELECT id, titulo AS title, ano AS year, imagem_url AS cover_image, 
                              artista, genero, preco, estilo, descricao 
                              FROM discos 
                              WHERE titulo LIKE ? OR artista LIKE ? 
                              ORDER BY titulo 
                              LIMIT 20");
        $busca_param = "%$query%";
        $stmt->execute([$busca_param, $busca_param]);
    }

    $discos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Garantir que cover_image não seja null
    $discos_formatados = array_map(function($disco) {
        return [
            'id' => $disco['id'],
            'title' => $disco['title'] ?? 'Título não informado',
            'year' => $disco['year'] ?? '',
            'cover_image' => $disco['cover_image'] ?? 'imgs/sem-capa.jpg',
            'artista' => $disco['artista'] ?? 'Artista não informado',
            'genero' => $disco['genero'] ?? '',
            'preco' => $disco['preco'] ?? '0.00',
            'estilo' => $disco['estilo'] ?? '',
            'descricao' => $disco['descricao'] ?? '',
            'fonte' => 'local'
        ];
    }, $discos);

    echo json_encode(['status' => 'ok', 'results' => $discos_formatados]);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
?>