<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$termo = filter_input(INPUT_GET, 'termo', FILTER_SANITIZE_STRING);
$casasFile = __DIR__ . '/../casas.json';

if (!file_exists($casasFile)) {
    http_response_code(500);
    echo "Arquivo de casas não encontrado.";
    exit;
}

$casasRaw = file_get_contents($casasFile);
$casas = json_decode($casasRaw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo "Erro ao decodificar JSON: " . json_last_error_msg();
    exit;
}

$casasFiltradas = [];

if (!$termo || trim($termo) === '') {
    $casasFiltradas = $casas;
} else {
    $termoLower = mb_strtolower($termo);
    $casasFiltradas = array_filter($casas, function($casa) use ($termoLower) {
        $titulo = mb_strtolower($casa['titulo'] ?? '');
        $descricao = mb_strtolower($casa['descricao'] ?? '');
        return strpos($titulo, $termoLower) !== false || strpos($descricao, $termoLower) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<title>Resultado da Busca: <?= htmlspecialchars($termo) ?></title>
<style>
/* --- SEU CSS EXISTENTE --- */
.mosaico-container {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  padding: 10px;
  justify-content: center;
}

.casa-card {
  border: 1px solid #ccc;
  padding: 10px;
  width: 220px; /* desktop */
  max-width: 100%;
  cursor: pointer;
  text-align: center;
  background: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border-radius: 8px;
  transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.casa-card:hover {
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  transform: scale(1.05);
  z-index: 10;
}

.casa-card img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 6px;
  transition: transform 0.3s ease;
}

.casa-card:hover img {
  transform: scale(1.05);
}

.modal {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
  padding: 10px;
}

.modal.visible {
  opacity: 1;
  pointer-events: auto;
}

.modal-content {
  background: white;
  padding: 20px;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  border-radius: 8px;
  transform: scale(0.8);
  transition: transform 0.3s ease;
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.modal.visible .modal-content {
  transform: scale(1);
}

#close-modal {
  position: absolute;
  right: 10px;
  top: 5px;
  cursor: pointer;
  font-size: 26px;
  font-weight: bold;
  user-select: none;
}

.fade-in {
  animation: fadeIn 0.5s ease forwards;
}

@keyframes fadeIn {
  from {opacity: 0;}
  to {opacity: 1;}
}

@media (max-width: 600px) {
  .mosaico-container {
    gap: 10px;
  }
  .casa-card {
    width: 100%;
    padding: 8px;
  }
  .casa-card img {
    height: 120px;
  }
  .modal-content {
    padding: 15px;
    max-height: 85vh;
  }
  #close-modal {
    font-size: 28px;
    top: 8px;
    right: 15px;
  }
}

/* Estilo simples para a busca */
#busca-casas {
  display: block;
  margin: 20px auto;
  padding: 10px 15px;
  width: 90%;
  max-width: 400px;
  font-size: 16px;
  border: 1px solid #aaa;
  border-radius: 6px;
  box-sizing: border-box;
}
/* Título da busca */
.titulo-busca {
  font-size: 1.8rem;
  font-weight: 700;
  text-align: center;
  margin-top: 30px;
  margin-bottom: 10px;
  color: #222;
}

/* Link "Voltar" */
.link-voltar {
  display: block;
  text-align: center;
  margin: 0 auto 20px;
  width: fit-content;
  padding: 8px 16px;
  background-color: #f1f1f1;
  border-radius: 8px;
  color: #333;
  text-decoration: none;
  font-size: 1rem;
  transition: all 0.2s ease;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.link-voltar:hover {
  background-color: #e0e0e0;
  color: #000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

</style>
</head>
<body>
<h2 class="titulo-busca">Resultado da Busca: <?= htmlspecialchars($termo) ?></h2>
<p><a href="#" class="link-voltar" data-url="includes/mosaico.php">← Voltar</a></p>


<div class="mosaico-container" id="mosaico-casas">
<?php if (count($casasFiltradas) === 0): ?>
  <p>Nenhuma casa encontrada para "<?= htmlspecialchars($termo) ?>"</p>
<?php else: ?>
  <?php foreach ($casasFiltradas as $index => $casa): ?>
    <div class="casa-card" data-url="includes/modal.php?index=<?= $index ?>">
      <?php if (!empty($casa['imagem_destaque'])): ?>
        <img src="../uploads/<?= htmlspecialchars($casa['imagem_destaque']) ?>" alt="Destaque da casa <?= htmlspecialchars($casa['titulo'] ?? 'Sem título') ?>">
      <?php endif; ?>
      <h3><?= htmlspecialchars($casa['titulo'] ?? 'Sem título') ?></h3>
      <p><strong>R$ <?= isset($casa['preco']) ? number_format($casa['preco'], 2, ',', '.') : 'Preço não informado' ?></strong></p>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>
</body>
</html>
