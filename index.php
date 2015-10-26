<?php
  require "helpers.php";
  require "template-parts/login-header.php";
?>

    <body class="login-screen">
      <div class="col-xs-12 text-center logo-area">
        <img alt="CloudService.io" src="imagens/cloud2.png" />
      </div>
      a123123123123123
        <div class="container">
            <div class="login-container col-xs-12 col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
                <div id="output" class="col-xs-12">
                    <?php
                    if (isset($_GET['error'])) {
                        echo "<script>$('#output').addClass('alert alert-danger animated fadeInUp'); setTimeout(function () { $('#output').slideUp(); }, 5000); </script>";
                        echo "Usuário ou senha incorretos.";
                    }
                    elseif (isset($_GET['desativado'])) {
                        echo "<script>$('#output').addClass('alert alert-danger animated fadeInUp'); setTimeout(function () { $('#output').slideUp(); }, 5000); </script>";
                        echo "Conta inativada.";
                    }
                    ?>
                </div>
                <div class="avatar" class="col-xs-12">
                <?php
                  //  require_once 'config.php';
                  //  echo "<img src='". LOGO_LOGIN ."' class='img-responsive' alt='Logomarca'>";
                ?>

                </div>
                <div class="form-box">
                    <form action="usuario/account_login.php" method="post">
                        <input name="admin" type="hidden" value="18">
                        <input name="grupo" type="hidden" value="2">
                        <input name="auth_user" type="text" placeholder="Usuário">
                        <input name="auth_pw" type="password" placeholder="Senha">
                        <button class="btn btn-primary btn-block login" type="submit">Login</button>
                        <br>
						            <a  href="usuario/account_change.php">Esqueceu a senha?</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap JavaScript -->
        <script src="js/bootstrap.min.js"></script>
    </body>
</html>
