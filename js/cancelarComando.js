var xmlhttp;

function cancelarComando(imei) 
{
	if (imei != '') {
		xmlhttp=GetXmlHttpObject();
		
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		var url="menu_comandos.php";
		url=url+"?cancelar="+imei;
		//url=url+"&sid="+Math.random();
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);
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