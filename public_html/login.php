
<?php
session_start();
include __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/head.php';
    include __DIR__ . '/../includes/header-center.php';
    ?>
    <main>
        <a href="javascript:history.back()" class="volver"><i class="fa-solid fa-arrow-left-long"></i>&nbsp;&nbsp;Volver</a><br><br>
        <form method="POST" action="/signin" class="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <p id="error-message" style="color: red;"></p>
            <label for="email">Correo electrónico:</label><br>
            <input type="email" id="email" name="email" required autocomplete="email"><br>
            <label for="password">Contraseña:</label><br>
            <div class="password-container">
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <i class="fas fa-eye" id="toggle-password"></i>
            </div>
            <input type="submit" value="Iniciar sesión">
            <p class="signup-link">¿No tienes cuenta? <a href="/signup">Regístrate</a></p>
        </form>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script>
    $(document).ready(function(){
        $('.login-form').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '/signin',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        $('#error-message').text(response.message);
                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                    } else if (response.message === 'success') {
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