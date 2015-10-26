var xmlhttpStatusGPS;

function verificarStatusSinalGPS() 
{
	xmlhttpStatusGPS=GetXmlHttpObject();
	
	if (xmlhttpStatusGPS==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	var url="menu_status_sinal_gps.php";
	//url=url+"?imei="+imei;
	//url=url+"&sid="+Math.random();
	xmlhttpStatusGPS.onreadystatechange = stateChangedStatusGPS;
	xmlhttpStatusGPS.open("GET", url, true);
	xmlhttpStatusGPS.send(null);
	
	//Refresh na div de imagens de status do sinal gps a cada 30 segundos.
	setTimeout("verificarStatusSinalGPS()", 30000);
	
}

function stateChangedStatusGPS()
{
	if (xmlhttpStatusGPS.readyState == 4)
	{
		if (xmlhttpStatusGPS.responseText != '') {
			document.getElementById("imagens_status_veiculos").innerHTML=xmlhttpStatusGPS.responseText;
			if (document.getElementById('nrimei').value != "") {
				var imei = document.getElementById('nrimei').value;
				try {
					document.getElementById('statusSinalGPS').src = document.getElementById('img_status_sinal'+ imei).src;
				} catch (err) {
					//alert(err); nao montou a lista de imagens do status
				}
			}
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