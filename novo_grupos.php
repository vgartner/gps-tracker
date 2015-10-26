<?php
	if (isset($_GET['id_grupo'])) {
		include_once 'seguranca.php';
		include_once 'usuario/config.php';
		$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
		mysql_select_db($DB_NAME);

		$idGrupo = $_GET['id_grupo'];
		if (isset($_GET['acao'])) $acao = $_GET['acao'];
		elseif ($idGrupo) $acao = 'grupo_alterar';
		else $acao = 'grupo_adicionar';

		if ($acao == 'dados') {
			$listaBens = "";
			$bensCliente = mysql_query("SELECT name, id FROM bem WHERE cliente = $cliente") or die(mysql_error());
			$bensGrupo = mysql_query("SELECT b.name, b.id FROM bem b JOIN grupo_bem gb ON gb.bem = b.id WHERE b.cliente = $cliente AND gb.grupo = $idGrupo");

			if (mysql_num_rows($bensGrupo) > 0) {
				$veiculos = array();
				$i = 0;
				while ($row = mysql_fetch_assoc($bensGrupo)) {
					$veiculos[$i]['name'] = $row['name'];
					$veiculos[$i]['id'] = $row['id'];
					$i++;
				}
				$contador = count($veiculos);
			}
			else {
				$contador = 0;
				$selecionado = " ";
			}

			while ($option = mysql_fetch_assoc($bensCliente)) {
				for ($i=0; $i < $contador; $i++) {
					if ($option['id'] == $veiculos[$i]['id']) {
						$selecionado = " selected ";
						break;
					}
					else $selecionado = " ";
				}
				$listaBens .= "<option". $selecionado ."value=\"". $option['id'] ."\">". $option['name'] ."</option>";
			}
			echo json_encode($listaBens);
		}

		if ($acao == 'remover') {
			if (mysql_query("DELETE FROM grupo WHERE id = $idGrupo")) {
				mysql_query("DELETE FROM grupo_bem WHERE grupo = $idGrupo");
				echo "OK";
			}
			else echo "Ops! Algo deu errado: ". mysql_error();
		}

		if ($acao == 'grupo_adicionar') {
			$nome = $_GET['nome_grupo'];
			$senha = $_GET['senha_grupo'];
			$veiculos = $_GET['veiculos_grupo'];

			if (mysql_query("INSERT INTO grupo(nome, senha, cliente) VALUES('$nome', '".md5($senha)."', $cliente)")) {
				$insertID = mysql_insert_id();
				foreach ($veiculos as $valor) {
					$query = mysql_query("SELECT imei, name FROM bem WHERE id = $valor AND cliente = $cliente");
					if (mysql_num_rows($query) > 0) {
						$data = mysql_fetch_assoc($query);
						if (!mysql_query("INSERT INTO grupo_bem(bem, cliente, imei, descricao, grupo) VALUES($valor, $cliente, '".$data['imei']."', '".$data['name']."', $insertID)")) {
							$error_gb = true;
						}
					}
					else $error_b = true;
				}

				if (isset($error_b)) {
					echo "<div class='alert alert-danger'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Erro: </strong>Bem inexistente no cadastro do cliente.</div>";
				}
				elseif (isset($error_gb)) {
					echo "<div class='alert alert-warning'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Falha no Banco de Dados: </strong>" . mysql_error() . ".</div>";
				}
				else echo "<div class='alert alert-success'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Sucesso! </strong>O grupo foi cadastrado com êxito.</div>";
			}
			else die("<div class='alert alert-warning'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Falha no Banco de Dados: </strong>" . mysql_error() . ".</div>");
		}

		if ($acao == 'grupo_alterar') {
			$nome = $_GET['nome_grupo'];
			$senha = $_GET['senha_grupo'];
			$veiculos = $_GET['veiculos_grupo'];

			if ($senha != "") $retorno = mysql_query("UPDATE grupo SET nome = '$nome', senha = '".md5($senha)."' WHERE id = $idGrupo");
			else $retorno = mysql_query("UPDATE grupo SET nome = '$nome' WHERE id = $idGrupo");

			/**
			 * Verifica se o grupo foi alterado com sucesso
			 */
			if ($retorno) {
				/**
				 * Deleta os bens do grupo, para reinseri-los posteriormente
				 * (Solução com menor tempo-resposta, uma vez que verificar os existentes demoraria mais.)
				 */
				if (mysql_query("DELETE FROM grupo_bem WHERE grupo = $idGrupo")) {
					/**
					 * Insere os veiculos em grupo_bem
					 */
					foreach ($veiculos as $valor) {
						$query = mysql_query("SELECT imei, name FROM bem WHERE id = $valor AND cliente = $cliente");
						if (mysql_num_rows($query) > 0) {
							$data = mysql_fetch_assoc($query);
							if (!mysql_query("INSERT INTO grupo_bem(bem, cliente, imei, descricao, grupo) VALUES($valor, $cliente, '".$data['imei']."', '".$data['name']."', $idGrupo)")) {
								$error_gb = true;
							}
						}
						else $error_b = true;
					}
				}
				else $error_del = true;

				if (isset($error_del)) {
					echo "<div class='alert alert-warning'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Falha no Banco de Dados: </strong>" . mysql_error() . ".</div>";
				}
				elseif (isset($error_b)) {
					echo "<div class='alert alert-danger'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Erro: </strong>Bem inexistente no cadastro do cliente.</div>";
				}
				elseif (isset($error_gb)) {
				 	echo "<div class='alert alert-warning'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Falha no Banco de Dados: </strong>" . mysql_error() . ".</div>";
				}
				else echo "<div class='alert alert-success'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Sucesso! </strong>O grupo foi alterado com êxito.</div>";
			}
			else {
				echo "<div class='alert alert-danger'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Erro: </strong>" . mysql_error() . ".</div>";
			}
		}
	}
	/**
	 * Funciona como uma espécie de MYSQL_INSERT_ID, para retornar o ID do grupo e evitar alteração de todo o script para modo JSON
	 */
	if (isset($_GET['id_inserido'])) {
		include_once 'seguranca.php';
		include_once 'usuario/config.php';
		$con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
		mysql_select_db($DB_NAME);

		$nome = $_GET['id_inserido'];
		$query = mysql_query("SELECT id FROM grupo WHERE nome = '$nome'");
		echo mysql_result($query, 0);
	}
?>