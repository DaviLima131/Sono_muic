<?php
session_start();
require_once 'conn.php'; // conexão com o banco

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    // ======= 1. Validações básicas =======
    if (empty($email) || empty($senha) || empty($confirmar_senha)) {
        die("Preencha todos os campos.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email inválido.");
    }

    if ($senha !== $confirmar_senha) {
        die("As senhas não coincidem.");
    }

    // ======= 2. Verifica se email já existe =======
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Email já registrado.");
    }
    $stmt->close();

    // ======= 3. Criptografar senha =======
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // ======= 4. Inserir no banco (sem nome agora) =======
    $sql = "INSERT INTO usuarios (email, senha) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senhaHash);

    if ($stmt->execute()) {
        echo "Conta criada com sucesso! <a href='login.php'>Fazer login</a>";
    } else {
        echo "Erro ao registrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: register.php");
    exit;
}
?>
