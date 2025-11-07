<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'conn.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'not_logged']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$musica_id = isset($_POST['musica_id']) ? (int)$_POST['musica_id'] : 0;

if (!$musica_id) {
    echo json_encode(['error' => 'invalid_id']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND musica_id = ?");
$stmt->bind_param("ii", $usuario_id, $musica_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $conn->query("DELETE FROM favoritos WHERE usuario_id = $usuario_id AND musica_id = $musica_id");
    echo json_encode(['status' => 'removed']);
} else {
    $stmt = $conn->prepare("INSERT INTO favoritos (usuario_id, musica_id, data_adicionado) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $usuario_id, $musica_id);
    $stmt->execute();
    echo json_encode(['status' => 'added']);
}
