<?
include('../seguranca.php');
include('../usuario/config.php');

$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Could not connect: " . mysql_error());
mysql_select_db($DB_NAME, $cnx);

$cliente_id = isset($_POST['cliente_id']) ? $_POST['cliente_id'] : $_GET['cliente_id'];

$acao = $_POST['acao'];
$imei = $_POST['imei'];
$name = $_POST['name'];
$identificacao = $_POST['identificacao'];
$ativo = $_POST['ativo'];
$tipo = $_POST['tipo'];
$rastreador = $_POST['rastreador'];
$hodometro = $_POST['hodometro'];
/*if(!empty($hodometro)){
	$hodometro = $hodometro * 1000;
} else $hodometro = 0;*/
$apelido = $_POST['apelido'];
$modelo = $_POST['modelo'];
$marca = $_POST['marca'];
$cor = $_POST['cor'];
$ano = $_POST['ano'];
$operadora = $_POST['operadora'];
$id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
$dt_recarga = $_POST['dt_recarga'];
$apelido = $_POST['apelido'];
$envia_sms = $_POST['envia_sms'];
$clienteId = $_POST['cliente_id'];

if($acao == 'incluir'){
	if(!mysql_query("INSERT INTO bem (imei, name, identificacao, cliente, activated, modo_operacao, liberado, tipo, hodometro, envia_sms, modelo, marca, cor, ano, operadora, modelo_rastreador, apelido, dt_recarga)
					VALUES('$imei', '$name', '$identificacao', '$clienteId', '$ativo', 'GPRS', 'S', '$tipo', '$hodometro', '$envia_sms', '$modelo', '$marca', '$cor', '$ano', '$operadora', '$rastreador', '$apelido', '$dt_recarga')")){
		echo mysql_error();
	} else echo 'OK';
	
	mysql_close($cnx);
	return;
}

if($acao == 'excluir'){
	mysql_query("delete from gprmc where imei = '$imei'");
	mysql_query("delete from loc_atual where imei = '$imei'");
	if(!mysql_query("delete from bem where imei = '$imei'")){
		echo mysql_error();
	} else echo 'OK';
	
	mysql_close($cnx);
	return;
}

if($acao == 'alterar'){
	if(!mysql_query("update bem set imei = '$imei', name = '$name', identificacao = '$identificacao', tipo = '$tipo', 
					hodometro = '$hodometro', envia_sms = '$envia_sms', modelo = '$modelo', marca = '$marca', cor = '$cor', ano = '$ano', 
					operadora = '$operadora', modelo_rastreador = '$rastreador', apelido = '$apelido', activated = '$ativo', dt_recarga = '$dt_recarga' where id = $id")){
		echo mysql_error();
	} else echo 'OK';
	mysql_close($cnx);
	return;
}

?>
<div class="get-code-window">
<h1>Novo Veículo</h1>

    <div class="alert">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>Atenção!</strong> Para Rastreadores Suntech digite somente os 6 penultimos digitos do IMEI, exemplo: XXXXXXXX123456X - Desconciderando os outros representado por "X"
    </div>

<form id="formNovoVeiculo" action="ajax/veiculo_form.php" method="post">
<input type="hidden" name="acao" value="incluir" />
<input type="hidden" name="id" value="<?=$id?>" />
<input type="hidden" name="cliente_id" value="<?=$cliente_id?>" id="cliente_id"/>
<table>
	<tr><td>IMEI</td><td><input name="imei" type="text" maxlength="17" required></td></tr>
    <tr><td>Placa</td><td><input name="name" type="text" ></td></tr>
    <tr><td>Identificação</td><td>
		<input name="apelido" type="text" maxlength="30">
    </td></tr>
    <tr><td>Num. Chip</td><td><input name="identificacao" type="text" maxlength="20"></td></tr>
    <tr><td>Operadora</td><td>
        <select name="operadora">
        	<option value="TIM">TIM</option>
            <option value="Claro">Claro</option>
            <option value="Oi">Oi</option>
            <option value="Vivo">Vivo</option>
            <option value="Nextel">Nextel</option>
            <option value="Outras">Outras</option>
        </select>
    </td></tr>
    <tr><td>Ativo</td><td>
    <select name="ativo">
    	<option value="S">Sim</option>
        <option value="N">Não</option>
    </select>
    </td></tr>
    <tr><td>Tipo</td><td>
    <select name="tipo">
    	<option value="CARRO">Carro</option>
        <option value="MOTO">Moto</option>
        <option value="VAN">Van</option>
        <option value="JET">Veiculo Aquatico</option>
        <option value="CAMINHAO">Caminhão</option>
        <option value="TRATOR">Trator</option>
        <option value="ONIBUS">Onibus</option>
        <option value="PICKUP">Pickup</option>
    </select>
    </td></tr>
    <tr><td>Rastreador</td><td>
     <select name="rastreador">
    	<?php
           $Qy = "
		   		select *
				  from rastreadores
		   ";
		   $rs = mysql_query($Qy) or die(mysql_error());
		   while ($row = mysql_fetch_assoc($rs))
		   {
			   echo '<option value="'.$row["rastreador"].'">'.strtoupper($row["rastreador"]).'</option>';
		   }
		?>
    </select>
    </td></tr>
    <tr><td>Modelo</td><td>
        <input name="modelo" type="text" maxlength="30">
    </td></tr>
    <tr><td>Marca</td><td>
		<input name="marca" type="text" maxlength="30">
    </td></tr>
    <tr><td>Cor</td><td>
		<input name="cor" type="text" maxlength="30">
    </td></tr>
    <tr><td>Ano</td><td>
		<input name="ano" type="text" maxlength="4">
    </td></tr>
    <tr><td>Hodômetro</td><td>
		<input name="hodometro" type="text">
    </td></tr>
    <tr><td>Data da Recarga</td><td>
        <input name="dt_recarga" type="text">
    </td></tr>
    <tr><td>Envia SMS?</td><td>
    <select name="envia_sms">
    	<option value="S">Sim</option>
        <option value="N">Não</option>
    </select>
    </td></tr>
    <tr><td colspan="2">
        <img src="../imagens/salvar.png" style="cursor:pointer" onclick="$('#formNovoVeiculo').submit()"/>
        <img src="../imagens/lixeira.png" style="cursor:pointer" />
        <img style="display:none" src="../imagens/executando.gif" id="veiculoFormExecutando" />
        <img id="veiculoFormSucesso" style="display:none" src="../imagens/sucesso.png" />
    </td></tr>
</table>
</form>
</div>
<!-- <script type="text/javascript" src="../javascript/jquery.validate.min.js"></script> -->
<script type="text/javascript">
	$('#formNovoVeiculo').validate({
        rules: {
            imei: { required: true, minlength: 6 }
        },
        messages: {
            imei: { required: "Você deverá informar um IMEI", minlength: "Mínimo de {0} caracteres." }
        }
    });

    $('#formNovoVeiculo').ajaxForm({
		beforeSubmit:function(){
            return $('#formNovoVeiculo').validate();
            $('#veiculoFormExecutando').show();
		},
		success:function(responseText){
			$('#veiculoFormExecutando').hide();
			if(responseText.indexOf('OK')>-1){
				$('#veiculoFormSucesso').show();
				setTimeout(function(){$('#veiculoFormSucesso').hide()}, 2000);
				var cliente_id = $('#cliente_id').val();
				carregarConteudo("#ui-tabs-1", "ajax/usuarios_form.php?acao=view&id="+cliente_id);
				$.magnificPopup.close();
			} else {
				alert(responseText);
			}
		}
	});
</script>