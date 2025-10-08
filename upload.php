<?php
require_once 'conn.php';


$maxFileSize = 50 * 1024 * 1024; // 50MB    
$maxImageSize = 5 * 1024 * 1024; 
$allowedMusicExt = ['mp3', 'wav', 'ogg'];
$allowedImageExt = ['jpg', 'jpeg', 'png', 'gif'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $artista = trim($_POST['artista']);

    // Pastas de upload
    $uploadDirMusica = "uploads/musicas/";
    $uploadDirCapa = "uploads/capas/";

    if (!is_dir($uploadDirMusica)) mkdir($uploadDirMusica, 0777, true);
    if (!is_dir($uploadDirCapa)) mkdir($uploadDirCapa, 0777, true);

    // --- Música ---
    $arquivoMusica = null;
    $fileMusica = $_FILES['arquivo'];

    if ($fileMusica['error'] === UPLOAD_ERR_OK) {
        if ($fileMusica['size'] > $maxFileSize) {
            $msg = "A música excede o tamanho máximo de 50MB.";
        } else {
            $ext = strtolower(pathinfo($fileMusica['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedMusicExt)) {
                $msg = "Formato de música inválido. Apenas MP3, WAV e OGG são permitidos.";
            } else {
                $arquivoMusica = $uploadDirMusica . uniqid('music_', true) . '.' . $ext;
                if (!move_uploaded_file($fileMusica['tmp_name'], $arquivoMusica)) {
                    $msg = "Erro ao enviar a música para o servidor.";
                }
            }
        }
    } else {
        $msg = "Erro no upload da música. Código: {$fileMusica['error']}";
    }

    // --- Capa (opcional) ---
    $arquivoCapa = null;
    $fileCapa = $_FILES['capa'];

    if (empty($msg) && $fileCapa['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($fileCapa['error'] !== UPLOAD_ERR_OK) {
            $msg = "Erro no upload da capa. Código: {$fileCapa['error']}";
        } elseif ($fileCapa['size'] > $maxImageSize) {
            $msg = "A capa excede o tamanho máximo de 5MB.";
        } else {
            $ext = strtolower(pathinfo($fileCapa['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedImageExt)) {
                $msg = "Formato de capa inválido. Apenas JPG, PNG e GIF são permitidos.";
            } else {
                $arquivoCapa = file_get_contents($fileCapa['tmp_name']); // Salva conteúdo binário
            }
        }
    }

    // --- Inserção no banco ---
    if (empty($msg) && $arquivoMusica) {
        $sql = "INSERT INTO musicas (titulo, artista, arquivo_mp3, capa, data_upload) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        // 'capa' agora é um BLOB, então use 'b' no bind_param
        $stmt->bind_param("ssss", $titulo, $artista, $arquivoMusica, $arquivoCapa);

        if ($stmt->execute()) {
            $msg = "<span style='color:#00ff00;'>Música enviada com sucesso!</span>";
        } else {
            die("Erro no banco: " . $stmt->error);
        }
        $stmt->close();
        // Limpar campos
        $titulo = $artista = '';
    } elseif (empty($msg)) {
        $msg = "Ocorreu um erro desconhecido. Tente novamente.";
    }

    // Se houve erro no upload da música, remover capa se foi enviada
    if (!empty($msg) && $arquivoCapa && file_exists($arquivoCapa)) {
        unlink($arquivoCapa);
    }

    // Redirecionar para evitar reenvio do formulário
    if (empty($msg)) {
        header("Location: upload.php"); 
exit;
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
            <div class="logo">
                <h1>Sono Musics</h1>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li class="nav-item"><a href="index.php"><span class="material-icons">home</span>Início</a></li>
                    <li class="nav-item active"><a href="upload.php"><span class="material-icons">upload</span>Upload</a></li>
                    <li class="nav-item"><a href="catalogo.php"><span class="material-icons">library_music</span>Catálogo</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content-container">
            <div class="upload-section">
                <h2>Upload de Músicas</h2>
                <?php if (!empty($msg)) echo "<div class='feedback'>$msg</div>"; ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título da Música</label>
                        <input type="text" id="titulo" name="titulo" placeholder="" required>
                    </div>

                    <div class="form-group">
                        <label for="artista">Artista</label>
                        <input type="text" id="artista" name="artista" placeholder="" required>
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
        </main>
    </div>

    <script>
        // Mostrar nome do arquivo selecionado
        const arquivoInput = document.getElementById('arquivo');
        const arquivoLabel = document.getElementById('arquivo-label');
        arquivoInput.addEventListener('change', () => {
            if (arquivoInput.files.length > 0) arquivoLabel.textContent = arquivoInput.files[0].name;
        });

        const capaInput = document.getElementById('capa');
        const capaLabel = document.getElementById('capa-label');
        capaInput.addEventListener('change', () => {
            if (capaInput.files.length > 0) capaLabel.textContent = capaInput.files[0].name;

        });
    </script>
</body>

</html>