<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'conn.php';
require_once('getid3/getid3.php');

if (!isset($_SESSION['usuario_id'])) {
    die("<p style='color:red; text-align:center; margin-top:30px;'>Você precisa estar logado para enviar músicas.</p>");
}

$usuario_id = intval($_SESSION['usuario_id']);
$maxFileSize = 50 * 1024 * 1024; 
$maxImageSize = 5 * 1024 * 1024;
$allowedMusicExt = ['mp3', 'wav', 'ogg'];
$allowedImageExt = ['jpg', 'jpeg', 'png'];

$msg = '';
$titulo = '';
$artista = '';
$genero = '';
$arquivoMusicaExistente = '';
$arquivoCapaExistente = '';

if (isset($_GET['id'])) {
    $musica_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM musicas WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $musica_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($musica = $result->fetch_assoc()) {
        $titulo = $musica['titulo'];
        $artista = $musica['artista'];
        $genero = $musica['genero'];
        $arquivoMusicaExistente = $musica['arquivo_mp3'];
        $arquivoCapaExistente = $musica['capa'];
    } else {
        $msg = "<span style='color:red;'>Música não encontrada ou você não tem permissão para editar.</span>";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $artista = trim($_POST['artista']);
    $genero = trim($_POST['genero']);
    if ($genero === 'Outro' && !empty($_POST['genero_outro'])) {
        $genero = trim($_POST['genero_outro']);
    }

    $uploadDirMusica = "uploads/musicas/";
    $uploadDirCapa = "uploads/capas/";
    if (!is_dir($uploadDirMusica)) mkdir($uploadDirMusica, 0777, true);
    if (!is_dir($uploadDirCapa)) mkdir($uploadDirCapa, 0777, true);

    $arquivoMusica = $arquivoMusicaExistente;
    $duracaoMusica = 0;

    // Upload do arquivo de música
    $fileMusica = $_FILES['arquivo'];
    if ($fileMusica['error'] === UPLOAD_ERR_OK) {
        if ($fileMusica['size'] > $maxFileSize) $msg = "A música excede 50MB.";
        else {
            $ext = strtolower(pathinfo($fileMusica['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedMusicExt)) $msg = "Formato inválido. Apenas MP3, WAV, OGG.";
            else {
                $arquivoMusica = $uploadDirMusica . uniqid('music_', true) . '.' . $ext;
                if (!move_uploaded_file($fileMusica['tmp_name'], $arquivoMusica)) $msg = "Erro ao enviar música.";
                else {
                    // Remove antigo se existir
                    if ($arquivoMusicaExistente && file_exists($arquivoMusicaExistente)) unlink($arquivoMusicaExistente);
                    // Calcula duração usando getID3
                    $getID3 = new getID3();
                    $info = $getID3->analyze($arquivoMusica);
                    getid3_lib::CopyTagsToComments($info);
                    $duracaoMusica = isset($info['playtime_seconds']) ? floatval($info['playtime_seconds']) : 0;
                }
            }
        }
    }

    // Upload da capa
    $arquivoCapa = $arquivoCapaExistente;
    $fileCapa = $_FILES['capa'];
    if (empty($msg) && $fileCapa['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($fileCapa['error'] !== UPLOAD_ERR_OK) $msg = "Erro no upload da capa.";
        elseif ($fileCapa['size'] > $maxImageSize) $msg = "Capa excede 5MB.";
        else {
            $ext = strtolower(pathinfo($fileCapa['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedImageExt)) $msg = "Formato de capa inválido.";
            else $arquivoCapa = file_get_contents($fileCapa['tmp_name']);
        }
    }

    if (empty($msg)) {
        if (isset($musica_id)) {
            $sql = "UPDATE musicas SET titulo=?, artista=?, genero=?, arquivo_mp3=?, capa=?, duracao_musica=? WHERE id=? AND usuario_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiii", $titulo, $artista, $genero, $arquivoMusica, $arquivoCapa, $duracaoMusica, $musica_id, $usuario_id);
        } else {
            $sql = "INSERT INTO musicas (titulo, artista, genero, arquivo_mp3, capa, duracao_musica, data_upload, usuario_id)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssdi", $titulo, $artista, $genero, $arquivoMusica, $arquivoCapa, $duracaoMusica, $usuario_id);
        }
        if ($stmt->execute()) $msg = "<span style='color:#27ae60;'>Música salva com sucesso!</span>";
        else $msg = "Erro no banco: ".$stmt->error;
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - Sono Musics</title>
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="upload.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="logo"><h1>Sono Musics</h1></div>
            <nav class="nav-menu">
                <ul>
                    <li class="nav-item"><a href="index.php"><span class="material-icons">home</span>Início</a></li>
                    <li class="nav-item active"><a href="upload.php"><span class="material-icons">upload</span>Upload</a></li>
                    <li class="nav-item"><a href="catalogo.php"><span class="material-icons">library_music</span>Catálogo</a></li>
                    <li class="nav-item"><a href="favoritos.php"><span class="material-icons">favorite</span>Favoritos</a></li>
                </ul>
            </nav>
        <a href="logout.php" class="logout-btn">
                <span class="material-icons">logout</span>Sair
            </a>

        </aside>

        <main class="content-container">
            <!-- Upload Section -->
            <div class="upload-section">
                <h2>Upload de Músicas</h2>
                <?php if (!empty($msg)) echo "<div class='feedback'>$msg</div>"; ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título da Música</label>
                        <input type="text" id="titulo" name="titulo" required value="<?= htmlspecialchars($titulo) ?>">
                    </div>
                    <div class="form-group">
                        <label for="artista">Artista</label>
                        <input type="text" id="artista" name="artista" required value="<?= htmlspecialchars($artista) ?>">
                    </div>
                    <div class="form-group">
                        <label for="genero">Gênero Musical</label>
                        <select id="genero" name="genero" required onchange="toggleOutro()">
                            <option value="" disabled selected>Selecione o gênero</option>
                            <option value="Pop" <?= ($genero == 'Pop' ? 'selected' : '') ?>>Pop</option>
                            <option value="Rock" <?= ($genero == 'Rock' ? 'selected' : '') ?>>Rock</option>
                            <option value="Hip-Hop" <?= ($genero == 'Hip-Hop' ? 'selected' : '') ?>>Hip-Hop</option>
                            <option value="Eletrônica" <?= ($genero == 'Eletrônica' ? 'selected' : '') ?>>Eletrônica</option>
                            <option value="MPB" <?= ($genero == 'MPB' ? 'selected' : '') ?>>MPB</option>
                            <option value="Sertanejo" <?= ($genero == 'Sertanejo' ? 'selected' : '') ?>>Sertanejo</option>
                            <option value="Funk" <?= ($genero == 'Funk' ? 'selected' : '') ?>>Funk</option>
                            <option value="Reggae" <?= ($genero == 'Reggae' ? 'selected' : '') ?>>Reggae</option>
                            <option value="Jazz" <?= ($genero == 'Jazz' ? 'selected' : '') ?>>Jazz</option>
                            <option value="Outro" <?= ($genero !== '' && !in_array($genero, ['Pop','Rock','Hip-Hop','Eletrônica','MPB','Sertanejo','Funk','Reggae','Jazz']) ? 'selected' : '') ?>>Outro</option>
                        </select>
                        <input type="text" id="genero_outro" name="genero_outro" placeholder="Digite o gênero" value="<?= ($genero !== '' && !in_array($genero, ['Pop','Rock','Hip-Hop','Eletrônica','MPB','Sertanejo','Funk','Reggae','Jazz','Outro']) ? htmlspecialchars($genero) : '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="arquivo" class="custom-file-upload-label" id="arquivo-label">Selecione o arquivo de música</label>
                        <input type="file" id="arquivo" name="arquivo" accept=".mp3,.wav,.ogg" required>
                    </div>
                    <div class="form-group">
                        <label for="capa" class="custom-file-upload-label" id="capa-label">Capa do Álbum (opcional)</label>
                        <input type="file" id="capa" name="capa" accept="image/*">
                    </div>
                    <button type="submit" class="btn">Enviar Música</button>
                </form>
            </div>

            <!-- Tutorial Section -->
            <section class="tutorial-section">
                <h2>Passo a passo de como eu consigo o arquivo MP3</h2>
                <p>Se a música estiver no YouTube, você pode convertê-la para MP3.</p>
                <h3>Como fazer:</h3>
                <p>
                    1) Abra <a href="https://v2.youconvert.net/pth/" target="_blank">https://v2.youconvert.net/pth/</a><br>
                    2) Cole o link da música<br>
                    3) Escolha MP3 → Download
                </p>
            </section>
        </main>
    </div>

    <script>
        const arquivoInput = document.getElementById('arquivo');
        const arquivoLabel = document.getElementById('arquivo-label');
        arquivoInput?.addEventListener('change', () => {
            arquivoLabel.textContent = arquivoInput.files.length > 0 ? arquivoInput.files[0].name : "Selecione o arquivo de música";
        });

        const capaInput = document.getElementById('capa');
        const capaLabel = document.getElementById('capa-label');
        capaInput?.addEventListener('change', () => {
            capaLabel.textContent = capaInput.files.length > 0 ? capaInput.files[0].name : "Capa do Álbum (opcional)";
        });

        const generoSelect = document.getElementById('genero');
        const generoOutro = document.getElementById('genero_outro');

        function toggleOutro() {
            const isOther = generoSelect.value === 'Outro' || (generoSelect.value === '' && generoOutro.value !== '');
            generoOutro.style.display = isOther ? 'block' : 'none';
            generoOutro.required = isOther;
            if (!isOther) generoOutro.value = '';
        }
        document.addEventListener('DOMContentLoaded', toggleOutro);
        generoSelect?.addEventListener('change', toggleOutro);
    </script>
</body>
</html>
