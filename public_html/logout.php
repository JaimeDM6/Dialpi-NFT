<?php
    session_start();
    echo '<script>localStorage.clear();</script>';
    unset($_SESSION['usuario']);
    unset($_SESSION['invitado']);
    unset($_SESSION['checkout']);
    unset($_SESSION['numero_factura']);
    unset($_SESSION['foto_perfil']);
    unset($_SESSION['administrador']);
    header('Location: /');
    exit();
