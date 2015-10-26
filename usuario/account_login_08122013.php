<?php

if ($_POST["auth_user"] != "") 
	$auth_user = $_POST["auth_user"];
else
	echo "Usuário não preenchido <br />";
	
if ($_POST["auth_pw"] != "") 
	$auth_pw = $_POST["auth_pw"];
else
	echo "Senha não preenchida <br />";

$auth = false;

$diasInativacao = "";
$flAtivo = "";
$cliente = "";
$master = ""; //usuário master do sistema
$admin = ""; //usuário administrador do sistema
$grupo = ""; //usuário é um grupo

/*if ($auth_user == "gpsadmin" and $auth_pw == "gps123456") {
	session_start();
	$_SESSION['logSession'] = "true";
	$_SESSION['logSessioUser'] = $auth_user;
	$_SESSION['clienteSession'] = "master";
	$auth = true;
	header("Location: /administracao.html");
	//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/administracao.html");
	exit;
}*/

if (isset( $auth_user ) && isset($auth_pw)) {

    include("config.php");
    $errormsg = "Incorrect password";
    $con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die ("Não foi possivel conectar ao Mysql") ;
    mysql_select_db($DB_NAME, $con);
	
	$auth_user = strtolower(mysql_real_escape_string($auth_user));
	$auth_pw = strtolower(mysql_real_escape_string($auth_pw));
	
	//if(strtolower($auth_user) != 'adm')
	//if(strpos($auth_user, '@') === false)
	//	$auth_user = $auth_user.'@';
	
    $sql = "SELECT 
				DATEDIFF(a.data_inativacao, NOW()) as diasInat, 
				CAST(a.id AS DECIMAL(10,0)) as idCliente,
				a.*
			FROM cliente a 
			WHERE (a.email = '$auth_user' OR a.apelido = '$auth_user') AND 
				  a.senha = '". md5($auth_pw)."' 
			LIMIT 1"; 

    $result = mysql_query( $sql ) 
        or die ( 'Unable to execute query.' ); 
		
    // Get number of rows in $result. 
    $num = mysql_numrows( $result ); 

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
			$admin = $data['admin'];
			
			//Atualizando os STATUS dos rastreadores do cliente para desligado
			mysql_query("UPDATE bem set date = date, status_sinal = 'D' WHERE cliente = $cliente", $con);
			
			//Registrando log de acesso
			mysql_query("INSERT INTO cliente_log (id, ip) VALUES ($cliente, '$ip')", $con);			
		} 
	} else {
		$sql = "SELECT 
				*
			FROM grupo 
			WHERE (nome = '$auth_user') AND 
				  senha = '". md5($auth_pw)."' 
			LIMIT 1"; 

    $result = mysql_query( $sql ) 
        or die ( 'Unable to execute query.' ); 
		
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
			} 
		}
	}
	
	mysql_close($con);
}

if ( !$auth ) {
	header("Location: /erro_login.html");
	//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/erro_login.html");
	
    exit; 

} else {

	//Se usuário administrador, redireciona para administração
	if ($master == "S") 
	{
		session_start();
		$_SESSION['logSession'] = "true";
		$_SESSION['logSessioUser'] = $auth_user;
		$_SESSION['clienteSession'] = "master";
		$auth = true;
		header("Location: /administracao.html");
		//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/administracao.html");
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
				header("Location: /login_aviso_desativacao.php");
				exit;
			} else {
				$_SESSION['logSession'] = "true";
				$_SESSION['logSessioUser'] = $auth_user;
				$_SESSION['clienteSession'] = "admin";
				$_SESSION['idClienteSession'] = $cliente;
				$auth = true;
				header("Location: /administracao_cliente.html");
				//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/administracao.html");
				exit;
			}
			
		} else {
			session_start();
			$_SESSION['logSession'] = "true";
			$_SESSION['logSessioUser'] = $auth_user;
			$_SESSION['clienteSession'] = $cliente;
			$_SESSION['grupoSession'] = $grupo;
			
			if ($diasInativacao != null) {
				$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
			}
			
			if ($flAtivo == "N") {
				$diasInativacao = "0";
				$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
			}
		}
	}
}

//require("account_inc.php");


if ($diasInativacao != null) {
	header("Location: /login_aviso_desativacao.php");
	//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/login_aviso_desativacao.php");
} else {
	header("Location: /default.php");
	//header("Location: /novidade2.php");
	//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/default.php");
	//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/novidade.php");
}

?>
Redirecionando...