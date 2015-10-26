<?php include('seguranca.php');

//Variavel $cliente setada na sessão no include de segurança

$fechar=isset($_GET['fechar']) ? $_GET['fechar'] : "";
$imei=isset($_GET['imei']) ? $_GET['imei'] : "";
$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
if (!$con){ die('Could not connect: ' . mysql_error()); }
mysql_select_db("tracker", $con);

if ($cliente != ""){
	$sql="";
		  
	if($grupo != ''){
		$sql="SELECT b.name, m.message, m.imei, date_format(m.date, '%d/%c/%y') date, count(*) as qtde
		  FROM bem b inner join message m on (b.imei = m.imei)
		  JOIN grupo_bem gb on gb.bem = b.id
		  WHERE b.cliente = $cliente and m.viewed = 'N' and gb.grupo = $grupo
		  GROUP BY 1, 2, 3, 4 ORDER BY m.date DESC";
	} else {
		$sql="SELECT b.name, m.message, m.imei, date_format(m.date, '%d/%c/%y') date, count(*) as qtde
		  FROM bem b inner join message m on (b.imei = m.imei)
		  WHERE b.cliente = $cliente and m.viewed = 'N'
		  GROUP BY 1, 2, 3, 4 ORDER BY m.date DESC";
	}

	$result = mysql_query($sql);
}

/**
 * Define os alertas como vistos
 */
if ($fechar != "" and $imei != ""){
	if ($fechar != "tudo") {
		if (!mysql_query("UPDATE message set viewed = 'S', date = date WHERE imei = '$imei' and message = '$fechar' and viewed = 'N'", $con)){
			die('Error: ' . mysql_error());
		}	
	}
	else {
		while ($bensCliente = mysql_fetch_array($result)) {
			if (!mysql_query("UPDATE message set viewed = 'S', date = date WHERE imei = '$bensCliente[imei]' and viewed = 'N'", $con)){
				die('Error: ' . mysql_error());
			}
		}
	}
}
/**
 * Faz a consulta dos alertas do cliente
 */
else {
	$loopcount = 0;
	$dados = array('lista' => '');

	while($data = mysql_fetch_assoc($result))
	{
		/*echo "<tr> 
				<td>" . $data['name'] . "</td>
				<td><b>" . $data['message'] . "</b> (". $data['qtde'] .") ".$data['date']." </td>
				<td><input type=\"button\" value=\"Seguir\" title=\"Clique para seguir no mapa\" class=\"botaoBranco\" onclick=\"seguirBemAlertado('". $data['imei'] ."'); this.style.color='silver'; this.style.border='1px solid silver'; this.onclick=''; this.value='Seguindo...'; this.disabled=true; \" /></td>
				<td><input type=\"button\" value=\"Visto\" title=\"Clique para fechar o alerta\" class=\"botaoBranco\" onclick=\"fecharAlerta('". $data['message'] ."', '". $data['imei'] ."'); this.style.color='silver'; this.style.border='1px solid silver'; this.onclick=''; this.value='OK'; this.disabled=true; \" /></td>
			  </tr>";*/
		switch ($data['message']) {
			case 'SOS!':
			case 'Rastreador Desat.' :
				$alertaClass = 'danger';
			break;

			case 'Cerca Violada':
			case 'Bateria Fraca':
			case 'Bat. Fraca':
			case 'Alarme Disparado':
				$alertaClass = 'warning';
			break;
			
			default:
				$alertaClass = 'info';
			break;
		}
		$dados['lista'] .= "<li><a href='javascript:fecharAlerta(\"". $data['message'] ."\", \"". $data['imei'] ."\");' title='Clique para marcar como visualizada'><span class='label label-$alertaClass'>" . $data['name'] . "</span> " . $data['message'] . " (" . $data['date'] .")</a></li>";
		$loopcount++;
	}

	if ($loopcount == 0) {
	  $dados['lista'] = "<li><a href='javascript:void(0)'><span class='label label-default'>OK</span> Nenhum alerta</a></li>";
	  $dados['count'] = 0;
	} 
	else {
		$dados['lista'] .= "<li class='divider'></li><li><a href='javascript:fecharAlerta(\"tudo\", \"tudo\");'>Marcar Todos Como Visto</a></li>";
		$dados['count'] = $loopcount;
	}
	echo json_encode($dados);
}
mysql_close($con);
?>