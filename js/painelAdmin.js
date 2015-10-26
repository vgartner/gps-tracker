function getEl(id){
	return document.getElementById(id);
}

var totalVeiculos = 0;
var totalPesquisaIdentificacao = 0;

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

function stAba(menu,conteudo)
{
	this.menu = menu;
	this.conteudo = conteudo;
}

var arAbas = new Array();
arAbas[0] = new stAba('td_cadastro','div_cadastro');
arAbas[1] = new stAba('td_consulta','div_consulta');
arAbas[2] = new stAba('td_manutencao','div_manutencao');
arAbas[3] = new stAba('td_status','div_status');
arAbas[4] = new stAba('td_hist_com','div_hist_com');
arAbas[5] = new stAba('td_termos','div_termos');

var subArAbas = new Array();
subArAbas[0] = new stAba('td_sub_cadastro','div_sub_cadastro');

function AlternarAbas(menu,conteudo)
{
	for (i=0;i<arAbas.length;i++)
	{
		m = document.getElementById(arAbas[i].menu);
		if(m)
		m.className = 'menu';
		c = document.getElementById(arAbas[i].conteudo)
		if(c)
		c.style.display = 'none';
	}
	m = document.getElementById(menu)
	if(m)
	m.className = 'menu-sel';
	
	c = document.getElementById(conteudo)
	if(c)
	c.style.display = '';
	//if (conteudo == 'div_cadastro')
		//c.style.height = document.body.parentNode.clientHeight - 145 + "px";
}

