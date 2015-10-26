<?php
include('seguranca.php');
error_reporting(0);

$con = mysql_connect('cloudservice.cgejdsdl842e.sa-east-1.rds.amazonaws.com', 'gpstracker', 'd1$1793689');
mysql_select_db('tracker');

$result      = mysql_query("SELECT * FROM cliente WHERE id_admin = 0");
$dataCliente = mysql_fetch_assoc($result);
$numrows     = mysql_num_rows($result);


$acao          = $_POST['acao'];
$nome          = $_POST['nome'];
$email         = $_POST['email'];
$apelido       = $_POST['login'];
$senha         = $_POST['senha'];
$cpf           = $_POST['cpf'];
$celular       = $_POST['celular'];
$telefone1     = $_POST['telefone1'];
$telefone2     = $_POST['telefone2'];
$cep           = $_POST['cep'];
$endereco      = $_POST['endereco'];
$bairro        = $_POST['bairro'];
$cidade        = $_POST['cidade'];
$estado        = $_POST['estado'];
$rg            = $_POST['rg'];

$id_admin= 0;

if ($acao=='inserir')
{
mysql_query("
		insert into cliente 
		( nome, email, apelido, cpf, celular, telefone1, telefone2, cep, endereco, bairro, cidade, estado, senha, id_admin, rg)
        values
		('$nome','$email','$apelido', '$cpf', '$celular', '$telefone1','$telefone2', '$cep','$endereco','$bairro','$cidade','$estado', '".md5($senha)."', 0, '$rg'") or die(mysql_error());
}
 
if ($acao=='alterar')
{
	$ans = mysql_query("
			update cliente 
			   set nome='$nome', 
			       email='$email', 
				   apelido='$apelido', 
				   cpf='$cpf', 
				   celular='$celular',  
				   telefone1='$telefone1', 
				   telefone2='$telefone2', 
				   cep='$cep', 
				   endereco='$endereco', 
				   bairro='$bairro', 
				   cidade='$cidade', 
				   estado='$estado', 
				   rg='$rg',
				   senha='".md5($senha)."' 
		     where id_admin = 0
	") or die(mysql_error());  
    header('location: ../nova_index.php?message=Gravou com sucess. ');
}

?>
<div class="row get-code-window">

<h1>Informações do Usuário Master</h1>	
    <form id="form_usuario" action="ajax/usuarios_master.php" method="post" class="form-horizontal" role="form">
    	<input type="hidden" name="acao" value="alterar"/>
        <input type="hidden" name="codigo" value="<?=$dataCliente['id']?>" id="cliente_id"/>
        <fieldset class="container">
            <div class="row">
                <div class="form-group col-sm-2">
                    <label for="codigo">Código</label>
                    <input type="text" disabled value="<?=$dataCliente['id']?>" class="form-control" id="codigo" size="10">
                </div>
                <div class="form-group col-sm-5">
                    <label for="nome">Nome</label>
                    <input type="text" value="<?=$dataCliente['nome']?>" name="nome" required size="50" id="nome" class="form-control">
                </div>                
            </div>

            <div class="row">
                <div class="form-group col-sm-3">
                    <label for="email">Email</label>
                    <input type="text" value="<?=$dataCliente['email']?>" name="email" size="50" id="email" class="form-control" required>
                </div>
                <div class="form-group col-sm-2">
                    <label for="login">Login</label>
                    <input type="text" value="<?=$dataCliente['apelido']?>" name="login" id="login" required class="form-control">
                </div>
                <div class="form-group col-sm-2">
                    <label for="senha">Senha</label>
                    <input type="text" name="senha" id="senha" class="form-control">
                </div>
            </div>

            <div class="row">
                
                <div class="form-group col-sm-3">
                    <label for="cpf">CPF/CNPJ</label>
                    <input type="text" value="<?=$dataCliente['cpf']?>" name="cpf" size="20" id="cpf" class="form-control">
                </div>
                <div class="form-group col-sm-3">
                    <label for="rg">RG</label>
                    <input type="text" name="rg" id="rg" value="<?=$dataCliente['rg']?>" required class="form-control">
                </div>               
            </div>

            <div class="row">
                <div class="form-group col-sm-2">
                    <label for="cep">CEP</label>
                    <input type="text" value="<?=$dataCliente['cep']?>" name="cep" size="15" id="cep" class="form-control">
                </div>
                <div class="form-group col-sm-4">
                    <label for="endereco">Endereço</label>
                    <input type="text" value="<?=$dataCliente['endereco']?>" name="endereco" size="50" id="endereco" class="form-control">
                </div>
                <div class="form-group col-sm-2">
                    <label for="bairro">Bairro</label>
                    <input type="text" value="<?=$dataCliente['bairro']?>" name="bairro" id="bairro" class="form-control">
                </div>
                <div class="form-group col-sm-2">
                    <label for="cidade">Cidade</label>
                    <input type="text" value="<?=$dataCliente['cidade']?>" name="cidade" id="cidade" class="form-control">
                </div>
                <div class="form-group col-sm-1">
                    <label for="estado">UF</label>
                    <input type="text" value="<?=$dataCliente['estado']?>" name="estado" maxlength="2" size="2" id="estado" class="form-control">
                </div>
            </div>

           

            <div class="row">
                <div class="form-group col-sm-3">
                    <label for="celular">Celular</label>
                    <input type="tel" value="<?=$dataCliente['celular']?>" name="celular" required size="12" id="celular" class="form-control">
                </div>
                <div class="form-group col-sm-3">
                    <label for="telefone1">Telefone 1</label>
                    <input type="tel" value="<?=$dataCliente['telefone1']?>" name="telefone1" size="12" id="telefone1" class="form-control">
                </div>
                <div class="form-group col-sm-3">
                    <label for="telefone2">Telefone 2</label>
                    <input type="tel" value="<?=$dataCliente['telefone2']?>" name="telefone2" size="12" id="telefone2" class="form-control">
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
