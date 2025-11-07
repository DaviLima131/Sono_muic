<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Sono Musics</title>
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
  <h2>Entrar</h2>

  <?php if (!empty($_SESSION['message'])): ?>
    <div class="alert <?= htmlspecialchars($_SESSION['message_type']) ?>">
      <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
  <?php unset($_SESSION['message'], $_SESSION['message_type']); endif; ?>

  <form method="POST" action="authenticate.php" autocomplete="off">
    <div class="input-field">
      <input type="text" name="username" id="username" required>
      <label for="username">Usuário ou E-mail</label>
    </div>

    <div class="input-field">
      <input type="password" name="password" id="password" required>
      <label for="password">Senha</label>
      <i class="bi bi-eye toggle-password" id="togglePassword"></i>
    </div>

    <button type="submit">Entrar</button>

    <div class="register">
      <p>Não tem conta? <a href="register.php">Cadastrar</a></p>
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
</script>
</body>
</html>
