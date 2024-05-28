<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('No tienes permiso para acceder a esta imagen.');
}

$token_foto = basename($_GET['token_foto']);
$ruta_png = "/../profile_images/{$token_foto}.png";
$ruta_jpg = "/../profile_images/{$token_foto}.jpg";
$ruta_jpeg = "/../profile_images/{$token_foto}.jpeg";

if (file_exists($ruta_png)) {
    $ruta = $ruta_png;
} elseif (file_exists($ruta_jpg)) {
    $ruta = $ruta_jpg;
} elseif (file_exists($ruta_jpeg)) {
    $ruta = $ruta_jpeg;
} else {
    $ruta = "/img/perfil.png";
}

header('Content-Type: ' . mime_content_type($ruta));
readfile($ruta);
exit;
