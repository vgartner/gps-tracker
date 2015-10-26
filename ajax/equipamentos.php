<?
include('../seguranca.php');
?>
<div id="loading"></div>
<div class="row">
	<div class="col-lg-5">
		<div id="mapa_box">
			<iframe src="/mapa.php" scrolling="no" name="mapa" id="mapa"></iframe>
		    <a href="javascript:void(0)" id="apagaTudo" style="font-size:14px; color:#0099ff; margin:0 10px;">LIMPAR MARCADORES</a>
		</div>
		<div id="alertas"></div>
	</div>
	<div class="col-lg-7">
		<div id="equipamentos"></div>
	</div>
</div>
<div id="dialog_box">
	<div class="header"><span onclick="showConfirm('hide');">X</span></div>
	<div class="content">
		<b>DADOS DO VEÍCULO</b><hr>
		<b>IMEI: </b><span id="iIMEI"></span><br>
		<b>Placa: </b><span id="iPlaca"></span><br>
		<b>Marca: </b><span id="iMarca"></span><br>
		<b>Cor: </b><span id="iCor"></span><br>
		<b>Ano: </b><span id="iAno"></span><br>
		<b>Hodômetro: </b><span id="iHodometro"></span><br>
		<b>Identificação: </b><span id="iIdentificacao"></span><br>
		<b>Data Recarga: </b><span id="iRecarga"></span><br><br>
		<b>DADOS DO CLIENTE&nbsp;<img border=0 src='../imagens/frota.gif' style='height:25px;' title='Frota do cliente' alt='Frota do cliente' href='javascript:void(0)' id='img_mostra_frota' /></b><hr>
		<b>Nome: </b><span id="iCliente"></span><br>
		<b>E-mail: </b><span id="iEmail"></span><br>
		<b>Apelido: </b><span id="iApelido"></span><br>
		<b>CPF: </b><span id="iCPF"></span><br>
		<b>Endereço: </b><span id="iEndereco"></span><br>
		<b>Bairro: </b><span id="iBairro"></span><br>
		<b>Cidade: </b><span id="iCidade"></span><br>
		<b>Estado: </b><span id="iEstado"></span><br>
		<b>CEP: </b><span id="iCEP"></span><br>
		<b>Data Contrato: </b><span id="iContrato"></span><br>
		<b>Dia Vencimento: </b><span id="iVencimento"></span>
	</div>
	<!-- <input type="button" value="Não" class="btn_blue" onclick="showConfirm('hide');">
	<form name="confirm" action="produtos.php" method="post">
		<input type="hidden" name="id_action">
		<input type="submit" value="Sim" class="btn_black" style="float: right; margin: 5px 0 10px;">
	</form> -->
