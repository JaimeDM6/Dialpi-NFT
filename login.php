<?php
    session_start();
    if (isset($_SESSION['nombre_usuario'])) {
        header('Location: /');
        exit;
    }
    require_once 'conexion.php';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $response = array('error' => false, 'message' => '');
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM Usuarios WHERE email_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_usuario'])) {
                $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                $_SESSION['apellidos_usuario'] = $user['apellidos_usuario'];
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['ruta_perfil'] = $user['ruta_perfil'];
                $response['message'] = 'success';
            }
            else if (hash('sha512', $password) === $user['password_usuario']) {
                $new_hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE Usuarios SET password_usuario = ? WHERE email_usuario = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ss", $new_hashed_password, $email);
                $stmt->execute();

                $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                $_SESSION['apellidos_usuario'] = $user['apellidos_usuario'];
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['ruta_perfil'] = $user['ruta_perfil'];
                $response['message'] = 'success';
            } else {
                $password = "";
                $response['error'] = true;
                $response['message'] = "Contraseña incorrecta";
            }
        } else {
            $email = "";
            $password = "";
            $response['error'] = true;
            $response['message'] = "Usuario no encontrado";
        }

        echo json_encode($response);
        exit;
    }
?>
<?php include 'head.php'; ?>
    <?php include 'header-center.php'; ?>
    <main>
        <a href="javascript:history.back()" class="volver"><i class="fa-solid fa-arrow-left-long"></i>&nbsp;&nbsp;Volver</a><br><br>
        <form method="POST" action="/login" class="login-form">
            <p id="error-message" style="color: red;"></p>
            <label for="email">Correo electrónico:</label><br>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autocomplete="email"><br>
            <label for="password">Contraseña:</label><br>
            <div class="password-container">
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <i class="fas fa-eye" id="toggle-password"></i>
            </div>
            <input type="submit" value="Iniciar sesión">
            <p>¿No tienes cuenta? <a href="/signup">Regístrate</a></p>
        </form>
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        $('.login-form').on('submit', function(e) {
            e.preventDefault();
    
            $.ajax({
                url: '/login',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        $('#error-message').text(response.message);
                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                    } else {
                        window.location.href = '/';
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
    });
    </script>
</body>
</html>