<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://bootswatch.com/4/yeti/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                <?php endif; ?>

                <div class="card card-body">
                    <h3 class="text-center">Login</h3>
                    <form action="authenticate.php" method="POST">
                        <div class="form-group">
                            <input type="text" name="username" class="form-control" placeholder="Usuário" required>
                        </div>
                        <div class="form-group mt-2">
                            <input type="password" name="password" class="form-control" placeholder="Senha" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3">Entrar</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="register.php">Criar conta</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>