<?php
require_once 'conn.php';

// Buscar dados
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM musicas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $musica = $stmt->get_result()->fetch_assoc();
}

// Atualizar dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $artista = $_POST['artista'];
    $id = intval($_POST['id']);

    $sql = "UPDATE musicas SET titulo=?, artista=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $titulo, $artista, $id);
    $stmt->execute();

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
    <link rel="stylesheet" href="css.css">

</head>
<body>
    <div class="main-container">

        <!-- Conteúdo centralizado -->
        <main class="content-container edit-page">
            <div class="form-wrapper">
                
                <h2>Editar Música</h2>
                <form method="post" class="form-edit">
                    <input type="hidden" name="id" value="<?= $musica['id'] ?>">

                    <label for="titulo">Título:</label>
                    <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($musica['titulo']) ?>" required>

                    <label for="artista">Artista:</label>
                    <input type="text" name="artista" id="artista" value="<?= htmlspecialchars($musica['artista']) ?>" required>

                    <button type="submit" class="btn-salvar">Salvar Alterações</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
