<?php
session_start();
include_once 'db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
        $stmt = $conn->prepare("SELECT id, nome, senha, is_admin FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $nome, $hashSenha, $isAdmin);

        if ($stmt->fetch()) {
            if (password_verify($senha, $hashSenha)) {
                $_SESSION['logado'] = true;
                $_SESSION['nome'] = $nome;
                $_SESSION['email'] = $email;
                $_SESSION['is_admin'] = (bool)$isAdmin;

                $response = ['success' => true, 'message' => 'Login realizado com sucesso!'];
            } else {
                $response = ['success' => false, 'message' => 'Senha incorreta!'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Usuário não encontrado!'];
        }

        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => 'Preencha todos os campos.'];
    }

    $conn->close();
}
?>

<div class="center-wrapper">
  <div class="login-container fade-in">
    <h2>Login</h2>
    <form method="POST" action="includes/login.php">
      <label for="email">Email:</label>
      <input type="email" name="email" required>

      <label for="senha">Senha:</label>
      <input type="password" name="senha" required>

      <button class="btn-secundario">Entrar</button>
    </form>
    <button class="btn-secundario" data-url="includes/register.php">
        Registrar
    </button>
    <?php if (!empty($response['message'])): ?>
          <p style="color: <?php echo $response['success'] ? 'green' : 'red'; ?>; margin-top: 1rem;">
            <?php echo htmlspecialchars($response['message']); ?>
          </p>
      <?php endif; ?>
  </div>
</div>
