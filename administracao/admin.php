<?php include('../seguranca.php');

if ($cliente != "master") {
	header("Location: /logout.php");
	//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/logout.php");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Administração - Rastreamento GPS</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<meta name="googlebot" content="noindex,nofollow">
<meta name="robots" content="noarchive">

<?php

$acao = isset($_GET['acao']) ? $_GET['acao'] : "novoUsuario";

if (isset($_POST['acao']))
{
	$acao = $_POST['acao'];
}

$sucesso = null;
$email = $_POST['email'];
$senha = $_POST['senha'];
$nomeCliente = $_POST['nomeCliente'];
$codigoCliente = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];
$admin = $_POST['rdAdmin'];

$buscaComando = $_POST['buscaComando'];

//echo "<br /><br /><br /><br />";//echo "acao: " . $acao . "<br />";//echo "codigoCliente: " . $codigoCliente . "--" . $_POST['codigo'] . "<br />";

//Conectando
$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")
  or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

$countCliente = 0;
$res = mysql_query("select count(*) as countCliente from cliente where master = 'N'");

for ($i=0; $i < 1; $i++) {
	$row = mysql_fetch_assoc($res);	
	$countCliente = (int)$row[countCliente];
}

$countIMEI = 0;
$res = mysql_query("select count(*) as countIMEI from bem");

for ($i=0; $i < 1; $i++) {
	$row = mysql_fetch_assoc($res);	
	$countIMEI = (int)$row[countIMEI];
}

/** Nao alterar ordem da acao */
if ($acao == "atualizarUsuario") {
	if ($email != null and $nomeCliente != null and $codigoCliente != null) {
		if (strpos($email, "@") === false)
		{
			$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Digite um e-mail.</span>";
		}
		else 
		{
			if ($senha != null) 
				$sql = "UPDATE cliente set nome = '$nomeCliente', email = '$email', senha = '". md5($senha) ."', admin = '$admin' WHERE id = $codigoCliente and master = 'N'";
			else
				$sql = "UPDATE cliente set nome = '$nomeCliente', email = '$email', admin = '$admin' WHERE id = $codigoCliente and master = 'N'";		

			if (!mysql_query($sql, $cnx))
			{
				// Se der erro, envia alerta que houve falha
				if (mysql_error() == "Duplicate entry '". $email ."' for key 'email_unq'")
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Usuário já existe!</span>";
				else
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Falha no cadastro.</span>";
				//die('Error: ' . mysql_error());
			}
			else
			{
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:black\">Alterado com sucesso!</span>";
				$resCodigo = mysql_query("select id from cliente where email = '$email' and master = 'N'");
				for ($j=0; $j < mysql_num_rows($resCodigo); $j++) {
					$rowCodigo = mysql_fetch_assoc($resCodigo);
					$codigoCliente = $rowCodigo[id];
				}
				//Envia e-mail para ativar a conta
			}
		}
	}
}

/** Nao alterar ordem da acao */
if ($acao == "novoUsuario") {
	if ($email != null and $nomeCliente != null) {
		
		if (strpos($email, "@") === false) 
		{
			$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Digite um e-mail.</span>";
			$acao = "novoUsuario";
		}
		else 
		{
			if (!mysql_query("INSERT INTO cliente (email, nome, senha, admin) VALUES ('$email', '$nomeCliente', '". md5($senha) ."', '$admin')", $cnx))
			{
				// Se der erro, envia alerta que houve falha
				if (mysql_error() == "Duplicate entry '". $email ."' for key 'email_unq'") {
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Usuário já existe!</span>";
					$acao = "novoUsuario";
				} else {
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Falha no cadastro.</span>";
					//die('Error: ' . mysql_error());
				}
			}
			else
			{
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:black\">Cadastrado com sucesso!</span>";
				$acao = "atualizarUsuario";
				$resCodigo = mysql_query("select id from cliente where email = '$email' and master = 'N'");
				for ($j=0; $j < mysql_num_rows($resCodigo); $j++) {
					$rowCodigo = mysql_fetch_assoc($resCodigo);
					$codigoCliente = $rowCodigo[id];
				}
				
				//ini_set("allow_url_fopen", 1);
				//ini_set("allow_url_include", 1); 
				
				/*
				//Envia e-mail para ativar a conta
				require_once('class.phpmailer.php');
				//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

				$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

				$mail->IsSMTP(); // telling the class to use SMTP

				try {
				  $mail->Host       = "mail.gmail.com"; // SMTP server
				  //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
				  $mail->SMTPAuth   = true;                  // enable SMTP authentication
				  $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
				  $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
				  $mail->Port       = 465;                   // set the SMTP port for the GMAIL server
				  $mail->Username   = "contato@gmail.com";  // GMAIL username
				  $mail->Password   = "senha_aqui";            // GMAIL password
				  $mail->CharSet 	= "UTF-8";
				  $mail->AddReplyTo('contato@gmail.com', 'Agile GPS');
				  $mail->AddAddress($email, 'Cliente');
				  $mail->SetFrom('contato@gmail.com', 'Agile GPS');
				  $mail->AddReplyTo($email, 'Cliente');
				  $mail->Subject = 'Ativacao - Sistema de Rastreamento GPS';
				  $mail->AltBody = 'Para ver esta mensagem, por favor use um leitor de email compatível com HTML!'; // optional - MsgHTML will create an alternate automatically
				  $mail->MsgHTML(file_get_contents('modelo_email.html'));
				  $mail->AddAttachment('imagens/logo_agile.jpg');      // attachment
				  //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
				  $mail->Send();
				  //echo "Message Sent OK</p>\n";
				} catch (phpmailerException $e) {
					//echo $e->errorMessage(); //Pretty error messages from PHPMailer
				} catch (Exception $e) {
					//echo $e->getMessage(); //Boring error messages from anything else!
				}		
				
				//Fim de envio e-mail*/
				
			}
		}
	}
}