</div>
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
                <label class="sr-only" for="nomeCerca"></label>
                <input type="text" id="nomeCerca" name="nomeCerca" class="form-control" placeholder="Nome da cerca">
              </div>
              <button type="button" id="apagarPontos" class="btn btn-default"><i class="fa fa-eraser"></i> Apagar Pontos</button>
            </form>
            <div id="instrucoes" class="alert alert-info hide">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <strong>Instruções</strong>
              <ol>
                <li>Clique e arraste os círculos arredondados nas bordas da cerca para alterá-la.</li>
                <li>Clique em qualquer local dentro da área da cerca para confirmar as modificações.</li>
              </ol>
            </div>
          </div>
          <div class="col-sm-6"><div id="message-cerca"></div></div>
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
        <button type="button" id="salvarCerca" class="btn btn-primary"><i class="fa fa-save"></i> Salvar Cerca</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
	/**
	 * [modalCerca Chama a DIV Modal e cria um mapa para criação de uma nova cerca]
	 */

	var strPesquisa = "";
	var interAutoReload;

	function reprogReload(seconds){
		clearInterval(interAutoReload);
		interAutoReload = setInterval(autoReload, seconds);
	}

	function modalCerca (imei) {
	  $('#form-cerca').removeClass('hide');
	  $('#instrucoes').addClass('hide');
	  $('#salvarCerca').prop('disabled', false);
	  // var imei = $('#veiculoCerca').val();

	  if (imei) {
	    $('#nomeCerca').val('');
	    $('#message-cerca').html('');
	    $('#mapa-cerca').fadeTo('fast', 1);

	    var aracaju = new google.maps.LatLng(-10.947765,-37.072953);
	    var opcoesCerca = {
	      zoom: 13,
	      center: aracaju,
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
	            url: "../incluir_cerca.php",
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
	                if (resCerca == "Detectamos uma cerca existente para o veículo selecionado.OK") mensagem = "Detectamos uma cerca existente para o veículo selecionado.";
	                else mensagem = "Ops! Algo deu errado...";
	                $('#mapa-cerca').fadeTo('fast', 1);
	                $('#message-cerca').html("<div class='alert alert-danger'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <strong>Erro: </strong> " + mensagem + " </div> </div>");
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
     * [removeCerca Realiza a exclusão da cerca]
     * @param  {inteiro} idCerca Número do ID da cerca existente
     */
    function removeCerca (idCerca) {
      if (confirm("Realmente deseja excluir esta cerca? Está ação não poderá ser desfeita.")) {
        $.ajax({
          url: "excluir_cerca.php",
          type: "GET",
          data: { 'codImei': idCerca },
          success: function (result) {
            if (result != "OK") {
              alert(result);
              console.log(result);
            }
          }
        });
      }
    }

    /**
     * [editaCerca Realiza a edição dos pontos da cerca]
     * @param  {inteiro} value Número formado pelo ID da cerca e IMEI do veículo
     */
    function editaCerca (id, imei) {
      $.ajax({
        url: "../editar_cercanovo.php",
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

          var aracaju = new google.maps.LatLng(-10.947765,-37.072953);
          var opcoesCerca = {
            zoom: 13,
            center: aracaju,
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
                url: "../alterar_cerca.php?" + contentString,
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
</script>
<script type="text/javascript">
	$(document).ready(function(){
		/* ALTERA O STATUS DE VISUALIZAÇÃO DOS ALERTAS */
		$("#alertas").on('click', '.alert-box', function () {
			var msg = $(this).attr('data_msg');
			var imei = $(this).attr('data_imei');
			$.ajax({
				type: "GET",
				url: "ajax/equipamentos_info.php",
				data: { fechar: msg , imeimsg: imei },
				success: function (data) {
					if (data == "OK") {
						$(this).remove();
						autoReload();
					}
					else alert(data);
				}
			});
		});

		var opts = {
		  lines: 13, // The number of lines to draw
		  length: 20, // The length of each line
		  width: 10, // The line thickness
		  radius: 30, // The radius of the inner circle
		  corners: 1, // Corner roundness (0..1)
		  rotate: 0, // The rotation offset
		  direction: 1, // 1: clockwise, -1: counterclockwise
		  color: '#000', // #rgb or #rrggbb or array of colors
		  speed: 1, // Rounds per second
		  trail: 60, // Afterglow percentage
		  shadow: false, // Whether to render a shadow
		  hwaccel: false, // Whether to use hardware acceleration
		  className: 'spinner', // The CSS class to assign to the spinner
		  zIndex: 2e9, // The z-index (defaults to 2000000000)
		  top: 'auto', // Top position relative to parent in px
		  left: 'auto' // Left position relative to parent in px
		};
		var target = document.getElementById('loading');
		var spinner = new Spinner(opts).spin(target);
		$('#equipamentos').load('ajax/equipamentos_list.php', {}, function(){spinner.stop()});
	});

	function pesquisa(){
		var opts = {
		  lines: 13, // The number of lines to draw
		  length: 20, // The length of each line
		  width: 10, // The line thickness
		  radius: 30, // The radius of the inner circle
		  corners: 1, // Corner roundness (0..1)
		  rotate: 0, // The rotation offset
		  direction: 1, // 1: clockwise, -1: counterclockwise
		  color: '#000', // #rgb or #rrggbb or array of colors
		  speed: 1, // Rounds per second
		  trail: 60, // Afterglow percentage
		  shadow: false, // Whether to render a shadow
		  hwaccel: false, // Whether to use hardware acceleration
		  className: 'spinner', // The CSS class to assign to the spinner
		  zIndex: 2e9, // The z-index (defaults to 2000000000)
		  top: 'auto', // Top position relative to parent in px
		  left: 'auto' // Left position relative to parent in px
		};
		var target = document.getElementById('loading');
		var spinner = new Spinner(opts).spin(target);
		var palavra = strPesquisa = $('#pesquisa').val();

		$('#equipamentos').load('ajax/equipamentos_list.php?query='+palavra, {}, function(){spinner.stop()});
	}

	//863070015832172
	function carregarMapa(id){
		var oIframe = document.getElementById('mapa');
		var oDoc = (oIframe.contentWindow || oIframe.contentDocument);

		var aDados = {
				name:$('#name_'+id+' a').html(),
				identificacao:$('#identificacao_'+id).html(),
				apelido:$('#apelido_'+id).html(),
				modelo:$('#modelo_'+id).html(),
				endereco:$('#endereco_'+id).html(),
				tipo:$('#tipo_'+id).html(),
				sinal:$('#sinal_'+id).html(),
				block:$('#block_'+id).html(),
				imei: id
			};


		var lat = $('#veiculoLatitude'+id).html();
		var lon = $('#veiculoLongitude'+id).html();

		if (!$('#mapa'+id).is(':checked')) {
			oDoc.clearMarkers(id);
		}
		else oDoc.addMarker(lat,lon,aDados);
		/*
		var imeis = [];

		var tableEquip = document.getElementById('tableEquip');

		var inputs = $('#tableEquip tr td > input');

		for(i=0; i<inputs.length; i++){
			if(inputs[i].checked){
				imeis.push(inputs[i].value);
			}
		}

		oDoc.updateMyKmlArray(imeis);
		*/
	}

	$('#apagaTudo').click(function(){
		var oIframe = document.getElementById('mapa');
		var oDoc = (oIframe.contentWindow || oIframe.contentDocument);
		$("input:checkbox").prop("checked", false);
		oDoc.setAllMap(null);
	});

	function paginacao(pagina){
		var palavra = $('#pesquisa').val();
		strPesquisa = "";
		$('#equipamentos').load('ajax/equipamentos_list.php?pagina='+pagina+'&query='+palavra);
	}

	function mostraOff () {
		$('#equipamentos').load('ajax/equipamentos_list.php?off=true');
	}

	function combustivel (imei, comando) {
		if (comando == ",J") {
			if (confirm("Tem certeza que deseja bloquear este veículo? ATENÇÃO: o bloqueio do veículo em movimento pode colocar em risco os ocupantes ou causar acidentes! Confirma?")) {
				$.ajax({
					type: "GET",
					url: "ajax/comandos.php",
					data: { cod: imei, acao: comando },
					dataType: "JSON",
					success: function (data) {
						if (!data) alert("A ação não foi efetuada corretamente.");
						else paginacao(0);
					}
				});
			}
		}

		if (comando == ",K") {
			if (confirm("Realmente deseja desbloquear este veículo?")) {
				$.ajax({
					type: "GET",
					url: "ajax/comandos.php",
					data: { cod: imei, acao: comando },
					dataType: "JSON",
					success: function (data) {
						if (!data) alert("A ação não foi efetuada corretamente.");
						else paginacao(0);
					}
				});
			}
		}

		if (comando == ",H,060") {
			var veloc = prompt("Qual o limite de velocidade em Km/h ?", 60);
			if (veloc != null) {
				$.ajax({
					type: "GET",
					url: "ajax/comandos.php",
					data: { cod: imei, acao: comando, speed: veloc },
					dataType: "JSON",
					success: function (data) {
						if (!data) alert("A ação não foi efetuada corretamente.");
						else paginacao(0);
					}
				});
			}
		}

		if (comando == ",C,30s") {
			var tempo = prompt("Informe o intervalo de tempo. Os valores utilizados estão listados abaixo:\n\n15s\n30s\n01m\n05m\n10m\n30m\n01h\n05h\n10h", '15s');
			if (tempo != null) {
				$.ajax({
					type: "GET",
					url: "ajax/comandos.php",
					data: { cod: imei, acao: comando, time: tempo },
					dataType: "JSON",
					success: function (data) {
						if (!data) alert("A ação não foi efetuada corretamente.");
						else paginacao(0);
					}
				});
			}
		}

		if (comando == "FORCALOC") {
			$.ajax({
				type: "GET",
				url: "ajax/comandos.php",
				data: { cod: imei, acao: comando },
				dataType: "JSON",
				success: function (data) {
					if (!data) console.log("A ação não foi efetuada corretamente.");
					else console.log("DWXX para "+ imei);
				}
			});
		}
	}

	function autoReload(){
        var pagina = $('ul.pager li:last > a').attr('href');
        var pagina = parseInt(pagina.replace(/[^\d]+/g,''));
        var pagina = pagina - 1;
		var url = 'ajax/equipamentos_list.php?pagina='+pagina;
		if(strPesquisa != ""){
			url += "&query="+strPesquisa;
		}

        $('#equipamentos').load(url,{},function () {
        	var oIframe = document.getElementById('mapa');
        	var oDoc = (oIframe.contentWindow || oIframe.contentDocument);
        	var markersArray = oDoc.markersArray;
        	var marcadores = oDoc.marcadores;
        	var google = oDoc.google;

        	for (var i = 0; i < marcadores.length; i++) {
        		var imei = marcadores[i].imei;
        		var lat = $('#veiculoLatitude'+imei).html();
        		var lon = $('#veiculoLongitude'+imei).html();
        		var novaPos = new google.maps.LatLng(lat, lon);
        		markersArray[i].setPosition(novaPos);
        		$("#mapa"+imei).prop("checked",true);
        	}
        });
    }

    interAutoReload = setInterval(autoReload, 60000);

    /*************************************************
     * Exibe a div popup
     *************************************************/
    function showConfirm (acao,id) {
    	if (acao == 'show') {
    		// document.confirm.id_action.value = excluirID;
    		$("#dialog_box").fadeIn('fast').css({
    			// 'left': event.pageX - 600,
    			// 'top': event.pageY - 400,
    			'top': '20%',
    			'left': '55%',
    			'display': 'block',
    			'position': 'fixed'
    		});
    		$.ajax({
    			type: "GET",
    			url: "ajax/equipamentos_info.php",
    			data: { imei: id },
    			dataType: "JSON",
    			success: function (info) {
    				$("#iIMEI").html(info.imei);
    				$("#iPlaca").html(info.nome);
    				$("#iMarca").html(info.marca);
    				$("#iCor").html(info.cor);
    				$("#iAno").html(info.ano);
    				$("#iHodometro").html(info.hodometro);
    				$("#iIdentificacao").html(info.identificacao);
    				$("#iRecarga").html(info.dt_recarga);
    				$("#iCliente").html(info.cliente);
    				$("#iEmail").html(info.email);
    				$("#iApelido").html(info.apelido);
    				$("#iCPF").html(info.cpf);
    				$("#iEndereco").html(info.endereco);
    				$("#iBairro").html(info.bairro);
    				$("#iCidade").html(info.cidade);
    				$("#iEstado").html(info.estado);
    				$("#iCEP").html(info.cep);
    				$("#iContrato").html(info.data_contrato);
    				$("#iVencimento").html(info.dia_vencimento);
					//$("#img_mostra_frota").attr('onclick', '');
					$("#img_mostra_frota").click(function(){
						$( "#tabs" ).tabs( "option", "active", 1 );
						carregarConteudo("#ui-tabs-1", "ajax/usuarios_form.php?acao=view&id="+info.idCliente);
					});
    			}
    		});
    	}
    	else {
    		$('#dialog_box').fadeOut('fast');
    	}
    }
</script>
