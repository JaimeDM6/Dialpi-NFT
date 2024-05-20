<header>
    <div class="container-header">
        <div class="logo">
            <a href="/"><img src="/img/Dialpi_NFT.png" alt="Dialpi NFT Logo"></a>
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
                        $apellidos = explode(' ', $_SESSION['usuario']['apellidos']);
                        $primer_apellido = $apellidos[0];
                        echo '<img class="profile-pic" src="' . $ruta_perfil . '" alt="Foto de perfil">';
                        echo '<a class="user-name">' . $_SESSION['usuario']['nombre'] . ' ' . $primer_apellido . '&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></a>';
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
            <div class="cart-dropdown">
                <ul class="cart-items">
                    <?php
                        $subtotal = 0;
                        if (isset($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $productId) {
                                $query = "SELECT * FROM NFT WHERE id_nft = ?";
                                $stmt = $conexion->prepare($query);
                                $stmt->bind_param('i', $productId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();

                                    $query2 = "SELECT nombre_coleccion FROM Coleccion_NFT WHERE id_coleccion = ?";
                                    $stmt2 = $conexion->prepare($query2);
                                    $stmt2->bind_param('i', $row["coleccion"]);
                                    $stmt2->execute();
                                    $result2 = $stmt2->get_result();
                                    $row2 = $result2->fetch_assoc();

                                    echo '<li class="cart-item-2">';
                                    echo '<img src="img/colecciones/' . $row2["nombre_coleccion"] . '/' . $row["nombre_nft"] . '.png" alt="' . ucfirst($row["nombre_nft"]) . '">' . ucfirst($row["nombre_nft"]);
                                    echo '<span class="cart-item-price">' . $row["precio"] . ' ETH</span>';
                                    echo '</li>';
                                    $subtotal += $row["precio"];
                                }
                            }
                        } else {
                            echo '<li>El carrito está vacío</li>';
                        }
                    ?>
                </ul>
                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                    <div class="cart-total-2">
                        <p>Subtotal: <?php echo $subtotal; ?> ETH</p>
                    </div>
                    <div class="cart-buttons">
                        <a href="/carrito" class="cart-button">Ir al carrito</a>
                        <a href="/checkout" class="checkout-button">Proceder con la compra</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container-header-movil">
        <div class="logo-dropdown">
            <div class="logo-movil">
                <a href="/"><img src="/img/Dialpi_NFT.png" alt="Dialpi NFT Logo"></a>
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
                            <a href="#" id="logout"><li>Cerrar sesión</li></a>
                        </ul>
                    </li>
                <?php else: ?>
                    <a href="/login"><li>Iniciar sesión</li></a>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>