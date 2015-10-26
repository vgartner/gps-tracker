var xmlhttp;
var xmlhttpPainel;
var tempoDeReload = 60000; //60 segundos
var intervaloIdGrid = 0;
var intervaloIdKml = 0;

//Atualizando map e grid de rotas
function refreshGridMap(imei) {
	//Parando o refresh atual
	clearInterval(intervaloIdGrid);
	clearInterval(intervaloIdKml);
	//Refresh com novo imei
	parent.bottom.bindGrid(imei);
	intervaloIdGrid = setInterval("parent.bottom.bindGrid('"+imei+"')", tempoDeReload);
	try {
		parent.main.updateMyKml(imei);
	} catch(err) {
		//Erro, pois o mapa nao foi carregado ainda. Tentando novamente em 10 segundos
		setTimeout("parent.main.updateMyKml('"+imei+"')" , 10000);
	} finally {
		intervaloIdKml = setInterval("parent.main.updateMyKml('"+imei+"')", tempoDeReload);
	}
}

//Atualizando map e grid de rotas no fechar historico
function refreshGridMapFecharHistorico(imei) {
	//Parando o refresh atual
	clearInterval(intervaloIdGrid);
	clearInterval(intervaloIdKml);
	//Refresh com novo imei
	parent.main.updateMyKml(imei);
	intervaloIdKml = setInterval("parent.main.updateMyKml('"+imei+"')", tempoDeReload);
	
	try {
		parent.bottom.bindGrid(imei);
	} catch(err) {
		//Erro, pois a grid nao foi carregado ainda. Tentando novamente em 2 segundos [2 segundos, pois é só html]
		setTimeout("parent.bottom.bindGrid('"+imei+"')", 2000);
	} finally {
		intervaloIdGrid = setInterval("parent.bottom.bindGrid('"+imei+"')", tempoDeReload);
	}
}

//Parando atualizaçao dos mapas e grid rotas
function stopRefreshGridMap() {
	//Limpando a grid
	parent.bottom.bindGrid('');
	clearInterval(intervaloIdGrid);
	//Limpando o mapa
	parent.main.limparMapa();
	clearInterval(intervaloIdKml);
}

//Atualizando map e grid de rotas
function refreshGridMapNovo(imei) {
	//Parando o refresh atual
	clearInterval(intervaloIdGrid);
	clearInterval(intervaloIdKml);
	//Refresh com novo imei
	parent.bottom.bindGridNovo(imei);
	intervaloIdGrid = setInterval("parent.bottom.bindGridNovo('"+imei+"')", tempoDeReload);
	try {
		parent.main.updateMyKml(imei);
	} catch(err) {
		//Erro, pois o mapa nao foi carregado ainda. Tentando novamente em 10 segundos
		setTimeout("parent.main.updateMyKml('"+imei+"')" , 10000);
	} finally {
		intervaloIdKml = setInterval("parent.main.updateMyKml('"+imei+"')", tempoDeReload);
	}
}

//Atualizando map e grid de rotas no fechar historico
function refreshGridMapFecharHistoricoNovo(imei) {
	//Parando o refresh atual
	clearInterval(intervaloIdGrid);
	clearInterval(intervaloIdKml);
	//Refresh com novo imei
	parent.main.updateMyKml(imei);
	intervaloIdKml = setInterval("parent.main.updateMyKml('"+imei+"')", tempoDeReload);
	
	try {
		parent.bottom.bindGridNovo(imei);
	} catch(err) {
		//Erro, pois a grid nao foi carregado ainda. Tentando novamente em 2 segundos [2 segundos, pois é só html]
		setTimeout("parent.bottom.bindGridNovo('"+imei+"')", 2000);
	} finally {
		intervaloIdGrid = setInterval("parent.bottom.bindGridNovo('"+imei+"')", tempoDeReload);
	}
}

//Parando atualizaçao dos mapas e grid rotas
function stopRefreshGridMapNovo() {
	//Limpando a grid
	parent.bottom.bindGridNovo('');
	clearInterval(intervaloIdGrid);
	//Limpando o mapa
	parent.main.limparMapa();
	clearInterval(intervaloIdKml);
}


