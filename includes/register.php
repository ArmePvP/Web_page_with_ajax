<?php
session_start();
include_once 'db.php'; // arquivo que conecta ao MySQL
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if ($nome && $email && $senha) {
        // Verifica se email já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensagem = "Email já cadastrado!";
        } else {
            $stmt->close();

            // Insere usuário com senha hash
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $hashSenha);

            if ($stmt->execute()) {
                $_SESSION['logado'] = true;
                $_SESSION['nome'] = $nome;
                $_SESSION['email'] = $email;
                $mensagem = "Usuário registrado com sucesso!";
            } else {
                $mensagem = "Erro no cadastro!";
            }

            $stmt->close();
        }

        $conn->close();
    } else {
        $mensagem = "Preencha todos os campos.";
    }
}
?>



<div class="center-wrapper">
  <div class="login-container fade-in">
    <h2>Registrar</h2>
    <form method="POST" action="includes/register.php" id="formRegistro">
      <label for="nome">Nome:</label>
      <input type="text" name="nome" required>
      <label for="email">Email:</label>
      <input type="email" name="email" required>
      <label for="senha">Senha:</label>
      <input type="password" name="senha" required>
      <button class="btn-secundario">Registrar</button>

      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($mensagem)) : ?>
        <div style="margin-top: 10px; color: red;"><?php echo htmlspecialchars($mensagem); ?></div>
      <?php endif; ?>
    </form>
  </div>
</div>


