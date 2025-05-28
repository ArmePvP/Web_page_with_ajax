<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Site Responsivo</title>
</head>
<body>
  <link rel="stylesheet" href="css/style.css">
  
  <script src="js/cabecalho_js.js"></script>
  <script defer src="js/mudar_pagina.js"></script>


  <?php include("includes/cabecalho.php"); ?>

<main>
  <div id="conteudo-dinamico">
    <?php include("includes/mosaico.php"); ?>
  </div>
</main>



  
</body>
</html>


<?php session_start(); ?>
<script>
  const usuarioEstaLogado = <?php echo isset($_SESSION['logado']) && $_SESSION['logado'] ? 'true' : 'false'; ?>;
</script>
