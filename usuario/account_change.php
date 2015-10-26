<?php
  require "../helpers.php";
  require_once 'config.php';
  require "../template-parts/login-header.php";

?>
    <body class="login-screen">
      <div class="col-xs-12 text-center logo-area">
        <img alt="CloudService.io" src="../imagens/cloud2.png" />
      </div>
        <div class="container">
            <div class="login-container col-xs-12 col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
              <h2>Trocar Senha</h2>
                <div class="form-box">
                    <form>
                    	<p>Por favor informe seu e-mail no campo abaixo. Em seguida, uma nova senha será enviado para seu email.</p>
                        <input  id="txtEmail" name="auth_email" type="text" placeholder="e-mail">
                        <button id="btnChangeLogin" class="btn btn-primary btn-block login" type="button">Trocar Senha</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xs-12 text-center">
        <a class="white-link" href="<?php echo SITE_URL ?>">← Voltar para tela de Login</a>
        </div>

<!-- Bootstrap JavaScript -->
<!-- <script src="<?php echo SITE_URL ?>/js/bootstrap.min.js"></script> -->
<script src="<?php echo SITE_URL ?>/js/jquery-1.10.2.js"></script>
<script src="<?php echo SITE_URL ?>/js/bootstrap.js"></script>
<script src="<?php echo SITE_URL ?>/js/jquery.validate.min.js"></script>
<script src="<?php echo SITE_URL ?>/js/bootstrap-waitingfor.js"></script>
<script type="text/javascript">
$("#btnChangeLogin").click(function(){

	myApp.showPleaseWait();

	if ($("#txtEmail").val()=='')
	{
		alert("Por favor informe seu e-mail.");
	}
	else
	{
		verifyEmail();
	}

	function verifyEmail()
	{
		$.ajax({
			type: "POST",
			url: 'verify_email.php',
			data: {email: $("#txtEmail").val()},
			success: function (response) {
			   if (response!='')
			   {
				   changePassword(response);
			   }
			   else
			   {
				   alert('E-mail não encontrado. Por favor verifique ou entre em contato com o suporte.');
				   $("#txtEmail").empty();
				   myApp.hidePleaseWait();
			   }
			},
			error: function(ajaxError){
			}
		});
	}

	function changePassword(obj)
	{
		$.ajax({
			type: "POST",
			url: 'change_password.php',
			data: {id: obj, email: $("#txtEmail").val() },
			success: function (response) {
				if (response=='OK')
			   	{
				   alert('Sua nova senha foi enviada para seu e-mail. Por favor verifique sua caixa de entrada e caixa de spam.');
				   alert('Redirecionando para a página de login.');
				   window.location="../";

			   }
			   else
			   {
				   alert(response);
				   myApp.hidePleaseWait();
			   }
			},
			error: function(ajaxError){
			}
		});
	}
});

var myApp;
myApp = myApp || (function () {
    var pleaseWaitDiv = $('<div class="modal hide" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false"><div class="modal-header"><h1>Processing...</h1></div><div class="modal-body"><div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div></div></div>');
    return {
        showPleaseWait: function() {
            pleaseWaitDiv.modal();
        },
        hidePleaseWait: function () {
            pleaseWaitDiv.modal('hide');
        },
    };
})();

</script>
</body>
</html>