function alterarComboVeiculo(obj) {
	var hod = getEl('hod_atual');
	var hodAlerta = getEl('hod_atual');
	if(hod)
		getEl('hod_atual').value = '';
	if(hodAlerta)
		getEl('alerta_hodometro').value = '';
	if(obj.value.indexOf('grupo') == -1) {
		if (obj.value != '' && obj.value != 'ALL' && obj.value.indexOf('grupo') == -1) {
			//Exibindo imagem de status do sinal
			document.getElementById('statusSinalGPS').style.display = 'inline';
			document.getElementById('statusSinalGPS').src = document.getElementById('img_status_sinal'+ obj.value).src;
			
			//Se nao estiver na página do mapa, carrega o mapa
			if (parent.main.location.href.indexOf("mapa.php") == -1) {
				//Carregando o mapa.
				parent.main.location.href = 'mapa.php';
			}
			
			//Se estiver visualizando o histórico, atualiza com o novo imei;
			//e nao permitir dar novo refresh no mapa e na grid. Ver abaixo a condição.
			if (parent.bottom.location.href.indexOf("listagem_historico.php") != -1) {
				consultarHistoricoData();
			} else {
				if(parent.bottom.location.href.indexOf("listagem_nova.html") != -1){
					parent.bottom.location.href = "listagem.html";
				}
				//Loading
				carregandoRotas();
				parent.bottom.document.getElementById('nrImeiLista').value = obj.value;
			}
			
			document.getElementById('spanComandos').innerHTML='Envia comandos ao gps';
			document.getElementById('spanHistorico').innerHTML='Consulta rotas por data';
			document.getElementById('spanCarroSelecionado').innerHTML=obj.options[obj.selectedIndex].text;
			document.getElementById('btnEnviarComando').disabled=false;
			document.getElementById('btnConsultar').disabled=false;
			document.getElementById('btnConsultarImp').disabled=false;
			document.getElementById('command').disabled=false;
			document.getElementById('nrimei').value = obj.value;
			document.getElementById('imgApagarBem').style.display='inline';
			
			try {
				parent.main.document.getElementById('nrImeiMapa').value = obj.value; 
			} catch (err) {
				//Nada. Pode ser q nao tenha carregado o mapa ainda.
			}
			
			// Se estiver na grid de rotas, dou refresh. Senao, é pq estou visualizando o historico.
			if (parent.bottom.location.href.indexOf("listagem.html") != -1 || parent.bottom.location.href.indexOf("listagem_nova.html") != -1) 
				refreshGridMap(obj.value);
			
			/*
			carregar hodometro e alerta
			*/
			
			xmlhttp=GetXmlHttpObject();
		
			if (xmlhttp==null) {
				alert ("Browser does not support HTTP Request");
				return;
			}
			
			var url="menu_hodometro.php";
			url=url+"?imei="+obj.value;
			url=url+"&acao=hodometro_atual";
			
			xmlhttp.onreadystatechange = function(){
				if(xmlhttp.readyState == 4){
					var aHodometro = eval(xmlhttp.responseText);
					var hod = getEl('hod_atual');
					var hodAlerta = getEl('alerta_hodometro');
					if(hod)
					getEl('hod_atual').value = aHodometro[0];
					if(hodAlerta)
					getEl('alerta_hodometro').value = aHodometro[1];
				}
			}
			xmlhttp.open("GET", url, true);
			xmlhttp.send(null);
			
			
		} else {
			//Escondendo imagem de status do sinal
			document.getElementById('statusSinalGPS').style.display = 'none';
			document.getElementById('statusSinalGPS').src = 'imagens/status_desligado.png';
			
			//Nenhum item selecionado
			document.getElementById('spanComandos').innerHTML = 'Para habilitar comandos selecione um ve&iacute;culo';
			document.getElementById('spanHistorico').innerHTML = 'Para consultar hist&oacute;rico selecione um ve&iacute;culo';
			document.getElementById('spanCarroSelecionado').innerHTML='';
			/*desabilita enviar comandos*/
			document.getElementById('btnEnviarComando').disabled = true;
			document.getElementById('btnConsultar').disabled = true;
			document.getElementById('btnConsultarImp').disabled = true;
			document.getElementById('command').disabled = true;
			document.getElementById('imgApagarBem').style.display = 'none';
			if (obj.value == 'ALL' || obj.value.indexOf('grupo') > -1) {
				//Se nao estiver na página do mapa, carrega o mapa
				if (parent.main.location.href.indexOf("mapa.php") == -1) {
					//Carregando o mapa.
					parent.main.location.href = 'mapa.php';
				}		
			
				try {
					parent.main.document.getElementById('nrImeiMapa').value = obj.value; 
				} catch (err) {
					//Nada. Pode ser q nao tenha carregado o mapa ainda.
				}
				
				if (parent.bottom.location.href.indexOf("listagem_nova.html") != -1) {
					carregandoRotasNovo();
					parent.bottom.document.getElementById('nrImeiLista').value = obj.value;
					refreshGridMapNovo(obj.value);
				} else {			
					parent.bottom.location.href = 'listagem_nova.html';
					try {setTimeout("carregandoRotasNovo()" , 1200);} catch (e) {}
					//carregandoRotas();
					//parent.bottom.document.getElementById('nrImeiLista').value = obj.value;
					//Tentando novamente em 5 segundos. Caso esteja no histórico
					setTimeout("refreshGridMapNovo('"+obj.value+"')" , 5000);
				}
			
			} else {
				if (parent.bottom.location.href.indexOf("listagem_nova.html") != -1) {
					stopRefreshGridMapNovo();
				} else {
					//Se estiver no histórico, ao setar vazio, volta para grid normal.
					parent.bottom.location.href = 'listagem_nova.html';
				}
			}
		}
	} else {
		window.open('mapa_todos.php?imei='+obj.value, 'mapa grande');
	}
}

