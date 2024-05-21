<?php
    $title = 'Registro';
    session_start();
    if (isset($_SESSION['usuario'])) {
        header('Location: /');
        exit;
    }
    require_once 'conexion.php';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    function validar_dni($dni) {
        $letra = substr($dni, -1);
        $numeros = substr($dni, 0, -1);
        return strtoupper($letra) === substr('TRWAGMYFPDXBNJZSQVHLCKE', strtr($numeros, 'XYZ', '012')%23, 1);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $response = array('error' => false, 'message' => '');
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $dni = $_POST['dni'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (!validar_dni($dni)) {
            $response['error'] = true;
            $response['message'] = 'El DNI no es válido.';
        } else if ($password !== $confirm_password) {
            $password = "";
            $confirm_password = "";
            $response['error'] = true;
            $response['message'] = 'Las contraseñas no coinciden.';
        } else {
            $sql = "SELECT * FROM Usuarios WHERE email_usuario = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $email = "";
                $password = "";
                $confirm_password = "";
                $response['error'] = true;
                $response['message'] = 'El usuario ya existe.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO Usuarios (dni_usuario, nombre_usuario, apellidos_usuario, email_usuario, password_usuario) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("sssss", $dni, $nombre, $apellidos, $email, $hashed_password);
                $stmt->execute();
                
                $response['message'] = 'success';
            }
        }

        echo json_encode($response);
        exit;
    }
?>
<?php include 'head.php'; ?>
<?php include 'header-center.php'; ?>
    <main>
        <a href="javascript:history.back()" class="volver"><i class="fa-solid fa-arrow-left-long"></i>&nbsp;&nbsp;Volver</a><br><br>
        <form method="POST" action="/signup" class="login-form">
            <p id="error-message" style="color: red;"></p>
            <label for="nombre">Nombre:</label><br>
            <input type="text" id="nombre" name="nombre" required autocomplete="name"><br>
            <label for="apellidos">Apellidos:</label><br>
            <input type="text" id="apellidos" name="apellidos" required autocomplete="family-name"><br>
            <label for="dni">DNI:</label><br>
            <input type="text" id="dni" name="dni" required maxlength="9" autocomplete="off"><br>
            <label for="email">Correo electrónico:</label><br>
            <input type="email" id="email" name="email" required autocomplete="email"><br>
            <label for="password">Contraseña:</label><br>
            <div class="password-container">
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <i class="fas fa-eye" id="toggle-password"></i>
            </div>
            <label for="confirm_password">Confirmar contraseña:</label><br>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <i class="fas fa-eye" id="toggle-password-2"></i>
            </div>
            <input type="submit" value="Registrarse">
            <p class="signup-link">¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>
        </form>
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        $('.login-form').on('submit', function(e) {
            e.preventDefault();
    
            $.ajax({
                url: '/signup',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        $('#error-message').css('color', 'red');
                        $('#error-message').text(response.message);
                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                    } else {
                        $('#error-message').css('color', 'green');
                        $('#error-message').html('Registro exitoso. Por favor, <a style="text-decoration: none; color: green;" href="/login">inicie sesión</a>.');
                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                    }
                }
            });
        });

        $('#toggle-password').click(function() {
            let passwordInput = $('#password');
            let passwordType = passwordInput.attr('type');

            if (passwordType === 'password') {
                passwordInput.attr('type', 'text');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $('#toggle-password-2').click(function() {
            let passwordInput = $('#confirm_password');
            let passwordType = passwordInput.attr('type');

            if (passwordType === 'password') {
                passwordInput.attr('type', 'text');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
    </script>
</body>
</html>