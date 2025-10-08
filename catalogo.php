<?php
require_once 'conn.php';

$result = $conn->query("SELECT * FROM musicas ORDER BY data_upload DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sono Musics</title>
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="catalogo.css">
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
                    <li class="nav-item">
                        <a href="index.php">
                            <span class="material-icons">home</span>
                            Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="upload.php">
                            <span class="material-icons">upload</span>
                            Upload
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="catalogo.php">
                            <span class="material-icons">library_music</span>
                            Catálogo
                        </a>
                    </li>
                </ul>
            </nav>

        </aside>
        <main class="content-container">
            <h2>Catálogo de Músicas</h2>

            <section class="music-list">
                <?php while ($musica = $result->fetch_assoc()): ?>
                    <div class="music-item" style="margin-bottom:20px;">
                        <!-- Exibe capa ou imagem padrão -->
                        <?php if (!empty($musica['capa'])): ?>
                            <img src="imagem.php?id=<?= $musica['id'] ?>" alt="Capa" width="120">
                        <?php else: ?>
                            <img src="sem_capa.png" style="max-width:80px;max-height:80px;">
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($musica['titulo']) ?></h3>
                        <p><?= htmlspecialchars($musica['artista']) ?></p>

                        <!-- Player de áudio -->
                        <audio controls>
                            <source src="<?= htmlspecialchars($musica['arquivo_mp3']) ?>" type="audio/mpeg">
                            Seu navegador não suporta áudio.
                        </audio>

                        <div class="music-actions">
                            <a href="editar.php?id=<?= $musica['id'] ?>">Editar</a> |
                            <a href="delete.php?id=<?= $musica['id'] ?>" onclick="confirm('Tem certeza que deseja excluir?')">Excluir</a>
                        </div>



                    </div>
                <?php endwhile; ?>
            </section>
        </main>
    </div>
</body>

</html>