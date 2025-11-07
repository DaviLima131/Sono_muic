<?php
session_start();
require_once 'conn.php';

header('X-Content-Type-Options: nosniff');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo 'ID inválido';
    exit;
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare('SELECT arquivo_mp3 FROM musicas WHERE id = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($arquivo_mp3);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo 'Arquivo não encontrado';
    exit;
}
$stmt->close();
$conn->close();

$filePath = 'uploads/musicas/' . basename($arquivo_mp3);
if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    echo 'Arquivo não encontrado';
    exit;
}

$fileSize = filesize($filePath);
$start = 0;
$length = $fileSize;
$end = $fileSize - 1;

header('Content-Type: audio/mpeg');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=604800');

// Suporte a Range
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE']; // e.g. bytes=12345-
    if (preg_match('/bytes=([0-9]*)-([0-9]*)/i', $range, $m)) {
        if ($m[1] !== '') {
            $start = (int) $m[1];
        }
        if ($m[2] !== '') {
            $end = (int) $m[2];
        }
        if ($end >= $fileSize) {
            $end = $fileSize - 1;
        }
        if ($start > $end || $start >= $fileSize) {
            header('Content-Range: bytes */' . $fileSize);
            http_response_code(416); // Range Not Satisfiable
            exit;
        }
        $length = $end - $start + 1;
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Content-Length: ' . $length);
        http_response_code(206); // Partial Content
    }
} else {
    header('Content-Length: ' . $fileSize);
}

$chunkSize = 8192; // 8KB
$fp = fopen($filePath, 'rb');
if ($fp === false) {
    http_response_code(500);
    exit;
}

// Pula até o início
if ($start > 0) {
    fseek($fp, $start);
}

// Envia em blocos
$bytesLeft = $length;
while ($bytesLeft > 0 && !feof($fp)) {
    $read = ($bytesLeft > $chunkSize) ? $chunkSize : $bytesLeft;
    $buffer = fread($fp, $read);
    if ($buffer === false) {
        break;
    }
    echo $buffer;
    flush();
    if (function_exists('fastcgi_finish_request')) {
        // não finalizar aqui; apenas garantir flush no FastCGI
    }
    $bytesLeft -= strlen($buffer);
}

fclose($fp);
exit;
?>


