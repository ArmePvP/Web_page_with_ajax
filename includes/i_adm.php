<?php
session_start();

if (!isset($_SESSION['logado']) || !$_SESSION['logado'] || empty($_SESSION['is_admin'])) {
    echo "Acesso negado. Faça login como administrador.";
    exit;
}

$casasFile = __DIR__ . '/../casas.json';
$uploadsDir = '../uploads/';
$casas = file_exists($casasFile) ? json_decode(file_get_contents($casasFile), true) : [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<title>Painel Administrativo - Lista de Casas</title>
<style>
    h2 {
        color: #333;
        text-align: center;
        margin-top: 20px;
    }
    table {
        border-collapse: collapse;
        width: 90%;
        max-width: 900px;
        margin: 20px auto 40px auto;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        cursor: pointer;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    th {
        background-color: #4CAF50;
        color: white;
        user-select: none;
    }
    tr:hover {
        background-color: #f1f9f1;
    }
    img {
        border-radius: 8px;
        max-width: 100px;
        height: auto;
    }
    /* Modal */
    .modal {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.75);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        padding: 15px;
    }
    .modal.visible {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-content {
        background: white;
        border-radius: 8px;
        max-width: 600px;
        max-height: 85vh;
        overflow-y: auto;
        padding: 25px 30px;
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        width: 100%;
        box-sizing: border-box;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }
    .modal.visible .modal-content {
        transform: scale(1);
    }
    #close-modal {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        user-select: none;
        color: #333;
    }
    #close-modal:hover {
        color: #4CAF50;
    }

    /* Fade in */
    .fade-in {
        animation: fadeIn 0.4s ease forwards;
    }
    @keyframes fadeIn {
        from {opacity: 0;}
        to {opacity: 1;}
    }
    /* Responsive */
    @media (max-width: 600px) {
        table {
            width: 95%;
        }
        img {
            max-width: 80px;
        }
        .modal-content {
            padding: 15px 20px;
            max-height: 90vh;
        }
        #close-modal {
            font-size: 26px;
            top: 8px;
            right: 10px;
        }
    }
    .btn-criar-casa {
    display: inline-block;
    padding: 12px 28px;
    background: linear-gradient(135deg, #ff6a00, #ee0979);
    color: white;
    font-weight: 700;
    font-size: 1.2rem;
    border-radius: 30px;
    box-shadow: 0 5px 15px rgba(238, 9, 121, 0.6);
    text-decoration: none;
    cursor: pointer;
    user-select: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-criar-casa:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(255, 106, 0, 0.8);
    }


    @keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 5px 15px rgba(238, 9, 121, 0.6);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 10px 30px rgba(238, 9, 121, 0.9);
    }
    }



</style>
</head>
<body>

<h2>Lista de Casas Cadastradas</h2>
<p style="text-align:center;">
    <a href="#" data-url="includes/criar_casas.php" class="btn-criar-casa">Criar casa</a>
</p>
<?php if (empty($casas)): ?>
    <p style="text-align:center;">Nenhuma casa cadastrada.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Imagem</th>
            <th>Preço</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($casas as $casa): ?>
        <tr data-url="includes/casas_edit.php?id=<?= urlencode($casa['id']) ?>" tabindex="0" aria-label="Editar casa <?= htmlspecialchars($casa['titulo']) ?>">
            <td><?= htmlspecialchars($casa['id']) ?></td>
            <td><?= htmlspecialchars($casa['titulo']) ?></td>
            <td>
                <?php if (!empty($casa['imagem_destaque'])): ?>
                    <img src="<?= $uploadsDir . $casa['imagem_destaque'] ?>" alt="Imagem da casa <?= htmlspecialchars($casa['titulo']) ?>">
                <?php else: ?>
                    Sem imagem
                <?php endif; ?>
            </td>
            <td>R$ <?= number_format($casa['preco'], 2, ',', '.') ?></td>
            <td>
                <button type="button" class="btn-edit" aria-label="Editar casa <?= htmlspecialchars($casa['titulo']) ?>">Editar</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Modal -->
<div id="modal" class="modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-content">
        <span id="close-modal" aria-label="Fechar modal" role="button" tabindex="0">&times;</span>
        <div id="conteudo-dinamico">
            <p style="text-align:center;">Clique em "Editar" para modificar os dados da casa.</p>
        </div>
    </div>
</div>


</body>
</html>
