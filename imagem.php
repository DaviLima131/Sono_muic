<?php
// filepath: c:\Users\cedro.0990\Desktop\trabalho\image.php
require_once 'conn.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT capa FROM musicas WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($capa);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
    // Detecta tipo (simples, pode melhorar)
    header("Content-Type: image/jpeg");
    echo $capa;
} else {
    header("Content-Type: image/png");
    readfile("sem_capa.png");
}
$stmt->close();
?>