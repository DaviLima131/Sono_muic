<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'conn.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("
    SELECT m.id, m.titulo, m.artista, m.genero, m.capa
    FROM favoritos f
    JOIN musicas m ON f.musica_id = m.id
    WHERE f.usuario_id = ?
    ORDER BY f.data_adicionado DESC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritos - Sono Musics</title>
    <link rel="stylesheet" href="css.css?v=<?= filemtime('css.css') ?>">
    <link rel="stylesheet" href="catalogo.css?v=<?= filemtime('catalogo.css') ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        audio {
            width: 100%;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="logo">
                <h1>Sono Musics</h1>
            </div>

            <nav class="nav-menu">
                <ul>
                    <li class="nav-item"><a href="index.php"><span class="material-icons">home</span>Início</a></li>
                    <li class="nav-item"><a href="upload.php"><span class="material-icons">upload</span>Upload</a></li>
                    <li class="nav-item"><a href="catalogo.php"><span class="material-icons">library_music</span>Catálogo</a></li>
                    <li class="nav-item active"><a href="favoritos.php"><span class="material-icons">favorite</span>Favoritos</a></li>
                </ul>
            </nav>

            <a href="logout.php" class="logout-btn">
                <span class="material-icons">logout</span>Sair
            </a>
        </aside>

        <main class="content-container">
            <h2> Músicas Favoritas</h2>
            <section class="music-list">
                <?php if ($result->num_rows === 0): ?>
                    <p>Você ainda não favoritou nenhuma música. Vá até o <a href="catalogo.php">catálogo</a> e clique no coração!</p>
                <?php else: ?>
                    <?php while ($musica = $result->fetch_assoc()): ?>
                        <div class="music-card">
                            <a href="musica.php?id=<?= (int)$musica['id'] ?>" class="music-cover-link">
                                <div class="music-cover">
                                    <?php if (!empty($musica['capa'])): ?>
                                        <img src="imagem.php?id=<?= (int)$musica['id'] ?>&t=<?= time() ?>" alt="Capa da Música">
                                    <?php else: ?>
                                        <img src="sem_capa.png" alt="Sem Capa">
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="music-info">
                                <h3>
                                    <a href="musica.php?id=<?= (int)$musica['id'] ?>" style="text-decoration:none;color:#fff;">
                                        <?= htmlspecialchars($musica['titulo'] ?? 'Sem título') ?>
                                    </a>
                                </h3>
                                <p>
                                    <a href="artista.php?nome=<?= urlencode($musica['artista'] ?? 'Desconhecido') ?>" class="artist-btn">
                                        <?= htmlspecialchars($musica['artista'] ?? 'Artista desconhecido') ?>
                                    </a>
                                </p>
                                <div class="genero"><?= htmlspecialchars($musica['genero'] ?? 'Não informado') ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                <?php endif; ?>
            </section>
        </main>
    </div>
</body>

</html>