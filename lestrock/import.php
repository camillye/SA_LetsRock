<?php
require 'conexao.php';

// === CONFIGURAÇÃO ===
$token = 'VYDCUuUzLhDCKknVLCjSrdGObRGeyLVDexwPyXgH'; // Substitua pelo seu token da Discogs
$genero = 'legião urbana';
$url = "https://api.discogs.com/database/search?q=" . urlencode($genero) . "&format=vinyl&type=release&token=$token";

// === REQUISIÇÃO SEGURA COM cURL ===
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'DiscogsAPIImport/1.0'); // Obrigatório pela Discogs
$response = curl_exec($ch);

if (curl_errno($ch)) {
    die('Erro na requisição cURL: ' . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("Erro HTTP na API Discogs: Código $httpCode");
}

$data = json_decode($response, true);

// === INSERIR NO BANCO ===
$importados = 0;

foreach ($data['results'] as $item) {
    $titulo   = $item['title']         ?? 'Desconhecido';
    $artista  = $item['title']         ?? 'Desconhecido'; // (Pode incluir artista no título)
    $ano      = $item['year']          ?? 0;
    $genero   = $item['genre'][0]      ?? 'Desconhecido';
    $pais     = $item['country']       ?? 'Desconhecido';
    $imagem   = $item['cover_image']   ?? '';
    $estoque  = rand(1, 10);
    $preco    = rand(9000, 20000) / 100.0; // Entre 90.00 e 200.00

    // Inserir no banco
    $stmt = $pdo->prepare("INSERT INTO discos (titulo, artista, ano, genero, preco, pais, imagem_url, estoque)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([$titulo, $artista, $ano, $genero, $preco, $pais, $imagem, $estoque]);
    $importados++;
}

echo "Importação concluída com sucesso! $importados discos adicionados.";
?>