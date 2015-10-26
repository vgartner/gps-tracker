<?
include('../seguranca.php');
include('../usuario/config.php');

$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS)
  or die("Could not connect: " . mysql_error());
mysql_select_db($DB_NAME, $cnx);


$acao = $_POST['acao'];
if($acao == 'salvar'){
	$comandos = $_POST['comandos'];
	$grupos = $_POST['grupos'];
	$dados = $_POST['dados'];
	$despesas = $_POST['despesas'];
	$cerca = $_POST['cerca'];
	$rota = $_POST['rota'];
	$hodometro = $_POST['hodometro'];
	$logo = $_POST['logo'];
	$senha = $_POST['senha'];
	$strConfig = '{';
	$strConfig .= '"comandos":'.(!empty($comandos)?'true':'false').",";
	$strConfig .= '"grupos":'.(!empty($grupos)?'true':'false').",";
	$strConfig .= '"dados":'.(!empty($dados)?'true':'false').",";
	$strConfig .= '"despesas":'.(!empty($despesas)?'true':'false').",";
	$strConfig .= '"cerca":'.(!empty($cerca)?'true':'false').",";
	$strConfig .= '"rota":'.(!empty($rota)?'true':'false').",";
	$strConfig .= '"hodometro":'.(!empty($hodometro)?'true':'false').",";
	$strConfig .= '"logo":'.(!empty($logo)?'true':'false').",";
	$strConfig .= '"senha":'.(!empty($senha)?'true':'false');
	$strConfig .= "}";
	if(mysql_query("update cliente set configuracoes = '$strConfig' where id = $id_admin", $cnx)) 
		echo 'OK';
	else echo mysql_error($cnx);
	return;
}

$resConfig = mysql_query("select configuracoes from cliente where id = $id_admin", $cnx);
$data = mysql_fetch_assoc($resConfig);
if(!empty($data['configuracoes'])){
	$json = json_decode($data['configuracoes']);
}

mysql_close($cnx);
?>
<table>
<tr><th>Clientes</th></tr>
<tr><td><input type="checkbox" name="comandos" id="chk_comandos" value="S" <?=$json->comandos?'checked=checked':''?> />Visualiza Comandos</td></tr>
<tr><td><input type="checkbox" name="grupos" id="chk_grupos" value="S" <?=$json->grupos?'checked=checked':''?>/>Visualiza Grupos</td></tr>
<tr><td><input type="checkbox" name="dados" id="chk_dados" value="S" <?=$json->dados?'checked=checked':''?>/>Visualiza Dados Pessoais</td></tr>
<tr><td><input type="checkbox" name="despesas" id="chk_despesas" value="S"  <?=$json->despesas?'checked=checked':''?>/>Visualiza Despesas</td></tr>
<tr><td><input type="checkbox" name="cerca" id="chk_cerca" value="S" <?=$json->cerca?'checked=checked':''?>/>Cerca</td></tr>
<tr><td><input type="checkbox" name="rota" id="chk_rota" value="S" <?=$json->rota?'checked=checked':''?>/>Rota</td></tr>
<tr><td><input type="checkbox" name="hodometro" id="chk_hodometro" value="S" <?=$json->hodometro?'checked=checked':''?>/>Hodômetro</td></tr>
<tr><td><input type="checkbox" name="logo" id="chk_logo" value="S" <?=$json->logo?'checked=checked':''?>/>Logomarca</td></tr>
<tr><td><input type="checkbox" name="senha" id="chk_senha" value="S" <?=$json->senha?'checked=checked':''?>/>Troca Senha</td></tr>
<tr><td><input type="button" name="salva" id="salvar" value="Salvar" onClick="javascript:salvar()" /></td></tr>
</table>

<script type="text/javascript">
function salvar(){
	var comandos = $('#chk_comandos').attr("checked");
	var dados = $('#chk_dados').attr("checked");
	var despesas = $('#chk_despesas').attr("checked");
	var grupos = $('#chk_grupos').attr("checked");
	var cerca = $('#chk_cerca').attr("checked");
	var rota = $('#chk_rota').attr("checked");
	var hodometro = $('#chk_hodometro').attr("checked");
	var logo = $('#chk_logo').attr("checked");
	var senha = $('#chk_senha').attr("checked");
	
	$.post('ajax/preferencias_form.php', 
			{'acao':'salvar', 'comandos':comandos, 'dados':dados, 'despesas':despesas, 'grupos':grupos, 'senha':senha, 'cerca':cerca, 'rota':rota, 'hodometro':hodometro, 'logo':logo},
			function(data){
				//$('#veiculoFormExecutando_'+idVeiculo).hide();
				if(data.indexOf('OK') > -1){
					alert('Configuracao salva!');
				} else alert('Houve um erro interno!');
			}
		);
}
</script>