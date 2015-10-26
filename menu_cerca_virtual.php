<?php include('seguranca.php'); ?>

<!--<script type="text/javascript">
function showUser(str) {
	if (confirm("Deseja realmente EXCLUIR esta cerca? Esta opera\u00e7\u00e3o n\u00e3o poder\u00e1 ser desfeita!")) {
		if (str == "") {
			//document.getElementById("txtHint").innerHTML="";
			return;
		}

		if (window.XMLHttpRequest) {
			xmlhttp = new XMLHttpRequest();
		} else {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}

		if (str != '') {
			//Exibe icone de executando o link
			document.getElementById('processando' + str).style.display='inline';	

			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
				document.getElementById('linhaRegistro' + str).style.display='none';
				}
			}

			xmlhttp.open("GET","excluir_cerca.php?codCerca="+str,true);
			xmlhttp.send();

		}
	}
}
</script>-->

<?php
if ($cliente == '') {
	$cliente = "0";
}

$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die("Could not connect: " . mysql_error());
mysql_select_db('tracker', $cnx);

$sql = "SELECT a.name, b.id, b.imei, b.nome, a.identificacao FROM bem a INNER JOIN geo_fence b ON (a.imei = b.imei) WHERE b.disp = 'S' AND a.cliente = $cliente ORDER BY id DESC";

$result = mysql_query($sql);

while($row = mysql_fetch_array($result)) {
	echo "<tr id='linhaRegistro". $row['id'] ."'><td>". $row['name'] ." - ". $row['nome'] ."</td><td><a href=editar_cerca.php?imei=". $row['id'] . $row['imei'] ."><img src='imagens/edit.gif' border='0'></a></td><td><a href=\"javascript:func()\" onclick=\"showUser('". $row[$b.id] ."')\"><img border=0 src='imagens/lixeira.png' title='Excluir Cerca' alt='Excluir Cerca' /></a><img id='processando". $row[$b.id] ."' style='display:none' src='imagens/executando.gif' title='Executando...' alt='Executando...' /></td></tr>";
	echo "<li>". $row['name'] ." - ". $row['nome'] . " <a href='javascript:void(0);' onclick='removeCerca(". $row['id'] ."'><i class='fa fa-trash-o fa-lg'></i></a></li>";
}

mysql_close($cnx);
?>
