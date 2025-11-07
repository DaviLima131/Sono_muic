<?php
session_start();
require_once 'conn.php';

// BUSCAR DADOS DA MÚSICA

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM musicas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $musica = $stmt->get_result()->fetch_assoc();

    if (!$musica) {
        $_SESSION['errorMessage'] = "Música não encontrada.";
        header("Location: catalogo.php");
        exit;
    }
} else {
    $_SESSION['errorMessage'] = "ID inválido.";
    header("Location: catalogo.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $titulo = trim($_POST['titulo']);
    $artista = trim($_POST['artista']);
    $genero = trim($_POST['genero']);

    if (empty($titulo) || empty($artista)) {
        $_SESSION['errorMessage'] = "Título e artista são obrigatórios.";
        header("Location: editar.php?id=$id");
        exit;
    }

    // SE O USUÁRIO ENVIOU NOVA CAPA
    if (!empty($_FILES['capa']['name']) && $_FILES['capa']['error'] === 0) {
        $imgTmp = $_FILES['capa']['tmp_name'];
        $imgData = file_get_contents($imgTmp);
        if ($imgData === false) {
            $_SESSION['errorMessage'] = "Falha ao ler arquivo de imagem enviado.";
            header("Location: editar.php?id=$id");
            exit;
        }

        // Atualiza incluindo a capa usando parâmetro preparado (BLOB)
        $sql = "UPDATE musicas 
                SET titulo = ?, artista = ?, genero = ?, capa = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        // usar marcador de blob com send_long_data
        $null = null;
        $stmt->bind_param("sssbi", $titulo, $artista, $genero, $null, $id);
        $stmt->send_long_data(3, $imgData);
    } else {
        // Sem nova capa
        $sql = "UPDATE musicas 
                SET titulo = ?, artista = ?, genero = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $titulo, $artista, $genero, $id);
    }

    if (!$stmt->execute()) {
        die("Erro ao atualizar: " . $stmt->error);
    }

    $_SESSION['successMessage'] = "Música atualizada com sucesso!";
    header("Location: catalogo.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Música - Sono Musics</title>
    <link rel="stylesheet" href="editar.css">
</head>
<body>
    <div class="main-container">
        <main class="content-container edit-page">
            <div class="form-wrapper">
                <a href="catalogo.php" class="back-btn">Voltar</a>

                <h2>Editar Música</h2>

                <!-- Mostra a capa atual -->
                <div class="capa-preview">
                    <img src="imagem.php?id=<?= $musica['id'] ?>&t=<?= time() ?>" alt="Capa atual" class="edit-page-image">
                </div>

                <form method="post" class="form-edit" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $musica['id'] ?>">

                    <label for="titulo">Título:</label>
                    <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($musica['titulo']) ?>" required>

                    <label for="artista">Artista:</label>
                    <input type="text" name="artista" id="artista" value="<?= htmlspecialchars($musica['artista']) ?>" required>

                    <label for="genero">Gênero:</label>
                    <input type="text" name="genero" id="genero" value="<?= htmlspecialchars($musica['genero']) ?>" required>

                    <label for="capa">Nova Capa:</label>
                    <input type="file" class="form-control" name="capa" id="capa" accept="image/*">

                    <button type="submit" class="btn-salvar">Salvar Alterações</button>
                </form>
            </div>
        </main>
    </div>
        <script>
    const capaInput = document.getElementById('capa');
    const capaPreviewImg = document.querySelector('.capa-preview img');

    capaInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                capaPreviewImg.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>
