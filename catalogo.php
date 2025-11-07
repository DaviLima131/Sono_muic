<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
session_start();
require_once 'conn.php';
require_once 'getID3/getid3.php';

// Verifica se a coluna usuario_id existe
$check = $conn->query("SHOW COLUMNS FROM musicas LIKE 'usuario_id'");
$usuarioIdExiste = $check && $check->num_rows > 0;

if ($usuarioIdExiste) {
    $stmt = $conn->prepare("SELECT * FROM musicas WHERE usuario_id = ? ORDER BY data_upload DESC");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM musicas ORDER BY data_upload DESC");
}

// Função para verificar se a música está nos favoritos
function isFavorito($usuarioId, $musicaId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id=? AND musica_id=?");
    $stmt->bind_param("ii", $usuarioId, $musicaId);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Sono Musics</title>
    <link rel="stylesheet" href="catalogo.css">
    <link rel="stylesheet" href="css.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .favorito-icon {
            cursor: pointer;
            font-size: 22px;
            color: crimson;
            transition: transform 0.2s;
        }

        .favorito-icon.inactive {
            color: #bbb;
        }

        .favorito-icon:hover {
            transform: scale(1.2);
        }
    </style>
</head>

<body>
    <div class="main-container">

        <!-- Sidebar atualizada -->
        <aside class="sidebar">
            <div class="logo">
                <h1>Sono Musics</h1>
            </div>

            <nav class="nav-menu">
                <ul>
                    <li class="nav-item"><a href="index.php"><span class="material-icons">home</span>Início</a></li>
                    <li class="nav-item"><a href="upload.php"><span class="material-icons">upload</span>Upload</a></li>
                    <li class="nav-item active"><a href="catalogo.php"><span class="material-icons">library_music</span>Catálogo</a></li>
                    <li class="nav-item"><a href="favoritos.php"><span class="material-icons">favorite</span>Favoritos</a></li>
                </ul>
            </nav>

           <a href="logout.php" class="logout-btn">
                <span class="material-icons">logout</span>Sair
            </a>
        </aside>

        <main class="content-container">
            <h2>Catálogo de Músicas</h2>
            <section class="music-list">
                <?php if ($result->num_rows === 0): ?>
                    <p>Nenhuma música encontrada.</p>
                <?php else: ?>
                    <?php while ($musica = $result->fetch_assoc()):
                        $isFav = isFavorito($_SESSION['usuario_id'], $musica['id']);
                    ?>
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
                            <div class="music-actions">
                                <span
                                    class="favorito-icon material-icons <?= $isFav ? '' : 'inactive' ?>"
                                    data-musica-id="<?= (int)$musica['id'] ?>" title="Adicionar/remover dos favoritos">
                                    favorite
                                </span>

                                <a href="editar.php?id=<?= (int)$musica['id'] ?>">Editar</a> |
                                <a href="delete.php?id=<?= (int)$musica['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        document.querySelectorAll('.favorito-icon').forEach(icon => {
            icon.addEventListener('click', () => {
                const musicaId = icon.dataset.musicaId;
                fetch('favoritos_toggle.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'musica_id=' + musicaId
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'added') {
                            icon.classList.remove('inactive');
                        } else if (res.status === 'removed') {
                            icon.classList.add('inactive');
                        }
                    });
            });
        });
    </script>
</body>

</html>