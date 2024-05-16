<header>
    <div class="container-header">
        <div class="logo">
            <a href="/"><img src="img/Dialpi NFT.png" alt="Dialpi NFT Logo"></a>
            <a href="/"><h1>Dialpi NFT</h1></a>
        </div>
        <nav class="barra-navegacion">
            <ul class="menu">
                <li><a href="/">Inicio</a></li>
                <li><a href="#">Comprar NFT</a></li>
                <li><a href="#">Vender NFT</a></li>
            </ul>
        </nav>
        <form class="search-form" action="/buscar" method="get">
            <input type="text" name="q" placeholder="Buscar colecciones" class="search-input">
            <button type="submit" class="search-button">Buscar</button>
        </form>
        <button type="button" class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></button>
        <div class="login">
            <?php
                if (isset($_SESSION['usuario'])) {
            ?>
                <nav class="dropdown user-info">
                    <?php
                        $ruta_perfil = isset($_SESSION['usuario']['ruta_perfil']) && $_SESSION['usuario']['ruta_perfil'] != '' ? $_SESSION['usuario']['ruta_perfil'] : '/img/perfil.png';
                        echo '<img class="profile-pic" src="' . $ruta_perfil . '" alt="Foto de perfil">';
                        echo '<a class="user-name">' . $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos'] . '&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></a>';
                    ?>
                    <ul class="dropdown-menu">
                        <li><a href="/cuenta" id="perfil">Cuenta</a></li>
                        <li><a href="/mis_nft" id="mis_nft">Mis NFT</a></li>
                        <li><a href="#" id="logout">Cerrar sesión</a></li>
                    </ul>
                </nav>
            <?php
                } else {
                    echo '<a href="/login" class="login-button">Iniciar sesión</a>';
                }
            ?>
        </div>
        <div id="cart-icon">
            <a href="/carrito"><i class="fas fa-shopping-cart"></i></a>
            <?php
                $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                echo "<span class='cart-count'>$cartCount</span>";
            ?>
        </div>
    </div>
    <div class="container-header-movil">
        <div class="logo-dropdown">
            <div class="logo-movil">
                <a href="/"><img src="img/Dialpi NFT.png" alt="Dialpi NFT Logo"></a>
                <a href="/"><h1>Dialpi NFT</h1></a>
            </div>
            <div class="boton-menu">
                <button class="dropdown-menu-button">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </button>
            </div>
        </div>
        <nav class="barra-navegacion-movil">
            <ul class="dropdown-menu-mobile">
                <a href="/"><li>Inicio</li></a>
                <a href="#"><li>Comprar NFT</li></a>
                <a href="#"><li>Vender NFT</li></a>
                <a href="#"><li>Buscar</li></a>
                <?php if(isset($_SESSION['usuario'])): ?>
                    <li class="dropdown-mobile">
                        <a class="dropdown-toggle">Mi Perfil&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu-mobile-2">
                            <a href="/cuenta"><li>Cuenta</li></a>
                            <a href="/mis_nft"><li>Mis NFT</li></a>
                            <a href="/logout"><li>Cerrar sesión</li></a>
                        </ul>
                    </li>
                <?php else: ?>
                    <a href="/login"><li>Iniciar sesión</li></a>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>