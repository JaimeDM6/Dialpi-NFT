<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $response = array('error' => true, 'message' => 'CSRF token inválido');
        echo json_encode($response);
        exit;
    }

    $response = array('error' => false, 'message' => '');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['message'] = "Correo electrónico no válido";
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM Usuarios WHERE email_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_usuario'])) {
            $_SESSION['usuario'] = [
                'dni' => $user['dni_usuario'],
                'nombre' => $user['nombre_usuario'],
                'apellidos' => $user['apellidos_usuario'],
                'id' => $user['id_usuario'],
                'token_foto' => $user['token_foto'],
                'direccion' => $user['direccion_usuario'],
            ];

            $_SESSION['checkout'] = [
                'direccion' => true,
            ];

            if ($email === 'admin@nftdialpi.ddns.net') {
                $_SESSION['administrador'] = true;
            }

            unset($_SESSION['invitado']);
            $response['message'] = 'success';
        } else if (hash('sha512', $password) === $user['password_usuario']) {
            $new_hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE Usuarios SET password_usuario = ? WHERE email_usuario = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ss", $new_hashed_password, $email);
            $stmt->execute();

            $_SESSION['usuario'] = [
                'dni' => $user['dni_usuario'],
                'nombre' => $user['nombre_usuario'],
                'apellidos' => $user['apellidos_usuario'],
                'id' => $user['id_usuario'],
                'token_foto' => $user['token_foto'],
                'direccion' => $user['direccion_usuario'],
            ];

            $_SESSION['checkout'] = [
                'direccion' => true,
            ];

            if ($email === 'admin@nftdialpi.ddns.net') {
                $_SESSION['administrador'] = true;
            }

            unset($_SESSION['invitado']);
            $response['message'] = 'success';
        } else {
            $response['error'] = true;
            $response['message'] = "Contraseña incorrecta";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Usuario no encontrado";
    }

    echo json_encode($response);
    exit;
}
