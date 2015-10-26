<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">


<head>
<meta http-equiv="Content-Language" content="en-us" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Google Maps</title>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="https://www.google.com/jsapi?key=ABQIAAAA5pzvaLdsIayFOGuFm5utshSDUZb0tybSNbGX2Y1JhPD9Zju_XhSypGy_aBlrZ-tP3nj5rzgxeuwCgg"></script>
<script type="text/javascript" src="js/geoxml3.js"></script>
<script type="text/javascript" src="js/ProjectedOverlay.js"></script>
<script type="text/javascript" src="js/ge.js"></script>

<script type="text/javascript"> 

	google.load('earth', '1');
   
    var map;
    var geoXml;
	var kmlLayer;
	var myParser;
	
	var ge;
	var geInstalado;
	
	var markersArray = [];
	
    function initialize() {
		var latlng = new google.maps.LatLng(-13.496473,-55.722656);
		var myOptions = {
		  zoom: 4,
		  center: latlng,
		  navigationControl: true,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
		try {
			geInstalado = google.earth.isInstalled() && google.earth.isSupported();
			ge = new GoogleEarth(map);
		} catch(e) {
			geInstalado = false;
		}

    }
	
	listenerHandle = null;
	
	function updateMyKml(imei) {

		if (imei != '') 
		{
		    imeiHandle = imei;
			
			var entries = document.getElementById("CheckboxRastro").checked==true ? 20 : 1;
		
			var kmlLayerOptions = {
				map:map, 
				preserveViewport:false
			};
			
			if (map.getZoom() <= '4')
				map.setZoom(15);

			limparMapa();
			
			myParser = new geoXML3.parser({
									map: map,
									zoom: true,
									processStyles: false
								});			
			
			var chk = document.getElementById("CheckboxCerca");
			
			if (imei == 'ALL') {
				//kmlLayer = new google.maps.KmlLayer("http://cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com/sistema/server/all_kml.php?entries=20&imei=" + imei, kmlLayerOptions);
				//myParser.parse("server/all_kml.php?entries=20&cliente=672");
				
				if (chk.checked == true)
					myParser.parse(["server/all_kml.php?entries=20&cliente=672", "cerca/cercas_kml.php?cliente=672"]);
				else
					myParser.parse(["server/all_kml.php?entries=20&cliente=672"]);				
				
				if (geInstalado && ge.earthVisible_)
					ge.addKML_("server/all_kml.php?entries=20&cliente=672");
					
				listenerHandle = google.maps.event.addListener(map, "tilesloaded", handleGeoXMLLoadNoZoom);
					
			} else {
				//kmlLayer = new google.maps.KmlLayer("server/kml.php?entries=20&imei=" + imei, kmlLayerOptions);
				//myParser.parse("server/kml.php?entries="+ entries +"&imei=" + imei);
				
				if (chk.checked == true)
					myParser.parse(["server/kml.php?entries="+ entries +"&imei=" + imei, "cerca/cercas_kml.php?cliente=672"]);
				else
					myParser.parse(["server/kml.php?entries="+ entries +"&imei=" + imei]);

				if (geInstalado && ge.earthVisible_)
					ge.addKML_("server/kml.php?entries="+ entries +"&imei=" + imei);
					
				listenerHandle = google.maps.event.addListener(map, "tilesloaded", handleGeoXMLLoad);
			}
			
		}
	}
	
	function handleGeoXMLLoad() {
		if (map.getZoom() >= '18')
			map.setZoom(16);
		
		google.maps.event.clearListeners(map, "tilesloaded");
			
		if (geInstalado && ge.earthVisible_)	
			ge.flyToMapView_(true); //ge.refresh_();
	} 	
	
	function handleGeoXMLLoadNoZoom() {
	
		if (geInstalado && ge.earthVisible_)	
			ge.flyToMapView_(true); //ge.refresh_();
	}
	
	function limparCercas() {
		try {
			var chk = document.getElementById("CheckboxCerca");
			
			if (chk.checked == true)
				myParser.showDocument(myParser.docs[2]);
			else
				myParser.hideDocument(myParser.docs[2]);
			
		} catch(e) { }
	}
	
	function verNoMapa(lat, lon) {
	
		var image = '/imagens/coordenada.png';
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
		
		if (geInstalado && ge.earthVisible_) 
		{
			ge.clearPlacemarks_();
			ge.createPoint_(pointMarker);
			ge.flyToMapView_(true);
		}
    }
	
	function limparMapa() {
		try {
			myParser.hideDocument(myParser.docs[0]);
			myParser.hideDocument(myParser.docs[1]);
			clearOverlays();
			if (geInstalado && ge.earthVisible_)
				ge.clearPlacemarks_();
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
	var intervaloReplay = 2000; //2 seg
	//var stop = false;
	
	var points = [];
	
	function play() 
	{
		google.maps.event.removeListener(listenerHandle);
		
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
		intervaloIdHistorico = setInterval("addRota()", intervaloReplay);
	}
	
	var contAddOver = 0;
	var flightPlanCoordinates = [];
	
	function addRota() 
	{
		//Tento colocar o ponto anterior, o try evita o pto negativo, abafando.
		try {
			coord = points[contTotal-1];
			lat = coord[0];
			lon = coord[1];
			var myLatLngAnt = new google.maps.LatLng(lat, lon);
			flightPlanCoordinates.push(myLatLngAnt);
		} catch(e) {}
		
		contAddOver++;
		
		if (true) {
			coord = points[contTotal];
			lat = coord[0];
			lon = coord[1];
			endr = coord[2];
			dtpos  = coord[3];
			veloc = coord[4];
			idLocal = coord[5];

			var image = new google.maps.MarkerImage('imagens/marcador_mapa.gif',
													  new google.maps.Size(32, 32),
													  new google.maps.Point(0,0),
													  new google.maps.Point(16,16));

			var myLatLng = new google.maps.LatLng(lat, lon);
			pointMarker = new google.maps.Marker({
			  position: myLatLng,
			  map: map,
			  icon: image
			});
			
			
			var contentString = '<div id="content">'+
								'<div id="siteNotice">'+
								'</div>'+
								'<h3 id="firstHeading" class="firstHeading">Detalhes da posição</h3>'+
								'<div id="bodyContent">'+
								'<p>' +
								'Velocidade: '+ veloc + ' - ' +
								'Data: ' + dtpos + '<br>' +
								'Lat: '+ lat +','+
								'Long: '+ lon +' <br>'+
								'<span style="font-size:12px">End: '+ endr +'</span>'+
								'</p>'+
								'</div>'+
								'</div>';
		
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			google.maps.event.addListener(pointMarker, 'click', function() {
				infowindow.open(map,pointMarker);
			});
			
			panoramica(myLatLng);
			
			markersArray.push(pointMarker);
			
			map.panTo(myLatLng);
			
			if (geInstalado && ge.earthVisible_)
			{
				//ge.clearPlacemarks_();
				ge.createPoint_(pointMarker);
				ge.flyToMapView_(true);
			}
			
			//map.addOverlay(new GPolyline(localPointsAdd, "#0000FF"));
			//localPointsAdd = [];
			
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
			
			//Foca no endereço na listagem
			parent.bottom.document.getElementById('gridHistorico').contentWindow.focarEndereco(idLocal);
		}		
		
		contTotal++;
		
		if (contTotal == points.length)	{
			//Acabou
			contTotal = 0;
			clearInterval(intervaloIdHistorico);
			parent.bottom.document.getElementById('spanComandoAcionado').innerHTML='fim';
			parent.bottom.document.getElementById('velocidade').style.display='none';
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
	
	function velocidadeReplay(valor)
	{
		intervaloReplay = (2000/valor);
		clearInterval(intervaloIdHistorico);
		intervaloIdHistorico = setInterval("addRota()", intervaloReplay);
	}
	
	function panoramica(posicao) {
	
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
div.over_map {
	position:absolute;
	top:50%;
	left:40%;
	z-index:2;
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

input.smallCheckbox
{
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
		Visualize o <a style="color:#FFFFFF" href="/mapa_todos.php?imei=ALL" target="_blank">Mapa Grande</a>, Com todos os veiculos.
	</div>
	
		
        <div id="map_canvas" style="width: 100.5%; height:768px ; float:left; border: 1px solid #c0c0c0; margin-top: -8px; margin-left:-8px; margin-right:0px;" align="center">
        </div>
	    <div id="pano" style="position: absolute; width: 350px; height: 250px; left: 5px; top: 5px;" align="center"></div>
	    <!--div id="over_map" class="over_map"> Carregando mapa... </div-->
        <br/> 
	    <div style="height:5px;">
		    <span class="linkStyle">
			    <a href="#" onclick="aumentarLista();return false;" class="linkStyle"><img border="0" src="imagens/aumenta_lista.gif" title="(+)" alt="(+)" /> aumentar lista </a> | 
			    <a href="#" onclick="diminuirLista();return false;" class="linkStyle">diminuir lista <img border="0" src="imagens/diminui_lista.gif" title="(-)" alt="(-)" /> </a> |
			    <a href="javascript:;" onclick="abreFecha('pano');" class="linkStyle">exibir e ocultar o street view</a>
			    <label style="cursor:pointer" title="Exibe/Oculta rastro" ><input name="CheckboxRastro" id="CheckboxRastro" type="checkbox" class="smallCheckbox" checked="checked" /> exibe rastro</label>
			    <label style="cursor:pointer" title="Exibe/Oculta cercas" ><input name="CheckboxCerca" id="CheckboxCerca" type="checkbox" class="smallCheckbox" onclick="limparCercas();" /> exibe cercas</label>
			</span>
		
		<div style="float:right">
			<script type="text/javascript"><!--
			google_ad_client = "";
			/* GPS WebArch */
			google_ad_slot = "";
			google_ad_width = 468;
			google_ad_height = 15;
			//-->
			</script>
			<script type="text/javascript"
			src="">
			</script>
		</div>

	</div>
    <br/>
	
</body>

</html>