if ($acao == "obterUsuario") {
	if ($codigoCliente != null) {
	
		$resUsuario = mysql_query("select * from cliente where id = '$codigoCliente' and master = 'N'");
		for ($k=0; $k < mysql_num_rows($resUsuario); $k++) {
			$rowUsuario = mysql_fetch_assoc($resUsuario);
			$codigoCliente = $rowUsuario[id];
			$nomeCliente = $rowUsuario[nome];
			$email = $rowUsuario[email];
			$admin = $rowUsuario[admin];
		}
		$acao = "atualizarUsuario";
	}
}

$acaoLinkTermos = $_POST['acaoLinkTermos'];


$command_path = ROOT_URL."/sites/1/";

if ($acaoLinkTermos == "true") 
{
	//salvar link
	$nmLinkTermos = $_POST['nmLinkTermos'];
	
	$fn = "$command_path/termos_uso.txt";
	$fh = fopen($fn, 'w') or die ("Can not create file");
	$tempstr = "$nmLinkTermos"; 
	fwrite($fh, $tempstr);
	fclose($fh);
}

//obtendo link termos
$nmLinkTermos = file_get_contents("$command_path/termos_uso.txt");

?>
<style>

body, table {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #A6A6A6;
}

.menu {
	border-color: #CCCCCC;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 20px;
	font-weight: bold;
	color: #CCCCCC;
	background-color: #FFFFFF;
	border-right: 1px solid #CCCCCC;
	border-top: 1px solid #CCCCCC;
	border-bottom: 1px solid #CCCCCC;
	padding: 5px;
	cursor: pointer;
}
.menu-sel {
	border-color: #CCCCCC;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 20px;
	font-weight: bold;
	color: #CCCCCC;
	background-color: #F7F7F7;
	border-right: 1px solid #CCCCCC;
	border-top: 1px solid #CCCCCC;
	padding: 5px;
	cursor: default;
}
.tb-conteudo {
	border-right: 1px solid #CCCCCC;
	border-bottom: 1px solid #CCCCCC;
	border-right-color: #CCCCCC;
	border-bottom-color: #CCCCCC;
}
.conteudo {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	font-weight: normal;
	color: #A6A6A6;
	background-color: #F7F7F7;
	padding: 5px;
/*height: 435px;*/	height: 100%;
	width: auto;
	filter:alpha(opacity=90); 
  	-moz-opacity: 0.90; 
   	opacity: 0.90; 
}

.textoEsquerda {
	text-align: right;
	padding-right:5px;
	width:25%;
}

.campoNovoVeiculo {
	border: 1px solid #C0C0C0;
}

.dicaCadastro {
	font-size:xx-small;
}

.btnAcao {
	border: 1px solid #808080;
	background-color: #E0E0E0;
}

.resumo {
	font-size:16px;
	font-weight: bold;
	color: #000000;
}

.divisorLog {
	border-right-style: solid;
	border-right-width: 1px;
	padding-right: 4px;
	border-right-color: #E2E2E2;
	text-align:center;
}
</style>

<script type="text/javascript" src="../javascript/painelAdmin.js"></script>
<script type="text/javascript" src="../javascript/popup.js"></script>
<script type="text/javascript" src="../javascript/jquery-1.7.min.js"></script>


<script type="text/javascript">

	function formataData(Campo, teclapres)
	{
		var tecla = teclapres.keyCode;
		var vr = new String(Campo.value);
		vr = vr.replace("/", "");
		vr = vr.replace("/", "");
		vr = vr.replace("/", "");
		tam = vr.length + 1;
		if (tecla != 8 && tecla != 8)
		{
			if (tam > 0 && tam < 2)
				Campo.value = vr.substr(0, 2) ;
			if (tam > 2 && tam < 4)
				Campo.value = vr.substr(0, 2) + '/' + vr.substr(2, 2);
			if (tam > 4 && tam < 7)
				Campo.value = vr.substr(0, 2) + '/' + vr.substr(2, 2) + '/' + vr.substr(4, 7);
		}
	}
	
	function formataHora(valor)
	{
		var vr = new String(valor.value);
		vr = vr.replace(/\D/g,"");
		vr = vr.replace(/(\d{2})(\d)/,"$1:$2");
		
		valor.value = vr;
	}
</script>

</head>

<?php 
$aba = $_GET['aba'];
$abaInicial = "'td_cadastro','div_cadastro'";

if ($aba != null && $aba == "usuarios")
	$abaInicial = "'td_consulta','div_consulta'";
	
if ($aba != null && $aba == "status")
	$abaInicial = "'td_status','div_status'";	

if ($buscaComando != "")
	$abaInicial = "'td_hist_com','div_hist_com'";
	
if ($acaoLinkTermos == "true") 
	$abaInicial = "'td_termos','div_termos'";
