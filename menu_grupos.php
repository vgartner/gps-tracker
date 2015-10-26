<?php
include('seguranca.php');
include('usuario/config.php');

$cliente = isset($_POST['cliente']) ? $_POST['cliente'] : $_GET['cliente'];
$acao = $_GET['acao'];
$nome = $_GET['nome'];
$senha = $_GET['senha'];
$imei = $_GET['imei'];
$bem = $_GET['bem'];
$_grupo = $_GET['grupo'];
$grupoBem = $_GET['grupo_bem'];

$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);

mysql_select_db($DB_NAME);

if($acao == 'grupo_adicionar'){
	if($nome != '' && $senha != '' && $cliente!=''){
		$mudou = false;
		if($grupo != ''){
			$mudou = mysql_query("insert into grupo(nome, senha, cliente, grupo) values('$nome', '".md5($senha)."', $cliente, $grupo)", $con);
		} else {
			$mudou = mysql_query("insert into grupo(nome, senha, cliente) values('$nome', '".md5($senha)."', $cliente)", $con);
		}
		if($mudou){
			$id = mysql_insert_id($con);
			echo "OK_".$id;
		} else {
			if(strpos('Duplicate entry', mysql_error($con)) >= 0){
				echo "Erro: Já existe um grupo com esse nome.";
			} else {
				echo "ERRO: Falhar ao incluir o item!";
			}
		}
	}
	return;
}

if($acao == 'grupo_remover'){
	if($_grupo!=''){
		if(mysql_query("delete from grupo where id = $_grupo", $con)){
			mysql_query("delete from grupo_bem where grupo = $_grupo", $con);
			echo "OK";
		} else {
			echo "ERRO: Falhar ao excluir o item!";
		}
	}
	return;
}

if($acao == 'grupo_alterar'){
	if($nome != '' && $_grupo!=''){
		$retorno = false;
		if($senha != ''){
			$retorno = mysql_query("update grupo set nome = '$nome', senha = '".md5($senha)."' where id = $_grupo", $con);
		} else {
			$retorno = mysql_query("update grupo set nome = '$nome' where id = $_grupo", $con);
		}
		if($retorno){
			echo "OK";
		} else {
			if(strpos('Duplicate entry', mysql_error($con)) >= 0){
				echo "Erro: Já existe um grupo com esse nome.";
			} else {
				echo "ERRO: Falhar ao alterar o item!";
			}
		}
	}
	return;
}

if($acao == 'grupobem_adicionar'){
	if($bem != '' && $cliente!='' && $_grupo!=''){
		$consulta = mysql_query("select * from bem where id=$bem and cliente=$cliente", $con);
		if(mysql_num_rows($consulta) > 0) {
			$data = mysql_fetch_assoc($consulta);
			if(mysql_query("insert into grupo_bem(bem, cliente, imei, descricao, grupo) values($bem, $cliente, '".$data[imei]."', '".$data[name]."', $_grupo)", $con)){
				echo "OK ".mysql_insert_id($con);
			} else {
				if(strpos('Duplicate entry', mysql_error($con)) >= 0){
					echo "Erro: O veículo já existe nesse grupo.";
				} else {
					echo "ERRO: Falhar ao incluir o item!";
				}
			}
		} else {
			echo "ERRO: Item não existe no cadastro do cliente!";
		}
	}
	return;
}

if($acao == 'grupobem_remover'){
	if($grupoBem!=''){
		if(mysql_query("delete from grupo_bem where id = $grupoBem", $con)){
			echo "OK";
		} else {
			echo "ERRO: Falhar ao excluir o item!";
		}
	}
	return;
}

if($acao == 'grupobem_remover2'){
	if($_grupo!='' && $bem != '' && $cliente != ''){
		if(mysql_query("delete from grupo_bem where grupo = $_grupo and bem = $bem and cliente = $cliente", $con)){
			echo "OK";
		} else {
			echo "ERRO: Falhar ao excluir o item!";
		}
	}
	return;
}

