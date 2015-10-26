<?php
  require "helpers.php";
  include_once 'seguranca.php';
  include_once 'usuario/config.php';
  include_once 'config.php';
  $token      = (isset($_POST['token'])) ? $_POST['token'] : false ;
  $auth_user  = isset($_SESSION['logSessioUser']) ? $_SESSION['logSessioUser'] : false;
  $logado     = isset($_SESSION['logSession']) ? $_SESSION['logSession'] : false;

  if (!$logado) {
  	header("Location: index.php");
  	exit();
  }
  $_SESSION['tokenSession'] = $token;	//Se estiver ok, coloca na nessao, e checa sempre na segurança

  $cnx = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
  mysql_select_db($DB_NAME);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>CloudService - TrackService</title>
  <link rel="shortcut icon" href="http://cloudservice.io/img/favicon.png">
  <script>
  var SITE_URL = '<?php echo SITE_URL ?>';
  </script>

  <?php
     $QyTemas = "
	 	select estilo
		  from preferencias
	 ";
	 $rsTemas = mysql_query($QyTemas) or die(mysql_error());
	 $rowTemas = mysql_fetch_assoc($rsTemas);
  ?>
  <!-- Bootstrap core CSS -->
  <link href="css/<?php echo $rowTemas["estilo"];?>.css" rel="stylesheet">

  <!-- Add custom CSS here -->
  <link href="css/sb-admin.css" rel="stylesheet">
  <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/nova_interface.css" rel="stylesheet">
  <!-- Page Specific CSS -->
  <!-- <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css"> -->
  <script type="text/javascript" src="js/listagemAjax.js"></script>
  <link rel="stylesheet" href="css/custom.css">
</head>

