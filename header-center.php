<?php
    $current_url = $_SERVER['REQUEST_URI'];
    $logo_link = $current_url == '/procesar-pago?confirmar' ? '/procesar-pago.php?procesado=1' : '';
?>
<header>
    <div class="container-header-centered">
        <div class="logo">
            <a href="<?php echo $logo_link; ?>"><img src="/img/Dialpi_NFT.png" alt="Dialpi NFT Logo"></a>
            <a href="<?php echo $logo_link; ?>"><h1>Dialpi NFT</h1></a>
        </div>
    </div>
</header>