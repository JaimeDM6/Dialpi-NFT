<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['croppedImage'])) {
        $file = $_FILES['croppedImage'];
        $originalExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadPath = '../profile_images/' . $_SESSION['usuario']['token_foto'] . '.' . $originalExtension;

		if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode(['status' => 'success', 'url' => $uploadPath]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se recibió ningún archivo.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de solicitud no permitido.']);
}
