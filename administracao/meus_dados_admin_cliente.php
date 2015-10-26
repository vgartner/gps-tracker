<?php include('../seguranca.php');

if ($cliente != "admin") {
	header("Location: /logout.php");
	//header("Location: http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/logout.php");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Alteração de dados do usuário</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

$sucesso = null;
$erro = false;
//$nome = $_POST['nome'];
//$apelido = $_POST['apelido'];
$email = $_POST['email'];
$novaSenha = $_POST['novaSenha'];
$senhaAtual = $_POST['senhaAtual'];

$sql = null;

//Conectando
$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689")
  or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

if ($email != null and $novaSenha != null and $senhaAtual != null) {

	if (!$erro) {
	
		$email = mysql_real_escape_string($email);
		$novaSenha = mysql_real_escape_string($novaSenha);
		$senhaAtual = mysql_real_escape_string($senhaAtual);
	
		$sql = "UPDATE cliente set senha = '". md5($novaSenha) ."' WHERE email = '$email' and master = 'N' and id = $id_admin and senha = '". md5($senhaAtual) ."'";

		if (!mysql_query($sql, $cnx))
		{			
			if (mysql_error() == "Duplicate entry '". $email ."' for key 'email_unq'" or mysql_error() == "Duplicate entry '". $apelido ."' for key 2") {
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Login já existe.</span>";
			}
			else 
			{
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Falha no cadastro.</span>";			
			}
		}
		else
		{
			if (mysql_affected_rows() == 0)
			{
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Para cadastrar nova senha, digite a senha atual corretamente. A senha NÃO foi alterada!</span>";
			}
			else 
			{
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:black\">Sua senha foi alterada com sucesso!</span>";
			}
		}
	}
}

$resUsuario = mysql_query("select email from cliente where master = 'N' and id = $id_admin limit 1");
for ($k=0; $k < mysql_num_rows($resUsuario); $k++) {
	$rowUsuario = mysql_fetch_assoc($resUsuario);
	//$nome = $rowUsuario[nome];
	//$apelido = $rowUsuario[apelido];
	$email = $rowUsuario[email];
}

mysql_close($cnx);
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
	cursor: hand;
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
	cursor: hand;
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
	width:10%;
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

</style>
<script type="text/javascript" src="js/alterarVeiculo.js"></script>
<script language="JavaScript">

	function stAba(menu,conteudo)
	{
		this.menu = menu;
		this.conteudo = conteudo;
	}

	var arAbas = new Array();
	arAbas[0] = new stAba('td_cadastro','div_cadastro');
	//arAbas[1] = new stAba('td_consulta','div_consulta');
	//arAbas[2] = new stAba('td_manutencao','div_manutencao');

	function AlternarAbas(menu,conteudo)
	{
		for (i=0;i<arAbas.length;i++)
		{
			m = document.getElementById(arAbas[i].menu);
			m.className = 'menu';
			c = document.getElementById(arAbas[i].conteudo)
			c.style.display = 'none';
		}
		m = document.getElementById(menu)
		m.className = 'menu-sel';
		
		c = document.getElementById(conteudo)
		c.style.display = '';
	}
	
	function esconderAlerta() {
		try
		  {
		  	var existeSpan = document.getElementById('alertaCadastro');
		  	existeSpan.style.display='none';
		  }
		catch(err)
		  {
			  //Abafo se o campo não existir
		  }
  	}
	
</script>

</head>

<body onLoad="AlternarAbas('td_cadastro','div_cadastro'); setTimeout('esconderAlerta()', 10000); " 
	style=" background-position: right bottom; height:auto; border-left:thin; border-left-style: solid; 
			border-left-width: 1px; border-left-color: #CCCCCC; margin-left:0px; margin-top:-17px; 
			/*background-image:url('/imagens/fundo_logo_webarch.png');*/ background-repeat: no-repeat;">

<h2 align="center" style="font-size:20px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #666666;">Meus dados administrativos</h2>
<table width="80%" height="80%" cellspacing="0" cellpadding="0" border="0" style="border-left: 1px solid #000000; border-left-color: #CCCCCC;" align="center">

	<tr>
		<td width="100" class="menu" id="td_cadastro"
		onclick="AlternarAbas('td_cadastro','div_cadastro')" style="height: 7px">
			Cadastro
		</td>
		<!--td width="100" class="menu" id="td_consulta"
		onclick="AlternarAbas('td_consulta','div_consulta')" style="height: 7px">
			Consulta
		</td-->
		<!--td width="100" class="menu" id="td_manutencao"
		onclick="AlternarAbas('td_manutencao','div_manutencao')" style="height: 7px">
			Manutenção
		</td-->
		<td style="border-bottom: 1px solid #CCCCCC; height: 7px;">
			&nbsp;</td>
		<td style="height: 7px"></td>
	</tr>
	<tr>
		<td class="tb-conteudo" colspan="4">
			<div id="div_cadastro" class="conteudo" style="display:block;">
				<div>
					Altere seus dados cadastrados <br />

					<form name="alteraDados" method="post" action="meus_dados_admin_cliente.php" autocomplete="off">
						<table style="width: 70%" cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="2">
									<?php echo $sucesso ?>
									<br />
								</td>
							</tr>						
							<tr>
								<td class="textoEsquerda">Login:</td>
								<td><input name="email" maxlength="30" size="20" type="text" class="campoNovoVeiculo" value="<?php echo $email ?>" readonly />
									<span class="dicaCadastro">Dica: Login de acesso administrador</span>
								</td>
							</tr>
							<tr>
								<td nowrap="nowrap" class="textoEsquerda">Senha atual:</td>
								<td><input id="senhaAtual" name="senhaAtual" maxlength="50" size="20" type="password" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Informe sua senha atual</span>
								</td>
							</tr>
							<tr>
								<td><br/></td>
								<td><br/></td>
							</tr>
							<tr>
								<td>Alterar senha</td>
								<td><br/><br/></td>
							</tr>
							<tr>
								<td nowrap="nowrap" class="textoEsquerda">Nova senha:</td>
								<td><input id="novaSenha" name="novaSenha" maxlength="50" size="20" type="password" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Digite sua nova senha</span>
								</td>
							</tr>
							<tr>
								<td nowrap="nowrap" class="textoEsquerda">Confirme senha:</td>
								<td><input id="confirmeSenha" name="confirmeSenha" maxlength="50" size="20" type="password" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Confirme sua nova senha</span>
								</td>
							</tr>
							<tr>
								<td><br/></td>
								<td><br/></td>
							</tr>
							<tr>
								<td colspan="2">
									<input name="btnCadastrar" type="submit" value="Salvar" class="btnAcao" onclick=" if (document.getElementById('novaSenha').value != document.getElementById('confirmeSenha').value) { alert('Confirmação de senha está errada!');return false } else { return true; } " />
									&nbsp;
									<a href="admin_cliente.php" style="color:#0099FF">Voltar</a>
								</td>
							</tr>
						</table>
					</form>
					
				</div>
			</div>

			<!--div id="div_consulta" class="conteudo" style="display: none;">
				<div>
					Listagem e Alteração de bens <br />
					
					<form name="listaBens" method="post" action="menu_novo_veiculo.php">
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>
					</table>
					</form>
					<br />
					<a href="mapa.php" style="color:#0099FF">Cancelar</a>
				</div>
			</div-->
			<!--div id="div_manutencao" class="conteudo" style="display: none">
				MANUTENÇÃO
			</div-->
		</td>
	</tr>
</table>
<br /><br /><br />
</body>
</html>

