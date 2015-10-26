<?php
include('../seguranca.php');
$cnx = mysql_connect("cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com", "gpstracker", "d1$1793689") or die("Could not connect: " .mysql_error());
mysql_select_db('tracker', $cnx); 

$acao   = $_POST['acao'];
$estilo = $_POST['estilo'];

if($acao == 'alterar' && !empty($estilo)){
	$qyUpdCores = "update preferencias
	                  set estilo = '".$estilo."'";
	$ans = mysql_query($qyUpdCores) or die(mysql_error());
	if ($ans)
	{
		header('location: ../nova_index.php?message=Gravou com sucesso!');
	}
	else
	{
	}
}

?>
<div class="row get-code-window">

<h1>Modificar Tema</h1>	
    <form id="form_usuario" action="ajax/preferencias_cores.php" method="post" class="form-horizontal" role="form">
    	<input type="hidden" name="acao" value="alterar"/>
        <fieldset class="container">
            <div class="row">
                <div class="form-group col-sm-12">
                    <label for="admin">Temas</label>
                    <select name="estilo" id="estilo" class="form-control">
                        <option value="bootstrap1">Azul Claro</option>
                        <option value="bootstrap2">Azul</option>
                        <option value="bootstrap3">Vermelho</option>
                        <option value="bootstrap4">Azul Petróleo</option>
                        <option value="bootstrap5">Azul Real</option>
                        <option value="bootstrap6">Azul e Branco</option>
                        <option value="bootstrap7">Cinza</option>
                        <option value="bootstrap8">Azul e Cinza</option>
                        <option value="bootstrap9">Laranja</option>
			<option value="bootstrap10">Verde</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-sm-12 text-center">
                    <input type="submit" class="btn btn-success btn-lg" value="Salvar" class="form-control">
                    <img style="display:none" src="../imagens/executando.gif" id="usuarioFormExecutando">
                    <img id="usuarioFormSucesso" style="display:none" src="../imagens/sucesso.png">
                    <span id="formError" style="display:none; color:red; font-size:14px;"></span>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<?php
mysql_close($cnx);
?>
