<?php
include("config.php");

$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Could not connect: " . mysql_error());
mysql_select_db($DB_NAME, $cnx);


function random_password( $length = 8 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}

//gera nova senha
$senha = random_password(8);
$novaSenha = $senha;
$senha = md5($senha);


$query = "update cliente set senha = '".$senha."'
  		   where id = '".$_POST["id"]."'";
$ans = mysql_query($query);

//envia para o email do cliente
/*
* todo
*/
if ($ans==true)
{
	$msgEmail = '
	<table width="100%" border="0" cellpadding="0" cellspacing="6">
	 <tr>
	  <td>Sua nova senha de acesso:</td>
	 </tr>
	 <tr>
	  <td>'.$novaSenha.'</td>
	 </tr>
	 <tr>
	  <td>
	  	Recomendamos que copie essa senha e cole no campo SENHA de login de acesso e em seguida altere esta senha após entrar no sistema.
	    <br>
		Atenção: todos os caracteres acima fazem parte da senha inclusive pontos, traços e vírgulas.
	   </td>
	 </tr>
	 <tr>
	  <td>Mensagem enviada em: '.date('d/m/Y h:i:s',time()).'</td>
	 </tr>
	</table>
';
	
	require_once('phpmailer/PHPMailerAutoload.php');

	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPDebug  = 0;
	$mail->SMTPAuth   = 'webota@ig.com.br';
	$mail->SMTPSecure = 'ssl';
	$mail->Host       = 'smtp.ig.com.br';
	$mail->Port       = 465;
	$mail->Username   = 'webota@ig.com.br';
	$mail->Password   = 'fenomenos';
	$mail->SetFrom('webota@ig.com.br', 'Troca de Senha');
	$mail->Subject    = '[Rastreador] Troca de senha';
	$mail->Body       = utf8_decode($msgEmail);
	$mail->IsHTML(true);
	$mail->AddAddress($_POST["email"]);
	
	
	if ($mail->Send())
	{
		echo 'OK';
	}
	else
	{
		echo 'Desculpe, não consegui enviar o e-mail com sua nova senha. Por favor tente novamente';
	}

}
else
{
	echo 'Error: Não consegui gravar a nova senha no Banco de Dados. Por favor tente novamente.';
}

?>
