<?php
// Inclui o arquivo de conexão com o banco de dados
require_once 'conn.php';

// 1. Verifica se o parâmetro 'id' foi passado na URL
if (isset($_GET['id'])) {
    // 2. Converte o ID para um número inteiro por segurança (sanitização)
    $id = intval($_GET['id']);

    // 3. Verifica se o ID é válido (maior que zero)
    if ($id > 0) {
        
        // 4. Prepara a query SQL para DELETE usando prepared statements
        $sql = "DELETE FROM musicas WHERE id = ?";
        
        // 5. Prepara a declaração SQL
        $stmt = $conn->prepare($sql);
        
        // 6. Vincula o parâmetro 'id' (tipo inteiro 'i')
        $stmt->bind_param("i", $id);
        
        // 7. Executa a declaração
        if ($stmt->execute()) {
            // Sucesso na exclusão
            // Opcional: Você pode adicionar uma mensagem de sucesso aqui.
        } else {
            // Erro na exclusão (exibe o erro para debug)
            // echo "Erro ao deletar: " . $stmt->error;
        }

        // 8. Fecha a declaração
        $stmt->close();
    }
}

// 9. Redireciona o usuário de volta para a página do catálogo
header("Location: catalogo.php");
exit;
?>