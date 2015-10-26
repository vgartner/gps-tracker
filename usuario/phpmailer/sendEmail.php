<?php

$nome		= 'ota';//$_POST["nome"];
$email		= 'otagomes@hotmail.com';//$_POST["email"];	
$telefone	= '88611304';//$_POST["telefone"];
$mensagem	= 'envio do rastreador';//$_POST["mensagem"];

$body = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Documento sem título</title>
<style type="text/css">
.titulo {
	font-family: Verdana, Geneva, sans-serif;
	font-weight: bold;
	font-size: 14px;
}
.texto {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
}
.rodape {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 9px;
	border-top: 1px;
	border-top-color:#039
}
</style>
	<body>
		<table width="100%" border="0" cellspacing="4">
		 <tr>
		  <td colspan="2" class="titulo">ALUMIPRATA</td>
		 </tr>
		 <tr>
		  <td colspan="2" class="titulo">ESQUADRIAS DE ALUMÍNIO</td>
		 </tr>
		 <tr>
		  <td width="5%" class="texto">Nome:</td>
		  <td width="95%" valign="top" class="texto">'.$nome.'</td>
		 </tr>
		 <tr>
		  <td class="texto">E-mail:</td>
		  <td class="texto">'.$email.'</td>
		 </tr>
		 <tr>
		  <td class="texto">Telefone:</td>
		  <td class="texto">'.$telefone.'</td>
		 </tr>
		 <tr>
		  <td class="texto">Mensagem:</td>
		  <td rowspan="2" valign="top" class="texto">'.$mensagem.'</td>
		 </tr>
		 <tr>
		  <td>&nbsp;</td>
		 </tr>
		 <tr>
		  <td colspan="2" class="rodape">Esta mensagem foi enviáda à partir da aplicação mobile Alumiprata © Todos os direitos reservados</td>
		 </tr>
		</table>
	</body>
</html>
';

require_once('phpmailer/PHPMailerAutoload.php');
/***** Ota ***/
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPDebug  = 2;
$mail->SMTPAuth   = 'webota@ig.com.br';
$mail->SMTPSecure = 'ssl';
$mail->Host       = 'smtp.ig.com.br';
$mail->Port       = 465;
$mail->Username   = 'webota@ig.com.br';
$mail->Password   = 'fenomenos';
$mail->SetFrom('webota@ig.com.br', 'Alumiprata');
$mail->Subject    = '[Alumiprata Mobile] Contato';
$mail->Body       = utf8_decode($body);
$mail->IsHTML(true);
$mail->AddAddress('webota@ig.com.br');


if ($mail->Send())
{
	echo 'Success';
}
else
{
	echo 'Error';
}

?>