function AlternarSubAbas(menu,conteudo)
{
	try
	  {
		for (i=0;i<subArAbas.length;i++)
		{
			m = document.getElementById(subArAbas[i].menu);
			m.className = 'menu';
			c = document.getElementById(subArAbas[i].conteudo)
			c.style.display = 'none';
		}
		m = document.getElementById(menu)
		m.className = 'menu-sel';
		
		c = document.getElementById(conteudo)
		c.style.display = '';
	} catch(err) {
		//Abafo
	}
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

function adicionarNovaLinhaVeiculos() {

	tbl = document.getElementById("tabelaVeiculos");
	
	//if (document.getElementById("alertNenhumVeiculo") != null) {
	//	document.getElementById("alertNenhumVeiculo").style.display='none';
		
		//if (totalVeiculos == 0) {
			var novaLinhaTopo = tbl.insertRow(-1);
			var novaCelulaTopo;
			
			novaCelulaTopo = novaLinhaTopo.insertCell(0);
			novaCelulaTopo.innerHTML = "<td>N&uacute;mero imei</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(1);
			novaCelulaTopo.innerHTML = "<td>Ve&iacute;culo</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(2);
			novaCelulaTopo.innerHTML = "<td>N&uacute;mero do Chip</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(3);
			novaCelulaTopo.innerHTML = "<td>Tipo do Bem</td>";			
			novaCelulaTopo = novaLinhaTopo.insertCell(4);
			novaCelulaTopo.innerHTML = "<td>Envia SMS?</td>";			
			novaCelulaTopo = novaLinhaTopo.insertCell(5);
			novaCelulaTopo.innerHTML = "<td>Ativo?</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(6);
			novaCelulaTopo.innerHTML = "<td><img src='../imagens/salvar_todos.gif' title='Salvar todos' alt='Salvar todos' onclick='salvarTodos();' /></td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(7);
			novaCelulaTopo.innerHTML = "<td>Excluir</td>";
		//}
	//}
	
	totalVeiculos++;

	var novaLinha = tbl.insertRow(-1);
	var novaCelula;

	novaCelula = novaLinha.insertCell(0);
	novaCelula.innerHTML =  "<input maxlength='15' size='17' id='listaImei" + totalVeiculos + "' name='listaImei" + totalVeiculos + "' type='text' value='' class='campoNovoVeiculo' />" +
							"<input maxlength='15' size='17' id='listaImeiHidden" + totalVeiculos + "' name='listaImeiHidden" + totalVeiculos + "' type='hidden' value='' />";
							"<input maxlength='15' id='listaIdBemHidden" + totalVeiculos + "' name='listaIdBemHidden" + totalVeiculos + "' type='hidden' value='0' />";

	novaCelula = novaLinha.insertCell(1);
	novaCelula.innerHTML = "<input id='listaNome"+ totalVeiculos +"' name='listaNome"+ totalVeiculos +"' type='text' value='' class='campoNovoVeiculo' />";

	novaCelula = novaLinha.insertCell(2);
	novaCelula.innerHTML = "<input id='listaIdent"+ totalVeiculos +"' name='listaIdent"+ totalVeiculos +"' type='text' value='' class='campoNovoVeiculo' />";
	
	novaCelula = novaLinha.insertCell(3);
	
	novaCelula.innerHTML = "<input id='listaCor"+ totalVeiculos +"' name='listaCor"+ totalVeiculos +"' type='hidden' value='' />";	
	
	novaCelula.innerHTML += "<select id='tipoBem"+ totalVeiculos +"' name='tipoBem"+ totalVeiculos +"' class='campoNovoVeiculo'> <option value='CARRO'>Carro</option> <option value='MOTO'>Moto</option> <option value='JET'>Veic. Aqu&aacute;tico</option> <option value='CAMINHAO'>Caminh&atilde;o</option> <option value='VAN'>Van</option> <option value='TRATOR'>Trator</option> <option value='ONIBUS'>Onibus</option> <option value='PICKUP'>Pickup</option> </select>";
	
	novaCelula = novaLinha.insertCell(4);
	novaCelula.innerHTML = "<select id='listaEnviaSms"+ totalVeiculos +"' name='listaEnviaSms"+ totalVeiculos +"' class='campoNovoVeiculo'> <option selected value='S'>Sim</option> <option value='N'>N&atilde;o</option> </select>";
	
	novaCelula = novaLinha.insertCell(5);
	novaCelula.innerHTML = "<select id='listaAtivo"+ totalVeiculos +"' name='listaAtivo"+ totalVeiculos +"' class='campoNovoVeiculo'> <option selected value='S'>Sim</option> <option value='N'>N&atilde;o</option> </select>";
	
	//novaCelula = novaLinha.insertCell(6);
	//novaCelula.innerHTML = "";
	
	//novaCelula = novaLinha.insertCell(7);
	//novaCelula.innerHTML = "";	
	
	var novaLinha = tbl.insertRow(-1);
	var novaCelula;
	
	novaCelula = novaLinha.insertCell(0);
	novaCelula.colSpan = 8;
	novaCelula.innerHTML = "<table style='border-bottom:1px black solid' width='100%'>"
	+ "<tr>"
	+ "<td>Marca</td>"
	+ "<td>Cor</td>"
	+ "<td>Ano</td>"
	+ "<td>Operadora</td>"
	+ "<td>Modelo Rastreador</td>"
	+ "<td><img src='../imagens/salvar_todos.gif' title='Salvar todos' alt='Salvar todos' onclick='salvarTodos();' /></td>"
	+ "<td>Backup</td></tr></tr>"
	+ "<tr>"
	+ "<td><input type='text' name='marca' id='marca"+totalVeiculos+"' size='20' /></td>"
	+ "<td><input type='text' name='cor' id='cor"+totalVeiculos+"' size='20' /></td>"
	+ "<td><input type='text' name='ano' id='ano"+totalVeiculos+"' size='10' /></td>"
	+ "<td><input type='text' name='operadora' id='operadora"+totalVeiculos+"' size='20' /></td>"
	+ "<td><select id='modelo_rastreador"+ totalVeiculos +"' name='modelo_rastreador"+ totalVeiculos +"' class='campoNovoVeiculo'> <option value='tk103'>TK 103</option><option value='tk104'>TK 104</option> <option value='gt06'>GT06</option><option value='gt06n'>GT06N</option> <option value='h02'>H02</option> <option value='h08'>H08</option></select></td>"
	+ "<td><div style='width:40px'><img id='imgGravar"+ totalVeiculos +"' src='../imagens/salvar.png' title='Salvar veiculo' alt='Salvar veiculo' onclick='adicionarVeiculoAdmin("+ totalVeiculos +");' /><img id='imgExecutando"+ totalVeiculos +"' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' /><img id='imgSucesso"+ totalVeiculos +"' style='display:none' src='../imagens/sucesso.png' title='Alteração salva' alt='Alteração salva' /></div></td>"
	+ "<td><div style='width:40px'><a href='javascript:void(0);'><img border=0 id='imgExcluirBem"+ totalVeiculos +"' src='../imagens/lixeira.png' title='Excluir item' alt='Excluir item' onclick='excluirBemUsuario("+ totalVeiculos +");' /></a> <img border=0 id='imgExcluindo" + totalVeiculos + "' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' /> </div></td>"
	+ "</tr>";
	//novaCelula.style.paddingLeft = '15px';
}

function adicionarVeiculoAdmin(id) 
{
	var codigoCliente = document.getElementById('codigoCliente').value;
	var imei = document.getElementById('listaImei' + id).value;
	var nome = document.getElementById('listaNome' + id).value;
	var ident = document.getElementById('listaIdent' + id).value;
	var cor = document.getElementById('listaCor' + id).value;
	var tipoCombo = document.getElementById('tipoBem' + id);
	var marca = document.getElementById('marca' + id).value;
	var ano = document.getElementById('ano' + id).value;
	var cor = document.getElementById('cor' + id).value;
	var operadora = document.getElementById('operadora' + id).value;
	var tipoBem = tipoCombo.options[tipoCombo.selectedIndex].value;
	var ativoCombo = document.getElementById('listaAtivo' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;
	var enviaSmsCombo = document.getElementById('listaEnviaSms' + id);
	var enviaSms = enviaSmsCombo.options[enviaSmsCombo.selectedIndex].value;
	var modeloCombo = document.getElementById('modelo_rastreador' + id);
	var modelo_rastreador = modeloCombo.options[modeloCombo.selectedIndex].value;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && imei != '' && nome != '') {
		if (imei.length >= 5) {
			//Exibe icone de executando o link
			document.getElementById('imgExecutando'+id).style.display='inline';
		
			var url="adicionar_novo_veiculo.php";
			url=url+"?codCliente="+codigoCliente;
			url=url+"&imei="+imei;
			url=url+"&nome="+nome;
			url=url+"&ident="+ident;
			url=url+"&cor="+cor;
			url=url+"&marca="+marca;
			url=url+"&operadora="+operadora;
			url=url+"&ano="+ano;
			url=url+"&tipo="+tipoBem;
			url=url+"&ativo="+ativo;
			url=url+"&enviaSms="+enviaSms;
			url=url+"&modelo_rastreador="+modelo_rastreador;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('imgExecutando' + id).style.display='none';
														document.getElementById('imgSucesso' + id).style.display='inline';
														setTimeout("document.getElementById('imgSucesso"+id+"').style.display='none'", 5000);
														//Guardando o valor do imei, caso precise alterá-lo
														document.getElementById('listaImeiHidden' + id).value = imei;
														//Alterando o botão para salvar, ao invés de incluir
														document.getElementById('imgGravar' + id).onclick=new Function("alterarVeiculoAdmin(" + id + ")"); 
													} else {
														if (xmlhttpPainel.responseText == 'IMEI duplicado') {
															document.getElementById('imgExecutando' + id).style.display='none';
															alert('ERRO: Este imei j\u00e1 existe!');
														}
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		} else {
			alert('N\u00famero imei est\u00e1 incompleto\u0021');
		}
	}
}


function alterarVeiculoAdmin(id) 
{	
	var codigoCliente = document.getElementById('codigoCliente').value;
	var imei = document.getElementById('listaImei' + id).value;
	var imeiAntigo = document.getElementById('listaImeiHidden' + id).value;
	var nome = document.getElementById('listaNome' + id).value;
	var ident = document.getElementById('listaIdent' + id).value;
	var cor = document.getElementById('listaCor' + id).value;
	var tipoCombo = document.getElementById('tipoBem' + id);
	var tipoBem = tipoCombo.options[tipoCombo.selectedIndex].value;
	var ativoCombo = document.getElementById('listaAtivo' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;
	var enviaSmsCombo = document.getElementById('listaEnviaSms' + id);
	var enviaSms = enviaSmsCombo.options[enviaSmsCombo.selectedIndex].value;
	var marca = document.getElementById('marca' + id).value;
	var corNova = document.getElementById('cor' + id).value;
	var ano = document.getElementById('ano' + id).value;
	var operadora = document.getElementById('operadora' + id).value;
	var modeloCombo = document.getElementById('modelo_rastreador' + id);
	var modelo_rastreador = modeloCombo.options[modeloCombo.selectedIndex].value;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && imei != '' && nome != '') {
		if (imei.length >= 5) {
			//Exibe icone de executando o link
			document.getElementById('imgExecutando'+id).style.display='inline';
			
			var url="alterar_veiculo.php";
			url=url+"?codCliente="+codigoCliente;
			url=url+"&imei="+imei;
			url=url+"&imeiAntigo="+imeiAntigo;
			url=url+"&nome="+nome;
			url=url+"&ident="+ident;
			url=url+"&cor="+cor;
			url=url+"&tipo="+tipoBem;
			url=url+"&ativo="+ativo;
			url=url+"&enviaSms="+enviaSms;
			url=url+"&marca="+marca;
			url=url+"&cor="+corNova;
			url=url+"&operadora="+operadora;
			url=url+"&ano="+ano;
			url=url+"&modelo_rastreador="+modelo_rastreador;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('listaImeiHidden' + id).value = imei;
														document.getElementById('imgExecutando' + id).style.display='none';
														document.getElementById('imgSucesso' + id).style.display='inline';
														setTimeout("document.getElementById('imgSucesso"+id+"').style.display='none'", 5000);
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		} else {
			alert('N\u00famero imei est\u00e1 incompleto\u0021');
		}
	}
}

function salvarUsuarioAdmin(id)
{
	var codigoCliente = document.getElementById('listaCodigoCliente' + id).value;
	var nomeCliente = document.getElementById('listaNomeCliente' + id).value;
	var ativoCombo = document.getElementById('listaAtivoCliente' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && nomeCliente != '') {
		//Exibe icone de executando o link
		document.getElementById('imgExecutandoCliente' + id).style.display='inline';	
		
		var url="alterar_usuario.php";
		url=url+"?codCliente="+codigoCliente;
		url=url+"&nomeCliente="+nomeCliente;
		url=url+"&ativo="+ativo;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												if (xmlhttpPainel.responseText == 'OK') {
													document.getElementById('imgExecutandoCliente' + id).style.display='none';
													document.getElementById('imgSucessoCliente' + id).style.display='inline';
													setTimeout("document.getElementById('imgSucessoCliente"+id+"').style.display='none'", 5000);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
}

function registrarPagamentoMesAdmin(mes, idCliente, img)
{
	var imgId = img.src;
	
	if (imgId.indexOf("registra_pgto.gif") != -1) 
	{
		//registra pagamento, chama função
		registrarPagamento(mes, idCliente, 'S');
		document.getElementById("imgRegistraPagto"+ mes + idCliente + "").src = '../imagens/pagou.gif';
	}
	if (imgId.indexOf("pagou.gif") != -1) 
	{
		//registra nao pagamento, chama função de nao pagamento
		registrarPagamento(mes, idCliente, 'N');
		document.getElementById("imgRegistraPagto"+ mes + idCliente + "").src = '../imagens/sem_pagamento.gif';
	}
	if (imgId.indexOf("sem_pagamento.gif") != -1) 
	{
		//registra retirada de pagamento, chama função de retirada
		registrarPagamento(mes, idCliente, 'F');
		document.getElementById("imgRegistraPagto"+ mes + idCliente + "").src = '../imagens/registra_pgto.gif';
	}
}

function registrarPagamento(mes, idCliente, pgto)
{
	var codigoCliente = idCliente;
	var mesPagamento = mes;
	var pagamento = pgto;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && mesPagamento != '') {
		var url="registrar_pagamento_usuario.php";
		url=url+"?codigoCliente="+codigoCliente;
		url=url+"&mesPagamento="+mesPagamento;
		url=url+"&pagamento="+pagamento;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												if (xmlhttpPainel.responseText == 'OK') {
													//document.getElementById('imgSucessoCliente' + id).style.display='inline';
													//setTimeout("document.getElementById('imgSucessoCliente"+id+"').style.display='none'", 5000);
													//Pagamento registrado!
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
}

function salvarUsuarioAdminPgto(id)
{
	var codigoCliente = document.getElementById('listaCodigoClientePgto' + id).value;
	var nomeCliente = document.getElementById('listaNomeClientePgto' + id).value;
	var ativoCombo = document.getElementById('listaAtivoClientePgto' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;
	var obsCliente = document.getElementById('listaObsClientePgto' + id).value;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && nomeCliente != '') {
		//Exibe icone de executando o link
		document.getElementById('imgExecutandoClientePgto'+id).style.display='inline';	
		
		var url="alterar_usuario.php";
		url=url+"?codCliente="+codigoCliente;
		url=url+"&nomeCliente="+nomeCliente;
		url=url+"&ativo="+ativo;
		url=url+"&obsCliente="+obsCliente;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												if (xmlhttpPainel.responseText == 'OK') {
													document.getElementById('imgExecutandoClientePgto' + id).style.display='none';
													document.getElementById('imgSucessoClientePgto' + id).style.display='inline';
													setTimeout("document.getElementById('imgSucessoClientePgto"+id+"').style.display='none'", 5000);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
}

function abrirFrotaCliente(id)
{
	window.location='admin.php?acao=obterUsuario&codigo=' + id;
}

function abrirFrotaClienteSub(id)
{
	window.location='admin_cliente.php?acao=obterUsuario&codigo=' + id;
}

function pesquisaIdentificacao(key)
{
	var achou = false;
	
	if (key == "") 
	{
		for (i=1; i <= totalPesquisaIdentificacao; i++)
		{
			document.getElementById('stKey' + i).style.display = '';
		}
	}

	for (i=1; i <= totalPesquisaIdentificacao; i++)
	{
		if (document.getElementById('key' + i).value.toLowerCase().indexOf(key.toLowerCase()) != -1) {
			//Existe
			document.getElementById('stKey' + i).style.display = '';
			achou = true;
		}
		else
		{
			//Não existe
			document.getElementById('stKey' + i).style.display = 'none';
		}
	}
	
	if (!achou) 
	{
		document.getElementById('resultadoPesquisaVazio').style.display='block';
	}
	else 
	{
		document.getElementById('resultadoPesquisaVazio').style.display='none';
	}

}

function bloquearExibirFundoModal(status)
{

	var testObj = document.getElementById('all_content');
	
	if (status == 1) {
		//bloqueado
		testObj.onClick='return false';
		testObj.disabled=true;
		testObj.style.opacity = 0.3;
		testObj.style.filter = 'alpha(opacity=30)';
	} else {
		//desbloquado
		testObj.onClick='';
		testObj.disabled=false;
		testObj.style.opacity = 1.0;
		testObj.style.filter = 'alpha(opacity=100)';
	}

}

var popup2;

function exibirPopUpBackup(id)
{
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth(); //months are zero based
	var curr_year = d.getFullYear();
	curr_month++;
	
	curr_date = curr_date < 10 ? '0'+curr_date : curr_date;
	curr_month = curr_month < 10 ? '0'+curr_month : curr_month;
	
	var hoje = curr_date + "/" + curr_month + "/" + curr_year;
	
	var imeiPopup = document.getElementById('listaImei' + id).value;

	bloquearExibirFundoModal(1);

	popup2 = popup2 == null ? new Popup() : popup2;
	
	popup2.autoHide = false;
	popup2.content = "<span style='color:black'>Informe os dados de backup</span><br><br>" + 
					 "   IMEI: " + imeiPopup + "<br>" +
					 "Dt. Inicio: <input maxlength='10' size='12' value='"+ hoje +"' name='dtIniBkp' id='dtIniBkp' type='text' class='campoNovoVeiculo' onkeyup='formataData(this,event)' /> " +
							  "<input maxlength='4' size='6' value='00:00' name='dtHrIniBkp' id='dtHrIniBkp' type='text' class='campoNovoVeiculo' onclick='formataHora(this);' onblur='formataHora(this);' /> a <br>" +
					 "Dt. Final: <input maxlength='10' size='12' value='"+ hoje +"' name='dtFimBkp' id='dtFimBkp' type='text' class='campoNovoVeiculo' onkeyup='formataData(this,event)' /> " +
							  "<input maxlength='4' size='6' value='23:59' name='dtHrFimBkp' id='dtHrFimBkp' type='text' class='campoNovoVeiculo' onclick='formataHora(this);' onblur='formataHora(this);' /> <br>" +
							  "<br>" +
					 "Apagar historico: <input name='CheckboxHistorico' id='CheckboxHistorico' type='checkbox' /> <br>" + 
										"<span id='spamPopAlertaHist' style='color:red;font-size:10px' >Aten\u00e7\u00e3o\u002c o hist\u00f3rico n\u00e3o poder\u00e1 ser recuperado!</span>" + 
					
							  
							  
							  "<br><br><br>" +
					 "<div id='botoesPopHist'> <input name='btnSalvarHistorico' type='button' value='Gerar arquivo' class='btnAcao' onclick='gerarArquivoBackup("+ id +")' /> " +
					 "      <a href='#' onclick='"+popup2.ref+".hide(); bloquearExibirFundoModal(2); return false;'>Fechar</a>" +
					 "</div>" +
					 "<div id='botoesPopHistExecutando' style='display:none'> " +
					 "      Gerando arquivo, aguarde..." +
					 "</div>";
					 
	popup2.width=350;
	popup2.height=200;
	popup2.style = {'border':'1px solid #c0c0c0','backgroundColor':'#FFFFFF' , 'padding':'10px'};
	
	popup2.show();return false;
	
	document.getElementById('CheckboxHistorico').checked=0;
	document.getElementById('botoesPopHist').style.display='block';
	document.getElementById('botoesPopHistExecutando').style.display='none';
	
}

function gerarArquivoBackup(id)
{
	document.getElementById('botoesPopHist').style.display='none';
	document.getElementById('botoesPopHistExecutando').style.display='block';
	
	var imeiHist = document.getElementById('listaImei' + id).value;
	var dataInicio = document.getElementById('dtIniBkp').value;
	var dataInicioHr = document.getElementById('dtHrIniBkp').value;
	var dataFinal = document.getElementById('dtFimBkp').value;
	var dataFinalHr = document.getElementById('dtHrFimBkp').value;
	var chkBox = document.getElementById('CheckboxHistorico').checked;
	
	setTimeout('bloquearExibirFundoModal(2);popup2.hide()',4000);
	
	window.open('../administracao/gerar_arquivo_backup.php?imei=' + imeiHist + '&dtIniBkp=' + dataInicio + '&dtHrIniBkp=' + dataInicioHr + '&dtFimBkp=' + dataFinal + '&dtHrFimBkp=' + dataFinalHr + '&CheckboxHistorico=' + chkBox);
	
	//document.getElementById('botoesPopHistExecutando').style.display='none';
	
	return false;
}

//Exclui uma conta
function excluirUsuarioAdmin(id)
{
	if (confirm("Deseja realmente EXCLUIR esta conta e todos os bens cadastrados? Esta opera\u00e7\u00e3o n\u00e3o poder\u00e1 ser desfeita!"))
	{
		var codigoCliente = document.getElementById('listaCodigoCliente' + id).value;
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		if (codigoCliente != '') {
			//Exibe icone de executando o link
			document.getElementById('imgExcluindoCliente' + id).style.display='inline';	
			
			var url="excluir_usuario.php";
			url=url+"?codCliente="+codigoCliente;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('imgExcluindoCliente' + id).style.display='none';
														document.getElementById('linhaContaCliente' + id).style.display='none';
														document.getElementById('linhaContaPagtoCliente' + id).style.display='none';
														alert('Conta exclu\u00edda com sucesso.');
													} else {
														alert('ERRO: Falhar ao excluir a conta!');
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		}
	}
}

function excluirUsuarioAdmin_Novo(id)
{
	if (confirm("Deseja realmente EXCLUIR esta conta e todos os bens cadastrados? Esta opera\u00e7\u00e3o n\u00e3o poder\u00e1 ser desfeita!"))
	{
		var codigoCliente = document.getElementById('listaCodigoCliente' + id).value;
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		if (codigoCliente != '') {
			//Exibe icone de executando o link
			document.getElementById('imgExcluindoCliente' + id).style.display='inline';	
			
			var url="/administracao/excluir_usuario.php";
			url=url+"?codCliente="+codigoCliente;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('imgExcluindoCliente' + id).style.display='none';
														carregarConteudo('#ui-tabs-1', 'administracao/ajax_usuarios.php');
														alert('Conta exclu\u00edda com sucesso.');
													} else {
														alert('ERRO: Falhar ao excluir a conta!');
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		}
	}
}


//Exclui um item da usuario
function excluirBemUsuario(id)
{
	if (confirm("Deseja realmente EXCLUIR este item da conta?"))
	{
		document.getElementById('imgExcluindo'+id).style.display='inline';	
		
		var codigoCliente = document.getElementById('codigoCliente').value;
		var imei = document.getElementById('listaImei' + id).value;
		var idBem = document.getElementById('listaIdBemHidden' + id).value;
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		if (codigoCliente != '' && imei != '') {
	
			var url="excluir_bem_conta.php";
			url=url+"?codCliente="+codigoCliente;
			url=url+"&imei="+imei;
			url=url+"&idBem="+idBem;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('linhaBemCliente' + id).style.display='none';
														document.getElementById('linhaBemCliente2' + id).style.display='none';
														document.getElementById('imgExcluindo'+id).style.display='none';
														alert('Item exclu\u00eddo com sucesso.');
													} else {
														alert('ERRO: Falhar ao excluir o item!');
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		}
	}
}

function salvarUsuarioAdminPref(id){
	var codCliente = id;
	var celular = getEl('clienteCelular'+id).value;
	var telefone1 = getEl('clienteTelefone1'+id).value;
	var telefone2 = getEl('clienteTelefone2'+id).value;
	var enviaSms = getEl('clienteEnviarSms'+id).value;
	var enviarACada = getEl('clienteEnviarACada'+id).value;
	var cpf = getEl('clienteCpf'+id).value;
	var endereco = getEl('clienteEndereco'+id).value;
	var bairro = getEl('clienteBairro'+id).value;
	var cidade = getEl('clienteCidade'+id).value;
	var estado = getEl('clienteEstado'+id).value;
	var cep = getEl('clienteCep'+id).value;
	var listaTipoPlano = getEl('clienteTipoPlano'+id);
	var tipoPlano = listaTipoPlano.options[listaTipoPlano.selectedIndex].value;
	
	var carregando = getEl('imgExecutandoClientePref'+id);
	var sucesso = getEl('imgSucessoClientePref'+id);
	
	var url = "alterar_usuario.php?";
	url += "acao=altera_pref";
	url += "&codCliente="+codCliente;
	url += "&celular="+celular;
	url += "&telefone1="+telefone1;
	url += "&telefone2="+telefone2;
	url += "&enviasms="+enviaSms;
	url += "&enviaracada="+enviarACada;
	url += "&cpf="+cpf;
	url += "&endereco="+endereco;
	url += "&bairro="+bairro;
	url += "&cidade="+cidade;
	url += "&estado="+estado;
	url += "&cep="+cep;
	url += "&tipoplano="+tipoPlano;
	
	carregando.style.display = 'inline';
	
	xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
	
	xmlhttpPainel.onreadystatechange = function () {
										if (xmlhttpPainel.readyState == 4)
										{
											carregando.style.display = 'none';
											if (xmlhttpPainel.responseText == 'OK') {
												sucesso.style.display = 'inline';
												setTimeout("getEl('imgSucessoClientePref"+id+"').style.display='none'", 2000);
											} else {
												alert('ERRO: Falhar ao alterar o item!');
											}
										}
									};
	xmlhttpPainel.open("GET", url, true);
	xmlhttpPainel.send(null);
}

function carregarConteudo(id, url){
	$(id).load(url);
}