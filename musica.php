<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'conn.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p>ID da música inválido.</p>");
}

$id = intval($_GET['id']);

// Busca a música
$stmt = $conn->prepare("SELECT * FROM musicas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("<p>Música não encontrada.</p>");
$musica = $result->fetch_assoc();
$stmt->close();

// Duração da música
$duracaoSegundos = floatval($musica['duracao_musica'] ?? 0);
$duracaoFormatada = gmdate("i:s", intval($duracaoSegundos));

// Caminho do arquivo
$arquivo = $musica['arquivo_mp3'] ?? null;
$arquivoValido = $arquivo && file_exists($arquivo);

// Verifica se está favoritada
$isFav = false;
if (isset($_SESSION['usuario_id'])) {
    $uid = $_SESSION['usuario_id'];
    $check = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id=? AND musica_id=?");
    $check->bind_param("ii", $uid, $id);
    $check->execute();
    $isFav = $check->get_result()->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($musica['titulo']) ?>-Sono Musics</title>
    <link rel="shortcut icon" <?= htmlspecialchars($musica['capa']) ?> type="image/x-icon">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="musica.css">
</head>

<body>

    <div class="music-container">
        <?php if (!empty($musica['capa'])): ?>
            <img src="imagem.php?id=<?= $musica['id'] ?>&t=<?= time() ?>" alt="Capa da música">
        <?php else: ?>
            <img src="sem_capa.png" alt="Sem capa">
        <?php endif; ?>

        <h2><?= htmlspecialchars($musica['titulo']) ?></h2>
        <p>
            <a href="artista.php?nome=<?= urlencode($musica['artista'] ?? 'Desconhecido') ?>" class="artist-btn">
                <?= htmlspecialchars($musica['artista'] ?? 'Artista desconhecido') ?>
            </a>
        </p>



        <?php if ($arquivoValido): ?>
            <div class="audio-player">
                <audio id="audio" preload="metadata">
                    <source src="stream.php?id=<?= $musica['id'] ?>" type="audio/mpeg">
                </audio>

                <div class="controls">
                    <button id="playBtn" class="play-btn material-icons">play_arrow</button>
                    <button id="favBtn"
                        class="fav-btn material-icons <?= $isFav ? 'active' : '' ?>"
                        data-musica-id="<?= $musica['id'] ?>">favorite</button>
                </div>

                <div class="progress-container" id="progressContainer">
                    <div class="progress-bar" id="progressBar"></div>
                </div>

                <div class="time">
                    <span id="currentTime">0:00</span>
                    <span id="duration"><?= $duracaoFormatada ?></span>
                </div>

            </div>
        <?php else: ?>
            <p>Arquivo de áudio não encontrado.</p>
        <?php endif; ?>

        <a href="catalogo.php" class="close-btn">Fechar</a>
    </div>

    <script>
      const audio = document.getElementById('audio');
const playBtn = document.getElementById('playBtn');
const favBtn = document.getElementById('favBtn');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const currentTimeEl = document.getElementById('currentTime');
const durationEl = document.getElementById('duration');

// Duração salva no banco
let duracaoBanco = <?= $duracaoSegundos ?>;

// PLAY / PAUSE
playBtn.addEventListener('click', () => {
    if (audio.paused) {
        audio.play();
        playBtn.textContent = 'pause';
    } else {
        audio.pause();
        playBtn.textContent = 'play_arrow';
    }
});

// Atualiza barra e tempo
audio.addEventListener('timeupdate', () => {
    const dur = isNaN(audio.duration) ? duracaoBanco : audio.duration;
    const percent = (audio.currentTime / dur) * 100;
    progressBar.style.width = percent + '%';
    currentTimeEl.textContent = formatTime(audio.currentTime);
});


audio.addEventListener('loadedmetadata', () => {
    const dur = isNaN(audio.duration) ? duracaoBanco : audio.duration;
    durationEl.textContent = formatTime(dur);
});

// Seek barr
let isDragging = false;

progressContainer.addEventListener('mousedown', (e) => { 
    isDragging = true; 
    seek(e); 
});
document.addEventListener('mouseup', () => isDragging = false);
document.addEventListener('mousemove', (e) => { 
    if(isDragging) seek(e); 
});

function seek(e) {
    const rect = progressContainer.getBoundingClientRect();
    const clickX = e.clientX - rect.left;
    const percent = Math.min(Math.max(clickX / rect.width, 0), 1);
    progressBar.style.width = (percent * 100) + '%';
    const dur = isNaN(audio.duration) ? duracaoBanco : audio.duration;
    audio.currentTime = percent * dur;
}

function formatTime(time) {
    if (isNaN(time)) time = 0;
    const min = Math.floor(time / 60);
    const sec = Math.floor(time % 60).toString().padStart(2, '0');
    return `${min}:${sec}`;
}

favBtn.addEventListener('click', () => {
    const musicaId = favBtn.dataset.musicaId;
    fetch('favoritos_toggle.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'musica_id=' + musicaId
    })
    .then(res => res.json())
    .then(r => {
        if (r.status === 'added') favBtn.classList.add('active');
        else if (r.status === 'removed') favBtn.classList.remove('active');
    });
});

    </script>

</body>

</html>