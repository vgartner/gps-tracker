<?php
	/* RETORNA OS DADOS PARA VISUALIZAÇÃO NO POPUP */
	if (isset($_GET['imei'])) {
		include '../seguranca.php';
		include '../usuario/config.php';
		$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Could not connect: " . mysql_error());
		mysql_select_db($DB_NAME, $cnx);

		$id		= $_GET['imei'];
		$info	= array();

		$query	= mysql_query("SELECT b.imei, b.name, b.marca, b.cor, b.ano, b.hodometro, b.dt_recarga, b.identificacao, c.email, c.nome, c.apelido, c.cpf, c.endereco, c.bairro, c.cidade, c.estado, c.cep, c.data_contrato, c.dia_vencimento, b.cliente FROM bem b JOIN cliente c ON c.id = b.cliente WHERE b.imei = $id", $cnx) or die(mysql_error());
		while ($row = mysql_fetch_assoc($query)) {
			$info['imei'] = $row['imei'];
			$info['nome'] = $row['name'];
			$info['marca'] = $row['marca'];
			$info['cor'] = $row['cor'];
			$info['ano'] = $row['ano'];
			$info['hodometro'] = $row['hodometro'];
			$info['dt_recarga'] = $row['dt_recarga'];
			$info['identificacao'] = $row['identificacao'];
			$info['email'] = $row['email'];
			$info['cliente'] = $row['nome'];
			$info['idCliente'] = $row['cliente'];
			$info['apelido'] = $row['apelido'];
			$info['cpf'] = $row['cpf'];
			$info['endereco'] = $row['endereco'];
			$info['bairro'] = $row['bairro'];
			$info['cidade'] = $row['cidade'];
			$info['estado'] = $row['estado'];
			$info['cep'] = $row['cep'];
			$info['data_contrato'] = $row['data_contrato'];
			$info['dia_vencimento'] = $row['dia_vencimento'];
		}
		echo json_encode($info);
	}

	/*ALTERA O ESTADO DE VISUALIZAÇÃO DOS ALERTAS*/
	if (isset($_GET['fechar'], $_GET['imeimsg'])) {
		include '../seguranca.php';
		include '../usuario/config.php';
		$cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Could not connect: " . mysql_error());
		mysql_select_db($DB_NAME, $cnx);

		$fechar = $_GET['fechar'];
		$imei = $_GET['imeimsg'];

		if (!mysql_query("UPDATE message SET viewed_adm = 'S', date = date WHERE imei = '$imei' and message = '$fechar' and viewed_adm = 'N'", $cnx)){
			die(mysql_error());
		}
		else echo "OK";
	}
?>