?>

<body onLoad="AlternarAbas(<?php echo $abaInicial; ?>); AlternarSubAbas('td_sub_cadastro','div_sub_cadastro'); setTimeout('esconderAlerta()', 10000); " 
	style=" background-position: right bottom; height:auto; border-left:thin; border-left-style: solid; 
			border-left-width: 1px; border-left-color: #CCCCCC; margin-left:0px; margin-top:-17px; 
			background-repeat: no-repeat;">

<div id="all_content" style="opacity:1.0;filter:alpha(opacity=100);">
<h2 align="center" style="font-size:20px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #666666;">Administração do sistema</h2>

<table width="80%" height="80%" cellspacing="0" cellpadding="0" border="0" style="border: 1px solid #000000; border-color: #CCCCCC; background-color: #F7F7F7;" align="center">
	<tr>
		<td><a href="#" style="color:#0099FF" onclick="if (document.getElementById('divResumo').style.display=='block') { document.getElementById('divResumo').style.display='none'; } else { document.getElementById('divResumo').style.display='block'; } ">Totais</a>
		<div id="divResumo" style="display:none">
			<br/>
			<table>
				<tr>
					<td>Total de Usuários: </td>
					<td><span class="resumo"><?php echo $countCliente ?></span></td>
				</tr>
				<tr>
					<td>Total de IMEIs: </td>
					<td><span class="resumo"><?php echo $countIMEI ?></span></td>
				</tr>
			</table>
		</div>
		</td>
	</tr>
