<?php
	if (file_exists('/var/www/html/gps-tracker/config.txt')){
		$arquivo		= file('/var/www/html/gps-tracker/config.txt');
		$conteudo		= array();
		foreach ($arquivo as $linha) {
			$conf = explode("=", $linha);
			if (count($conf) > 1) {
				$conteudo[trim($conf[0])] = trim($conf[1]);
			}
		}

		$logo_login		= (array_key_exists("logo_login", $conteudo)) ? $conteudo['logo_login'] : 'imagens/logo_login.jpg' ;
		$logo_admin		= (array_key_exists("logo_admin", $conteudo)) ? $conteudo['logo_admin'] : 'imagens/logo_admin.jpg' ;
		$rodape			= (array_key_exists("rodape", $conteudo)) ? $conteudo['rodape'] : 'Sistema de Rastreamento &copy; 2014. Todos os Direitos Reservados.' ;
		$hostSMTP		= (array_key_exists("host_smtp", $conteudo)) ? $conteudo['host_smtp'] : 'cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com' ;
		$autentSMTP		= (array_key_exists("autent_smtp", $conteudo)) ? $conteudo['autent_smtp'] : false ;
		$userSMTP		= (array_key_exists("usuario_smtp", $conteudo)) ? $conteudo['usuario_smtp'] : '' ;
		$senhaSMTP		= (array_key_exists("senha_smtp", $conteudo)) ? $conteudo['senha_smtp'] : '' ;
		$nomeEmpresa	= (array_key_exists("empresa", $conteudo)) ? $conteudo['empresa'] : 'Sistema de Rastreamento' ;
	}
	define('LOGO_LOGIN', $logo_login);
	define('LOGO_ADMIN', $logo_admin);
	define('RODAPE', $rodape);
	define('EMPRESA', $nomeEmpresa);

	define('ROOT_URL', '/var/www/html/gps-tracker');







	// Substituindo arquivo de texto por tabela no banco.

	$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689') or die(mysql_error());
	mysql_select_db("tracker", $con);

	$QySmtpParams = "
	   select smtp_host, smtp_auten, smtp_user, smtp_passwd
	     from preferencias
	";
	$rsSmtpParams = mysql_query($QySmtpParams, $con);

	if (mysql_num_rows($rsSmtpParams)>0)
	{
		$rowSmtpParams = mysql_fetch_assoc($rsSmtpParams);

		if ($rowSmtpParams["smtp_auten"]=='S')
		{
			$autenticacao = true;
		}
		else
		{
			$autenticacao = false;
		}

		define('HOST_SMTP',   $rowSmtpParams["smtp_host"]);
		define('AUTENT_SMTP', $autenticacao);
		define('USER_SMTP',   $rowSmtpParams["smtp_user"]);
		define('SENHA_SMTP',  $rowSmtpParams["smtp_passwd"]);

		mysql_close($con);
	}
	else
	{
		define('HOST_SMTP', $hostSMTP);
		define('AUTENT_SMTP', $autentSMTP);
		define('USER_SMTP', $userSMTP);
		define('SENHA_SMTP', $senhaSMTP);
	}
?>