if($acao == 'veiculos'){
	if($cliente!=''){
		$res = null;
		if($grupo != ''){
			$res = mysql_query("select b.id, b.imei, b.name, b.identificacao, b.tipo from bem b join grupo_bem gb on gb.bem = b.id where b.cliente = $cliente and b.liberado = 'S' and gb.grupo = $grupo", $con);
		} else {
			$res = mysql_query("select id, imei, name, identificacao, tipo from bem where cliente = $cliente and liberado = 'S'", $con);
		}
		$count = mysql_num_rows($res);
		$loop = 1;
		echo "[";
		while($data = mysql_fetch_assoc($res)){
			echo "['".$data[imei]."', '".$data[name]."', '".$data[identificacao]."', '".$data[id]."']";
			$loop++;
			if($loop <= $count){
				echo ",";
			}
		}
		echo "]";
	}
	return;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Grupos</title>
<script type="text/javascript">

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
             .toString(16)
             .substring(1);
};

function guid() {
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
         s4() + '-' + s4() + s4() + s4();
}

	var cliente = <?=$cliente?>;

	var xmlhttpPainel;

	function GetXmlHttpObject()
	{
		if (window.XMLHttpRequest)
		{
			// code for IE7+, Firefox, Chrome, Opera, Safari
			return new XMLHttpRequest();
		}
		
		if (window.ActiveXObject)
		{
			// code for IE6, IE5
			return new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		return null;
	}


	function getEl(id){
		return document.getElementById(id);
	}

	function addBemGrupo(id){
		var sel = getEl('sel_'+id);
		var row = getEl('veiculo_'+id);
		var sucesso = getEl('img_sucesso_'+id);
		var carregando = getEl('img_carregando_'+id);
		var table = row.parentElement.parentElement.parentElement.id;
		var aTable = table.split('_');
		var idGrupo = aTable[1];
		
		var idBem = sel.options[sel.selectedIndex].value;
		var nome = sel.options[sel.selectedIndex].text;
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		carregando.style.display = 'inline';
		
		var url="menu_grupos.php";
			url=url+"?acao=grupobem_adicionar";
			url=url+"&cliente="+cliente;
			url=url+"&bem="+idBem;
			url=url+"&grupo="+idGrupo;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												carregando.style.display = 'none';
												
												if(xmlhttpPainel.responseText.indexOf('OK') > -1){
													/*
													sucesso.style.display = 'inline';
													setTimeout("getEl('img_sucesso_"+id+"').style.display='none'", 2000);
													*/
													var novoId = xmlhttpPainel.responseText.replace('OK ', '');
													uid = guid();
													sel = nome+"&nbsp;<input type='hidden' value='"+novoId+"' id='hidden_"+uid+"'><img src='imagens/lixeira.png' style='cursor:pointer' onclick='javascript:removeBemGrupo(this.id);' id='"+uid+"'/>";
													sel = sel+'&nbsp;<img id="img_carregando_'+uid+'" src="imagens/executando.gif" style="display:none" />&nbsp;<img id="img_sucesso_'+uid+'" src="imagens/sucesso.png" style="display:none"/>';
													row.innerHTML = sel;
												} else {
													alert(xmlhttpPainel.responseText);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
		
	}
	
	function addBemGrupo2(obj){
		
		var id = obj.id;
		var aId = id.split('_');
		var idBem = aId[2];
		var idGrupo = aId[1];
		var sucesso = getEl('img_sucesso_'+aId[1]+'_'+aId[2])
		var carregando = getEl('img_carregando_'+aId[1]+'_'+aId[2])
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		carregando.style.display = 'inline';
		if(obj.checked){
		var url="menu_grupos.php";
			url=url+"?acao=grupobem_adicionar";
			url=url+"&cliente="+cliente;
			url=url+"&bem="+idBem;
			url=url+"&grupo="+idGrupo;
		} else {
			var url="menu_grupos.php";
			url=url+"?acao=grupobem_remover2";
			url=url+"&cliente="+cliente;
			url=url+"&bem="+idBem;
			url=url+"&grupo="+idGrupo;
		}
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												carregando.style.display = 'none';
												
												if(xmlhttpPainel.responseText.indexOf('OK') > -1){
													sucesso.style.display = 'inline';
													setTimeout("getEl('img_sucesso_"+aId[1]+'_'+aId[2]+"').style.display='none'", 2000);
													/*
													var novoId = xmlhttpPainel.responseText.replace('OK ', '');
													uid = guid();
													sel = nome+"&nbsp;<input type='hidden' value='"+novoId+"' id='hidden_"+uid+"'><img src='imagens/lixeira.png' style='cursor:pointer' onclick='javascript:removeBemGrupo(this.id);' id='"+uid+"'/>";
													sel = sel+'&nbsp;<img id="img_carregando_'+uid+'" src="imagens/executando.gif" style="display:none" />&nbsp;<img id="img_sucesso_'+uid+'" src="imagens/sucesso.png" style="display:none"/>';
													row.innerHTML = sel;
													*/
												} else {
													alert(xmlhttpPainel.responseText);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
		
	}
	
	function removeBemGrupo(id){
		var hidden = getEl('hidden_'+id);
		var row = getEl('linha_'+id);
		var table = row.parentElement.parentElement;
		var rowIndex = row.rowIndex;

		var carregando = getEl('img_carregando_'+id);
		
		var idBem = hidden.value;
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		carregando.style.display = 'inline';
		
		var url="menu_grupos.php";
			url=url+"?acao=grupobem_remover";
			url=url+"&grupo_bem="+idBem;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												carregando.style.display = 'none';
												
												if(xmlhttpPainel.responseText == "OK"){
													table.deleteRow(rowIndex);
												} else {
													alert(xmlhttpPainel.responseText);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}

	function mostraCadastroGrupo(){
		var divCadastro = document.getElementById('cadastro_grupo');
		if(divCadastro.style.display == 'none'){
			divCadastro.style.display = 'inline';
		} else {
			divCadastro.style.display = 'none';
		}
	}
	
	function verVeiculos(id){
		var divCadastro = document.getElementById('bem_'+id);
		if(divCadastro.style.display == 'none'){
			divCadastro.style.display = 'inline';
		} else {
			divCadastro.style.display = 'none';
		}
	}
	
	function novoVeiculo(id){
		tbl = document.getElementById("veiculos_"+id);
		
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		var url="menu_grupos.php";
			url=url+"?acao=veiculos";
			url=url+"&cliente="+cliente;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												var novaLinha = tbl.insertRow(-1);
												var novaCelula;
												bens = eval(xmlhttpPainel.responseText);
												novaCelula = novaLinha.insertCell(0);
												uid = guid();
												novaCelula.id = 'veiculo_'+uid;
												var sel =  "<select id='sel_"+uid+"'>";
												for(var i=0; i<bens.length;i++){
													sel = sel+"<option value='"+bens[i][3]+"'>"+bens[i][1]+"</option>";
												}
												sel = sel+"</select>&nbsp;<img id='"+uid+"' src='imagens/salvar.png' style='cursor:pointer' onclick='javascript:addBemGrupo(this.id);'/>";
												sel = sel+'&nbsp;<img id="img_carregando_'+uid+'" src="imagens/executando.gif" style="display:none" />&nbsp;<img id="img_sucesso_'+uid+'" src="imagens/sucesso.png" style="display:none"/>';
												novaCelula.innerHTML = sel;
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
		
	function cadastrarGrupo(){
		var txtNome = getEl('nome_grupo').value;
		var txtSenha = getEl('senha_grupo').value;
		var hdCliente = getEl('cliente_grupo').value;
		var hdGrupo = getEl('id_grupo').value;
		var idTxtNome = getEl('id_txt_nome').value;
		var hdAcao = getEl('acao_grupo').value;
		var carregando = getEl('img_carregando');
		var sucesso = getEl('img_sucesso');
		var tbl = getEl('tabela_grupos');

		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		if (txtNome != '' && txtSenha != '' && hdCliente != '') {
			
			carregando.style.display = 'inline';
			
			var url="menu_grupos.php";
			url=url+"?cliente="+hdCliente;
			url=url+"&nome="+txtNome;
			url=url+"&senha="+txtSenha;
			url=url+"&acao="+hdAcao;
			url=url+"&grupo="+hdGrupo;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													carregando.style.display = 'none';
													
													if (xmlhttpPainel.responseText.indexOf('OK') > -1) {
														var aGrupo = xmlhttpPainel.responseText.split('_');
														var novoIdGrupo = aGrupo[1];
														getEl('nome_grupo').value = '';
														getEl('senha_grupo').value = '';
														getEl('acao_grupo').value = 'grupo_adicionar';
														
														if(idTxtNome != ''){
															aId = idTxtNome.split('_');
															txtNovoNome = getEl('text_'+aId[1]);
															txtNovoNome.value = txtNome;
														}
														
														sucesso.style.display = 'inline';
														setTimeout("getEl('img_sucesso').style.display='none'", 2000);
														
														uid = guid();
														
														var novaLinha = tbl.insertRow(-1);
														novaLinha.id = 'linha_'+uid;
														var novaCelula;
														novaCelula = novaLinha.insertCell(0);
														var sel =  "<input type='text' value='"+txtNome+"' id='text_"+uid+"'/>"
														    sel += "<input type='hidden' value='"+novoIdGrupo+"' id='hidden_"+uid+"'/>&nbsp;"
														    sel += "<img onclick='javascript:alterarGrupo(this.id)' src='imagens/salvar.png' style='cursor:pointer' id='"+uid+"' />&nbsp;"
															sel += "<img onclick='javascript:alterarGrupoTodo(this.id)' src='imagens/edit.gif' style='cursor:pointer' id='edit_"+uid+"' />&nbsp;"
															sel += "<img src='imagens/lixeira.png' style='cursor:pointer' onclick='javascript:removeGrupo(this.id);' id='excluir_"+uid+"'/>&nbsp;"
															sel += "<img id='img_carregando_"+uid+"' src='imagens/executando.gif' style='display:none' />&nbsp;"
															sel += "<img id='img_sucesso_"+uid+"' src='imagens/sucesso.png' style='display:none'/>"
														novaCelula.innerHTML = sel;
														novaCelula = novaLinha.insertCell(1);
														sel = "<a href='javascript:verVeiculos("+novoIdGrupo+")'>Veículos</a>";
														novaCelula.innerHTML = sel;
														
														xmlhttpPainel1=GetXmlHttpObject();
														xmlhttpPainel1.onreadystatechange = function(){
															if (xmlhttpPainel1.readyState == 4){
																novaLinha = tbl.insertRow(-1);
																novaLinha.id = 'bem_'+novoIdGrupo;
																
																novaCelula = novaLinha.insertCell(0);
																novaCelula.colSpan = 2;
																var aVeic = eval(xmlhttpPainel1.responseText);
																sel = "";
																for(var i=0; i<aVeic.length; i++){
																	sel += "<input type='checkbox' name='veiculo' id='chk_"+novoIdGrupo+'_'+aVeic[i][3]+"' value='"+novoIdGrupo+'_'+aVeic[i][3]+"' onchange='javascript:addBemGrupo2(this);'/>&nbsp;";
																	sel += "<img id='img_carregando_"+novoIdGrupo+'_'+aVeic[i][3]+"' src='imagens/executando.gif' style='display:none' />&nbsp;";
																	sel += "<img id='img_sucesso_"+novoIdGrupo+'_'+aVeic[i][3]+"' src='imagens/sucesso.png' style='display:none'/>";
																	sel += aVeic[i][1]+"<br/>"
																}
																novaCelula.innerHTML = sel;
															}
														}
														xmlhttpPainel1.open("GET", "menu_grupos.php?acao=veiculos&cliente="+cliente, true);
														xmlhttpPainel1.send(null);
													} else {
														alert(xmlhttpPainel.responseText);
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
			
		} else {
			alert('Preencha todos os campos!');
		}
	}
	
	function alterarGrupo(id){
		var txtNome = getEl('text_'+id).value;
		var hidden = getEl('hidden_'+id).value;
		var carregando = getEl('img_carregando_'+id);
		var sucesso = getEl('img_sucesso_'+id);

		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		if (txtNome != '' && hidden != '') {
			
			carregando.style.display = 'inline';
			
			var url="menu_grupos.php";
			url=url+"?grupo="+hidden;
			url=url+"&nome="+txtNome;
			url=url+"&acao=grupo_alterar";
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													carregando.style.display = 'none';
													
													if (xmlhttpPainel.responseText == 'OK') {
														sucesso.style.display = 'inline';
														setTimeout("getEl('img_sucesso_"+id+"').style.display='none'", 2000);
													} else {
														alert(xmlhttpPainel.responseText);
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
			
		} else {
			alert('Preencha todos os campos!');
		}
	}
	
	function removeGrupo(id){
		var aId = id.split('_');
		var hidden = getEl('hidden_'+aId[1]);
		var row = getEl('linha_'+aId[1]);
		var table = row.parentElement.parentElement;
		var rowIndex = row.rowIndex;

		var carregando = getEl('img_carregando_'+aId[1]);
		
		var idBem = hidden.value;
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		carregando.style.display = 'inline';
		
		var url="menu_grupos.php";
			url=url+"?acao=grupo_remover";
			url=url+"&grupo="+idBem;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												carregando.style.display = 'none';
												
												if(xmlhttpPainel.responseText == "OK"){
													table.deleteRow(rowIndex);
												} else {
													alert(xmlhttpPainel.responseText);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
	
	function alterarGrupoTodo(id){
		var aId = id.split('_');
		var idGrupo = getEl('hidden_'+aId[1]);
		var nome = getEl('text_'+aId[1]);
		var txtNome = getEl('nome_grupo');
		var hdAcao = getEl('acao_grupo');
		var hdIdGrupo = getEl('id_grupo');
		var idTxtNome = getEl('id_txt_nome');
		
		txtNome.value = nome.value;
		hdAcao.value = 'grupo_alterar';
		hdIdGrupo.value = idGrupo.value;
		idTxtNome.value = id;
		
		mostraCadastroGrupo();
	}
</script>
</head>
<body style="margin-left:0px; margin-top:0px; border-top-style: solid; border-left-style: solid; border-top-width: 1px; border-left-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-top-color: #CCCCCC; border-left-color: #CCCCCC; border-right-color: #CCCCCC; border-bottom-color: #CCCCCC; background-color: #F8F8F8;">
<h1>Manutenção dos Grupo</h1>

<a href="javascript:mostraCadastroGrupo()">Novo Grupo</a>
<div id="cadastro_grupo" style="display:none">
	<input type="hidden" name="cliente" value="<?=$cliente?>" id="cliente_grupo"/>
    <input type="hidden" name="acao" value="grupo_adicionar" id="acao_grupo"/>
    <input type="hidden" name="grupo" value="" id="id_grupo"/>
    <input type="hidden" name="id_txt_nome" value="" id="id_txt_nome"/>
	<table>
    	<tr><td>Nome:</td><td><input type="text" name="nome" id="nome_grupo"/></td></tr>
        <tr><td>Senha:</td><td><input type="text" name="senha" id="senha_grupo"/>&nbsp;&nbsp;<img onclick="javascript:cadastrarGrupo()" src="imagens/salvar.png" style="cursor:pointer" />&nbsp;<img id="img_carregando" src="imagens/executando.gif" style="display:none" />&nbsp;<img id="img_sucesso" src="imagens/sucesso.png" style="display:none"/></td></tr>
    </table>
</div>
<br />
<br />
<table id="tabela_grupos" border="1">
	<tr>
    	<th width="100" align="left" colspan="2">Nome</th>
    </tr>
    <?php
		$resGrupos = null;
		if($grupo != ''){
			$resGrupos = mysql_query("select * from grupo where cliente = $cliente and grupo = $grupo", $con);
		} else {
			$resGrupos = mysql_query("select * from grupo where cliente = $cliente", $con);
		}
		while($data = mysql_fetch_assoc($resGrupos)){
			$uid = uniqid('');
			echo "<tr id='linha_".$uid."'>";
			echo "<td><input type='text' value='".$data[nome]."' id='text_".$uid."'/><input type='hidden' value='".$data[id]."' id='hidden_".$uid."'/>&nbsp;<img onclick='javascript:alterarGrupo(this.id)' src='imagens/salvar.png' style='cursor:pointer' id='".$uid."' />&nbsp;<img onclick='javascript:alterarGrupoTodo(this.id)' src='imagens/edit.gif' style='cursor:pointer' id='edit_".$uid."' />&nbsp;<img src='imagens/lixeira.png' style='cursor:pointer' onclick='javascript:removeGrupo(this.id);' id='excluir_".$uid."'/>&nbsp;<img id='img_carregando_".$uid."' src='imagens/executando.gif' style='display:none' />&nbsp;<img id='img_sucesso_".$uid."' src='imagens/sucesso.png' style='display:none'/></td><td><a href='javascript:verVeiculos(".$data[id].")'>Veículos</a></td>";
			echo "</tr>";
			echo "<tr id='bem_".$data[id]."' style='display:none'>";
			$resBem = mysql_query("select * from grupo_bem where grupo = ".$data[id], $con);
			echo "<td colspan='2'>";
			/*
			if(mysql_num_rows($resBem) > 0){
				echo "<a href='javascript:novoVeiculo(".$data[id].")'>Novo</a><br/>";
				echo "<table id='veiculos_".$data[id]."'>";
				echo "<tr><th>Placa/Nome</th></tr>";
				while($veic = mysql_fetch_assoc($resBem)){
					$uid = uniqid('');
					echo "<tr id='linha_".$uid."'><td>".$veic[descricao]."&nbsp;<input type='hidden' value='".$veic[id]."' id='hidden_".$uid."'/><img src='imagens/lixeira.png' style='cursor:pointer' onclick='javascript:removeBemGrupo(this.id);' id='".$uid."'/>&nbsp;<img id='img_carregando_".$uid."' src='imagens/executando.gif' style='display:none' /></td></tr>";
				}
				echo "</table>";
			} else {
				echo "Não existe veiculos nesse grupo. <a href='javascript:novoVeiculo(".$data[id].")'>Novo</a><br/>";
				echo "<table id='veiculos_".$data[id]."'>";
				echo "<tr><th>Placa/Nome</th></tr>";
				echo "</table>";
			}
			*/
			$aGrupoBem = array();
			while($bemveic = mysql_fetch_assoc($resBem)){
				$aGrupoBem[$bemveic[grupo].'_'.$bemveic[bem]] = $bemveic[grupo].'_'.$bemveic[bem];
			}
			$resVeiculos = null;
			if($grupo != ''){
				$resVeiculos = mysql_query("select b.name, b.id from bem b join grupo_bem gb on gb.bem = b.id where b.cliente = $cliente and gb.grupo = $grupo", $con);
			} else {
				$resVeiculos = mysql_query("select name, id from bem where cliente = $cliente", $con);
			}
			
			while($veiculo = mysql_fetch_assoc($resVeiculos)){
				$noGrupo = array_key_exists($data[id].'_'.$veiculo[id], $aGrupoBem);
				$cheked = $noGrupo ? 'checked=checked' : '';
				echo "<input type='checkbox' name='veiculo' id='chk_".$data[id].'_'.$veiculo[id]."' value='".$data[id].'_'.$veiculo[id]."' ".$cheked." onchange='javascript:addBemGrupo2(this);'/>&nbsp;<img id='img_carregando_".$data[id].'_'.$veiculo[id]."' src='imagens/executando.gif' style='display:none' />&nbsp;<img id='img_sucesso_".$data[id].'_'.$veiculo[id]."' src='imagens/sucesso.png' style='display:none'/>";
				echo $veiculo[name].'<br/>';
			}
			echo "</td>";
			echo "</tr>";
		}	
	?>
</table>

</body>
</html>