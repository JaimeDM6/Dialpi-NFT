<?php
    session_start();
    unset($_SESSION['usuario']);
    unset($_SESSION['invitado']);
    unset($_SESSION['checkout']);
    unset($_SESSION['numero_factura']);
    unset($_SESSION['foto_perfil']);
    unset($_SESSION['administrador']);