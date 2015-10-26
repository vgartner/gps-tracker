//Funções de comando de histórico

function pressPlay(img) {
	document.getElementById('pauseRotaHistorico').src='imagens/pause_rota_historico.jpg';
	document.getElementById('stopRotaHistorico').src='imagens/stop_rota_historico.jpg';
	
	if ( document.getElementById('gridHistorico').contentWindow.playHistorico() ) 
	{ 
		document.getElementById('spanComandoAcionado').innerHTML='playing...';
		img.src='imagens/play_rota_historico_on.jpg';
	}
}

function pressPause(img) {
	document.getElementById('playRotaHistorico').src='imagens/play_rota_historico.jpg';
	document.getElementById('stopRotaHistorico').src='imagens/stop_rota_historico.jpg';

	document.getElementById('gridHistorico').contentWindow.pauseHistorico(); 
	document.getElementById('spanComandoAcionado').innerHTML='pausado'; 
	img.src='imagens/pause_rota_historico_on.jpg';
}

function pressStop(img) {
	document.getElementById('playRotaHistorico').src='imagens/play_rota_historico.jpg';
	document.getElementById('pauseRotaHistorico').src='imagens/pause_rota_historico.jpg';

	document.getElementById('gridHistorico').contentWindow.stopHistorico(); 
	document.getElementById('spanComandoAcionado').innerHTML='parado';
	img.src='imagens/stop_rota_historico_on.jpg';
}