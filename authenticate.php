<?php
session_start();
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT id, username, senha FROM usuarios WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['senha'])) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['message'] = "Senha incorreta!";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Usuário não encontrado!";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        $_SESSION['message'] = "Preencha todos os campos!";
        $_SESSION['message_type'] = "warning";
    }

    header("Location: login.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
