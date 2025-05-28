<?php
session_start();

if (!isset($_SESSION['logado']) || !$_SESSION['logado'] || empty($_SESSION['is_admin'])) {
    echo "Acesso negado. Faça login como administrador.";
    exit;
}

$casasFile = __DIR__ . '/../casas.json';
$uploadsDir = __DIR__ . '/../uploads/';
$casas = file_exists($casasFile) ? json_decode(file_get_contents($casasFile), true) : [];

// Função para salvar casas
function salvarCasas($casas, $casasFile) {
    file_put_contents($casasFile, json_encode($casas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ------------------------
// PROCESSAMENTO DO POST
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Editar casa (atualizar título, preço, imagens)
    if (isset($_POST['id']) && !isset($_POST['remover_imagem'])) {
        $id = $_POST['id'];

        foreach ($casas as &$casa) {
            if ($casa['id'] == $id) {
                // Atualiza título e preço
                $casa['titulo'] = $_POST['titulo'] ?? $casa['titulo'];
                $casa['preco'] = $_POST['preco'] ?? $casa['preco'];

                // Upload imagem destaque (substitui)
                if (isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['imagem_destaque']['name'], PATHINFO_EXTENSION);
                    $novoNome = uniqid('imgdestaque_') . '.' . $ext;
                    move_uploaded_file($_FILES['imagem_destaque']['tmp_name'], $uploadsDir . $novoNome);

                    // Se já tinha imagem destaque, pode apagar a antiga (opcional)
                    if (!empty($casa['imagem_destaque']) && file_exists($uploadsDir . $casa['imagem_destaque'])) {
                        @unlink($uploadsDir . $casa['imagem_destaque']);
                    }

                    $casa['imagem_destaque'] = $novoNome;
                }

                // Upload outras imagens (adiciona)
                if (isset($_FILES['outras_imagens'])) {
                    foreach ($_FILES['outras_imagens']['tmp_name'] as $i => $tmpName) {
                        if ($_FILES['outras_imagens']['error'][$i] === UPLOAD_ERR_OK) {
                            $ext = pathinfo($_FILES['outras_imagens']['name'][$i], PATHINFO_EXTENSION);
                            $nomeUnico = uniqid('imgoutra_') . '.' . $ext;
                            move_uploaded_file($tmpName, $uploadsDir . $nomeUnico);

                            if (!isset($casa['outras_imagens']) || !is_array($casa['outras_imagens'])) {
                                $casa['outras_imagens'] = [];
                            }
                            $casa['outras_imagens'][] = $nomeUnico;
                        }
                    }
                }

                break;
            }
        }
        salvarCasas($casas, $casasFile);

        header("Location: casas_edit.php?id=" . urlencode($id));
        exit;
    }

    // Remover imagem das outras imagens
    if (isset($_POST['id'], $_POST['imagem'], $_POST['remover_imagem'])) {
        $id = $_POST['id'];
        $imagemARemover = $_POST['imagem'];

        foreach ($casas as &$casa) {
            if ($casa['id'] == $id && !empty($casa['outras_imagens']) && is_array($casa['outras_imagens'])) {
                $key = array_search($imagemARemover, $casa['outras_imagens']);
                if ($key !== false) {
                    // Remove arquivo físico
                    if (file_exists($uploadsDir . $imagemARemover)) {
                        @unlink($uploadsDir . $imagemARemover);
                    }
                    // Remove do array
                    array_splice($casa['outras_imagens'], $key, 1);
                }
                break;
            }
        }
        salvarCasas($casas, $casasFile);

        header("Location: casas_edit.php?id=" . urlencode($id));
        exit;
    }
}

// ------------------------
// PEGAR CASA PARA EDIÇÃO
// ------------------------
$casaAtual = null;
if (isset($_GET['id'])) {
    foreach ($casas as $casa) {
        if ($casa['id'] == $_GET['id']) {
            $casaAtual = $casa;
            break;
        }
    }
}

if (!$casaAtual) {
    echo "Casa não encontrada.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Casa - ID <?= htmlspecialchars($casaAtual['id']) ?></title>
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

<h2>Editando Casa ID <?= htmlspecialchars($casaAtual['id']) ?></h2>

<form method="POST" action="includes/casas_edit.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($casaAtual['id']) ?>">

    <label>Título:</label><br>
    <input type="text" name="titulo" value="<?= htmlspecialchars($casaAtual['titulo']) ?>"><br><br>

    <label>Preço:</label><br>
    <input type="text" name="preco" value="<?= htmlspecialchars($casaAtual['preco']) ?>"><br><br>

    <label>Imagem Destaque:</label><br>
    <?php if (!empty($casaAtual['imagem_destaque'])): ?>
        <img src="../uploads/<?= htmlspecialchars($casaAtual['imagem_destaque']) ?>" width="150" alt="Imagem destaque"><br>
    <?php endif; ?>
    <input type="file" name="imagem_destaque"><br><br>

    <label>Outras Imagens:</label><br>
    <input type="file" name="outras_imagens[]" multiple><br><br>

    <button class="edit" type="submit">Salvar Alterações</button>
</form>

<h3>Outras Imagens Existentes</h3>
<?php if (!empty($casaAtual['outras_imagens'])): ?>
    <ul>
        <?php foreach ($casaAtual['outras_imagens'] as $imagem): ?>
            <li>
                <img src="../uploads/<?= htmlspecialchars($imagem) ?>" width="100" alt="Outra imagem">
                <form method="POST" action="includes/casas_edit.php" class="form-remover-imagem" style="display:inline-block; margin-left:10px;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($casaAtual['id']) ?>">
                    <input type="hidden" name="imagem" value="<?= htmlspecialchars($imagem) ?>">
                    <input type="hidden" name="remover_imagem" value="1">
                    <button class="edit" type="submit" style="background-color:#e74c3c; border-color:#e74c3c;">Remover</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Sem outras imagens.</p>
<?php endif; ?>

<br>
<a href="#" data-url="includes/i_adm.php" class="edit">Voltar para a Lista</a>

</body>
</html>