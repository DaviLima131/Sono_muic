<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
session_start();
require_once 'conn.php';

$artista = $_GET['nome'] ?? '';

if (empty($artista)) {
    echo "Artista não especificado.";
    exit;
}

// Busca músicas do artista apenas do usuário logado
$stmt = $conn->prepare("SELECT * FROM musicas WHERE artista = ? AND usuario_id = ? ORDER BY data_upload DESC");
$stmt->bind_param("si", $artista, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

// Contagem de músicas
$totalMusicas = $result->num_rows;

// Função de favoritos
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
    <title><?= htmlspecialchars($artista) ?> - Sono Musics</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="artista.css">
    <style>
        body {
            background-color: #0d0d0d;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        .favorito-icon {
            cursor: pointer;
            font-size: 22px;
            color: crimson;
            transition: transform 0.2s, color 0.3s;
        }

        .favorito-icon.inactive {
            color: #777;
        }

        .favorito-icon:hover {
            transform: scale(1.2);
            color: #ff4c6d;
        }

        .music-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .voltar-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            padding: 10px 18px;
            background: #221632;
            color: #d0b8ff;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            transition: 0.2s;
        }

        .voltar-btn:hover {
            background: #301c45;
            color: #fff;
        }
    </style>
</head>

<body>
    <a href="catalogo.php" class="voltar-btn">
        <span class="material-icons" style="font-size:18px;">arrow_back</span>
        Voltar
    </a>

    <div class="main-container">
        <main class="content-container">
            <div class="header">
                <h2><?= htmlspecialchars($artista) ?></h2>
                <p>Todas as músicas de <span style="color:#b07fff;"><?= htmlspecialchars($artista) ?></span> do seu perfil</p>
            </div>

            <section class="music-list">
                <?php if ($result->num_rows === 0): ?>
                    <p>Nenhuma música encontrada para este artista.</p>
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

                                <!-- link pro artista -->
                                <p>
                                    <a href="artista.php?nome=<?= urlencode($musica['artista'] ?? 'Desconhecido') ?>" class="artist-btn">
                                        <?= htmlspecialchars($musica['artista'] ?? 'Artista desconhecido') ?>
                                    </a>
                                </p>


                                <div class="genero"><?= htmlspecialchars($musica['genero'] ?? 'Não informado') ?></div>
                            </div>
                            </a>

                            <div class="music-actions">
                                <span
                                    class="favorito-icon material-icons <?= $isFav ? '' : 'inactive' ?>"
                                    data-musica-id="<?= (int)$musica['id'] ?>"
                                    title="Adicionar/remover dos favoritos">
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
                        if (res.status === 'added') icon.classList.remove('inactive');
                        else if (res.status === 'removed') icon.classList.add('inactive');
                    });
            });
        });
    </script>
</body>

</html>