<body>
	<div id="wrapper">
    <button type="button" class="btn config-btn">
      <span class="fa fa-cog" aria-hidden="true"></span>
    </button>

    <img class="loading" alt="Carregando..." src="imagens/gif-load.gif" />
    teste
    <img class="logo-cloud-service" alt="CloudService.io" src="imagens/cloud2.png" />

    <!-- Sidebar -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <!-- <a class="navbar-brand" href="index.html">InovarSat</a> -->
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse navbar-ex1-collapse">


        <ul class="nav navbar-nav navbar-right navbar-user">
          <!-- STATUS DO SINAL -->
          <li class="status-sinal" rel="tooltip" title="Logo após o login essa informação pode divergir, aguarde alguns minutos para que seja atualizada."></li>
          <li class="status-ignicao" rel="tooltip" title="Logo após a troca de veículo essa informação pode divergir, aguarde alguns segundos para que seja atualizada."></li>
          <!-- VEÍCULOS -->
          <li class="dropdown veiculos-dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-truck"></i> <span>Veículos</span> <b class="caret"></b></a>
            <ul class="dropdown-menu text-center">
              <form action="" method="POST" class="form-inline" role="form">
                <li>
                  <?php include_once 'menu_veiculos.php'; ?>
                </li>
              </form>
            </ul>
          </li>
          <!-- HODÔMETRO -->
          <?php if (empty($config) || $config->hodometro): ?>
            <li class="dropdown hodometro-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-dashboard"></i> <span>Hodômetro</span> <b class="caret"></b></a>
              <ul class="dropdown-menu text-center">
                <form action="" method="POST" class="form-inline" role="form">
                  <li rel="tooltip" title="Quilometragem do veículo."><input type="text" class="form-control" id="hod_atual" name="hodometro" placeholder="Quilometragem" disabled></li>
                  <li class="divider"></li>
                  <li rel="tooltip" title="Avisar quando este valor for atingido."><input type="text" class="form-control" id="alerta_hodometro" name="alerta_hodometro" placeholder="Avisar a Cada" disabled></li>
                  <li class="divider"></li>
                  <li>
                    <button type="button" class="btn btn-default" onclick="habilitarHodometro()" title="Clique para habilitar a edição dos campos"><i class="fa fa-edit"></i> </button>
                    <button type="button" class="btn btn-primary" onclick="alterarHodometro()" title="Clique para salvar as alterações realizadas" id="enviaHodometro" disabled><i class="fa fa-save"></i> </button>
                  </li>
                </form>
              </ul>
            </li>
          <?php endif ?>
          <!-- COMANDOS -->
          <?php if (empty($config) || $config->comandos): ?>
            <li class="dropdown comandos-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-tasks"></i> <span>Comandos</span> <b class="caret"></b></a>
              <ul class="dropdown-menu text-center">
                <form id="comandos" class="form-inline" role="form">
                  <li rel="tooltip" title="Aplica o comando ao veículo selecionado.">
                    <select name="command" id="command" class="form-control" required>
                      <option value=",B">Rastrear Uma Vez</option>
                      <option value=",C,30s">Rastrear a Cada</option>
                      <option value=",H,060">Velocidade Limite</option>
                      <option value=",G">Alertar Movimento</option>
                      <option value=",E">Cancelar Alerta</option>
                      <option value=",J">Bloquear Combustível</option>
                      <option value=",K">Liberar Combustível</option>
                    </select>
                  </li>
                  <li class="tempo divider hide"></li>
                  <li class="hide">
                    <select name="commandTime" id="commandTime" class="form-control" required>
                      <option value=",C,15s">15 segundos</option>
                      <option value=",C,30s">30 segundos</option>
                      <option value=",C,01m">1 minuto</option>
                      <option value=",C,05m">5 minutos</option>
                      <option value=",C,10m">10 minutos</option>
                      <option value=",C,30m">30 minutos</option>
                      <option value=",C,01h">1 hora</option>
                      <option value=",C,05h">5 horas</option>
                      <option value=",C,10h">10 horas</option>
                    </select>
                  </li>
                  <li class="parametro divider hide"></li>
                  <li class="hide" rel="tooltip" title="Velocidade em Km/h"><input type="text" name="commandSpeedLimit" id="commandSpeedLimit" class="form-control" maxlength="3" placeholder="60"></li>
                  <li class="divider"></li>
                  <button type="button" id="enviarcomando" class="btn btn-primary" disabled><i class="fa fa-upload"></i> Enviar</button>
                </form>
              </ul>
            </li>
          <?php endif ?>
          <!-- CERCA VIRTUAL -->
          <?php if (empty($config) || $config->cerca): ?>
            <li class="dropdown cerca-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bullseye"></i> <span>Cerca Virtual</span> <b class="caret"></b></a>
              <ul class="dropdown-menu text-center" style="width:235px;">
                <form class="form-inline" role="form">
                  <li>
                    <select name="veiculoCerca" id="veiculoCerca" class="form-control" required>
                      <option value="">Escolha</option>
                      <optgroup label="-- VEÍCULOS">
                        <?php
                        $veiculosCerca = mysql_query("SELECT imei, name FROM bem WHERE activated = 'S' AND cliente = $cliente ORDER BY name DESC");
                        while ($row = mysql_fetch_assoc($veiculosCerca)) {
                          echo "<option value='".$row['imei']."'>".$row['name']."</option>";
                        }
                        ?>
                      </optgroup>
                    </select>
                  </li>
                  <li class="divider"></li>
                  <li><button title="Cria uma cerca virtual para o veículo escolhido." type="button" class="btn btn-primary" onclick="modalCerca();"><i class="fa fa-plus-circle"></i> Nova Cerca</button></li>
                  <li class="divider"></li>
                  <?php
                  $listaCercas = mysql_query("SELECT a.name, b.id, b.imei, b.nome, a.identificacao FROM bem a INNER JOIN geo_fence b ON (a.imei = b.imei) WHERE b.disp = 'S' AND a.cliente = $cliente ORDER BY id DESC");
                  while ($cerca = mysql_fetch_array($listaCercas)) {
                    echo "<li id='". (int)$cerca['id'] ."'><span rel='tooltip' title='Clique para editar' onclick='editaCerca(".(int)$cerca['id'].",".$cerca['imei'].");'>". $cerca['name'] ." - ". $cerca['nome'] . "</span> <a href='javascript:void(0);' rel='tooltip' title='Clique para excluir' class='delcerca' onclick='removeCerca(". (int)$cerca['id'] .");'><i class='fa fa-trash-o fa-lg'></i></a></li>";
                  }
                  ?>
                </form>
              </ul>
            </li>
          <?php endif ?>
          <!-- ROTAS -->
          <?php if (empty($config) || $config->rota): ?>
            <li class="dropdown cerca-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-location-arrow"></i> <span>Rotas</span> <b class="caret"></b></a>
              <ul class="dropdown-menu text-center">
                <form class="form-inline" role="form">
                  <li><input type="text" class="form-control" id="inicio_rota" placeholder="De: Origem" required></li>
                  <li class="divider"></li>
                  <li><input type="text" class="form-control" id="destino_rota" placeholder="Para: Destino" required></li>
                  <li class="divider"></li>
                  <li><button type="submit" id="calculaRota" class="btn btn-primary"><i class="fa fa-road"></i> Traçar Rota</button></li>
                </form>
              </ul>
            </li>
          <?php endif ?>
          <!-- ALERTAS -->
          <li class="dropdown alerts-dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> <span>Alertas</span> <span class="badge">0</span> <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <!-- <li><a href="#"><span class="label label-info">NVI8189</span> Desbloqueio Efetuado (09/05/14)</a></li>
              <li><a href="#"><span class="label label-warning">NVI8189</span> Rastreador Desat. (09/05/14)</a></li>
              <li><a href="#"><span class="label label-danger">NVI8189</span> SOS! (09/05/14)</a></li> -->
              <li class="divider"></li>
              <li><a href="#">Marcar Todos Como Visto</a></li>
            </ul>
          </li>
          <!-- USUARIO -->
          <li class="dropdown user-dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <span><?=$nmCliente?></span> <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <?php if (empty($grupo) && (empty($config) || $config->dados)): ?>
                <li><a href="#modal-dados" data-toggle="modal"><i class="fa fa-user"></i> Dados Cadastrais</a></li>
              <?php endif ?>
              <?php if (empty($config) || $config->senha): ?>
                <li><a href="#modal-senha" data-toggle="modal" onclick="limparReveal('#alterar-senha')"><i class="fa fa-key"></i> Senha</a></li>

              <?php endif ?>
              <?php if (empty($config) || $config->grupos): ?>
                <li class="divider"></li>
                <li><a href="#modal-grupos" data-toggle="modal"><i class="fa fa-group"></i> Grupos</a></li>
              <?php endif ?>
            </ul>
          </li>
        </ul>


        <ul class="nav navbar-nav side-nav">
          <?php
            // if (empty($config) || $config->logo) {
            //   echo "<li class='logo text-center'><img src='". LOGO_ADMIN ."' alt='". EMPRESA ."'></li>";
            // }
          ?>
          <li><a href="javascript:void(0)"><i class="fa fa-clock-o fa-lg"></i> Hora do Servidor: <b id="clock">09:44</b></a></li>
          <li><a href="default.php"><i class="fa fa-refresh fa-lg"></i> Recarregar Página</a></li>
          <li><a href="logout.php"><i class="fa fa-power-off fa-lg"></i> Sair do Sistema</a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </nav>

    <div id="page-wrapper">

      <div class="row" style="margin:0">
        <div id="slide-panel" class="hide">
          <button id="opener" class="btn btn-primary" title="Visualizar Itinerário da Rota"><i class="fa fa-list-alt fa-2x"> </i></button>
          <div class="row">

          <div class="col-xs-12" id="directionsPanel"></div>
          </div>
        </div>
        <button id="close-street" type="button" class="btn btn-lg btn-danger hide">&times;</button>

      <div class="col-lg-12" id="map-canvas"></div>
        <div id="historico" class="navbar-inverse text-center col-xs-12 col-sm-10 hide">
          <div class="clear-trace hide">
            <button title="Limpar rota de histórico" type="button" class="btn btn-success" id="erase-trace"><i class="fa fa-eraser"></i></button>
          </div>
          <div class="header navbar-inverse">
            <i class="fa fa-angle-double-up fa-lg"></i> Histórico
          </div>
          <div class="content">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th class="text-center">Data</th>
                    <th class="text-center">Hora</th>
                    <th class="text-center">Latitude</th>
                    <th class="text-center">Longitude</th>
                    <th class="text-center">Velocidade</th>
                    <th class="text-center">Local</th>
                    <th class="text-center">
                      <a class="btn btn-primary" title="Consultar histórico por período" data-toggle="modal" href='#modal-historico'><i class="fa fa-calendar"></i></a>
                      <button type="button" title="Clique para limpar os marcadores" class="btn btn-danger" onclick="limparMapaHist();"><i class="fa fa-eye-slash"></i></button>
                    </th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div><!-- /.row -->
    </div><!-- /#page-wrapper -->
    <!-- MODAL DADOS CADASTRAIS -->
    <div class="modal fade" id="modal-dados">

    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Dados Cadastrais</h3>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            As Informações abaixo são <strong>apenas para fins de consulta</strong>.
            Caso deseje alterá-las solicite ao seu administrador. </div>
          <div class="row">
            <?php
    						$query	 			= mysql_query("SELECT * FROM cliente WHERE id = $cliente");
    						$dadosCadastrais	= mysql_fetch_assoc($query);
    					?>
            <form role="form">
              <div class="form-group">
                <div class="col-lg-6">
                  <label for="nome">Nome</label>
                  <input type="text" class="form-control" id="nome" disabled value="<?=$dadosCadastrais['nome']?>">
                </div>
                <div class="col-lg-6">
                  <label for="email">E-mail</label>
                  <input type="text" class="form-control" id="email" disabled value="<?=$dadosCadastrais['email']?>">
                </div>
              </div>
              <div class="form-group">
                <div class="col-lg-4">
                  <label for="celular">Celular</label>
                  <input type="text" class="form-control" id="celular" disabled value="<?=$dadosCadastrais['celular']?>">
                </div>
                <div class="col-lg-4">
                  <label for="telefone1">Telefone Fixo</label>
                  <input type="text" class="form-control" id="telefone1" disabled value="<?=$dadosCadastrais['telefone1']?>">
                </div>
                <div class="col-lg-4">
                  <label for="telefone2">Telefone Recado</label>
                  <input type="text" class="form-control" id="telefone2" disabled value="<?=$dadosCadastrais['telefone2']?>">
                </div>
              </div>
              <div class="form-group">
                <div class="col-lg-8">
                  <label for="endereco">Endereço</label>
                  <input type="text" class="form-control" id="endereco" disabled value="<?=$dadosCadastrais['endereco']?>">
                </div>
                <div class="col-lg-4">
                  <label for="bairro">Bairro</label>
                  <input type="text" class="form-control" id="bairro" disabled value="<?=$dadosCadastrais['bairro']?>">
                </div>
              </div>
              <div class="form-group">
                <div class="col-lg-6">
                  <label for="cidade">Cidade</label>
                  <input type="text" class="form-control" id="cidade" disabled value="<?=$dadosCadastrais['cidade']?>">
                </div>
                <div class="col-lg-2">
                  <label for="estado">Estado</label>
                  <input type="text" class="form-control" id="estado" disabled value="<?=$dadosCadastrais['estado']?>">
                </div>
                <div class="col-lg-4">
                  <label for="cep">CEP</label>
                  <input type="text" class="form-control" id="cep" disabled value="<?=$dadosCadastrais['cep']?>">
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <!-- <button type="button" class="btn btn-primary" disabled><i class="fa fa-save"></i> Salvar Alterações</button> -->
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- MODAL SENHA -->
    <div class="modal fade" id="modal-senha">

    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Alterar Senha</h3>
        </div>
        <form method="GET" id="alterar-senha" class="form-horizontal" role="form">
          <div class="modal-body">
            <div class="form-group">
              <div class="col-lg-4">
                <label class="control-label" for="senha_atual">Senha Atual</label>
                <input type="password" name="senha_atual" id="senha_atual" class="form-control">
              </div>
              <div class="col-lg-4">
                <label class="control-label" for="nova_senha">Nova Senha</label>
                <input type="password" name="nova_senha" id="nova_senha" class="form-control">
              </div>
              <div class="col-lg-4">
                <label class="control-label" for="repita_senha">Repita a Senha</label>
                <input type="password" name="repita_senha" id="repita_senha" class="form-control">
              </div>
            </div>
            <div id='message' class='row'></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>
            Salvar Alterações</button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- MODAL GRUPOS -->
    <div class="modal fade" id="modal-grupos">

    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Gerenciar Grupos</h3>
        </div>
        <div class="modal-body"> <a href="#submodal-grupos" onclick="obterDados(0);" data-toggle="modal" class="btn btn-success"><i class="fa fa-plus-circle"></i>
          Novo Grupo</a>
          <div class="table-responsive">
            <table class="table table-hover table-striped">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>Ações</i></th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $resGrupos = mysql_query("SELECT id, nome FROM grupo WHERE cliente = $cliente");
                  while ($dadosGrupo = mysql_fetch_assoc($resGrupos)) {
                    echo "
                      <tr>
                        <td>" . $dadosGrupo['nome'] . "</td>
                        <td>
                          <a href='#submodal-grupos' onclick='obterDados(". $dadosGrupo['id'] .", \"". $dadosGrupo['nome'] ."\");' data-toggle='modal' rel='tooltip' title='Editar' class='btn btn-sm btn-primary'><i class='fa fa-pencil fa-fw'></i></a>
                          <a href='javascript:void(0);' remover='". $dadosGrupo['id'] ."' rel='tooltip' title='Excluir' class='del-group btn btn-sm btn-danger'><i class='fa fa-trash-o fa-fw'></i></a>
                        </td>
                      </tr>
                    ";
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- SUBMODAL DE GRUPOS -->
    <div class="modal fade" id="submodal-grupos">

    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Editar Grupo</h4>
        </div>
        <!-- FORMULÁRIO -->
        <form id="enviarGrupos" class="form-horizontal" role="form">
          <div class="modal-body">
            <input type="hidden" name="id_grupo" id="id_grupo">
            <div class="form-group">
              <label for="nome_grupo" class="col-sm-2 control-label">Nome:</label>
              <div class="col-sm-5">
                <input type="text" name="nome_grupo" id="nome_grupo" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label for="senha_grupo" class="col-sm-2 control-label">Senha:</label>
              <div class="col-sm-5">
                <input type="text" name="senha_grupo" id="senha_grupo" class="form-control">
              </div>
            </div>
            <div class="form-group">
              <label for="veiculos_grupo" class="col-sm-2 control-label">Veículos:</label>
              <div class="col-sm-5">
                <select multiple name="veiculos_grupo[]" id="veiculos_grupo" class="form-control" rel="tooltip" title="Segure CTRL para selecionar vários">
                  <option value=""></option>
                </select>
              </div>
            </div>
            <div id='message-grupos' class='row'></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>
            Salvar Alterações</button>
          </div>
        </form>
        <!-- FIM DO FORMULÁRIO -->
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- MODAL CERCA VIRTUAL -->
    <div class="modal fade" id="modal-cerca">
      <div class="modal-dialog modal-lg">

      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Cerca Virtual</h3>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-6">
              <form id="form-cerca" class="form-inline" role="form">
                <div class="form-group">
                  <label class="sr-only" for="">label</label>
                  <input type="text" id="nomeCerca" name="nomeCerca" class="form-control" placeholder="Nome da cerca">
                </div>
                <button type="button" id="apagarPontos" class="btn btn-default"><i class="fa fa-eraser"></i>
                Apagar Pontos</button>
              </form>
              <div id="instrucoes" class="alert alert-info hide">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Instruções</strong>
                <ol>
                  <li>Clique e arraste os círculos arredondados nas bordas da
                    cerca para alterá-la.</li>
                  <li>Clique em qualquer local dentro da área da cerca para confirmar
                    as modificações.</li>
                </ol>
              </div>
            </div>
            <div class="col-sm-6">
              <div id="message-cerca"></div>
            </div>
          </div>
          <div id="loader-cerca"><img src="imagens/loader.gif" class="img-responsive" alt="Carregando..."></div>
          <div id="mapa-cerca"></div>
          <!-- <div class="alert alert-info">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <strong>Informação</strong>
              <ol>
                <li>Defina um nome para a cerca.</li>
                <li>Desenhe a cerca no mapa clicando nos locais onde deseja criar os pontos.</li>
                <li>Caso a figura desenhada não fique como o esperado, você pode a qualquer momento clicar em "<i class="fa fa-eraser"></i> Apagar Pontos"</li>
                <li>Clique em "<i class="fa fa-save"></i> Salvar Cerca" para finalizar.</li>
              </ol>
            </div> -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" id="salvarCerca" class="btn btn-primary"><i class="fa fa-save"></i>
          Salvar Cerca</button>
        </div>
      </div>
      <!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- MODAL CONSULTA HISTÓRICO -->
    <div class="modal fade" id="modal-historico">
      <div class="modal-dialog modal-lg">

      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Histórico por Período</h3>
        </div>
        <div class="modal-body">
          <form method="POST" action="listagem_historico_novo.php" class="form-inline" role="form" id="consultarHistorico">
            <input type="hidden" name="nrImeiConsulta" id="nrImeiConsulta">
            <input type="hidden" name="nomeVeiculo" id="nomeVeiculo">
            <input type="hidden" name="mnDataInicio" value="00">
            <input type="hidden" name="mnDataFinal" value="00">
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <label for="commandDateIni">Início: </label>
                  <input type="date" class="form-control" id="commandDateIni" name="txtDataInicio" value="<?php echo date("Y-m-d") ?>" max="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="form-group">
                  <label class="sr-only" for="commandHourTimeIni">Hora</label>
                  <select name="hrDataInicio" id="commandHourTimeIni" class="form-control">
                    <option value="0">00h</option>
                    <option value="1">01h</option>
                    <option value="2">02h</option>
                    <option value="3">03h</option>
                    <option value="4">04h</option>
                    <option value="5">05h</option>
                    <option value="6">06h</option>
                    <option value="7">07h</option>
                    <option value="8">08h</option>
                    <option value="9">09h</option>
                    <option value="10">10h</option>
                    <option value="11">11h</option>
                    <option value="12">12h</option>
                    <option value="13">13h</option>
                    <option value="14">14h</option>
                    <option value="15">15h</option>
                    <option value="16">16h</option>
                    <option value="17">17h</option>
                    <option value="18">18h</option>
                    <option value="19">19h</option>
                    <option value="20">20h</option>
                    <option value="21">21h</option>
                    <option value="22">22h</option>
                    <option value="23">23h</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label for="commandDateFim">Fim: </label>
                  <input type="date" class="form-control" id="commandDateFim" name="txtDataFinal" value="<?php echo date("Y-m-d") ?>" max="<?php echo date("Y-m-d") ?>">
                </div>
                <div class="form-group">
                  <label class="sr-only" for="commandHourTimeIni">Hora</label>
                  <select name="hrDataFinal" id="commandHourTimeFim" class="form-control">
                    <option value="0">00h</option>
                    <option value="1">01h</option>
                    <option value="2">02h</option>
                    <option value="3">03h</option>
                    <option value="4">04h</option>
                    <option value="5">05h</option>
                    <option value="6">06h</option>
                    <option value="7">07h</option>
                    <option value="8">08h</option>
                    <option value="9">09h</option>
                    <option value="10">10h</option>
                    <option value="11">11h</option>
                    <option value="12">12h</option>
                    <option value="13">13h</option>
                    <option value="14">14h</option>
                    <option value="15">15h</option>
                    <option value="16">16h</option>
                    <option value="17">17h</option>
                    <option value="18">18h</option>
                    <option value="19">19h</option>
                    <option value="20">20h</option>
                    <option value="21">21h</option>
                    <option value="22">22h</option>
                    <option value="23">23h</option>
                  </select>
                </div>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-lg-9">
                <div class="form-group">
                  <button type="button" class="btn btn-default" onclick="acertaHistorico(4);">Últimas
                  4 horas</button>
                  <button type="button" class="btn btn-default" onclick="acertaHistorico(12);">Últimas
                  12 horas</button>
                  <button type="button" class="btn btn-default" onclick="acertaHistorico(24);">Últimas
                  24 horas</button>
                  <button type="button" class="btn btn-default" onclick="acertaHistorico(48);">Últimas
                  48 horas</button>
                  <select id="cmbLimite" name="cmbLimite" class="form-control">
                  	<option value="20">20</option>
                    <option value="40">40</option>
                    <option value="60">60</option>
                    <option value="80">80</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                    <option value="2000">2000</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-3 text-center">
                <div class="form-group">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-book"></i>
                  Consultar Histórico</button>
                </div>
              </div>
            </div>
          </form>
          <hr>
          <div id="relatorio"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
      <!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </div><!-- /#wrapper -->



  <!-- JavaScript -->
  <script src="js/jquery-1.10.2.js"></script>
  <script src="js/bootstrap.js"></script>
  <script src="js/jquery.validate.min.js"></script>
  <script src="js/bootstrap-waitingfor.js"></script>


  <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAHtRwtz8ReaYTsyPOD83ojXSgcGw-koe0&amp;sensor=false"></script>
  <script src="js/markerwithlabel.js" type="text/javascript"></script>
  <script src="js/polygon.min.js" type="text/javascript"></script>
  <script src="js/latlong.js" type="text/javascript"></script>
  <script src="js/geo.js" type="text/javascript"></script>
  <script type="text/javascript">
	var intervalTraceRoute;
	var counterTrace = 0;
	var markers;
	var path;
	var poly;
	var lat_lng = [];
    var latlngbounds = new google.maps.LatLngBounds();
	var estadoIgnicao;

  	$(document).ready(function () {
  		/*Determina as funções inicializadas com o carregamento da página */
      moveRelogio();
  		verificarAlertas();

      /*VALIDAÇÃO DO FORMULÁRIO DE SENHAS */
      $("#alterar-senha").validate({
        // Define as regras
        rules: {
          senha_atual: { required: true },
          nova_senha: { required: true, minlength: 3 },
          repita_senha: { required: true, minlength: 3, equalTo: 'input#nova_senha' }
        },
        // Define as mensagens de erro
        messages: {
          senha_atual: { required: "Informe a senha atual" },
          nova_senha: { required: "Informe uma nova senha", minlength: "Mínimo de {0} caracteres" },
          repita_senha: { required: "Confirme a senha", minlength: "Mínimo de {0} caracteres", equalTo: "A nova senha não coincide" }
        },
        submitHandler: function (form) {
          $.ajax({
            type: "GET",
            url: 'alterar_senha.php',
            data: $(form).serialize(),
            success: function (status) {
              $('#message').html("<p class='text-center'><img src='imagens/loader.gif' alt='Carregando..'></p>")
                .hide()
                .fadeIn(1500, function () {
                  $('#message').append(status);
                  $('#message p').remove();
                  $('#alterar-senha').each(function () { this.reset(); });
              });
            }
          });
          return false;
        }
      });

      /*VALIDAÇÃO DO FORMULÁRIO DE GRUPOS*/
      $("#enviarGrupos").validate({
        // Define as regras
        rules: {
          nome_grupo: { minlength: 3, required: true },
          senha_grupo: { minlength: 3 },
          veiculos_grupo: { required: true }
        },
        messages: {
          nome_grupo: {
            required: "Informe um nome para o grupo",
            minlength: "Mínimo de {0} caracteres"
          },
          senha_grupo: { minlength: "Mínimo de {0} caracteres" },
          veiculos_grupo: { required: "Selecione ao menos um veículo"}
        },
        submitHandler: function (form) {
          $.ajax({
            url: "novo_grupos.php",
            type: "GET",
            data: $(form).serialize(),
            success: function (resposta) {
              $('#message-grupos').html("<p class='text-center'><img src='imagens/loader.gif' alt='Carregando..'></p>")
                .hide()
                .fadeIn(1500, function () {
                  $('#message-grupos').append(resposta);
                  $('#message-grupos p').remove();
                  // Se cadastrado com êxito, adiciona o item na tabela de listagem
                  if (resposta == "<div class='alert alert-success'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Sucesso! </strong>O grupo foi cadastrado com êxito.</div>") {
                    // Requesita ao arquivo o ID do grupo inserido
                    var nome = $('#nome_grupo').val();
                    $.get('novo_grupos.php',{'id_inserido': nome}, function (id_retornado) {
                      $('#modal-grupos .modal-body table tbody').append("<tr><td>" + nome + "</td> <td> <a href='#submodal-grupos' onclick=\"obterDados(" + id_retornado + ", '" + nome + "');\" data-toggle='modal' rel='tooltip' title='Editar' class='btn btn-sm btn-primary'><i class='fa fa-pencil fa-fw'></i></a> <a href='javascript:void(0);' remover='" + id_retornado + "' rel='tooltip' title='Excluir' class='del-group btn btn-sm btn-danger' disabled><i class='fa fa-trash-o fa-fw'></i></a> </td> </tr>");
                    });
                  }
              });
            }
          });
          return false;
        }
      });

      // VALIDAÇÃO DO FORMULÁRIO DE CONSULTA DE HISTÓRICO
      $("#consultarHistorico").validate({
        submitHandler: function (form) {
          // Desabilita o botão de submit para evitar várias requisições
          $("#consultarHistorico button[type=submit]").prop('disabled', true);
          $("#consultarHistorico button[type=submit] i").removeClass('fa-book').addClass('fa-refresh fa-spin');
          // Adiciona o IMEI e o NOME do veiculo selecionado aos inputs do formulário
          $("#nrImeiConsulta").val($('#bens').val());
          $("#nomeVeiculo").val($("#bens").find(":selected").text());
          $.ajax({
            url: "listagem_historico_novo.php",
            type: "POST",
            data: $(form).serialize(),
            success: function (lista) {
              // Exibe a tabela de resultados e põe o botão no estado normal.
              $("#relatorio").html(lista);
              $("#consultarHistorico button[type=submit]").prop('disabled', false);
              $("#consultarHistorico button[type=submit] i").removeClass('fa-refresh fa-spin').addClass('fa-book');
              // Inicializa as variáveis que serão utilizadas para ler os dados da tabela
              var latAnt = 0;
              var latAtu = 0;
              var lonAtu = 0;
              var lonAnt = 0;
              var distance = 0.00;
              var tabRel = $('#relatorio table').find('tbody').find('tr');
              var latAtu = $(tabRel[0]).find('td:eq(1)').html();
              var lonAtu = $(tabRel[0]).find('td:eq(2)').html();
              // Checa se houve mais de um resultado retornado
              if (tabRel.length > 2) {
                // Limpa o array com as posições de latitude e longitude da tabela
                while(posicoes.length > 0) {
                  posicoes.pop();
                }

                for (var i = 1; i < tabRel.length - 1; i++) {
                  // Cria um array com as latitudes e longitudes para ser usado no traçado da rota
                  posicoes.push({
                    'lat': latAtu,
                    'lng': lonAtu
                  });

                  // Calcula a quilometragem rodada
                  var latAnt = $(tabRel[i]).find('td:eq(1)').html();
                  var lonAnt = $(tabRel[i]).find('td:eq(2)').html();

                  var p1 = new LatLon(latAtu, lonAtu);
                  var p2 = new LatLon(latAnt, lonAnt);

                  distance += parseFloat(p1.distanceTo(p2));
                  //console.log('Distancia calculada ' + i + ' ' + distance);

                  latAtu = latAnt;
                  lonAtu = lonAnt;
                }

                $('#km-rodado').html(parseInt(distance) + ' Km rodados');
                $('#tracar').removeClass('hide');
              }
              else {
                console.log('Apenas um registro na tabela');
                $('#tracar').addClass('hide');
              }

              // document.getElementById('total_km').innerHTML = parseInt(distance)+' km';
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
              console.log(XMLHttpRequest);
              console.log(textStatus);
              console.log(errorThrown);
              $("#consultarHistorico button[type=submit]").prop('disabled', false);
              $("#consultarHistorico button[type=submit] i").removeClass('fa-refresh fa-spin').addClass('fa-book');
            }
          });
          return false;
        }
      });

      /*ROTINAS DO TRAÇAR ROTA */
      $("#calculaRota").click(function (e) {
        e.preventDefault();
        calcRoute();
        $("#slide-panel").removeClass('hide');
      });

      /*ROTINAS PARA VISUALIZAÇÃO DO ETINERÁRIO DAS ROTAS*/
      $('#opener').on('click', function() {
        var panel = $('#slide-panel');
        if (panel.hasClass("visible")) {
          panel.removeClass('visible').animate({'margin-right':'-300px'});
        } else {
          panel.addClass('visible').animate({'margin-right':'0px'});
        }
        return false;
      });

      /*EXIBE OS INPUTS ADICIONAIS PARA ALGUNS COMANDOS*/
      $('#command').change(function () {
        var valor = $(this).val();
        if (valor == ',C,30s') {
          $('li.tempo').removeClass('hide');
          $('#commandTime').parent('li').removeClass('hide');
        }
        else {
          $('li.tempo').addClass('hide');
          $('#commandTime').parent('li').addClass('hide');
        }

        if (valor == ',H,060') {
          $('li.parametro').removeClass('hide');
          $('#commandSpeedLimit').parent('li').removeClass('hide');
        }
        else {
          $('li.parametro').addClass('hide');
          $('#commandSpeedLimit').parent('li').addClass('hide');
        }
      });

      /*ALTERA A DISPONIBILIDADE DO ENVIO DE COMANDOS*/
      $('.comandos-dropdown').click(function () {
        if ($('#bens').val()) $('#enviarcomando').prop('disabled', false);
        else $('#enviarcomando').prop('disabled', true);
      });

      /*ENVIA O COMANDO PARA O RASTREADOR*/
      $('#enviarcomando').click(function (e) {
        e.preventDefault();
        $('#enviarcomando i').removeClass('fa-upload').addClass('fa-refresh fa-spin');

        var imei                = $('#bens').val();
        var nomeBem             = $('#bens option:selected').text();
        var comandoSelecionado  = $('#command').val();
        var intervaloComando    = $('#commandTime').val();
        var velocidadeLimite    = $('#commandSpeedLimit').val();

        $.ajax({
          url: "menu_comandos.php",
          type: "POST",
          data: {'imei': imei, 'command': comandoSelecionado, 'commandTime': intervaloComando, 'commandSpeedLimit': velocidadeLimite },
          success: function (resultComandos) {
            if (resultComandos == "OKOK") {
              $('#enviarcomando').addClass('btn-success');
              $('#enviarcomando > i').removeClass('fa-refresh fa-spin').addClass('fa-check');
              setTimeout(function () {
                $('#enviarcomando').removeClass('btn-success');
                $('#enviarcomando > i').removeClass('fa-check').addClass('fa-upload');
              }, 3000);
            }
            else {
              $('#enviarcomando').addClass('btn-danger');
              $('#enviarcomando > i').removeClass('fa-refresh fa-spin').addClass('fa-times');
              setTimeout(function () {
                $('#enviarcomando').removeClass('btn-danger');
                $('#enviarcomando > i').removeClass('fa-times').addClass('fa-upload');
              }, 3000);
              console.log(resultComandos);
              alert('Ops! Não foi possível executar o comando.');
            }
          }
        });
      });

      /**
       * [Deleta o grupo escolhido.]
       */
      $('.del-group').click(function () {
        var botao = $(this);
        var id = botao.attr('remover');
        botao.children('i').removeClass('fa-trash-o').addClass('fa-refresh fa-spin');
        botao.attr('disabled', true);

        $.ajax({
          url: "novo_grupos.php",
          type: "GET",
          data: { id_grupo: id, acao: 'remover' },
          success: function (resposta) {
            if (resposta == "OK") {
              botao.children('i').removeClass('fa-refresh fa-spin').addClass('fa-check');
              setTimeout(function () {
                botao.parent().parent('tr').remove();
              },2000);
            }
            else {
              botao.children('i').removeClass('fa-refresh fa-spin').addClass('fa-trash-o');
              botao.attr('disabled', false);
              alert(resposta);
            }
          }
        });
      });

      // ADICIONAM O TOOLTIP
      $("a[rel=tooltip], .status-sinal").tooltip({ placement: 'bottom' });
	  $("a[rel=tooltip], .status-ignicao").tooltip({ placement: 'bottom' });
      $("li[rel=tooltip], select[rel=tooltip], .delcerca").tooltip({ placement: 'right' });
      $("span[rel=tooltip]").tooltip({ placement: 'left' });
  	});

    $(window).resize(function () {
      posicionaHistorico();
    });
    /**
     * [posicoes Global que guarda as latitudes e longitudes retornadas na tabela de histórico]
     * @type {Array}
     */
    var posicoes = [];

    /*********************************
     * Efeito Slide do Histórico
     ********************************/
    function posicionaHistorico () {
      if ($("#bens").val()) {
        $("#historico").removeClass('hide');
        // var altura = ($("#historico").height() - $("#historico .header").height()) * -0.95;
        // // console.log(altura);
        // $("#historico").css({ 'bottom': altura });
        //
        // $("#historico .header").click(function () {
        //
        //
        //   var posicao = parseInt($("#historico").css('bottom'));
        //   // var novaAltura = posicao < 0 ? 0 : altura;
        //   if (posicao < 0) {
        //     novaAltura = 0;
        //     $("#historico .header i").removeClass('fa-angle-double-up').addClass('fa-angle-double-down');
        //   }
        //   else {
        //     novaAltura = altura;
        //     $("#historico .header i").removeClass('fa-angle-double-down').addClass('fa-angle-double-up');
        //   }
        //   $("#historico").animate({ bottom: novaAltura });
        //});
      }
      else $("#historico").addClass('hide');
    }

    /**
     * [acertaHistorico Atualiza os campos de data e hora de acordo com o intervalo escolhido]
     * @param  {int} horas Intervalo que deseja ser visto
     */
    function acertaHistorico (horas) {
      horaIni = $('#commandHourTimeIni');
      horaFim = $('#commandHourTimeFim');
      dataIni = $('#commandDateIni');

      horaAtual = new Date();
      horaFim.val(horaAtual.getHours());

      if (horaAtual.getHours() - horas < 0) {
        horaIni.val(24 - (horas - horaAtual.getHours()));
        dia = horaAtual.getDate() - 1;
        mes = horaAtual.getMonth() + 1;
        if (dia < 0 || dia === 0) {
          dia = 1;
          mes = mes - 1;
        }
        dataIni.val(horaAtual.getFullYear() + '-' + ('0' + mes).slice(-2) + '-' + ('0' + dia).slice(-2));
        if (horas == 48) {
          dataIni.val(horaAtual.getFullYear() + '-' + ('0' + mes).slice(-2) + '-' + ('0' + (dia - 1)).slice(-2));
          horaIni.val(horaAtual.getHours());
        }
      } else {
        dataIni.val(horaAtual.getFullYear() + '-' + ('0' + (horaAtual.getMonth() + 1)).slice(-2) + '-' + ('0' + horaAtual.getDate()).slice(-2));
        horaIni.val(horaAtual.getHours() - horas);
      }
    }

    /**
     * [exibirListagemHistorico description]
     * @param  {int} imei Número do IMEI do veículo selecionado
     */
    function exibirListagemHistorico (imei) {
      $.ajax({
        url: "listagem_nova_interface.php",
        type: "GET",
        data: { 'imei': imei },
        success: function (result) {
          $("#historico .content table tbody").html(result);
          posicionaHistorico();
        }
      });
    }

    /**
     * [imprimirHistorico Imprime a tabela de histórico]
     */
    function imprimirHistorico () {
      var data = $('#areaImpressa').html();
      // var mywindow = window.open('', 'Imprimir Histórico', 'height=500,width=950, scrollbars=1');

      var mywindow = window.open('', 'Histórico de Localização', 'left=200, width=950, height=500, scrollbars=1');
      mywindow.document.write('<html><head><title>Histórico de Localização</title>');
      mywindow.document.write('<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">');
      mywindow.document.write('</head><body>');
      mywindow.document.write(data);
      mywindow.document.write('</body></html>');

      mywindow.addEventListener('load', function(){
          mywindow.print();
          // printWindow.close();
      }, true);
    }

    /**
     * [limparReveal Remove as mensagens de validação das DIVs popup]
     * @param  {string} idReveal Nome do ID (CSS) do form a ser resetado
     */
    function limparReveal (idReveal) {
      $('#message').html("");
      $(idReveal).each(function () { this.reset(); });
      $(idReveal).find('input').removeClass('valid error');
      $(idReveal).find('label.error').remove();
    }

  	/**
  	 * [moveRelogio Função para atualizar o relógio de acordo com a hora do servidor]
  	 * @return {[string]} [Horário do servidor]
  	 */
  	function moveRelogio () {
  		momentoAtual = new Date();
  		hora = momentoAtual.getHours();
  		minuto = momentoAtual.getMinutes();

  		str_minuto = new String (minuto);
  		if (str_minuto.length == 1) minuto = "0" + minuto;

  		str_hora = new String (hora);
  		if (str_hora.length == 1) hora = "0" + hora;

  		horaImprimivel = hora + "h" + minuto; //+ ":" + segundo

  		$("#clock").html(horaImprimivel);

  		setTimeout("moveRelogio()",1000);
  	}

  	/**
  	 * [verificarAlertas Obtém todos os alertas para o usuário]
  	 * @return {[array]} [Array com o HTML contendo placa, mensagem, e data]
  	 */
  	function verificarAlertas () {
  		$.ajax({
  			url: "menu_alertas.php",
  			type: "GET",
  			dataType: "JSON",
  			success: function (alertas) {
  				$(".alerts-dropdown ul").html(alertas.lista);
  				$(".alerts-dropdown .badge").html(alertas.count);
  			}
  		});
  	}

  	/**
  	 * [fecharAlerta Define o status da mensagem como visualizado]
  	 * @param  {string} message Nome do alerta gravado no banco
  	 * @param  {string} imei    Número de IMEI relacionado a mensagem
  	 * @return {boolean}        TRUE - se visualizar sem erros | FALSE - erros no banco
  	 */
  	function fecharAlerta (message, valor_imei) {
  		$.ajax({
  			url: "menu_alertas.php",
  			type: "GET",
  			data: { fechar: message, imei: valor_imei },
  			success: function () {
  				verificarAlertas();
  			}
  		});
  	}

    /**
     * [habilitarHodometro Habilita os campos para alteração das informações de alerta de quilometragem]
     */
    function habilitarHodometro () {
      try {
        if ($('#bens').val() == "") {
          $('#hod_atual').val("").prop('disabled', true);
          $('#alerta_hodometro').val("").prop('disabled', true);
          $('#enviaHodometro').prop('disabled', true);
          alert('Para realizar esta ação selecione um veículo.');
        }
        else {
          $('#hod_atual').prop('disabled', false);
          $('#alerta_hodometro').prop('disabled', false);
          $('#enviaHodometro').prop('disabled', false);
        }
      }
      catch(error) {
        alert('Selecione um veículo! ' + error);
      }
    }

    /**
     * [alterarHodometro Altera as informações de alerta de quilometragem]
     */
    function alterarHodometro () {
      try {
        $('#enviaHodometro > i').removeClass('fa-save').addClass('fa-refresh fa-spin');

        var hodometro = $('#hod_atual').val();
        var alerta_hodometro = $('#alerta_hodometro').val();
        var numImei = $('#bens').val();

        $.ajax({
          url: "menu_hodometro.php",
          type: "GET",
          data: { imei: numImei, acao: 'salvar_hodometro', hodometro: hodometro, alerta_hodometro: alerta_hodometro },
          success: function (dataHodometro) {
            console.log(dataHodometro);
            if (dataHodometro == "OK") {
              $('#enviaHodometro').addClass('btn-success');
              $('#enviaHodometro > i').removeClass('fa-refresh fa-spin').addClass('fa-check');
              $('#hod_atual').prop('disabled', true);
              $('#alerta_hodometro').prop('disabled', true);
              $('#enviaHodometro').prop('disabled', true);
              setTimeout(function () {
                $('#enviaHodometro').removeClass('btn-success');
                $('#enviaHodometro > i').removeClass('fa-check').addClass('fa-save');
              }, 3000);
            }
            else {
              $('#enviaHodometro').addClass('btn-danger');
              $('#enviaHodometro > i').removeClass('fa-refresh fa-spin').addClass('fa-times');
              alert(dataHodometro);
              setTimeout(function () {
                $('#enviaHodometro').removeClass('btn-danger');
                $('#enviaHodometro > i').removeClass('fa-times').addClass('fa-save');
              }, 3000);
            }
          }
        });
      }
      catch (error){
        alert('Ops! Algo deu errado: '+ error);
      }
    }

    /**
     * [obterDados Obtém a listagem de veículos exibida no select dos grupos]
     * @param  {int} id   ID do grupo a ser alterado
     * @param  {string} nome Nome do grupo a ser alterado
     */
    function obterDados (id, nome) {
      if (!id) $('#submodal-grupos h4').html("Cadastrar Grupo");
      else $('#submodal-grupos h4').html("Editar Grupo");

      $('#message-grupos').html("");

      $.ajax({
        url: "novo_grupos.php",
        type: "GET",
        data: { id_grupo: id, acao: 'dados' },
        dataType: "JSON",
        success: function (grupo) {
          $('#id_grupo').val(id);
          $('#nome_grupo').val(nome);
          $('#veiculos_grupo').html(grupo);
        }
      })
    }

    /**
     * [modalCerca Chama a DIV Modal e cria um mapa para criação de uma nova cerca]
     */
    function modalCerca () {
      $('#form-cerca').removeClass('hide');
      $('#instrucoes').addClass('hide');
      $('#salvarCerca').prop('disabled', false);
      var imei = $('#veiculoCerca').val();

      if (imei) {
        $('#nomeCerca').val('');
        $('#message-cerca').html('');
        $('#mapa-cerca').fadeTo('fast', 1);

        var aracaju = new google.maps.LatLng(-10.947765,-37.072953);
        var opcoesCerca = {
          zoom: 15,
          center: posVeiculoCerca,//aracaju,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        map = new google.maps.Map(document.getElementById('mapa-cerca'), opcoesCerca);

        google.maps.event.addDomListener(window, "resize", function() {
         var center = map.getCenter();
         google.maps.event.trigger(map, "resize");
         map.setCenter(center);
        });

        google.maps.event.addListenerOnce(map, 'idle', function(){
          var center = map.getCenter();
          google.maps.event.trigger(map, 'resize');
          map.setCenter(center);
        });

        var creator = new PolygonCreator(map);

        // BOTAO PARA APAGAR OS PONTOS DA CERCA
        $('#apagarPontos').click(function(){
          creator.destroy();
          creator=null;
          creator=new PolygonCreator(map);
        });

        /*BOTÃO DE ENVIO DA CERCA VIRTUAL*/
        $('#salvarCerca').click(function() {
          var nome = $('#nomeCerca').val();
          if (nome == "" || nome == null) {
            window.alert("Informe um nome para a cerca");
          } else {
            if (null == creator.showData()) {
              window.alert("Você deve fechar o polígono.");
            } else {
              $('#mapa-cerca').fadeTo('fast', 0.4);
              // window.location.href = "incluir_cerca.php?imei=" + imei + "&latitude=" + latitude + "&longitude=" + longitude + "&NomeCerca=" + nome + "&cerca=" + creator.showData() + "&tipoAcao=" + tipoAcao + "&tipoEnvio=" + tipoEnvio;
              var pontos = creator.showData();
              $.ajax({
                url: "incluir_cerca.php",
                type: "GET",
                data: { 'imei': imei, 'NomeCerca': nome, 'cerca': pontos },
                success: function (resCerca) {
                  if (resCerca == 'OK') {
                    $('#nomeCerca').val('');
                    $('#apagarPontos').click();
                    setTimeout(function () {
                      $('#mapa-cerca').fadeTo('fast', 1);
                      $('#message-cerca').html("<div class='alert alert-success'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Sucesso!</strong> A cerca foi criada com êxito. </div> </div>");
                    },1500);
                  }
                  else {
                    $('#mapa-cerca').fadeTo('fast', 1);
                    $('#message-cerca').html("<div class='alert alert-danger'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Erro: </strong> Ops! Algo deu errado... </div> </div>");
                    console.log(resCerca);
                  }
                }
              });
            }
          }
        });

        $('#form-cerca').submit(function (e) {
          e.preventDefault();
        });

        $('#modal-cerca').modal({show:true});
      }
      else {
        $('.cerca-dropdown button').addClass('btn-warning');
        $('.cerca-dropdown button i').removeClass('fa-plus-circle').addClass('fa-warning');
        setTimeout(function () {
          $('.cerca-dropdown button').removeClass('btn-warning');
          $('.cerca-dropdown button i').removeClass('fa-warning').addClass('fa-plus-circle');
        },1500);
      }
    }

    /**
     * [editaCerca Realiza a edição dos pontos da cerca]
     * @param  {inteiro} value Número formado pelo ID da cerca e IMEI do veículo
     */
    function editaCerca (id, imei) {
      $.ajax({
        url: "editar_cercanovo.php",
        type: "GET",
        data: { "imei": imei, 'id': id },
        dataType: "JSON",
        success: function (result) {
          $('#form-cerca').addClass('hide');
          $('#instrucoes').removeClass('hide');
          $('#salvarCerca').prop('disabled', true);

          var triangleCoords = [
            new google.maps.LatLng(result.latCoord[0],result.lngCoord[0]),
            new google.maps.LatLng(result.latCoord[1],result.lngCoord[1]),
            new google.maps.LatLng(result.latCoord[2],result.lngCoord[2]),
            new google.maps.LatLng(result.latCoord[3],result.lngCoord[3])
          ];

          //var aracaju = new google.maps.LatLng(-10.947765,-37.072953);
          var geoFenceInitialPoint = new google.maps.LatLng(result.latCoord[0],result.lngCoord[0]);
		  var opcoesCerca = {
            zoom: 12,
            center: geoFenceInitialPoint,//aracaju,
            mapTypeId: google.maps.MapTypeId.ROADMAP
          }
          map = new google.maps.Map(document.getElementById('mapa-cerca'), opcoesCerca);

          var bermudaTriangle = new google.maps.Polygon({
            paths: triangleCoords,
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 3,
            fillColor: "#FF0000",
            fillOpacity: 0.35,
            editable: true
          });

          bermudaTriangle.setMap(map);

          google.maps.event.addDomListener(window, "resize", function() {
           var center = map.getCenter();
           google.maps.event.trigger(map, "resize");
           map.setCenter(center);
          });

          google.maps.event.addListenerOnce(map, 'idle', function(){
            var center = map.getCenter();
            google.maps.event.trigger(map, 'resize');
            map.setCenter(center);
          });

          // Add a listener for the click event
          google.maps.event.addListener(bermudaTriangle, 'click', showArrays);
          infowindow = new google.maps.InfoWindow();

          function showArrays(event) {
            var imei = result.imei;
            var id = result.id;
            var latitude = result.latitude;
            var longitude = result.longitude;

            // Since this Polygon only has one path, we can call getPath()
            // to return the MVCArray of LatLngs
            var vertices = this.getPath();
            var contentString = "latitude="+ latitude +"&longitude="+ longitude +"&imei="+ imei +"&id="+ id +"&coordenadas=";

              // Iterate over the vertices.
            for (var i = 0; i < vertices.length; i++) {
            var xy = vertices.getAt(i);
              if (i+1 == vertices.length){
                contentString += xy.lat() +"," + xy.lng();
              } else {
                contentString += ''+ xy.lat() +"," + xy.lng() +'|';
              }
            }

            decisao = confirm("Deseja gravar o perímetro? ");
            if ( decisao ) {
              $('#mapa-cerca').fadeTo('fast', 0.4);
              $.ajax({
                url: "alterar_cerca.php?" + contentString,
                type: "GET",
                success: function (retorno) {
                  console.log(retorno);
                  if (retorno == "OK") {
                    setTimeout(function () {
                      $('#mapa-cerca').fadeTo('fast', 1);
                      $('#message-cerca').html("<div class='alert alert-success'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Sucesso!</strong> A cerca foi alterada com êxito. </div> </div>");
                      setTimeout(function () {
                        $('#message-cerca').html("");
                      },3000);
                    },1500);
                  }
                }
              });
              // location.href="alterar_cerca.php?" + contentString;
            } else {
              // initialize();
              alert('lol');
            }

            infowindow.open(map);
          }

          $('#modal-cerca').modal({show:true});
        }
      });
    }
    /**
     * [removeCerca Realiza a exclusão da cerca]
     * @param  {inteiro} idCerca Número do ID da cerca existente
     */
    function removeCerca (idCerca) {
      if (confirm("Realmente deseja excluir esta cerca? Está ação não poderá ser desfeita.")) {
        $.ajax({
          url: "excluir_cerca.php",
          type: "GET",
          data: { 'codCerca': idCerca },
          success: function (result) {
            if (result == "OK") {
              $('#'+idCerca).remove();
            }
            else {
              alert(result);
              console.log(result);
            }
          }
        });
      }
    }

    /*****************************************************
     *****************************************************
     * API DO GOOGLE MAPS
     *****************************************************
     *****************************************************/
    var directionsDisplay;
    var markerArray = [];
    var markerArrayHist = [];
    var marcadores = [];
    /**
     * [directionsService Inicializa o servico de rotas]
     * @type {Google Object}
     */
    var directionsService = new google.maps.DirectionsService();
    /**
     * [icons Define os ícones personalizados das rotas]
     * @type {Google Object}
     */
    var icons = {
      start: new google.maps.MarkerImage(
        'imagens/marker_start.png',  // URL
        new google.maps.Size( 48, 48 ), // (width,height)
        new google.maps.Point( 0, 0 ),  // The origin point (x,y)
        new google.maps.Point( 22, 32 ) // The anchor point (x,y)
      ),
      end: new google.maps.MarkerImage(
        'imagens/marker_finish.png',  // URL
        new google.maps.Size( 40, 40 ), // (width,height)
        new google.maps.Point( 0, 0 ),  // The origin point (x,y)
        new google.maps.Point( 22, 32 ) // The anchor point (x,y)
      )
    }
    /**
     * [var_location Variável que define a posição de centralização]
     * @type {Google Object}
     */
    var var_location = new google.maps.LatLng(-13.496473,-55.722656);
    /**
     * [var_mapoptions Define as opções do mapa]
     * @type {Google Object}
     */
    var var_mapoptions = {
      center: var_location,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      zoom: 5,

      panControl: false,
      panControlOptions: {
         position: google.maps.ControlPosition.RIGHT_CENTER
      },

      zoomControl: true,
      zoomControlOptions: {
       position: google.maps.ControlPosition.RIGHT_CENTER
     },

     mapTypeControl: true,
     mapTypeControlOptions: {
      position: google.maps.ControlPosition.RIGHT_BOTTOM
    },

    };
    /**
     * [var_map Cria efetivamente o objeto 'mapa' com base nas informações passadas]
     * @type {Google Object}
     */
    var var_map = new google.maps.Map(document.getElementById("map-canvas"),var_mapoptions);

    /**
     * [init_map Inicializa a API do Google Maps]
     * @return {[strings]} [Variáveis com os parâmetros de inicialização]
     */
  	function init_map() {
      /**
       * [directionsDisplay Define as opções padrão de visualização das rotas]
       * @type {Google Object}
       */
      directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true});
      directionsDisplay.setMap(var_map);  //Atrela as opções de inicialização ao mapa
      directionsDisplay.setPanel(document.getElementById("directionsPanel"));
      /**
       * BOTÃO PERSONALIZADO DO STREET VIEW
       */
      // Get close button and insert it into streetView
      // #button can be anyt dom element
      var closeButton = document.querySelector('#close-street'),
          controlPosition = google.maps.ControlPosition.RIGHT_TOP;

      // Assumes map has been initiated
      var streetView = var_map.getStreetView();

      // Hide useless and tiny default close button
      streetView.setOptions({ enableCloseButton: false });

      // Add to street view
      streetView.controls[ controlPosition ].push( closeButton );

      // Listen for click event on custom button
      // Can also be $(document).on('click') if using jQuery
      google.maps.event.addDomListener(closeButton, 'click', function(){
          streetView.setVisible(false);
      });
  	}
    google.maps.event.addDomListener(window, 'load', init_map);

    var thePanorama = var_map.getStreetView();
    google.maps.event.addListener(thePanorama, 'visible_changed', function() {
        if (thePanorama.getVisible()) {
          $('#close-street').removeClass('hide');
        } else {
          $('#close-street').addClass('hide');
        }
    });

    /**
     * [calcRoute Traça a rota no mapa e no intinerário]
     * @return {void}
     */
    function calcRoute() {
      $('#calculaRota i').removeClass('fa-road').addClass('fa-refresh fa-spin');

      // Retrieve the start and end locations and create
      // a DirectionsRequest using  directions.
      var start = document.getElementById('inicio_rota').value;
      var end = document.getElementById('destino_rota').value;
      var request = {
        origin: start,
        destination: end,
        travelMode: google.maps.TravelMode.DRIVING
      };

      limparMapa();

      // Route the directions and pass the response to a function to create markers
      directionsService.route(request, function(response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
          directionsDisplay.setDirections(response);
          var leg = response.routes[0].legs[0];
          makeMarker( leg.start_location, icons.start, "Ponto de Saída" );
          makeMarker( leg.end_location, icons.end, 'Ponto de Chegada' );
          $('#calculaRota').addClass('btn-success');
          $('#calculaRota > i').removeClass('fa-refresh fa-spin').addClass('fa-check');
          setTimeout(function () {
            $('#calculaRota').removeClass('btn-success');
            $('#calculaRota > i').removeClass('fa-check').addClass('fa-road');
          }, 3000);
        }
        else {
          $('#calculaRota').addClass('btn-danger');
          $('#calculaRota > i').removeClass('fa-refresh fa-spin').addClass('fa-times');
          setTimeout(function () {
            $('#calculaRota').removeClass('btn-danger');
            $('#calculaRota > i').removeClass('fa-times').addClass('fa-road');
          }, 3000);
          alert('Não foi possível calcular a rota: ' + status);
          $('#inicio_rota').focus();
        }
      });
    }

    /**
     * [tracarHistorico Desenha as rotas do histórico no mapa principal]
     * @param  {array} markers Contém a latitude e longitude de cada posicao da tabela de histórico
     */

    function tracarHistorico () {
      limparMapaHist();

      markers = posicoes;

      path = new google.maps.MVCArray();
      poly = new google.maps.Polyline({
        map: var_map,
        strokeColor: '#4986E7'
      });

	  waitingDialog.show('Traçando Mapa. Por favor aguarde.');
	  intervalTraceRoute = setInterval(function (){ drawNewAnimatedMap();},50)

	  /*var markers = [
        {
          'lat': '-10.856',
          'lng': '-37.05592666'
        }, {
          'lat': '-10.85584',
          'lng': '-37.05615833'
        }, {
          'lat': '-10.85610166',
          'lng': '-37.05608333'
        }, {
          'lat': '-10.85596833',
          'lng': '-37.05512666'
        }
      ];*/

	  /*
      var lat_lng = [];
      var latlngbounds = new google.maps.LatLngBounds();

      for (i = 0; i < markers.length; i++) {

        if (i == 0) var iconeImg = icons.end;
        else if (i == (markers.length -1)) var iconeImg = icons.start;
        else var iconeImg = 'imagens/marcador_historico.png';

        var data = markers[i];
        var myLatlng = new google.maps.LatLng(data.lat, data.lng);
        lat_lng.push(myLatlng);
        var marker = new google.maps.Marker({
          position: myLatlng,
          icon: iconeImg,
          map: var_map
        });
        latlngbounds.extend(marker.position);
        markerArrayHist.push(marker);
      }
      var_map.setCenter(latlngbounds.getCenter());
      var_map.fitBounds(latlngbounds);

      ***********ROUTING****************

      //Intialize the Path Array
      var path = new google.maps.MVCArray();

      //Intialize the Direction Service
      // var service = new google.maps.DirectionsService();

      //Set the Path Stroke Color
      var poly = new google.maps.Polyline({
        map: var_map,
        strokeColor: '#4986E7'
      });

      //Loop and Draw Path Route between the Points on MAP
      for (var i = 0; i < lat_lng.length; i++) {
        if ((i + 1) < lat_lng.length) {
          var src = lat_lng[i];
          var des = lat_lng[i + 1];
          path.push(src);
          poly.setPath(path);
          directionsService.route({
            origin: src,
            destination: des,
            travelMode: google.maps.DirectionsTravelMode.DRIVING
          }, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
              for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) {
                path.push(result.routes[0].overview_path[i]);
              }
            }
          });
        }
      }
	*/

      $('#modal-historico').modal('toggle');
      $("#historico .header").click();
      $('#erase-trace').parent('.clear-trace').removeClass('hide');
      $('#erase-trace').click(function () {
        for (var i = 0; i < lat_lng.length; i++) {
          lat_lng.splice(i, 1);
        }
        limparMapaHist();
        console.log(markerArrayHist.length);
        path.clear();
      });
    }

    /**
     * [makeMarker Criação de marcadores -- atualmente somente para ROTAS]
     * @param  {string} position Latitude e longitude do ponto
     * @param  {Google Object} icon     Ícones personalizados das rotas
     * @param  {string} title    Nome do title que será exibido no hover do marcador
     * @param  {boolean} retorna    Se o objecto marcador criado deverá ser retornado
     */
    function makeMarker (position, icon, title, retorna) {

	  /*
	  var marker = new google.maps.Marker({
        position: position,
        map: var_map,
        icon: icon,
        title: title,
        animation: google.maps.Animation.DROP
      });
	  */
	 var marker = new MarkerWithLabel({
       position: position,
       map: var_map,
       draggable: true,
       raiseOnDrag: true,
       labelContent: title,
	   icon: icon,
  	   animation: google.maps.Animation.DROP,
       labelAnchor: new google.maps.Point(5, 25),
       labelClass: "labels", // the CSS class for the label
       labelInBackground: false
     });

	  markerArray.push(marker);
      if (retorna) return marker;
    }

    /**
     * [verNoMapa Visualiza aquela localização mostrada no Histórico]
     * @param  {float} lat Latitude em decimal degrees
     * @param  {float} lon Longitude em decimal degrees
     */
    function verNoMapa (lat, lon) {
      var image = 'imagens/coordenada.png';
      var posHist = new google.maps.LatLng(lat, lon);
      var pointMarker = new google.maps.Marker({
        position: posHist,
        map: var_map,
        icon: image,
        animation: google.maps.Animation.DROP
      });

      markerArrayHist.push(pointMarker);
      pointMarker.setMap(var_map);
      var_map.setZoom(15);
      var_map.panTo(posHist);
    }

    /**
     * [limparMapa "Zera" as informações contidas no mapa]
     */
    function limparMapa () {
      var j = 0;
      // Repete o processo de limpeza 3 vezes
      // Contorna o BUG que mantém posiçoes nos arrays em algumas situações
      while (j < 3) {
        for (i = 0; i < markerArray.length; i++) {
          markerArray[i].setMap(null);  // Primeiro, remove todos os marcadores existentes no mapa.
          marcadores.splice(i, 1);
          markerArray.splice(i, 1);
        }

        for (var i = 0; i < markerArrayHist.length; i++) {
          markerArrayHist[i].setMap(null);
          markerArrayHist.splice(i, 1);
        }
        j++;
      }
      markerArray = []; // Agora, limpa o array em si.
      markerArrayHist = [];
      marcadores = [];
      directionsDisplay.setDirections({routes: []});  //Remove o traçado das rotas

      $("#slide-panel").addClass('hide'); // Remove o botão de collapse do itinerário de rotas
      $("#inicio_rota").val("");  //Apaga os inputs de rotas
      $("#destino_rota").val(""); //Apaga os inputs de rotas
    }


    function limparMapaHist () {
      var j = 0;
      // Repete o processo de limpeza 3 vezes
      // Contorna o BUG que mantém posiçoes nos arrays em algumas situações
      while (j < 3) {
        for (var i = 0; i < markerArrayHist.length; i++) {
          markerArrayHist[i].setMap(null);
          markerArrayHist.splice(i, 1);
        }
        j++;
      }
      markerArrayHist = [];
    }

    /**
     * [imagemSinal Atualiza o status do sinal do veículo]
     * @param  {char} sinal Caracter contendo o status do sinal [R]astreando | [D]esligado | [S]em sinal
     * @return {[string]}       [Caminho da imagem a ser adicionada de acordo com o status do sinal]
     */
    function imagemSinal (sinal) {
      switch (sinal) {
        case 'R':
          var caminho = 'imagens/status_rastreando.png';
        break;

        case 'D':
          var caminho = 'imagens/status_desligado.png';
        break;

        case 'S':
          //var caminho = 'imagens/status_sem_sinal.png';
		  var caminho = 'imagens/status_rastreando.png';
        break;
      }

      return ("<img src='" + caminho + "' alt='Status do Sinal'>");
    }

	var posVeiculoCerca;
    /**
     * [alterarComboVeiculo Marca o veículo ou grupo selecionado no mapa]
     * @param  {string} imei Número do IMEI do veículo ou ID do grupo
     */
    function alterarComboVeiculo (imei) {
      limparMapa();
      if (imei) {
        $.ajax({
          url: "dados_veiculo.php",
          type: "GET",
          data: { filtro: imei },
          success: function (aDados) {

			var infowindow = new google.maps.InfoWindow();
			var marker, i;
			var markers = new Array();
			var enderecos = new Array();
			var endereco;

            var aDados = eval('('+aDados+')');
            for (var i = 0; i < aDados.length; i++) {
              var dados = aDados[i];

              if (dados.sinal == 'D') {
                var imgTipo = '_inativo.png';
              }
              else if (dados.block == 'S') var imgTipo = '_bloqueado.png';
              else var imgTipo = '_ativo.png';

              switch (dados.tipo) {
                case 'MOTO':
                  var image = 'imagens/marker_moto' + imgTipo;
                break;

                case 'CARRO':
                  var image = 'imagens/marker_carro' + imgTipo;
                break;

                case 'JET':
                  var image = 'imagens/marker_jet' + imgTipo;
                break;

                case 'CAMINHAO':
                  var image = 'imagens/marker_caminhao' + imgTipo;
                break;

                case 'VAN':
                  var image = 'imagens/marker_van' + imgTipo;
                break;

                case 'PICKUP':
                  var image = 'imagens/marker_pickup' + imgTipo;
                break;

                case 'ONIBUS':
                  var image = 'imagens/marker_onibus' + imgTipo;
                break;

                default:
                  var image = 'imagens/marker_carro' + imgTipo;
                break;
              }

              var myLatLng    = new google.maps.LatLng(dados.latitude, dados.longitude);
              var pointMarker = makeMarker(myLatLng, image, dados.name, true);

			  // marca posição do veiculo para quando escolher a cerca
              posVeiculoCerca = new google.maps.LatLng(dados.latitude, dados.longitude);
              // OBTÉM O ENDEREÇO
			  /*
              geocoder = new google.maps.Geocoder();
              geocoder.geocode({'latLng': myLatLng}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                	dados.endereco = results[0].formatted_address;
                }
                else
				{
					dados.endereco = status;
				}
              });
              */
			  	google.maps.event.addListener(pointMarker, 'click', (function(pointMarker, i) {
					// OBTÉM O ENDEREÇO
					return function() {

					infowindow.setContent('	<table width="320" height="185" border="0" cellpadding="0" cellspacing="0"> '+
					'	 <tr> '+
					'	  <td width="320" height="19" align="center" bgcolor="#003399"><strong class="titulo"></br>DETALHES DO VEÍCULO</strong></td>'+
					'	 </tr> '+
					'	 <tr> '+
					'	  <td bgcolor="#003399"><table width="99%" border="0" align="center" cellpadding="0" cellspacing="4" class="bordatable"> '+
					'	   <tr> '+
					'		<td width="44%" class="textodescricao">Placa:</td> '+
					'		<td width="56%">'+aDados[i]["name"]+'</td> '+
					'	   </tr> '+
					'	   <tr> '+
					'		<td class="textodescricao">Endereço:</td> '+
					'		<td><a href=\"#\" class="linkAddress" onclick=javascript:getAddressGMaps('+aDados[i]["latitude"]+','+aDados[i]["longitude"]+')>Visualizar Endereço</a></td> '+
					'	   </tr> '+
					'	   <tr> '+
					'		<td class="textodescricao">Imei:</td> '+
					'		<td>'+aDados[i]["imei"]+'</td> '+
					'	   </tr> '+
					'	   <tr> '+
					'		<td class="textodescricao">Chip:</td> '+
					'		<td>'+aDados[i]["chip"]+'</td> '+
					'	   </tr> '+
					'	   <tr> '+
					'		<td class="textodescricao">identificação:</td> '+
					'		<td>'+aDados[i]["apelido"]+'</td> '+
					'	   </tr> '+
					'	   <tr> '+
					'		<td class="textodescricao">Tipo de rastreador:</td> '+
					'		<td>'+aDados[i]["modelo"]+'</td> '+
					'	   </tr> '+
					'	   <tr> '+
					'		<td class="textodescricao">Velocidade neste ponto:</td> '+
					'		<td>'+aDados[i]["velocidade"]+' Km/h</td> '+
					'	   </tr> '+
					'	   <tr> '+
					'		<td class="textodescricao">Total de Km rodados:</td> '+
					'		<td>'+aDados[i]["hodometro"]+' Km</td> '+
					'	   </tr> '+
					'	 </table></td> '+
					'	 </tr> '+
					'	</table> ');

						infowindow.open(var_map, pointMarker);
					}
				})(pointMarker, i));

				/*
              google.maps.event.addListener(pointMarker, 'click', function(e){
                var infoWindow = new google.maps.InfoWindow({
                  position: myLatLng,
                  content:"<div id='bodyContent' style='text-align:left'><p><b>Placa:</b> "+dados.name+"<br><b>Endereço:</b> "+dados.endereco+"<br><b>IMEI:</b> "+dados.imei+"<br><b>Chip: </b>"+dados.identificacao+"<br><b>Identificação: </b>"+dados.apelido+"<br><b>Rastreador: </b>"+dados.modelo+"</p></div>"
                });
                infoWindow.open(var_map);
              });
              */
              marcadores.push(dados);
            }
            var_map.panTo(myLatLng);
            // Caso seja visualização de GRUPO, define um zoom menor
            // Se for apenas um veículo, define um zoom maior e coloca as informações do hodometro
            if (aDados.length > 1) var_map.setZoom(10);
            else {
              $('li.status-sinal').html(imagemSinal(dados.sinal));
			  $('li.status-ignicao').html(estadoIgnicao);
              var_map.setZoom(15);

			  exibirListagemHistorico(imei);

              $.ajax({
                url: "menu_hodometro.php",
                type: "GET",
                data: { acao: 'hodometro_atual', imei: imei },
                dataType: "JSON",
                success: function (infoHodometro) {
                  $('#hod_atual').val(infoHodometro.hodometro);
                  $('#alerta_hodometro').val(infoHodometro.alerta_hodometro);
                }
              });
            }
          }
        });
      }
      else posicionaHistorico();
    }

    /**
     * [autoReload Atualiza automaticamente a posição dos marcadores a cada 1 min (se houver marcadores)]
     */
    function autoReload () {
      if (markerArray.length > 0) {
        for (var i = 0; i < markerArray.length; i++) {
          //console.log('marker ' + i);

		  //atualizar historico
		  $.ajax({
			url: "listagem_nova_interface.php",
			type: "GET",
			data: { 'imei': marcadores[i].imei },
			success: function (result) {
			  $("#historico .content table tbody").html(result);
			}
		  });

          $.ajax({
            url: "dados_veiculo.php",
            data: { posicao: marcadores[i].imei},
            success: function (coordenadas) {
              var coordenadas = eval('('+coordenadas+')');
              for (var i = 0; i < coordenadas.length; i++) {
                //console.log('coordenada '+ i);
                // DEFINE A NOVA POSIÇÃO
                var novaPos = new google.maps.LatLng(coordenadas[i].latitude, coordenadas[i].longitude);
                markerArray[i].setPosition(novaPos);

                // DEFINE O NOVO ICONE QUE SERÁ UTILIZADO
                var dados = coordenadas[i];

                if (dados.sinal == 'S' || dados.sinal == 'D') {
                  var imgTipo = '_inativo.png';
                }
                else if (dados.block == 'S') var imgTipo = '_bloqueado.png';
                else var imgTipo = '_ativo.png';

                switch (dados.tipo) {
                  case 'MOTO':
                    var image = 'imagens/marker_moto' + imgTipo;
                  break;

                  case 'CARRO':
                    var image = 'imagens/marker_carro' + imgTipo;
                  break;

                  case 'JET':
                    var image = 'imagens/marker_jet' + imgTipo;
                  break;

                  case 'CAMINHAO':
                    var image = 'imagens/marker_caminhao' + imgTipo;
                  break;

                  case 'VAN':
                    var image = 'imagens/marker_van' + imgTipo;
                  break;

                  case 'PICKUP':
                    var image = 'imagens/marker_pickup' + imgTipo;
                  break;

                  case 'ONIBUS':
                    var image = 'imagens/marker_onibus' + imgTipo;
                  break;

                  default:
                    var image = 'imagens/marker_carro' + imgTipo;
                  break;
                }
                // Atualiza a imagem de status do sinal se não for grupo (apenas uma posição no array)
                if (coordenadas.length == 1)
				{
					 $('li.status-sinal').html(imagemSinal(dados.sinal));
					 $('li.status-ignicao').html(estadoIgnicao);
					 if (coordenadas[i].ligado=='S')
					 {
						$('li.status-ignicao').html('<p style="font-face: verdana; font-size: 9px; color:#0F0; display: block; padding: 10px; padding-top: 20px;">Ligado</p>');
					 }
					 else
					 {
						$('li.status-ignicao').html('<p style="font-face: verdana; font-size: 9px; color:#CCC; display: block; padding: 10px; padding-top: 20px;">Desligado</p>');
					 }
				}
                if (markerArray[i].getIcon() != image) {
                  markerArray[i].setIcon(image);
                  //console.log(image);
                }
                //console.log(novaPos);

              }
            }
          });
        }
      }
    }
    setInterval(autoReload, 3000);

	function getAddressGMaps(lat,long)
	{
		var myLatLng = new google.maps.LatLng(lat, long);

		// OBTÉM O ENDEREÇO
		geocoder = new google.maps.Geocoder();
		geocoder.geocode({'latLng': myLatLng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				alert(results[0].formatted_address);
			}
			else
			{
				alert('Desculpe, não consegui identificar o endereço. Por favor tente novamente em instantes.');
				//return google.maps.GeocoderStatus;
			}
		});
	}

	var poly;
	var polyOptions = {
		strokeColor: '#000000',
		strokeOpacity: 1.0,
		strokeWeight: 3,
		map: var_map
	}
	poly = new google.maps.Polyline(polyOptions);

	// Mapa Animado - Otavio - Refeito em 170120151234
	function drawNewAnimatedMap()
	{
		i = counterTrace;

		var lat_lng = [];
      	//var latlngbounds = new google.maps.LatLngBounds();

        if (i == 0)
		{
			var iconeImg = icons.end;
		}
        else if (i == (markers.length -1))
		{
			var iconeImg = icons.start;
		}
  //       else
		// {
		// 	var iconeImg = 'imagens/marcador_historico.png';
		// }

        var data = markers[i];

        var myLatlng = new google.maps.LatLng(data.lat, data.lng);
        lat_lng.push(myLatlng);

		// polyline com a rota percorrida do ponto anterior até aqui.
		var path = poly.getPath();
		path.push(myLatlng);

        var marker = new google.maps.Marker({
          position: myLatlng,
          //icon: iconeImg,
          map: var_map
        });
		var_map.panTo(myLatlng);
		var_map.setZoom(17);

        latlngbounds.extend(marker.position);
        markerArrayHist.push(marker);

		if (i == markers.length-1)
		{
			clearInterval(intervalTraceRoute);
			var_map.setCenter(latlngbounds.getCenter());
			var_map.fitBounds(latlngbounds);
			waitingDialog.hide();

		}
		counterTrace++;
	}

	var myApp;
	myApp = myApp || (function () {
		var pleaseWaitDiv = $('<div class="modal hide" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false"><div class="modal-header"><h1>Processing...</h1></div><div class="modal-body"><div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div></div></div>');
		return {
			showPleaseWait: function() {
				pleaseWaitDiv.modal();
			},
			hidePleaseWait: function () {
				pleaseWaitDiv.modal('hide');
			},

		};
	})();

	// $("#cmbLimite").change(function(){
	// 	if ($("#cmbLimite").val()>100)
	// 	{
	// 		alert("Atenção: Escolhido mais de 100 pontos. É possível que o histórico e o mapa demorem a ser processados.");
	// 	}
	// });

  </script>

  <!-- Page Specific Plugins -->
  <!--<script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
  <script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
  <script src="js/morris/chart-data-morris.js"></script>
  <script src="js/tablesorter/jquery.tablesorter.js"></script>
  <script src="js/tablesorter/tables.js"></script>-->

  <script src="js/custom.js"></script>
</body>
</html>
