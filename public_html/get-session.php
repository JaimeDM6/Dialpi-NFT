<?php
session_start();
header('Content-Type: application/json');
echo json_encode(['administrador' => $_SESSION['administrador'] ?? false]);