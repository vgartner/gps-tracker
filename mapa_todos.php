<?php include('seguranca.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php

  if ($_GET['points'] <> "") {
	$points = $_GET['points'];
  } elseif ($_POST['points'] <> "") {
	$points = $_POST['points'];
  } else {
	$points = 20;
  }
  
  if ($_GET['imei'] <> "") {
	$imei = $_GET['imei'];
  } elseif ($_POST['imei'] <> "") {
	$imei = $_POST['imei'];
  }
  
?>

<head>
<META HTTP-EQUIV="Refresh" CONTENT="180 ; URL= /mapa_todos.php?imei=ALL">
<meta http-equiv="Content-Language" content="en-us" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Todos Veiculos no Mapa</title>
<link href="/imagens/icone.png" rel="shortcut icon" />

<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="js/geoxml3.js"></script>
<script type="text/javascript"> 
   
    var map;
	var kmlLayer;
	var markersArray = [];
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

				myParser = new geoXML3.parser({
					map: map,
					zoom: true,
					processStyles: false
				});
				if(imei.indexOf('grupo') == -1)
				{					
					myParser.parse("server/all_kml.php?cliente=<?php echo $cliente;?>&grupo=<?php echo $grupo;?>");
				} 
				else 
				{
					myParser.parse("server/all_kml.php?cliente=<?php echo $cliente;?>&imei="+imei);
				}
			
			} else {
			
				myParser.parse("server/kml.php?entries=<?php echo $points;?>&imei=" + imei);
				
				
			}
			
			myParser = new geoXML3.parser({
				map: map,
				zoom: false,
				processStyles: false
			});
			
			handleGeoXMLLoad();
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
	
	function verNoMapa(lat, lon) {
	
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


</script>
	
<style type="text/css">
.linkStyle {
	text-decoration: none;
	border: none;
	font-size: 12px;
	color: #000000;
	font-family:Arial, Helvetica, sans-serif;
}
</style>
	
</head>

<body onload="resizeDiv();initialize();updateMyKml(document.getElementById('nrImeiMapa').value);" onresize="resizeDiv();" onunload="">
	<input type="hidden" id="nrImeiMapa" name="imei" value="<?php echo $imei;?>" />
    <div id="map_canvas" style="width: 100.5%; height:768px ; float:left; border: 1px solid #c0c0c0; margin-top: -14px; margin-left:-2px; margin-right:0px" align="center">
    </div>
    <br/> 
	<div style="height:5px;">
		<span class="linkStyle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="/mapa_todos.php?imei=<?=$_GET['imei']?>" onclick="aumentarLista();return false;" class="linkStyle"><img border="0" src="/imagens/refresh-icon.gif" title="Atualizar Pagina" alt="(+)" /> Atualizar Pagina </a> 
			 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="" onclick="diminuirLista();return false;" class="linkStyle"><img border="0" src="imagens/alert.gif" title="" alt="" />&nbsp;&nbsp;&nbsp;*Obs: Este mapa atualiza automaticamente a cada 3 minutos!  </a>
		</span>
	</div>
    <br/> 


</body>

</html>