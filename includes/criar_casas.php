<?php
session_start();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Verifica se está logado e é admin
if (!isset($_SESSION['logado']) || !$_SESSION['logado'] || empty($_SESSION['is_admin'])) {
    $msg = "Acesso negado. Faça login como administrador.";
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $msg]);
    } else {
        echo $msg;
    }
    exit;
}

$casasFile = __DIR__ . '/../casas.json';
$uploadsDir = '../uploads/';
$casas = file_exists($casasFile) ? json_decode(file_get_contents($casasFile), true) : [];

function gerarNovoId($casas) {
    $ids = array_column($casas, 'id');
    return empty($ids) ? 1 : max($ids) + 1;
}

$response = ['success' => false, 'message' => ''];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $preco = trim($_POST['preco'] ?? '');

    if ($titulo === '') {
        $erro = "O título é obrigatório.";
        $response['message'] = $erro;
    } else {
        if ($preco === '') {
            $preco = "Converse conosco";
        }

        $novaCasa = [
            'id' => gerarNovoId($casas),
            'titulo' => $titulo,
            'preco' => $preco,
            'imagem_destaque' => null,
            'outras_imagens' => [],
        ];

        // Upload imagem destaque
        if (isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['imagem_destaque']['name'], PATHINFO_EXTENSION);
            $novoNome = uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['imagem_destaque']['tmp_name'], $uploadsDir . $novoNome)) {
                $novaCasa['imagem_destaque'] = $novoNome;
            }
        }

        // Upload outras imagens
        if (isset($_FILES['outras_imagens'])) {
            foreach ($_FILES['outras_imagens']['tmp_name'] as $i => $tmpName) {
                if ($_FILES['outras_imagens']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['outras_imagens']['name'][$i], PATHINFO_EXTENSION);
                    $nomeUnico = uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadsDir . $nomeUnico)) {
                        $novaCasa['outras_imagens'][] = $nomeUnico;
                    }
                }
            }
        }

        $casas[] = $novaCasa;
        file_put_contents($casasFile, json_encode($casas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = ['success' => true, 'message' => 'Casa cadastrada com sucesso!'];
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastrar Nova Casa</title>
  <style>
    
body {
    background: #f0f4f8;
    color: #333;
}

/* Título */
h2 {
    margin-top:100px;
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 30px;
    text-align: center;
    font-size: 2rem;
    text-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Formulário */
form {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    margin-bottom: 40px;
    transition: box-shadow 0.3s ease;
}
form:hover {
    box-shadow: 0 16px 40px rgba(0,0,0,0.15);
}

label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #34495e;
    font-size: 1.05rem;
}

input[type="text"],
input[type="file"] {
    width: 100%;
    padding: 10px 15px;
    font-size: 1rem;
    border: 2px solid #dce3e8;
    border-radius: 8px;
    transition: border-color 0.25s ease;
    outline-offset: 2px;
    margin-bottom: 20px;
}
input[type="text"]:focus,
input[type="file"]:focus {
    border-color: #27ae60;
    outline: none;
}

/* Botão salvar */
button.edit {
    background-color: #27ae60;
    color: white;
    border: none;
    padding: 14px 25px;
    font-size: 1.1rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
    box-shadow: 0 6px 15px rgba(39, 174, 96, 0.3);
}
button.edit:hover {
    background-color: #219150;
    box-shadow: 0 8px 20px rgba(33, 145, 80, 0.5);
}

/* Botão remover */
button.edit[style*="background-color:#e74c3c"] {
    background-color: #e74c3c;
    border-color: #e74c3c;
    box-shadow: 0 6px 15px rgba(231, 76, 60, 0.4);
    padding: 8px 15px;
    font-size: 0.95rem;
}
button.edit[style*="background-color:#e74c3c"]:hover {
    background-color: #c0392b;
    box-shadow: 0 8px 20px rgba(192, 57, 43, 0.6);
}

/* Imagens destaque e outras imagens */
img {
    border-radius: 12px;
    max-width: 100%;
    max-height: 120px;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    cursor: pointer;
}
img:hover {
    transform: scale(1.05);
}

/* Lista de outras imagens */
ul {
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    padding-left: 0;
}

ul li {
    background: #fff;
    padding: 10px;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Form remover imagem inline */
.form-remover-imagem {
    margin-left: auto;
}

/* Link voltar */
a.edit {
    display: inline-block;
    margin-top: 30px;
    background-color: #2980b9;
    color: white;
    padding: 12px 28px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    box-shadow: 0 6px 18px rgba(41, 128, 185, 0.4);
    transition: background-color 0.3s ease;
}
a.edit:hover {
    background-color: #1c5985;
    box-shadow: 0 8px 25px rgba(28, 89, 133, 0.7);
}

/* Responsividade */
@media (max-width: 650px) {
    form {
        padding: 25px 20px;
        max-width: 100%;
    }
    ul {
        justify-content: flex-start;
    }
    ul li {
        width: 100%;
        justify-content: flex-start;
        gap: 10px;
        padding: 12px;
    }
    button.edit[style*="background-color:#e74c3c"] {
        padding: 6px 12px;
        font-size: 0.9rem;
    }
}
  </style>
</head>
<body>
<h2>Cadastrar Nova Casa</h2>

<?php if (!empty($erro)): ?>
    <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
<?php elseif (!empty($response['success']) && $response['success'] === true): ?>
    <p style="color:green;"><?= htmlspecialchars($response['message']) ?></p>
<?php endif; ?>

<form method="POST" action="includes/criar_casas.php" enctype="multipart/form-data" id="form-casas">
    <label>Título:</label><br>
    <input type="text" name="titulo" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>"><br><br>

    <label>Preço:</label><br>
    <input type="text" name="preco" value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>"><br><br>

    <label>Imagem Destaque:</label><br>
    <input type="file" name="imagem_destaque"><br><br>

    <label>Outras Imagens:</label><br>
    <input type="file" name="outras_imagens[]" multiple><br><br>

    <button class="edit" type="submit" >Cadastrar Casa</button>
</form>

<br>
<a href="#" class="edit" data-url="includes/i_adm.php">Voltar ao Painel Administrativo</a>
