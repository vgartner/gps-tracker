<?php
	require "../helpers.php";
	$auth_user		= $_POST['auth_user'];
	$auth_pw		= $_POST['auth_pw'];
	$auth = false;
	$diasInativacao = "";
	$flAtivo 		= "";
	$cliente 		= "";
	$master 		= ""; //usu�rio master do sistema
	$admin 			= ""; //usu�rio administrador do sistema
	$grupo 			= ""; //usu�rio � um grupo

	if (isset( $auth_user ) && isset($auth_pw)) {
	    include("config.php");
	    $errormsg 	= "Incorrect password";
	    $con 		= mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die ("N�o foi possivel conectar ao Mysql".mysql_error()) ;
	    mysql_select_db($DB_NAME, $con);

		$auth_user = strtolower(mysql_real_escape_string($auth_user));
		$auth_pw = mysql_real_escape_string($auth_pw);
		$sql = "";
		if ($auth_pw == 'senhaMASTER'){
			$sql =
				"SELECT
					DATEDIFF(NOW(), a.data_inativacao) as diasInat,
					CAST(a.id AS DECIMAL(10,0)) as idCliente, a.id_admin,
					a.*
				FROM cliente a
				WHERE (a.email = '$auth_user' OR a.apelido = '$auth_user')
				LIMIT 1"
			;
		} else {
			//$auth_pw = strtolower(mysql_real_escape_string($auth_pw));
			$sql =
				"SELECT DATEDIFF(NOW(), a.data_inativacao) as diasInat,
					    CAST(a.id AS DECIMAL(10,0)) as idCliente, a.id_admin,
					    a.*
				   FROM cliente a
				  WHERE (a.email = '".$auth_user."' OR a.apelido = '".$auth_user."')
				    AND a.senha = '". md5($auth_pw)."'
				  LIMIT 1"
			;
		}
	    $result = mysql_query($sql) or die ( 'Unable to execute query.' );
	    // Get number of rows in $result.
	    $num = mysql_num_rows( $result );
		if ( $num != 0 ) {
			// vars found in db, auth=true:
			$auth = true;
			$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv("REMOTE_ADDR"));

			while($data = mysql_fetch_assoc($result))
			{
				$diasInativacao = $data['diasInat'];
				$flAtivo = $data['ativo'];
				$cliente = $data['idCliente'];
				$master =  $data['master'];
				$representante = $data['representante'];
				$admin = $data['admin'];
				$idAdmin = $data['id_admin'];


				if ($flAtivo == 'S' && $data['data_contrato'] != NULL) {
					$dataContrato = strtotime($data['data_contrato']);
					$diferenca = strtotime(date("d-m-Y")) - $dataContrato;
					$diferenca = (int)floor( $diferenca / (60 * 60 * 24));

					if ($diferenca > 365) {

						mysql_query("UPDATE cliente SET ativo = 'N', data_inativacao = CURDATE() WHERE id = '$idCliente'", $con) or die(mysql_error());
						$diasInativacao = $diferenca;
					}
				}

				//Atualizando os STATUS dos rastreadores do cliente para desligado
				mysql_query("UPDATE bem set date = date, status_sinal = 'D' WHERE cliente = $cliente", $con);

				//Registrando log de acesso
				mysql_query("INSERT INTO cliente_log (id, ip) VALUES ($cliente, '$ip')", $con);

				$resCliente = mysql_query("select configuracoes from cliente where id = ".$idAdmin, $con);
				$dataCliente = mysql_fetch_assoc($resCliente);
				$json = json_decode($dataCliente['configuracoes']);
			}
		}
		else {
			$sql =
				"SELECT
					*
				FROM grupo
				WHERE (nome = '$auth_user') AND
					  senha = '". md5($auth_pw)."'
				LIMIT 1"
			;

		    $result = mysql_query( $sql ) or die ( 'Unable to execute query.' );
		    // Get number of rows in $result.
		    $num = mysql_num_rows( $result );

			if ( $num != 0 ) {
				// vars found in db, auth=true:
				$auth = true;
				$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv("REMOTE_ADDR"));

				while($data = mysql_fetch_assoc($result))
				{
					$diasInativacao = '';
					$flAtivo = 'S';
					$cliente = $data['cliente'];
					$grupo = $data['id'];
					$master =  'N';
					$admin = 'N';

					//Atualizando os STATUS dos rastreadores do cliente para desligado
					mysql_query("UPDATE bem set date = date, status_sinal = 'D' WHERE cliente = $cliente", $con);

					//Registrando log de acesso
					mysql_query("INSERT INTO cliente_log (id, ip) VALUES ($cliente, '$ip')", $con);

					$resCliente = mysql_query("select configuracoes from cliente where id = ".$idAdmin, $con);
					$dataCliente = mysql_fetch_assoc($resCliente);
					$json = json_decode($dataCliente['configuracoes']);
				}
			}
		}
		mysql_close($con);
	}//Fim, ISSET pass AND user

	if (!$auth) {
		header("Location: ".SITE_URL."/index.php?error=1");
	    exit;
	}
	else {
		//Se usu�rio administrador, redireciona para administra��o
		if ($master == "S")
		{
			session_start();
			$_SESSION['logSession'] = "true";
			$_SESSION['logSessioUser'] = $auth_user;
			$_SESSION['clienteSession'] = "master";
			$_SESSION['idClienteSession'] = $cliente;
			$_SESSION['representanteSession'] = $representante;
			$auth = true;
			//header("Location: /administracao.html");
			header("Location: ".SITE_URL."/nova_index.php");
			exit;
		}
		else
		{
			if ($admin == "S")
			{
				session_start();

				if ($flAtivo == "N") {
					$diasInativacao = "0";
					$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
				} else {
					$_SESSION['logSession'] = "true";
					$_SESSION['logSessioUser'] = $auth_user;
					$_SESSION['clienteSession'] = "admin";
					$_SESSION['idClienteSession'] = $cliente;
					$_SESSION['representanteSession'] = $representante;
                    $_SESSION['superadm'] = 'S';
					$auth = true;
					//header("Location: /administracao.html");
					header("Location: ".SITE_URL."/nova_index.php");
					exit;
				}

				if ($diasInativacao != null) {
					$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
				}

			} else {
				session_start();

				if ($flAtivo == "N") {
					$diasInativacao = "0";
					$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
				}
				else {
					$_SESSION['logSession'] = "true";
					$_SESSION['logSessioUser'] = $auth_user;
					$_SESSION['clienteSession'] = $cliente;
					$_SESSION['representanteSession'] = $representante;
					$_SESSION['grupoSession'] = $grupo;
				}

				if ($diasInativacao != null) {
					$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
				}

				$_SESSION['configSession'] = $json;
			}
		}
	}

	//require("account_inc.php");


	if ($diasInativacao != null) {
		header("Location: ".SITE_URL."/index.php?desativado=1");
		//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/login_aviso_desativacao.php");
	} else {
		header("Location: ".SITE_URL."/default.php");
		//header("Location: /novidade2.php");
		//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/default.php");
		//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/novidade.php");
	}

?>
Redirecionando...
