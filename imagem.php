<?php
// filepath: c:\Users\cedro.0990\Desktop\trabalho\image.php
require_once 'conn.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    // Se o ID for inválido, enviamos a capa padrão
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400, immutable');
    readfile('sem_capa.png');
    exit;
}

$sql = "SELECT capa FROM musicas WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($capa);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
    $stmt->close(); // Fechar statement antes de enviar dados

    if (empty($capa)) {
        // Caso a capa no DB seja NULL/vazia, exibe a capa padrão
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400, immutable');
        readfile('sem_capa.png');
        exit;
    }

    // --- OTIMIZAÇÃO: Detecção de Mime Type e Content-Length ---
    
    // Assume que a imagem é JPEG como padrão (mais comum)
    $mime = 'image/jpeg';
    
    // Detecta tipo por assinatura simples
    if (strncmp($capa, "\x89PNG\x0D\x0A\x1A\x0A", 8) === 0) {
        $mime = 'image/png';
    } elseif (strncmp($capa, "GIF", 3) === 0) {
        $mime = 'image/gif';
    }

    header('Content-Type: ' . $mime);
    
    // **NOVO**: Adiciona o tamanho do conteúdo para o navegador
    header('Content-Length: ' . strlen($capa)); 
    
    header('Cache-Control: public, max-age=86400, immutable');
    echo $capa;
    exit; // Garantir que nada mais seja executado
    
} else {
    // Música não encontrada, envia capa padrão
    $stmt->close();
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400, immutable');
    readfile('sem_capa.png');
}
?>