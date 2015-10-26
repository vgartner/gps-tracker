var xmlhttp;
function saveAddress(row, add, lat, lng, attempts){
	$(row).removeClass('nAdrr');
	$(row).addClass('wAdrr');
	$(row).attr('data-pos',lat+''+lng);
	var tds = $(row).find('td')
	$(tds[5]).text(add);
	var idTr = $(row).attr('id');
    idTr = idTr.substr(4);
	$.post('updateAddress.php?'+Math.random(),{id: idTr , addr: add },function f01(response){
		//console.log(response)
	})	
	getAddress(attempts)
}
function cancelRow(row){
	$(row).removeClass('nAdrr');
	$(row).addClass('nsAdrr');
	var tds = $(row).find('td');
	$(tds[5]).text('--');
	getAddress(0)
}
function getAddress(attempts){
	//console.log('INICIO CON attempts : ' + attempts)
	var row = $('tr.nAdrr:first')
	if (row.size() == 1) {
		var tds = $(row).find('td')
		var lat = $.trim($(tds[2]).text())
		lat = Math.round(lat * 100000) / 100000;
		var lng = $.trim($(tds[3]).text())
		lng = Math.round(lng * 100000) / 100000;

		var cacheRow = $('.wAdrr[data-pos="'+lat+''+lng+'"]:first')
		if ( cacheRow.size() == 1 ) {
			var tdsc = $(cacheRow).find('td')
			var add = $(tdsc[5]).text()
			saveAddress(row, add, lat, lng, attempts);
		}else{
			if (attempts < 3) {
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode( { 'address': lat+","+lng},
				function(results, status){
					switch(status){
						case 'OK':
							var add = results[0].formatted_address;
							saveAddress(row, add, lat, lng, 0);
							break;
						case 'OVER_QUERY_LIMIT':
						case 'REQUEST_DENIED':
							setTimeout( function f03(){
								getAddress(attempts + 1)
							}, 1000 )
							break;
						default:
							cancelRow(row);
					}
				})
				/*
				$.get("http://maps.google.com/maps/api/geocode/json?sensor=false&latlng="+lat+","+lng+"&language=es-ES",function f02(response){
					console.log(response)
					switch(response.status){
						case 'OK':
							var add = response.results[0].formatted_address;
							saveAddress(row, add, lat, lng, 0);
							break;
						case 'OVER_QUERY_LIMIT':
						case 'REQUEST_DENIED':
							setTimeout( function f03(){
								getAddress(attempts + 1)
							}, 1000 )
							break;
						default:
							cancelRow(row);
					}
				},'json').fail(function f04() {
				    //console.log('PAUSA')
					setTimeout( function f05(){
						getAddress(attempts + 1)
					}, 1000 )
				})
				*/
			}else{
				cancelRow(row);
			}
		}
	}
}

function bindGrid(str)
{
	if (str == '') {
		document.getElementById("divListagem").innerHTML=
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
		"<tbody><tr class=''><td colspan='7' align='center'> Selecione um veículo no menu. </td></tr></tbody></table>";
	} else {
		strLocal = str;
		
		//xmlhttp.abort();
		xmlhttp=GetXmlHttpObject();
		
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		var url="listagem.php";
		url=url+"?imei="+str;
		//url=url+"&sid="+Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);
		
		//Refresh na grid a cada 4 minutos = 240.000 milisegundos
		//setTimeout("bindGrid(strLocal)", 240000);
	}
}

function stateChanged()
{
	if (xmlhttp.readyState == 4)
	{
		document.getElementById("divListagem").innerHTML=xmlhttp.responseText;
		getAddress(0);
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