// Adiciona um icone de loading
function carregandoRotas() {
	parent.bottom.document.getElementById("divListagem").innerHTML=
	"<table class='stripeMe'>" +
	"<thead>"+
	"<tr class='alt'>"+
		"<th>Data</th>"+
		"<th>Hora</th>"+
		"<th>Latitude</th>"+
		"<th>Longitude</th>"+
		"<th>Velocidade</th>"+
		"<th>Local</th>"+
		"<th>Ver Mapa</th>"+
	"</tr>"+
	"</thead>"+
	"<tbody>"+
	"<tr class=''>"+
	"<td colspan='7' align='center'>"+
		"<img src='imagens/carregando.gif' alt='Carregando rotas, aguarde...' title='Carregando rotas, aguarde...' "+
			"style='padding:0px; opacity:0.4; filter:alpha(opacity=40); -moz-opacity: 0.4;' /> "+
	"</td>"+
	"</tr>"+
	"</tbody>"+
	"</table>";
}

function carregandoRotasNovo() {
	if(parent.bottom.document.getElementById("divListagem")!=null){
	parent.bottom.document.getElementById("divListagem").innerHTML=
	"<table class='stripeMe'>" +
	"<thead>"+
	"<tr class='alt'>"+
			"<th>Identif.</th>"+
			"<th>Data/Hora</th>"+
			"<th>Local</th>"+
			"<th>Status</th>"+
			"<th>Velocidade</th>"+
			"<th>Alertas</th>"+
			"<th>Lat.</th>"+
			"<th>Long.</th>"+
			"<th>Ver</th>"+
		"</tr>"+
	"</thead>"+
	"<tbody>"+
	"<tr class=''>"+
	"<td colspan='9' align='center'>"+
		"<img src='imagens/carregando.gif' alt='Carregando rotas, aguarde...' title='Carregando rotas, aguarde...' "+
			"style='padding:0px; opacity:0.4; filter:alpha(opacity=40); -moz-opacity: 0.4;' /> "+
	"</td>"+
	"</tr>"+
	"</tbody>"+
	"</table>";
	}
}


function confirmaApagarVeiculo() {
	var benSelecionado = document.getElementById('bens');
	var imei = benSelecionado.options[benSelecionado.selectedIndex].value;
	var nome = benSelecionado.options[benSelecionado.selectedIndex].text;
	
	if (confirm("Confirma remo\u00e7\u00E3o do " + nome + " ?")) {
		//Chamo funçao para apagar aqui.
		apagarVeiculo(imei);
		//Removo do combo
		benSelecionado.remove(benSelecionado.selectedIndex);
		alterarComboVeiculo(benSelecionado);
	} else {
		return false;
	} 
}

function apagarVeiculo(imei) {
	if (imei != '') {
		xmlhttp=GetXmlHttpObject();
		
		if (xmlhttp==null) {
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		var url="menu_veiculos.php";
		url=url+"?inativarVeiculo="+imei;
		//url=url+"&sid="+Math.random();
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);
	}
}

function GetXmlHttpObject() {
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	}
	
	if (window.ActiveXObject) {
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	return null;
}

