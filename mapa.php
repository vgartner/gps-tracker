<?php include('seguranca.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Language" content="en-us" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Google Maps</title>
<!--
<link href="http://code.google.com/apis/maps/documentation/javascript/examples/default.css" rel="stylesheet" type="text/css" />
-->
<link href="css/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="http://maps.google.com/maps/api/js?v=3.exp&sensor=true"></script>
<script type="text/javascript" src="js/geoxml3.js"></script>
<script type="text/javascript" src="js/jquery-1.7.min.js"></script>
<script type="text/javascript" src="js/jquery.ui.position.js"></script>
<script type="text/javascript" src="js/jquery.contextMenu.js"></script>
<script type="text/javascript"> 
	var cliente = '<?=$cliente?>';
	var grupo = '<?=$grupo?>';
    var map;
	var kmlLayer;
	var markersArray = [];
	var marcadores = [];
	var myParser;
	var novo_imei;
	var imeiHandle;
	
	function initialize() {
        var latlng = new google.maps.LatLng(-13.496473,-55.722656);
		var myOptions = {
			zoom: 4,
			center: latlng,
			navigationControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		/*
		$.contextMenu({
			selector: '#map_canvas', 
			callback: function(key, options) {
				var m = "clicked: " + key;
				window.console && console.log(m) || alert(m); 
			},
			items: {
				"edit": {name: "Clickable", icon: "edit", disabled: false},
				"cut": {name: "Disabled", icon: "cut", disabled: true}
			}
		});
		
		google.maps.event.addListener(map, 'click', function(e){
			var image = 'imagens/coordenada.png';
			var pointMarker = new google.maps.Marker({
			  position: e.latLng,
			  map: map,
			  icon: image,
			  animation: google.maps.Animation.DROP,
			  flat:true
			});
			
			google.maps.event.addListener(pointMarker, 'rightclick', function(e){
				console.log(this);
				$('#map_canvas').contextMenu();
			});
			
			pointMarker.setMap(map);
			
		});*/
    }
	
	function updateMyKml(imei) {
		if (imei != '') {
		
			imeiHandle = imei;
		
			var kmlLayerOptions = {
				map:map, 
				preserveViewport:false
			};
			
			limparMapa();
			
			if (imei == 'ALL' || imei.indexOf('grupo') > -1) {
				var grupo = '';
				if(imei.indexOf('grupo') > -1){
					grupo = imei.split('_');
					grupo = grupo[1];
				}
				myParser = new geoXML3.parser({
					map: map,
					zoom: true,
					processStyles: false
				});
				myParser.parse("server/all_kml.php?cliente="+<?=$cliente?>+"&grupo="+grupo);
			
			} else {
			
				myParser = new geoXML3.parser({
					map: map,
					zoom: false,
					processStyles: false
				});
				myParser.parse("server/kml.php?entries=20&imei=" + imei);
				
				handleGeoXMLLoad();
			}
			
			//markersArray.push(kmlLayer);
			//var listenerHandle = google.maps.event.addListener(map, "tilesloaded", handleGeoXMLLoad);
			
		}
	}
	
	function updateMyKmlArray(imei) {
		if (imei.length > 0) {
		
			imeiHandle = imei;
			
			novoImei = '';
			for(i = 0; i <imei.length; i++ ){
				novoImei = imei[i];
				if(i <imei.length)
					novoImei = novoImei+',';
			}
			
		
			var kmlLayerOptions = {
				map:map, 
				preserveViewport:false
			};
			
			limparMapa();
			
			if (imei == 'ALL' || imei.indexOf('grupo') > -1) {
				var grupo = '';
				if(imei.indexOf('grupo') > -1){
					grupo = imei.split('_');
					grupo = grupo[1];
				}
				myParser = new geoXML3.parser({
					map: map,
					zoom: true,
					processStyles: false
				});
				myParser.parse("server/all_kml.php?cliente="+<?=$cliente?>+"&grupo="+grupo);
			
			} else {
			
				myParser = new geoXML3.parser({
					map: map,
					zoom: false,
					processStyles: false
				});
				myParser.parse("server/kml.php?entries=20&imei=" + novoImei);
				
				handleGeoXMLLoad();
			}
			
			//markersArray.push(kmlLayer);
			//var listenerHandle = google.maps.event.addListener(map, "tilesloaded", handleGeoXMLLoad);
			
		}
	}
	
	function updateMyKmlHistorico(imei, dataInicial, dataFinal) {
		if (imei != '') {
		
			imeiHandle = imei;
		
			var kmlLayerOptions = {
				map:map, 
				preserveViewport:false
			};
			
			limparMapa();
			
			if (imei == 'ALL' || imei.indexOf('grupo') > -1) {
				var grupo = '';
				if(imei.indexOf('grupo') > -1){
					grupo = imei.split('_');
					grupo = grupo[1];
				}
				myParser = new geoXML3.parser({
					map: map,
					zoom: true,
					processStyles: false
				});
				myParser.parse("server/all_kml.php?cliente="+<?=$cliente?>+"&grupo="+grupo);
			
			} else {
			
				myParser = new geoXML3.parser({
					map: map,
					zoom: false,
					processStyles: false
				});
				myParser.parse("server/kml.php?entries=20&imei=" + imei+'&data_inicial='+dataInicial+'&data_final='+dataFinal);
				
				handleGeoXMLLoad();
			}
			
			//markersArray.push(kmlLayer);
			//var listenerHandle = google.maps.event.addListener(map, "tilesloaded", handleGeoXMLLoad);
		}
	}
	
	function handleGeoXMLLoad() {
		//if (imeiHandle != novo_imei) 
		if (true) {
			if (map.getZoom() <= '4') {
				map.setZoom(16);
				novo_imei = imeiHandle;
			}
		}
	}	
	
	function verNoMapa(lat, lon, obj) {
		
		obj.onclick = function(){
			removerPanoramica(obj);
			obj.value = 'Ver';
			obj.style.color = '#000000';
		}
		obj.value = 'Remover';
		
		var image = 'imagens/coordenada.png';
		var myLatLng = new google.maps.LatLng(lat, lon);
		var pointMarker = new google.maps.Marker({
		  position: myLatLng,
		  map: map,
		  icon: image,
		  animation: google.maps.Animation.DROP
		});
		
		markersArray.push(pointMarker);
		pointMarker.setMap(map);
		map.setZoom(17);
		map.panTo(myLatLng);
		
		panoramica(myLatLng);
		
	}
	
	function addMarker(lat, lon, obj){
		if (obj.sinal == 'S' || obj.sinal == 'D') {
			var imgTipo = '_inativo.png';
		}
		else if (obj.block == 'S') var imgTipo = '_bloqueado.png';
		else var imgTipo = '_ativo.png';
		
		switch (obj.tipo) {			
			case 'MOTO':
				var image = 'imagens/marker_moto' + imgTipo;
			break;
			
			case 'CARRO':
				var image = 'imagens/marker_carro' + imgTipo;
			break;
			
			case 'JET':
				var image = 'imagens/marker_jet' + imgTipo;
			break;
			
			case 'CAMINHAO':
				var image = 'imagens/marker_caminhao' + imgTipo;
			break;
			
			case 'VAN':
				var image = 'imagens/marker_van' + imgTipo;
			break;
			
			case 'PICKUP':
				var image = 'imagens/marker_pickup' + imgTipo;
			break;
			
			case 'ONIBUS':
				var image = 'imagens/marker_onibus' + imgTipo;
			break;
			
			default:
				var image = 'imagens/marker_carro' + imgTipo;
			break;
		}
		//alert(obj.tipo + ' | ' + image);
		var myLatLng = new google.maps.LatLng(lat, lon);
		var pointMarker = new google.maps.Marker({
		  position: myLatLng,
		  map: map,
		  icon: image,
		  animation: google.maps.Animation.DROP,
		  title: obj.name
		});
		// OBTÉM O ENDEREÇO
		geocoder = new google.maps.Geocoder();
		geocoder.geocode({'latLng': myLatLng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				obj.endereco = results[0].formatted_address;
			}
			else obj.endereco = status;
		});
		
		google.maps.event.addListener(pointMarker, 'click', function(e){

			var infoWindow = new google.maps.InfoWindow({
			  position: myLatLng,
			  content:"<div id='bodyContent' style='text-align:left'><p><b>Placa:</b> "+obj.name+"<br><b>Endereço:</b> "+obj.endereco+"<br><b>IMEI:</b> "+obj.imei+"<br><b>Chip: </b>"+obj.identificacao+"<br><b>Identificação: </b>"+obj.apelido+"<br><b>Rastreador: </b>"+obj.modelo+"</p></div>"
			});
			
			infoWindow.open(map);	
		});
		
		markersArray.push(pointMarker);
		marcadores.push(obj);
		pointMarker.setMap(map);
		map.setZoom(14);
		map.panTo(myLatLng);
	}

	// Sets the map on all markers in the array.
	function setAllMap(map) {
		for (var i = 0; i < markersArray.length; i++) {
			markersArray[i].setMap(map);
		}
	}
	
	// Remove e marcador do mapa e do array.
	function clearMarkers(id) {
		for(var i = 0; i < marcadores.length; i++){
			if (marcadores[i].imei == id){
				markersArray[i].setMap(null);
				marcadores.splice(i, 1);
				markersArray.splice(i, 1);
			}
		}
	}

	function limparMapa() {
		try {
			myParser.hideDocument();
			//kmlLayer.setMap(null);
			clearOverlays();			
		} catch(e) {}

	}

	function clearOverlays() {
		if (markersArray) {
			for (i in markersArray) {
			  markersArray[i].setMap(null);
			}
		}
	}
    
	// Redimensionando a div do map
	function resizeDiv() {
		var frame = document.getElementById("map_canvas");  
		var htmlheight = document.body.parentNode.clientHeight;  
		
		if (htmlheight > 18) {
			htmlheight = htmlheight - 18;
		}
		
		frame.style.height = htmlheight + "px";
	}

	var tamanhoMax = 153; //num
	
	function aumentarLista() {
		var tamanho = parent.document.getElementById('mainFrame').rows; //texto
		var tamanhoIni = tamanho.substr(0, tamanho.indexOf(',*,')+3); //texto
	
		var tamanhoFim = tamanho.substr(tamanho.indexOf(',*,')+3, tamanho.length); //num
		var novoTamanho = parseInt(tamanhoFim) + 40; //num
		
		parent.document.getElementById('mainFrame').rows = tamanhoIni + novoTamanho;		
	}
	
	function diminuirLista() {
		var tamanho = parent.document.getElementById('mainFrame').rows; //texto
		var tamanhoIni = tamanho.substr(0, tamanho.indexOf(',*,')+3); //texto
		
		var tamanhoFim = tamanho.substr(tamanho.indexOf(',*,')+3, tamanho.length); //num
		var novoTamanho = parseInt(tamanhoFim) - 40; //num
		
		if (novoTamanho > tamanhoMax)
			parent.document.getElementById('mainFrame').rows = tamanhoIni + novoTamanho;
		else 
			parent.document.getElementById('mainFrame').rows = tamanhoIni + tamanhoMax;			
	}

	/* Funções de histórico */
	var intervaloIdHistorico = 0;
	var contTotal = 0;
	var pause = false;
	//var stop = false;
	
	var points = [];
	
	function play() {
		if (contTotal == 0)
			limparMapa();
	
		if (contTotal == points.length) {
			contTotal = 0;
			contAddOver = 0;
		}
	
		if (pause == false) {
			//Se nao tiver pausado, começo do inico
			contAddOver = 0;
		} else {
			//Se pausou, nao zera, pois continuará de onde parou
			pause = false;
		}
		addRota();
		clearInterval(intervaloIdHistorico);
		intervaloIdHistorico = setInterval("addRota()", 2000);
	}
	
	var contAddOver = 0;
	var flightPlanCoordinates = [];
	
	function addRota() {
		//Tento colocar o ponto anterior, o try evita o pto negativo, abafando.
		try {
			coord = points[contTotal-1].toString().split(',');
			lat = coord[0].substr(1,coord[0].length);
			lon = coord[1].substr(0,coord[1].length-1);
			var myLatLngAnt = new google.maps.LatLng(lat, lon);
			flightPlanCoordinates.push(myLatLngAnt);
		} catch(e) {}
		
		contAddOver++;
		
		if (true) {
			coord = points[contTotal].toString().split(',');
			lat = coord[0].substr(1,coord[0].length);
			lon = coord[1].substr(0,coord[1].length-1);

			var image = new google.maps.MarkerImage('imagens/marcador_mapa.gif', new google.maps.Size(32, 32), new google.maps.Point(0,0));

			var myLatLng = new google.maps.LatLng(lat, lon);
			pointMarker = new google.maps.Marker({
				position: myLatLng,
				map: map,
				icon: image
			});
			
			panoramica(myLatLng);
			
			markersArray.push(pointMarker);
			
			map.panTo(myLatLng);
			
			flightPlanCoordinates.push(myLatLng);
			
			var flightPath = new google.maps.Polyline({
				path: flightPlanCoordinates,
				strokeColor: "#0000FF",
				strokeOpacity: 0.5,
				strokeWeight: 5,
				map: map
			});

			flightPath.setMap(map);
			markersArray.push(flightPath);
			flightPlanCoordinates = [];
		}		
		
		contTotal++;
		
		if (contTotal == points.length) {
			//Acabou
			contTotal = 0;
			clearInterval(intervaloIdHistorico);
			parent.bottom.document.getElementById('spanComandoAcionado').innerHTML='fim';
			parent.bottom.document.getElementById('playRotaHistorico').src='imagens/play_rota_historico.jpg';
		}
	}
	
	function pausar() {
		pause = true;
		clearInterval(intervaloIdHistorico);
	}
	
	function stop() {
		limparMapa();
		contTotal = 0;
		contAddOver = 0;
		clearInterval(intervaloIdHistorico);
	}
	
	function panoramica(posicao) {
		document.getElementById("pano").style.display = 'inline';
		var panoramaOptions = {
		position: posicao,
			pov: {
				heading: 270,
				pitch: 0,
				zoom: 1
			}
		};
		
		var panorama = new  google.maps.StreetViewPanorama(document.getElementById("pano"),panoramaOptions);
		map.setStreetView(panorama);
	
	}
	
	function removerPanoramica() {
		var panorama = map.getStreetView();
		panorama.setVisible(false);
		document.getElementById("pano").style.display = 'none';
	}

	// var entries = document.getElementById("CheckboxRastro").checked==true ? 1 : 0;

	function abreFecha(id) {
		if (document.getElementById(id).style.display=='') {
			document.getElementById(id).style.display='none';
		} else {
			document.getElementById(id).style.display='';
		}
	}
	
</script>
	
<style type="text/css">
.linkStyle {
	text-decoration: none;
	border: none;
	font-size: 11px;
	color: #9E9E9E;
	font-family:Arial, Helvetica, sans-serif;
}

div.over_map2 {
	position: absolute;
	top: 0%;
	left: 15%;
	z-index: 2;
	background-color: #CC0000;;
	color: #FFFFFF;
	padding:5px;
	font-family: Arial, Helvetica, sans-serif;
	font-size:12px;
}

input.smallCheckbox {
	width: 10px;
	height: 10px;
	-webkit-transform: scale(1.0, 1.0);
	padding:0;
	margin:0;
}
</style>
	
</head>
	<body onload="resizeDiv();initialize();updateMyKml(document.getElementById('nrImeiMapa').value);" onresize="resizeDiv();" onunload="">
		<input type="hidden" id="nrImeiMapa" name="imei" value="" />
		
		<div id="over_map2" class="over_map2">
		Visualize o <a style="color:#FFFFFF" href="/map_all_devices.php" target="_blank">Mapa Grande</a>, Com todos os veiculos.
	</div>
		
		<div id="map_canvas" style="width: 100.5%; height:768px ; float:left; border: 1px solid #c0c0c0; margin-top: -8px; margin-left:-8px; margin-right:0px" align="center"></div>
		<div id="pano" style="position: absolute; width: 350px; height: 250px; right: 5px; top: 40px; display:none" align="center"></div>
		<br /> 
		<div style="height:5px;">
			<span class="linkStyle">
				<a href="#" onclick="aumentarLista();return false;" class="linkStyle"><img border="0" src="imagens/aumenta_lista.gif" title="(+)" alt="(+)" /> aumentar lista </a> | 
				<a href="#" onclick="diminuirLista();return false;" class="linkStyle">diminuir lista <img border="0" src="imagens/diminui_lista.gif" title="(-)" alt="(-)" /> </a> |
				<a href="javascript:;" onclick="abreFecha('pano');" class="linkStyle">exibir street view</a> 
				<!-- label style="cursor:pointer" title="Exibe/Oculta rastro" ><input name="CheckboxRastro" id="CheckboxRastro" type="checkbox" class="smallCheckbox" checked="checked" / -->
			</span>
		</div>
		<br />
	</body>

</html>