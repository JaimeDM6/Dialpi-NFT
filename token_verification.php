<?php
require_once __DIR__ . '/../backend/vendor/autoload.php';
use \Firebase\JWT\JWT;

$secret_key = getenv('JWT_SECRET_KEY');

function verify_jwt() {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        http_response_code(401);
        echo json_encode(array("message" => "Acceso denegado. No se proporcionó un token."));
        exit();
    }

    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);

    try {
        $decoded = JWT::decode($token, $secret_key, array('HS256'));
        return (array) $decoded->data;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array("message" => "Acceso denegado. Token inválido."));
        exit();
    }
}