</table>
<br/>
<br/>
<table width="80%" height="80%" cellspacing="0" cellpadding="0" border="0" style="border-left: 1px solid #000000; border-left-color: #CCCCCC;" align="center">

	<tr>
		<td width="100" class="menu" id="td_cadastro"
		onclick="AlternarAbas('td_cadastro','div_cadastro')" style="height: 7px">
			Cadastro
		</td>
		<td width="100" class="menu" id="td_consulta"
		onclick="AlternarAbas('td_consulta','div_consulta')" style="height: 7px">
			Usuários
		</td>
		<td width="100" class="menu" id="td_manutencao"
		onclick="AlternarAbas('td_manutencao','div_manutencao')" style="height: 7px">
			Pagamentos
		</td>
		<td width="100" class="menu" id="td_status"
		onclick="AlternarAbas('td_status','div_status')" style="height: 7px">
			Status
		</td>
		<td width="100" class="menu" id="td_hist_com"
		onclick="AlternarAbas('td_hist_com','div_hist_com')" style="height: 7px">
			Bloqueios
		</td>		
		<td width="100" class="menu" id="td_termos"
		onclick="AlternarAbas('td_termos','div_termos')" style="height: 7px">
			Termos
		</td>		
		<td style="border-bottom: 1px solid #CCCCCC; height: 7px;">
			&nbsp;</td>
		<td style="height: 7px"></td>
	</tr>
	<tr>
		<td class="tb-conteudo" colspan="8">
			<div id="div_cadastro" class="conteudo" style="display:block;">
				<div>
					Adicione um usuário <br />

					<form name="novoUsuario" id="novoUsuario" method="post" action="admin.php" autocomplete="off">
						<input name="acao" type="hidden" value="<?php echo $acao; ?>" />
						<table style="width: 70%" cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="2">
									<?php echo $sucesso ?><br />
									<img style="display:none" src="../imagens/carregando.gif" alt="Carregando, aguarde..." title="Carregando, aguarde..." id="imgCarregando" />
									<br />
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Código:</td>
								<td><input name="codigo" id="codigoCliente" maxlength="10" size="12" type="text" value="<?php echo $codigoCliente; ?>" readonly="true" style="background-color:#E0E0E0" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Código gerado pelo sistema</span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Nome do cliente:</td>
								<td><input name="nomeCliente" id="nomeCliente" maxlength="50" size="55" type="text" value="<?php echo $nomeCliente; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro"></span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">E-mail:</td>
								<td><input name="email" id="email" maxlength="45" size="25" type="text" value="<?php echo $email; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: E-mail do cliente para ativação/faturamento</span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Senha:</td>
								<td><input name="senha" id="senha" maxlength="45" size="25" type="text" value="" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Senha de acesso</span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Tipo:</td>
								<td>
									<!-- é admin -->
									<input name="rdAdmin" type="radio" value="N" <?php if ($admin=="N" or $admin == "") echo "checked='checked'"; ?> />Usuário
									<input name="rdAdmin" type="radio" value="S" <?php if ($admin=="S") echo "checked='checked'"; ?> />Administrador
									<span class="dicaCadastro">Dica: Tipo de acesso (administrador ou usuário)</span>
								</td>
							</tr>
							<tr>
								<td><br/></td>
								<td><br/></td>
							</tr>
							<tr>
								<td>
								<input name="btnCadastrar" type="submit" value="<?php if ($acao == "novoUsuario") { echo "Cadastrar"; } else { echo "Atualizar"; } ?>" class="btnAcao" onclick="if ((getElementById('email').value) == '' || (getElementById('nomeCliente').value) == '') { return false; } else { document.getElementById('imgCarregando').style.display='block'; } " />&nbsp;&nbsp;
								<a href="admin.php" style="color:#0099FF">Novo</a>
								</td>
								<td></td>
							</tr>
						</table>
					</form>
					<br />
					<?php if (($acao == "novoUsuario" or $acao == "atualizarUsuario") and $codigoCliente != null and $admin != 'S') { ?>
					<table width="98%" cellspacing="0" cellpadding="0" border="0" 
						style="border-left: 1px solid #000000; border-left-color: #CCCCCC; 
							   border-bottom: 1px solid #000000; border-bottom-color: #CCCCCC;"
						align="center">
					<tr>
						<td width="100" class="menu" id="td_sub_cadastro" style="height: 7px" onclick="AlternarSubAbas('td_sub_cadastro','div_sub_cadastro')">
							<span style="font-size:14px">Veículos</span>
						</td>
						<td style="border-bottom: 1px solid #CCCCCC; height: 7px;">
							&nbsp;</td>
						<td style="height: 7px"></td>						
					</tr>
					<tr>
						<td class="tb-sub-conteudo" colspan="4">
							<div id="div_sub_cadastro" class="conteudo" style="display:block; border-right: 1px solid #000000; border-right-color: #CCCCCC;">
								
								<div>
								
								
								<form name="listaBensUsuarios" method="post" action="">
								<table cellspacing="6" cellpadding="0" id="tabelaVeiculos">
										<tr>
											<td colspan="5">
												<br />
											</td>
										</tr>
									<?php 
									//Montando listagem - $cliente está na sessão
									$res = mysql_query("select * from bem where cliente = $codigoCliente order by name");
									
									if (mysql_num_rows($res) == 0) {
										echo "<tr><td colspan='5'><b id='alertNenhumVeiculo'>Nenhum veículo encontrado.</b></td> </tr>";
									} else {
									
										  echo "<tr>
													<td>Número imei</td>
													<td>Nome no menu</td>
													<td>Identificação</td>
													<td><a href='http://www.mxstudio.com.br/Conteudos/Dreamweaver/Cores.htm' style='color:#0099FF' target='_blank'>Gráfico</a></td>
													<td>Ativo?</td>
													<td><img src='../imagens/salvar_todos.gif' title='Salvar todos' alt='Salvar todos' onclick='salvarTodos();' /></td>
													<td>Backup</td>
													<td>Excluir</td>
												</tr>";
									}
									
									$maxId = 0;
									for ($i=0; $i < mysql_num_rows($res); $i++) {
										$row = mysql_fetch_assoc($res);
										
										if ($maxId < (int)$row[id]) 
										{
											$maxId = (int)$row[id];
										}
									
										echo "<tr id='linhaBemCliente". $row[id] ."'>";
											echo "<td><input maxlength='15' size='17' id='listaImei". $row[id] ."' name='listaImei". $row[id] ."' type='text' value='". $row[imei] ."' class='campoNovoVeiculo' />
											          <input maxlength='15' size='17' id='listaImeiHidden". $row[id] ."' name='listaImeiHidden". $row[id] ."' type='hidden' value='". $row[imei] ."' />
													  <input maxlength='15' id='listaIdBemHidden". $row[id] ."' name='listaIdBemHidden". $row[id] ."' type='hidden' value='". $row[id] ."' />
											      </td>";
											echo "<td><input id='listaNome". $row[id] ."' name='listaNome". $row[id] ."' type='text' value='". $row[name] ."' class='campoNovoVeiculo' /></td>";
											echo "<td><input id='listaIdent". $row[id] ."' name='listaIdent". $row[id] ."' type='text' value='". $row[identificacao] ."' class='campoNovoVeiculo' /></td>";
											echo "<td><input id='listaCor". $row[id] ."' name='listaCor". $row[id] ."' type='text' value='". $row[cor_grafico] ."' class='campoNovoVeiculo' maxlength='6' size='6' style='background-color: #". $row[cor_grafico] ."' onblur='this.value=this.value.toUpperCase();' /></td>";
											echo "<td><select id='listaAtivo". $row[id] ."' name='listaAtivo". $row[id] ."' class='campoNovoVeiculo'>";
												if ($row[activated] == 'S') {
													echo "<option selected value='S'>Sim</option>
														  <option value='N'>Não</option>";
												} else {
													echo "<option value='S'>Sim</option>
														  <option selected value='N'>Não</option>";
												}
												echo "</select>";
											echo "</td>";
											echo "<td> <div style='width:40px'>";
													echo "<img src='../imagens/salvar.png' title='Salvar alteração' alt='Salvar alteração' onclick='alterarVeiculoAdmin(". $row[id] .");' /> ";
													echo "<img id='imgExecutando". $row[id] ."' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' />";
													echo "<img id='imgSucesso". $row[id] ."' style='display:none' src='../imagens/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
											echo "</div></td>";
											echo "<td> <div style='width:40px;text-align:center'>";
													echo "<img src='../imagens/excel.png' style='cursor:pointer' title='Salvar backup' alt='Salvar backup' onclick='exibirPopUpBackup(". $row[id] .");' /> ";
												echo "</div></td>";												
											echo "<td> <div style='width:40px'>
													<a href='javascript:void(0);'><img border=0 id='imgExcluirBem". $row[id] ."' src='../imagens/lixeira.png' title='Excluir item' alt='Excluir item' onclick='excluirBemUsuario(". $row[id] .")' /><a>
																				  <img border=0 id='imgExcluindo". $row[id] ."' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' />
												  </div></td>";
										echo "</tr>";
									}
									
									echo "
										<script language='JavaScript'>
											totalVeiculos = ". $maxId .";
										</script>
										";
									?>									
									</table>
									<br />
									<img src='../imagens/btnNovoVeiculo.png' title='Adicionar novo veículo' alt='Adicionar novo veículo' onclick='adicionarNovaLinhaVeiculos();' /> 
									</form>
								</div>
								
							</div>
						</td>
					</tr>
					</table>
					<?php } ?>
				</div>
			</div>

			<div id="div_consulta" class="conteudo" style="display: none;">
				<div>
					Listagem e Alteração de usuários <br />
					
					<form name="listaTodosUsuarios" method="post" action="">
					<br />
					<!--input maxlength='20' size='20' id="campoPesquisa" name="campoPesquisa" type='text' class='campoNovoVeiculo' />
					<input name="btnPesquisar" type="button" value="Pesquisar" class="btnAcao" onclick="return false;" /-->
					
					<?php 
					
						$tipoLista = $_GET['lista'];
						$masterListaUsuarios = "N";
						
						if ($tipoLista == null or $tipoLista == "usuario") 
						{
							$masterListaUsuarios = "N";
							echo "Listar usuários | <a href='admin.php?lista=admin&aba=usuarios' style='color:#0099FF'>Listar administradores</a>";
						}
						else
							if ($tipoLista != null and $tipoLista == "admin") 
							{
								$masterListaUsuarios = "S";
								echo "<a href='admin.php?lista=usuario&aba=usuarios' style='color:#0099FF'>Listar usuários</a> | Listar administradores";
							}
						
					
					?>					
					
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>					
						<?php 
						//Montando listagem
						$resUsu = mysql_query("select CAST(c.id AS DECIMAL(10,0)) as id, c.id as codCliente, c.email, c.nome, c.ativo, 
													(select x.nome from cliente x where x.id = c.id_admin limit 1) as subAdmin,
													(select count(*) from bem where cliente = c.id) as qtFrota 
											  from cliente c 
											  where c.master = 'N' and admin = '$masterListaUsuarios'
											  order by c.nome");
						
						if (mysql_num_rows($resUsu) == 0) {
							echo "<tr><td colspan='5'><b>Nenhum item encontrado.</b></td> </tr>";
						} else {
							$cabFrota = $masterListaUsuarios == "N"? "Frota" : "Dados";
							$adminPor = $masterListaUsuarios == "N"? "<td>Administrado por</td>" : "";
							  echo "<tr>
										<td>Código</td>
										<td>e-mail</td>
										$adminPor
										<td>Nome do cliente</td>
										<td>Ativo?</td>
										<td>$cabFrota</td>
										<td>Salvar</td>
										<td>Excluir</td>
									</tr>";
						}
						
						for ($i=0; $i < mysql_num_rows($resUsu); $i++) {
							$rowUsu = mysql_fetch_assoc($resUsu);
						
							echo "<tr>";
								echo "<td><input disabled maxlength='10' size='12' id='listaCodigoCliente". $rowUsu[id] ."' name='listaCodigoCliente". $rowUsu[id] ."' type='text' value='". $rowUsu[codCliente] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input disabled id='listaEmailCliente". $rowUsu[id] ."' name='listaEmailCliente". $rowUsu[id] ."' type='text' value='". $rowUsu[email] ."' class='campoNovoVeiculo' /></td>";
								if ($masterListaUsuarios == "N")
									echo "<td><input disabled id='listaSubAdmin". $rowUsu[id] ."' name='listaSubAdmin". $rowUsu[id] ."' type='text' value='". $rowUsu[subAdmin] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input size='35' id='listaNomeCliente". $rowUsu[id] ."' name='listaNomeCliente". $rowUsu[id] ."' type='text' value='". $rowUsu[nome] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><select id='listaAtivoCliente". $rowUsu[id] ."' name='listaAtivoCliente". $rowUsu[id] ."' class='campoNovoVeiculo'>";
									if ($rowUsu[ativo] == 'S') {
										echo "<option selected value='S'>Sim</option>
											  <option value='N'>Não</option>";
									} else {
										echo "<option value='S'>Sim</option>
											  <option selected value='N'>Não</option>";
									}
									echo "</select>";
								echo "</td>";	
								
								echo "<td valign='top' style='color:black;font-weight:bold'> <div style='width:46px'> <a href='javascript:void(0);'>";
								if ($masterListaUsuarios == "N")
									echo "<img border=0 src='../imagens/frota.gif' style='height:25px' title='Frota do cliente' alt='Frota do cliente' onclick='abrirFrotaCliente(". $rowUsu[id] .");' /> </a> <sup>". $rowUsu[qtFrota] ."</sup></div></td>";
								else
									echo "<img border=0 src='../imagens/admin.png' style='height:25px' title='Dados administrador' alt='Dados administrador' onclick='abrirFrotaCliente(". $rowUsu[id] .");' /> </a> </div></td>";								

								echo "<td> <div style='width:40px'>";
										echo "<img src='../imagens/salvar.png' title='Salvar dados' alt='Salvar dados' onclick='salvarUsuarioAdmin(". $rowUsu[id] .");' /> ";
										echo "<img id='imgExecutandoCliente". $rowUsu[id] ."' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' />";
										echo "<img id='imgSucessoCliente". $rowUsu[id] ."' style='display:none' src='../imagens/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
							
									echo "<td> <div style='width:40px'>";
									echo "<a href='javascript:void(0);'><img border=0 src='../imagens/lixeira.png' title='Excluir conta' alt='Excluir conta' onclick='excluirUsuarioAdmin(". $rowUsu[id] .");' /></a>";
									echo "<img id='imgExcluindoCliente". $rowUsu[id] ."' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' />";
								echo "</div></td>";
							echo "</tr>";
						}
						?>
					</table>
					</form>
				</div>
			</div>
			
			<div id="div_manutencao" class="conteudo" style="display: none">
				Listagem de pagamentos <br />
				
					<form name="listaPagamentosUsuarios" method="post" action="">
					<br />
					<div>
					<input maxlength='20' size='20' id="campoPesquisa" name="campoPesquisa" type='text' class='campoNovoVeiculo' />
					<input name="btnPesquisarPgtos" type="button" value="Pesquisar" class="btnAcao" onclick="return false;" />
					</div>
					<div style="float:right; margin-right:20px">
						<table cellspacing="6" cellpadding="0" style="font-size: 12px;">
							<tr>
								<td><b>Legenda ícones de pagamento</b></td>
							</tr>
							<tr>
								<td><img alt='Sem registro de pagamento' title='Sem registro de pagamento' src='../imagens/registra_pgto.gif' /> Sem registro de pagamento</td>
							</tr>
							<tr>
								<td><img alt='Pagamento confirmado' title='Pagamento confirmado' src='../imagens/pagou.gif' /> Pagamento confirmado</td>
							</tr>
							<tr>
								<td><img alt='Sem pagamento' title='Sem pagamento' src='../imagens/sem_pagamento.gif' /> Sem pagamento</td>
							</tr>
						</table>
					</div>
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>					
						<?php 
						//Montando listagem
						$resPag = mysql_query("select CAST(c.id AS DECIMAL(10,0)) as id,
													  c.id as codCliente,
													  c.email,
													  c.nome,
													  c.ativo,
													  c.observacao,
													  p.*
													  from cliente c left join pagamento p on (c.id = p.cliente) 
													  where c.ativo = 'S' and c.master = 'N' and
														   (c.data_inativacao is null or c.data_inativacao >= CURDATE())
													  order by nome");
						if (mysql_num_rows($resPag) == 0) {
							echo "<tr><td colspan='5'><b>Nenhum item encontrado.</b></td> </tr>";
						} else {
							  echo "<tr>
										<td>Código</td>
										<td>e-mail</td>
										<td>Nome do cliente</td>
										<td>Ativo?</td>
										<td></td>
									</tr>";
						}
						
						/** Retorna a imagem de pagamento*/
						function obterImagemPagamento($flPagamento)
						{
							$imgPagamento = "";
							
							switch($flPagamento)
							{
								//F=falta informar; N=Nao pagou;S=pagou
								case "F": $imgPagamento = "registra_pgto.gif"; break;
								case "N": $imgPagamento = "sem_pagamento.gif"; break;
								case "S": $imgPagamento = "pagou.gif"; break;
								
								default: $imgPagamento = "registra_pgto.gif";
							}	

							return $imgPagamento;
						}
						
						for ($i=0; $i < mysql_num_rows($resPag); $i++) {
							$rowPag = mysql_fetch_assoc($resPag);
						
							echo "<tr>";
								echo "<td><input disabled maxlength='10' size='12' id='listaCodigoClientePgto". $rowPag[id] ."' name='listaCodigoClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[codCliente] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input disabled id='listaEmailClientePgto". $rowPag[id] ."' name='listaEmailClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[email] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input size='35' id='listaNomeClientePgto". $rowPag[id] ."' name='listaNomeClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[nome] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><select id='listaAtivoClientePgto". $rowPag[id] ."' name='listaAtivoClientePgto". $rowPag[id] ."' class='campoNovoVeiculo'>";
									if ($rowPag[ativo] == 'S') {
										echo "<option selected value='S'>Sim</option>
											  <option value='N'>Não</option>";
									} else {
										echo "<option value='S'>Sim</option>
											  <option selected value='N'>Não</option>";
									}
									echo "</select>";
								echo "</td>";	
								
								//echo "<td><img src='../imagens/frota.gif' style='height:25px' title='Frota do cliente' alt='Frota do cliente' onclick='abrirFrotaCliente(". $rowPag[id] .");' /> ";

								echo "<td> <div style='width:40px'>";
										echo "<img src='../imagens/salvar.png' title='Salvar dados' alt='Salvar dados' onclick='salvarUsuarioAdminPgto(". $rowPag[id] .");' /> ";
										echo "<img id='imgExecutandoClientePgto". $rowPag[id] ."' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' />";
										echo "<img id='imgSucessoClientePgto". $rowPag[id] ."' style='display:none' src='../imagens/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
								echo "</div></td>";
							echo "</tr>";
							echo "<tr>";
								echo "
									<td colspan='5'>
										Obs.: <input size='82' id='listaObsClientePgto". $rowPag[id] ."' name='listaObsClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[observacao] ."' class='campoNovoVeiculo' />
									</td>
								";
							echo "</tr>";
							echo "<tr>";
								echo "<td colspan='2'>
								
									<table style='width: 100%;text-align:center;font-size:xx-small'>
										<tr>
											<td>jan</td>
											<td>fev</td>
											<td>mar</td>
											<td>abr</td>
											<td>mai</td>
											<td>jun</td>
											<td>jul</td>
											<td>ago</td>
											<td>set</td>
											<td>out</td>
											<td>nov</td>
											<td>dez</td>
										</tr>
										<tr>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[jane]) ."' id='imgRegistraPagto1". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(1, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[feve]) ."' id='imgRegistraPagto2". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(2, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[marc]) ."' id='imgRegistraPagto3". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(3, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[abri]) ."' id='imgRegistraPagto4". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(4, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[maio]) ."' id='imgRegistraPagto5". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(5, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[junh]) ."' id='imgRegistraPagto6". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(6, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[julh]) ."' id='imgRegistraPagto7". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(7, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[agos]) ."' id='imgRegistraPagto8". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(8, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[sete]) ."' id='imgRegistraPagto9". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(9, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[outu]) ."' id='imgRegistraPagto10". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(10, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[nove]) ."' id='imgRegistraPagto11". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(11, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../imagens/". obterImagemPagamento($rowPag[deze]) ."' id='imgRegistraPagto12". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(12, ". $rowPag[id] .", this)' /></td>
										</tr>
									</table>
								<br />
								</td>";
							echo "<td colspan='3'><span class='dicaCadastro'>Dica: Clique no ícone referente ao mês para registrar o pagamento</span></td>";
							echo "</tr>";
						}
						?>
					</table>
					</form>
				
			</div>
			
			<div id="div_status" class="conteudo" style="display: none">
				Status dos aparelhos 
				<span style="font-size:10px">
					<i>(Atualizado as <?php echo date("H:i:s") ?>h) </i>
					<a href='admin.php?aba=status' style='color:#0099FF'>atualizar agora</a>
				</span> 
				<br /><br />
				
				<input maxlength='20' size='20' id="campoPesquisaStatus" name="campoPesquisaStatus" type='text' class='campoNovoVeiculo' />
				<input name="btnPesquisarIdenStatus" type="button" value="Pesquisar" class="btnAcao" onclick="pesquisaIdentificacao(document.getElementById('campoPesquisaStatus').value);" />
						
				<form name="listaAcessos" method="post" action="">
				<table cellspacing="6" cellpadding="0">
						<tr>
							<td colspan="5">
								<br />
							</td>
						</tr>
						<?php 
						
						//Conectando
						$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")
						  or die("Could not connect: " . mysql_error());
						mysql_select_db('tracker', $cnx);
						
						$resAcesso = mysql_query("select b.name, b.identificacao, b.imei, b.status_sinal, c.id, c.nome from bem b inner join cliente c on (c.id = b.cliente) where c.ativo = 'S' and c.master = 'N' order by c.nome");
						
						$numRowns = mysql_num_rows($resAcesso);
						
						?>
						
						<tr>
							<th valign="top">IMEI</th>
							<th valign="top">Nome no menu</th>
							<th valign="top">Identificação</th>
							<th valign="bottom" rowspan="<?php echo ((int)$numRowns + 1); ?>" class="divisorLog"></th>
							<th valign="top">Cliente</th>
							<th valign="bottom" rowspan="<?php echo ((int)$numRowns + 1); ?>" class="divisorLog"></th>
							<th valign="top">Status</th>
							<th valign="top"></th>
						</tr>
						
						<?php
						
						for ($k=1; $k <= mysql_num_rows($resAcesso); $k++) 
						{
							$rowAcesso = mysql_fetch_assoc($resAcesso);
							
							echo "<tr id='stKey". $k ."'>";
								echo "<td>". $rowAcesso[imei] ."</td>";
								echo "<td>". $rowAcesso[name] ."</td>";
								echo "<td>". $rowAcesso[identificacao] ."
											<input name='key". $k ."' id='key". $k ."' type='hidden' value='". $rowAcesso[identificacao] ."' />
									 </td>";
								echo "<td><a href='admin.php?acao=obterUsuario&codigo=". $rowAcesso[id] ."' style='color:#0099FF'>". $rowAcesso[nome] ."</a></td>";
								//Indica o status do aparelho. R=rastreando;S=sem sinal gps;D=desligado
								if ($rowAcesso[status_sinal] == "R")
									echo "<td>Rastreando</td> <td><img border=0 src='../imagens/status_rastreando.png' alt='Rastreando' title='Rastreando' /></td>";
								if ($rowAcesso[status_sinal] == "S")
									echo "<td>Sem satélite</td> <td><img border=0 src='../imagens/status_sem_sinal.png' alt='Sem satélite GPS' title='Sem satélite GPS' /></td>";
								if ($rowAcesso[status_sinal] == "D")
									echo "<td>Desligado</td> <td><img border=0 src='../imagens/status_desligado.png' alt='Desligado' title='Desligado' /></td>";
							echo "</tr>";
							
						}
						
						
						
						if (mysql_num_rows($resAcesso) == 0)
						{
							echo "<tr><td colspan='4'><i>Nenhum IMEI encontrado.</i></td>";
						}
						
						?>
				</table>
				<?php
					echo "<span id='resultadoPesquisaVazio' style='display:none'><br/><i>Nenhum item da pesquisa encontrado.</i></span>";
				?>
				</form>
				<?php
					echo "<script language='javascript'>
							totalPesquisaIdentificacao = ". mysql_num_rows($resAcesso) .";
						  </script>";
				?>
				<br />
			</div>		

			<div id="div_hist_com" class="conteudo" style="display: none">
				Histórico de comandos de bloqueio
				<br /><br />
				<?php 
				
				$dtIniBloqueio = $_POST["dtIniBloqueio"] == "" ? date("d/m/Y") : $_POST["dtIniBloqueio"]; // formato: 10/03/2006
				$dtFimBloqueio = $_POST["dtFimBloqueio"] == "" ? date("d/m/Y") : $_POST["dtFimBloqueio"];
				
				?>
				
				<form name="listaComandos" method="post" action="admin.php">
				<input maxlength='10' size='12' id="" value="<?php echo $dtIniBloqueio ?>" name="dtIniBloqueio" type='text' class='campoNovoVeiculo' onkeyup="formataData(this,event)" /> a <input maxlength='10' size='12' id="" value="<?php echo $dtFimBloqueio ?>" name="dtFimBloqueio" type='text' class='campoNovoVeiculo' onkeyup="formataData(this,event)" />
				<input name="btnPesquisarComandos" type="submit" value="Pesquisar" class="btnAcao" onclick="" />
				<input name="buscaComando" type="hidden" value="1" />
				
				<table cellspacing="6" cellpadding="0">
						<tr>
							<td colspan="5">
								<br />
							</td>
						</tr>
						<?php 
						
						
						
						$dtIniBloqueioSql = substr($dtIniBloqueio, 6, 4) . "-" . substr($dtIniBloqueio, 3, 2) . "-" . substr($dtIniBloqueio, 0, 2);
						$dtFimBloqueioSql = substr($dtFimBloqueio, 6, 4) . "-" . substr($dtFimBloqueio, 3, 2) . "-" . substr($dtFimBloqueio, 0, 2);
						
						//Conectando
						$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")
						  or die("Could not connect: " . mysql_error());
						mysql_select_db('tracker', $cnx);
						
						$resHistCom = mysql_query("select b.name, b.identificacao, b.imei, b.status_sinal, c.id, c.nome, l.data, l.ip
												   from bem b inner join cliente c on (c.id = b.cliente) 
															  inner join command_log l on (l.cliente = c.id and l.imei = b.imei)
												   where c.ativo = 'S' and c.master = 'N' and l.data between '$dtIniBloqueioSql 00:00:00' and '$dtFimBloqueioSql 23:59:59'
												   order by l.data DESC");
						
						$numRowns = mysql_num_rows($resHistCom);
						
						?>
						
						<tr>
							<th valign="top">IMEI</th>
							<th valign="top">Nome no menu</th>
							<th valign="top">Identificação</th>
							<th valign="bottom" rowspan="<?php echo ((int)$numRowns + 1); ?>" class="divisorLog"></th>
							<th valign="top">Cliente</th>
							<th valign="bottom" rowspan="<?php echo ((int)$numRowns + 1); ?>" class="divisorLog"></th>
							<th valign="top">Data envio</th>
							<th valign="bottom" rowspan="<?php echo ((int)$numRowns + 1); ?>" class="divisorLog"></th>
							<th valign="top">Endereço IP</th>							
						</tr>
						
						<?php
						
						for ($k=1; $k <= mysql_num_rows($resHistCom); $k++) 
						{
							$rowHistCom = mysql_fetch_assoc($resHistCom);
							
							echo "<tr>";
								echo "<td>". $rowHistCom[imei] ."</td>";
								echo "<td>". $rowHistCom[name] ."</td>";
								echo "<td>". $rowHistCom[identificacao] ."</td>";
								echo "<td><a href='admin.php?acao=obterUsuario&codigo=". $rowHistCom[id] ."' style='color:#0099FF'>". $rowHistCom[nome] ."</a></td>";
								echo "<td>". date('d/m/Y H:i:s', strtotime($rowHistCom[data])) ."</td>";
								echo "<td>". $rowHistCom[ip] ."</td>";
							echo "</tr>";
							
						}
						
						if (mysql_num_rows($resHistCom) == 0)
						{
							echo "<tr><td colspan='4'><i>Nenhum comando de bloqueio enviado.</i></td>";
						}
						
						?>
				</table>
				</form>
				<br />
			</div>
			
			<div id="div_termos" class="conteudo" style="display: none">
				Termos de Uso e Privacidade
				<br/>
				
					<form name="formTermos" id="formTermos" method="post" action="admin.php" autocomplete="off">
						
						<input name="acaoLinkTermos" type="hidden" value="true" />
						<table style="width: 70%" cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="2">
									<br />
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Link:</td>
								<td><input name="nmLinkTermos" id="nmLinkTermos" maxlength="400" size="70" type="text" value="<?php echo $nmLinkTermos; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro"><br/>Link da página inicial para termos de uso</span>
								</td>
							</tr>
							<tr>
								<td><br/></td>
								<td><br/></td>
							</tr>
							<tr>
								<td>
								</td>
								<td>
									<input name="btnCadastrarLink" type="submit" value="Salvar" class="btnAcao" />
								</td>
							</tr>
						</table>
					</form>				
					<br />
			</div>
		</td>
	</tr>
</table>
<?php
	mysql_close($cnx);
?>
<br /><br /><br />
</div>
</body>
</html>

