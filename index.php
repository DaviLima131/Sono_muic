<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
session_start();
require_once 'conn.php';

// bloqueia acesso se não estiver logado
if (!isset($_SESSION['usuario'])) {
    $_SESSION['message'] = "Por favor, faça login para acessar o sistema.";
    $_SESSION['message_type'] = "warning";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sono Musics - Início</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="banner.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="logo">
                <h1>Sono Musics</h1>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li class="nav-item active"><a href="index.php"><span class="material-icons">home</span>Início</a></li>
                    <li class="nav-item"><a href="upload.php"><span class="material-icons">upload</span>Upload</a></li>
                    <li class="nav-item"><a href="catalogo.php"><span class="material-icons">library_music</span>Catálogo</a></li>
                    <li class="nav-item"><a href="favoritos.php"><span class="material-icons">favorite</span>Favoritos</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout-btn">
                <span class="material-icons">logout</span>Sair
            </a>
        </aside>

        <main class="content-container">
            <header>
                <h1>Bem-vindo ao Sono Musics</h1>
            </header>
            <div class="carousel-container">
                <div class="carousel-img">
                    <img src="music production  sonora (2) (1).png" alt="Banner principal Sono Musics">
                </div>
            </div>

            <!-- Sobre o site -->
            <section class="sobre-site">
                <h2>Sobre o <span class="highlight">Sono Musics</span></h2>
                <p>
                    O <span class="highlight">Sono Musics</span> é uma plataforma criada para conectar pessoas através da música. 
                    Aqui, você pode enviar suas próprias faixas, descobrir novos artistas e criar uma biblioteca personalizada de sons que combinam com o seu estilo. 
                    Nosso objetivo é oferecer um espaço simples, moderno e envolvente, onde a criatividade sonora pode fluir sem limites.
                </p>
                <p>
                    Desenvolvido com dedicação e ritmo, o projeto busca valorizar a expressão musical independente, 
                    transformando cada usuário em parte de uma comunidade apaixonada por música.
                </p>
            </section>
        </main>
    </div>
</body>
</html>
