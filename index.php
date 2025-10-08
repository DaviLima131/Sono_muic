<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sono Musics - Início</title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>

    .h1 {
        text-align: center;
    }

        .banner {
            height: 300px;
            background: url('banner1.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 2px 2px 8px #000;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .banner h1 {
            font-size: 2.5em;
            text-align: center;
            padding: 0 10px;
        }

        .main-image {
            display: block;
            max-width: 80%;
            height: auto;
            margin: 0 auto 30px auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .welcome-text {
            text-align: center;
            color: #ddd;
            font-size: 1.2em;
            max-width: 800px;
            margin: 0 auto 40px auto;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
        
            .banner h1 {
                font-size: 1.8em;
            }

            .main-image {
                max-width: 90%;
            }

            .welcome-text {
                font-size: 1em;
                padding: 0 10px;
            }
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
                    <li class="nav-item active">
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
                    <li class="nav-item">
                        <a href="catalogo.php">
                            <span class="material-icons">library_music</span>
                            Catálogo
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="content-container">
            <div class="h1">
                <h1>Bem-vindo ao Sono Musics</h1>
            </div>

            <img src="music production  sonora (2) (1).png" alt="Música" class="main-image">

            <p class="welcome-text">
                Explore o universo da música com Sono Musics! Descubra novos artistas, playlists exclusivas e curta suas músicas favoritas diretamente do seu navegador.
            </p>
        </main>
    </div>
</body>
</html>
