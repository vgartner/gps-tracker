<?php
//header("Content-Type: text/html; charset=utf-8");

$grupo = $_SESSION['grupoSession'];

// if ($cliente == '') $cliente = "0";

$inativarVeiculo = isset($_GET['inativarVeiculo']) ? $_GET['inativarVeiculo'] : null;

if ($inativarVeiculo == null) {
	if($grupo == ''){
		$res = mysql_query("SELECT imei, name FROM bem WHERE activated = 'S' AND cliente = " . trim($cliente) . " ORDER BY name");
		$resGrupo = mysql_query("SELECT id, nome FROM grupo WHERE cliente = " . trim($cliente) . " ORDER BY nome");
	} else {
		// $res = mysql_query("SELECT bem.imei, name FROM bem JOIN grupo_bem gb ON gb.bem = bem.id AND gb.imei = bem.imei JOIN grupo g ON g.id = gb.grupo AND g.cliente = ".trim($cliente)." WHERE activated = 'S' AND bem.cliente = " . trim($cliente) . " AND g.id = ".$grupo." ORDER BY bem.name");
		$res = mysql_query("SELECT b.name, b.imei FROM bem b JOIN grupo_bem gb ON gb.bem = b.id JOIN grupo g ON g.id = gb.grupo WHERE g.id = $grupo");
	}
	
	if (mysql_num_rows($res) == 0) {
		echo "Nenhum bem encontrado.";
	}
	else
	{
		echo "<select id=\"bens\" name=\"bens\" class=\"form-control\" onchange=\"alterarComboVeiculo(this.value); \">";
		echo "<option value='' selected>Selecione</option>";

		if($resGrupo !== false && mysql_num_rows($resGrupo) > 0){
			echo "<optgroup label='-- GRUPOS'>";
			for($i=0; $i < mysql_num_rows($resGrupo); $i++) {
				$row = mysql_fetch_assoc($resGrupo);
				echo "<option value='grupo_$row[id]'>$row[nome]</option>";
			}
			echo "</optgroup>";
		}

		echo "<optgroup label='-- VEÍCULOS'>";
		for($i=0; $i < mysql_num_rows($res); $i++) {
			$row = mysql_fetch_assoc($res);
			echo "<option value='$row[imei]'>$row[name]</option>";
		}
		echo "</optgroup>";
		/*
		if ($i > 1) {
			echo "<option value=''>------</option>";
			echo "<option value='ALL'>TODOS</option>";
		}
		*/
		echo "</select>";
		/*echo "
			<td><a id='imgApagarBem' style='display:none; font-size:10px;' title='Remover bem' href=\"\" onclick=\"javascript:confirmaApagarVeiculo();return false;\"></a> </td>	
			<td><a href=\"menu_novo_veiculo.php\" style=\"font-size:10px;\" onclick=\"adicionarNovoImei();\" title=\"Cadastrar novo bem\"></a></td>
		</tr> </table>";
		echo "<br />";*/
	}
}
else 
{
	if (!mysql_query("UPDATE bem set activated = 'N' WHERE imei = '$inativarVeiculo' and activated = 'S'", $cnx))
	{
		die('Error: ' . mysql_error());
	}
}


// mysql_close($cnx);
?>
