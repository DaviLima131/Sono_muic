<?php
session_start();
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $_SESSION['message'] = "Preencha todos os campos!";
        $_SESSION['message_type'] = "warning";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['message'] = "As senhas não coincidem!";
        $_SESSION['message_type'] = "warning";
        header("Location: register.php");
        exit();
    }

    // Verifica se já existe
    $sql = "SELECT id FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "Usuário já cadastrado!";
        $_SESSION['message_type'] = "warning";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Cria hash da senha
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insere no banco
    $sql = "INSERT INTO usuarios (username, senha) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hash);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Conta criada com sucesso! Faça login.";
        $_SESSION['message_type'] = "success";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['message'] = "Erro ao registrar. Tente novamente.";
        $_SESSION['message_type'] = "danger";
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Criar Conta - Sono Musics</title>
  <link rel="stylesheet" href="login.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .input-field { position: relative; }
    .toggle-password {
      position: absolute;
      right: 10px; top: 50%;
      transform: translateY(-50%);
      cursor: pointer; color: #ccc;
      font-size: 20px;
      transition: color 0.3s, transform 0.2s;
      user-select: none;
      z-index: 3;
    }
    .toggle-password:hover { color: #fff; transform: translateY(-50%) scale(1.1); }
  </style>
</head>
<body>
<div class="wrapper">
  <h2>Criar Conta</h2>

  <?php if (!empty($_SESSION['message'])): ?>
    <div class="alert <?= htmlspecialchars($_SESSION['message_type']) ?>">
      <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
  <?php unset($_SESSION['message'], $_SESSION['message_type']); endif; ?>

  <form method="POST" autocomplete="off">
    <div class="input-field">
      <input type="text" name="username" id="username" required>
      <label for="username">Usuário ou E-mail</label>
    </div>

    <div class="input-field">
      <input type="password" name="password" id="password" required>
      <label for="password">Senha</label>
      <i class="bi bi-eye toggle-password" id="togglePassword"></i>
    </div>

    <div class="input-field">
      <input type="password" name="confirmPassword" id="confirmPassword" required>
      <label for="confirmPassword">Confirmar Senha</label>
      <i class="bi bi-eye toggle-password" id="toggleConfirmPassword"></i>
    </div>

    <button type="submit">Cadastrar</button>

    <div class="register">
      <p>Já tem conta? <a href="login.php">Entrar</a></p>
    </div>
  </form>
</div>

<script>
  // Mostrar/ocultar senha
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  togglePassword.addEventListener('click', () => {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    togglePassword.classList.toggle('bi-eye');
    togglePassword.classList.toggle('bi-eye-slash');
  });

  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  const confirmInput = document.getElementById('confirmPassword');
  toggleConfirmPassword.addEventListener('click', () => {
    const type = confirmInput.type === 'password' ? 'text' : 'password';
    confirmInput.type = type;
    toggleConfirmPassword.classList.toggle('bi-eye');
    toggleConfirmPassword.classList.toggle('bi-eye-slash');
  });
</script>
</body>
</html>
