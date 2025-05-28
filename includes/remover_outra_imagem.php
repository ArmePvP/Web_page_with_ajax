<?php
session_start();

if (!isset($_SESSION['logado']) || !$_SESSION['logado'] || empty($_SESSION['is_admin'])) {
    echo "Acesso negado";
    exit;
}

$id = $_POST['id'] ?? '';
$imagem = $_POST['imagem'] ?? '';

$casasFile = __DIR__ . '/../casas.json';
$uploadsDir = realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR; // resolve o caminho absoluto

$casas = file_exists($casasFile) ? json_decode(file_get_contents($casasFile), true) : [];

foreach ($casas as &$casa) {
    if ($casa['id'] == $id && !empty($casa['outras_imagens'])) {
        $index = array_search($imagem, $casa['outras_imagens']);
        if ($index !== false) {
            // Remove da lista
            unset($casa['outras_imagens'][$index]);
            $casa['outras_imagens'] = array_values($casa['outras_imagens']);

            // Remove do disco com verificação de segurança
            $caminhoImagem = $uploadsDir . basename($imagem); // impede path traversal
            if (file_exists($caminhoImagem)) {
                if (!unlink($caminhoImagem)) {
                    http_response_code(500);
                    echo "Erro ao deletar a imagem do servidor.";
                    exit;
                }
            }
        }
        break;
    }
}

file_put_contents($casasFile, json_encode($casas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "ok";
