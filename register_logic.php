<?php
session_start();
require_once 'conn.php'; // conexão com o banco

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    // ======= 1. Validações básicas =======
    if (empty($email) || empty($senha) || empty($confirmar_senha)) {
        $_SESSION['message'] = "Preencha todos os campos!";
        $_SESSION['message_type'] = "warning";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Digite um e-mail válido.";
        $_SESSION['message_type'] = "warning";
        header("Location: register.php");
        exit();
    }

    if ($senha !== $confirmar_senha) {
        $_SESSION['message'] = "As senhas não coincidem.";
        $_SESSION['message_type'] = "warning";
        header("Location: register.php");
        exit();
    }

    // ======= 2. Verifica se e-mail já existe =======
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "Este e-mail já está cadastrado.";
        $_SESSION['message_type'] = "warning";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // ======= 3. Criptografa senha =======
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // ======= 4. Inserir no banco =======
    $sql = "INSERT INTO usuarios (email, senha) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senhaHash);

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

    $stmt->close();
    $conn->close();
} else {
    header("Location: register.php");
    exit();
}
?>