function adicionarNovoImei() {

	//Escondendo imagem de status do sinal
	document.getElementById('statusSinalGPS').style.display = 'none';
	document.getElementById('statusSinalGPS').src = 'imagens/status_desligado.png';
	
	document.getElementById('bens').value = '';
	document.getElementById('spanComandos').innerHTML = 'Para habilitar comandos selecione um ve&iacute;culo';
	document.getElementById('spanHistorico').innerHTML = 'Para consultar hist&oacute;rico selecione um ve&iacute;culo';
	document.getElementById('spanCarroSelecionado').innerHTML = '';
	/*desabilita enviar comandos*/
	document.getElementById('btnEnviarComando').disabled = true;
	document.getElementById('btnConsultar').disabled = true;
	document.getElementById('command').disabled = true;
	document.getElementById('imgApagarBem').style.display = 'none';
	
	//Se estiver na grid de histórico, volta
	if (parent.bottom.location.href.indexOf("listagem_historico.php") != -1) {
		parent.bottom.location.href = 'listagem.html';
	} else {
		//Limpando a grid
		parent.bottom.bindGrid('');
	}
	
	//Parando a execução da funções
	clearInterval(intervaloIdGrid);
	clearInterval(intervaloIdKml);
}

function alterarVeiculoPainel(id) {
	//Exibe icone de executando o link
	document.getElementById('imgExecutando'+id).style.display='inline';
	
	var imei = document.getElementById('listaImei' + id).value;
	var nome = document.getElementById('listaNome' + id).value;
	var ident = document.getElementById('listaIdent' + id).value;
	var ativoCombo = document.getElementById('listaAtivo' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;
	var tipoCombo = document.getElementById('tipoBem' + id);
	var tipo = tipoCombo.options[tipoCombo.selectedIndex].value;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null) {
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	var url="alterar_bens_painel.php";
	url=url+"?imei="+imei;
	url=url+"&nome="+nome;
	url=url+"&ident="+ident;
	url=url+"&ativo="+ativo;
	url=url+"&tipo="+tipo;
	xmlhttpPainel.onreadystatechange = function () {
										if (xmlhttpPainel.readyState == 4) {
											if (xmlhttpPainel.responseText == 'OK') {
												//Se reativando veiculo, coloco ele no menu novamente, caso nao exista
												var existeItem = false;
												try {
													//Tentando acessar a combo de veículos
													var comboMenuVeiculos = parent.contents.document.getElementById("bens");
													var tamanhoComboMenuVei = comboMenuVeiculos.options.length;											
													if (ativo == 'S') {

														for (var i=0;i<tamanhoComboMenuVei;i++) {
															if (comboMenuVeiculos.options[i].value == imei) {
																//Já existe
																//Atualizo o nome
																comboMenuVeiculos.options[i].text=nome;
																existeItem = true;
																break;
															}
														}
														if (!existeItem) {
															//Colocando no menu
															var oOption = document.createElement('option');
															oOption.text=nome;
															oOption.value=imei;
															comboMenuVeiculos.options.add(oOption);
														}
													} else {
														//Vou remover ele do menu, pois estou inativando-o
														for (var i=0;i<tamanhoComboMenuVei;i++) {
															if (comboMenuVeiculos.options[i].value == imei) {
																//Achei! Remova
																existeItem = true;
																//comboMenuVeiculos.options.remove(i);
																comboMenuVeiculos.options[i] = null;
																break;
															}
														}													
													}
												} catch(err) {
													//Se nao tiver combo, nao existe bens ou estão inativados, reload no menu.
													parent.contents.window.location.href=parent.contents.window.location.href;
												}
												document.getElementById('imgExecutando' + id).style.display='none';
												document.getElementById('imgSucesso' + id).style.display='inline';
												setTimeout("document.getElementById('imgSucesso"+id+"').style.display='none'", 5000);
											}
										}
									};
	xmlhttpPainel.open("GET", url, true);
	xmlhttpPainel.send(null);
}

function consultarHistoricoData() {
	var benSelecionado = document.getElementById('bens');
	var imei = benSelecionado.options[benSelecionado.selectedIndex].value;
	
	document.getElementById('nrImeiConsulta').value = imei;
	//Parando auto-refresh
	clearInterval(intervaloIdGrid);
	//Limpando o mapa
	try{
		var dataInicial = document.getElementById('commandDateIni').value+' '+document.getElementById('commandHourTimeIni').value+':'+document.getElementById('commandMinuteTimeIni').value;
		var dataFinal = document.getElementById('commandDateFim').value+' '+document.getElementById('commandHourTimeFim').value+':'+document.getElementById('commandMinuteTimeFim').value;
		parent.main.limparMapa();
		parent.main.updateMyKmlHistorico(imei, dataInicial, dataFinal);
	} catch (e){
	
	}
	clearInterval(intervaloIdKml);
	
	document.forms["consultarHistorico"].target = "bottom";
	document.forms["consultarHistorico"].action = "listagem_historico.php";
	document.forms["consultarHistorico"].submit();
}

function abrirDespesa() {
	var benSelecionado = document.getElementById('bens');
	var imei = benSelecionado.options[benSelecionado.selectedIndex].value;
	
	document.getElementById('nrImeiConsulta').value = imei;
	
	if(imei != ''){
		//Parando auto-refresh
		clearInterval(intervaloIdGrid);
		//Limpando o mapa
		try{
			parent.main.limparMapa();
		} catch (e){
		
		}
		clearInterval(intervaloIdKml);
		parent.main.location = 'menu_despesas.php?imei='+imei;
	} else {
		alert('Selecione um veículo.');
		return null;
	}
}


function consultarHistoricoDataImpressao() {
	var benSelecionado = document.getElementById('bens');
	var imei = benSelecionado.options[benSelecionado.selectedIndex].value;
	
	document.getElementById('nrImeiConsulta').value = imei;
	//Parando auto-refresh
	clearInterval(intervaloIdGrid);
	//Limpando o mapa
	try{
		parent.main.limparMapa();
		var dataInicial 
		var dataFinal
		parent.main.updateMyKml(imei, dataInicial, dataFinal);
	} catch (e){
	
	}
	
	clearInterval(intervaloIdKml);
	
	document.forms["consultarHistorico"].target = "main";
	document.forms["consultarHistorico"].action = "listagem_historico_relatorio.php";
	document.forms["consultarHistorico"].submit();
}

function abrirHelp() {
	adicionarNovoImei();
}

function alterarSenha(){
	var senha = document.getElementById('senha_atual').value;
	var senhaNova = document.getElementById('nova_senha').value;
	var repitaSenha = document.getElementById('repita_senha').value;
	
	if(senhaNova == repitaSenha) {
	
	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null) {
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	var url="alterar_senha.php";
	url=url+"?senha="+senha;
	url=url+"&nova="+senhaNova;
	url=url+"&repita="+repitaSenha;
	xmlhttpPainel.onreadystatechange = function () {
										if (xmlhttpPainel.readyState == 4) {
											if (xmlhttpPainel.responseText == 'OK') {
												
												document.getElementById('imgExecutandoSenha').style.display='none';
												document.getElementById('imgSucessoSenha').style.display='inline';
												setTimeout("document.getElementById('imgSucessoSenha').style.display='none'", 5000);
												document.getElementById('senha_atual').value = '';
												document.getElementById('nova_senha').value = '';
												document.getElementById('repita_senha').value = '';
											} else {
												alert(xmlhttpPainel.responseText);
											}
										}
									};
	xmlhttpPainel.open("GET", url, true);
	xmlhttpPainel.send(null);
	} else {
		alert('As senhas novas nao conferem!');
	}
}

function getEl(id){
	return document.getElementById(id);
}

function habilitarHodometro(){
	try{
		if(parent.main.document.getElementById('nrImeiMapa').value != ''){
			getEl('hod_atual').disabled = false;
			getEl('alerta_hodometro').disabled = false;
		} else {
			getEl('hod_atual').disabled = true;
			getEl('alerta_hodometro').disabled = true;
			alert('Selecione um veiculo');
		}
	} catch(err) {
		getEl('hod_atual').disabled = true;
		alert('Selecione um veiculo');
	}
}

function alterarHodometro(){
	try{
		hodometro = getEl('hod_atual').value;
		alerta_hodometro = getEl('alerta_hodometro').value;
		sucesso = getEl('hodometro_sucesso');
		carregando = getEl('hodometro_carregando');
		imei = parent.main.document.getElementById('nrImeiMapa').value;
		
		xmlhttp=GetXmlHttpObject();
		
		if (xmlhttp==null) {
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		var url="menu_hodometro.php";
		url=url+"?imei="+imei;
		url=url+"&acao=salvar_hodometro";
		url=url+"&hodometro="+hodometro;
		url=url+"&alerta_hodometro="+alerta_hodometro;
		
		carregando.style.display = 'inline';
		
		xmlhttp.onreadystatechange = function(){
			if(xmlhttp.readyState == 4){
				carregando.style.display = 'none';
				if(xmlhttp.responseText == 'OK'){
					getEl('hod_atual').disabled = true;
					getEl('alerta_hodometro').disabled = true;
					sucesso.style.display = 'inline';
					setTimeout("getEl('hodometro_sucesso').style.display='none'", 2000);
				} else {
					alert(xmlhttp.responseText);
				}
			}
		}
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);
	} catch(err) {
		alert('Houve um erro:'+err);
	}
}