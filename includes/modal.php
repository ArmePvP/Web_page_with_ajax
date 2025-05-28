<?php
$casasFile = __DIR__ . '/../casas.json';
$casas = file_exists($casasFile) ? json_decode(file_get_contents($casasFile), true) : [];

$index = isset($_GET['index']) ? intval($_GET['index']) : 0;

if (!isset($casas[$index])) {
    echo '<p>Casa não encontrada.</p>';
    exit;
}

$casa = $casas[$index];

// Se não tiver array de imagens, cria só um com a imagem destaque
$imagemDestaque = $casa['imagem_destaque'] ?? '';
$imagensOutras = $casa['outras_imagens'] ?? [];

$imagens = array_unique(array_merge(
    $imagemDestaque ? [$imagemDestaque] : [],
    $imagensOutras
));

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<title><?= htmlspecialchars($casa['titulo']) ?></title>
<style>

    h2 {
    text-align: center;
    margin-top: 1rem;
    }

    #imagem-container {
        max-width: 80%;
        margin: 2rem auto 5rem auto; /* margem inferior aumentada */
        position: relative;
        user-select: none;
    }


    #imagem-container img {
    width: 100%;
    border-radius: 8px;
    cursor: zoom-in;
    transition: transform 0.5s ease, box-shadow 0.5s ease;
    display: block;
    }

    #imagem-container.zoomed img {
    transform: scale(1.5);
    box-shadow: 0 0 30px rgba(0,0,0,0.4);
    cursor: zoom-out;
    border-radius: 8px;
    }

    /* Botões de navegação */
    /* Botões de navegação — redondos e sempre embaixo da imagem */
    .nav-btn {
        position: absolute;
        bottom: -55px;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, 0.12);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        color: #fff;
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: 
            background 0.3s ease, 
            transform 0.25s ease,
            box-shadow 0.3s ease,
            color 0.25s ease;
    }

    .nav-btn:hover {
        background: rgba(255, 255, 255, 0.35);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
        color: #222;
        transform: translateY(-3px) scale(1.05);
    }

    #prev-btn {
        left: 20%;
    }

    #next-btn {
        right: 20%;
    }

    #imagem-container.zoomed .nav-btn {
        display: none;
    }



    /* Ícones SVG via ::before */
    #prev-btn::before, #next-btn::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    mask-size: contain;
    mask-repeat: no-repeat;
    background-color: currentColor;
    -webkit-mask-size: contain;
    -webkit-mask-repeat: no-repeat;
    }

    #prev-btn::before {
    mask-image: url('data:image/svg+xml;utf8,<svg fill="white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>');
    -webkit-mask-image: url('data:image/svg+xml;utf8,<svg fill="white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>');
    }

    #next-btn::before {
    mask-image: url('data:image/svg+xml;utf8,<svg fill="white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>');
    -webkit-mask-image: url('data:image/svg+xml;utf8,<svg fill="white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>');
    }

    @media (max-width: 600px) {
    #imagem-container {
        margin-bottom: 70px; /* espaço extra para os botões */
        position: relative;
    }
    
    .nav-btn {
        position: absolute;
        bottom: -40px; /* um pouco abaixo da imagem */
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
        padding: 0;
        border-radius: 6px;
        top: auto; /* remove top */
        transform: none;
        background: rgba(255 255 255 / 0.35);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        color: #222;
    }
    
    #prev-btn {
        left: 20%; /* posiciona à esquerda */
    }
    
    #next-btn {
        right: 20%; /* posiciona à direita */
    }

    #imagem-container.zoomed img {
        transform: scale(1.25);
        box-shadow: 0 0 30px rgba(0,0,0,0.4);
        cursor: zoom-out;
        border-radius: 8px;
    }
    }


    p {
    max-width: 700px;
    margin: 2rem auto 1rem auto; /* aumenta o espaço em cima para distanciar da imagem */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 1.1rem;
    line-height: 1.5;
    color: #333;
    background: #f9f9f9;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    p strong {
    color: #007BFF; /* destaca o label “Preço:” */
    font-weight: 700;
    }


</style>
</head>
<body>

<h2><?= htmlspecialchars($casa['titulo']) ?></h2>

<div id="imagem-container" data-imagens='<?= htmlspecialchars(json_encode($imagens), ENT_QUOTES, 'UTF-8') ?>'>
  <button id="prev-btn" class="nav-btn" aria-label="Imagem anterior">&#10094;</button>
  <img
    id="imagem-zoom"
    src="../uploads/<?= htmlspecialchars($imagens[0]) ?>"
    alt="Imagem da casa"
    aria-label="Imagem da casa, clique para zoom"
  />
  <button id="next-btn" class="nav-btn" aria-label="Próxima imagem">&#10095;</button>
</div>

<p><strong>Preço:</strong> R$ <?= number_format($casa['preco'], 2, ',', '.') ?></p>
<p><?= nl2br(htmlspecialchars($casa['descricao'] ?? 'Sem descrição')) ?></p>


</body>
</html>