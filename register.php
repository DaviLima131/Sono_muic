<?php
session_start();
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $senha = trim($_POST['password']);
    $confirmSenha = trim($_POST['confirmPassword']);

    if (!empty($username) && !empty($senha) && !empty($confirmSenha)) {
        if ($senha !== $confirmSenha) {
            $_SESSION['message'] = "As senhas não coincidem!";
            $_SESSION['message_type'] = "warning";
            header("Location: register.php");
            exit();
        }

        $sql = "SELECT id FROM usuarios WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['message'] = "Esse usuário já está cadastrado!";
            $_SESSION['message_type'] = "warning";
            header("Location: register.php");
            exit();
        }
        $stmt->close();

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (username, senha) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashSenha);

        if ($stmt->execute()) {
            $_SESSION['usuario'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['message'] = "Erro ao cadastrar: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Preencha todos os campos!";
        $_SESSION['message_type'] = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="https://bootswatch.com/4/yeti/bootstrap.min.css">
    <link rel="stylesheet" href="login.css">
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

            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h3>Cadastrar</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <input type="text" name="username" id="username" class="form-control"
                                   placeholder="Nome de usuário" required autofocus>
                        </div>
                        <div class="form-group mt-2">
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="Senha" required>
                        </div>
                        <div class="form-group mt-2">
                            <input type="password" name="confirmPassword" id="confirmPassword" class="form-control"
                                   placeholder="Confirmar senha" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-3">Cadastrar</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-secondary">Já possui uma conta? Faça login</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
