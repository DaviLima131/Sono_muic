<?php
$host = "localhost";  
$usuario = "root";    
$senha = "root";        
$banco = "sono_musics";  

$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>