<?php
session_start();

if (!isset($_SESSION['usuario']) || !isset($_GET['token_foto']) || $_GET['token_foto'] !== $_SESSION['usuario']['token_foto']) {
    http_response_code(403);
    include('403.php');
    exit();
}

$token_foto = basename($_GET['token_foto']);
$files = glob("../profile_images/{$token_foto}.*");

if (!empty($files)) {
    $ruta = $files[0];
    $_SESSION['foto_perfil'] = 'personalizada';
} else {
    $ruta = "img/perfil.png";
    $_SESSION['foto_perfil'] = 'default';
}

header('Content-Type: ' . mime_content_type($ruta));
readfile($ruta);
exit;
