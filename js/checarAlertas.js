var xmlhttp;
var xmlhttp2;

function verificarAlertas() 
{
	xmlhttp=GetXmlHttpObject();
	
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	var url="menu_alertas.php";
	//url=url+"?cliente="+cliente;
	//url=url+"&sid="+Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);
	
	//Refresh na div de alertas do menu a cada 1:30 minuto
	setTimeout("verificarAlertas()", 90000);
	
}

function fecharAlerta(message, imei)
{
	xmlhttp2=GetXmlHttpObject();
	
	if (xmlhttp2==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	var url="menu_alertas.php";
	url=url+"?fechar="+message;
	url=url+"&imei="+imei;
	xmlhttp2.open("GET", url, true);
	xmlhttp2.send(null);
}

function seguirBemAlertado(imei)
{
	//Se estiver rastreando(mapa), ou no help
	if (parent.bottom.location.href.indexOf("listagem.html") != -1) 
	{
		//Se nao estiver no mapa, é pq estou no help; vai para o mapa
		if (parent.main.location.href.indexOf("mapa.php") == -1)
		{
			parent.main.location.href = 'mapa.php';
		}
		else 
		{
			//Limpando o mapa
			parent.main.limparMapa();
		}
	
		var benSelecionado = document.getElementById('bens');
		benSelecionado.value = imei;
		var nome = benSelecionado.options[benSelecionado.selectedIndex].text;
		
		parent.bottom.document.getElementById('nrImeiLista').value = imei;

		var winJSMenu = parent.contents;
		//Loading animation
		winJSMenu.carregandoRotas();
		//Se estiver no help, atualiza os cabecalhos
		winJSMenu.document.getElementById('spanComandos').innerHTML='Envia comandos ao gps';
		winJSMenu.document.getElementById('spanHistorico').innerHTML='Consulta rotas por data';
		winJSMenu.document.getElementById('spanCarroSelecionado').innerHTML=nome;
		winJSMenu.document.getElementById('btnEnviarComando').disabled=false;
		winJSMenu.document.getElementById('btnConsultar').disabled=false;
		winJSMenu.document.getElementById('command').disabled=false;
		winJSMenu.document.getElementById('nrimei').value = imei;
		winJSMenu.document.getElementById('imgApagarBem').style.display='inline';
		
		try {
			parent.main.document.getElementById('nrImeiMapa').value = imei; 
		} catch (err) {
			//Nada. Pode ser q nao tenha carregado o mapa ainda; caso esteja no help
			//Nova tentativa abaixo em refreshGridMap(imei)
		}
		
		winJSMenu.refreshGridMap(imei);		
	}
	else 
	{
		//Se estiver no historico. Chamada de fechar histórico
		//Seta a grid de ocorrencias no bottom
		parent.bottom.location.href = 'listagem.html';
		//Parando a execução da simulação
		parent.main.stop();
		//Atualizando map e grid de rotas
		parent.contents.refreshGridMapFecharHistorico(imei);		
	}
	
	//Exibindo imagem de status do sinal
	parent.contents.document.getElementById('statusSinalGPS').style.display='inline';
	parent.contents.document.getElementById('statusSinalGPS').src = parent.contents.document.getElementById('img_status_sinal'+ imei).src;			

}

function stateChanged()
{
	if (xmlhttp.readyState == 4)
	{
		if (xmlhttp.responseText != '') {
			if (xmlhttp.responseText == '<p>Nenhum alerta.</p>') {
				//alert(document.getElementById("headerAlertas").className);
				if (document.getElementById("headerAlertas").className.indexOf("accordion_toggle_active") != -1 || 
				    document.getElementById("headerAlertas").className.indexOf("aberto") != -1)
					document.getElementById("headerAlertas").className='accordion_toggle accordion_toggle_active';
				else
					document.getElementById("headerAlertas").className='accordion_toggle';
			} else {
				//seta a cor vermelha ao menu, indicando que ocorreu alertas
				if (document.getElementById("headerAlertas").className.indexOf("accordion_toggle_active") != -1)
					document.getElementById("headerAlertas").className='accordion_toggle accordion_toggle_red_active aberto';
				else
					document.getElementById("headerAlertas").className='accordion_toggle accordion_toggle_red_active fechado';
			}

			document.getElementById("alertas").innerHTML='<span id="spanComandos" class="spanComentarios">Exibe alertas emitido pelo gps</span>' + xmlhttp.responseText;
		}
	}
}

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