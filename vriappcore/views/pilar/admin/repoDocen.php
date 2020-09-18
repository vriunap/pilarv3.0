<!-- == Column Two == -->
<div class="col-md-12">

    <h3> Registro Repositorio VRI </h3>
    <section class="no-ponga esto-content">
        <!-- inicio tab group -->
        <ul class="nav nav-pills">
            <li class="active"> <a data-toggle="tab" href="#dtab1">Búsqueda General</a> </li>
            <li> <a data-toggle="tab" href="#dtab2"> Nuevo Registro </a> </li>
            <li> <a data-toggle="tab" href="#dtab3"> Lista Generacional </a> </li>
        </ul>

        <!-- inicio tab slides -->
        <div class="tab-content nav-pills">
            <!-- tab1 xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->
            <div id="dtab1" class="tab-pane fade in active" style="border: 1px solid #C0C0FF; padding: 30px">
                <form class="form-horizontal" name="frmbusq" method=post onsubmit="listDocRepo(); return false;">
                    <fieldset>
                        <!-- Text input-->
                        <div class="form-group">
                            <label class="col-sm-2 col-md-2 control-label"> Expresión </label>
                            <div class="col-sm-6 col-md-6">
                                <input name="tipo" type="hidden" value="3">
                                <input name="expr" type="text" class="form-control input-md" placeholder="Ingrese DNI o Apellidos" required autofocus>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success form-control" onclick="listDocRepo()">
                                    <span class="glyphicon glyphicon-search"></span> Buscar </button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-warning form-control" onclick="listDocGrad()">
                                    <span class="glyphicon glyphicon-search"></span> SUNEDU </button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div id="tblist"> ... </div>
            </div>

            <!-- tab2 xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->
            <div id="dtab2" class="tab-pane fade" style="border: 1px solid #C0C0FF; padding: 30px">
                <div class="row">
					<form class="form-horizontal" name="frmnovo" method=post onsubmit="sndLoad('admin/execNewDocRepo',new FormData(frmnovo))">
                        <fieldset>
                            <!-- Select input-->
                            <div class="form-group">
                                <label class="col-md-3 control-label"> <i>Tipo de Docente</i> </label>
                                <div class="col-md-8">
                                    <select name="tipod" class="form-control" onchange="">
                                        <option value=2> CONTRATADO </option>
                                    </select>
                                </div>
                                <div class="col-md-1"></div>
                            </div>
                            <!-- Select input-->
                            <div class="form-group">
                                <label class="col-md-3 control-label"> <i>Categoria</i> </label>
                                <div class="col-md-8">
                                    <select name="categ" class="form-control" onchange="" required>
                                        <option value="" disabled selected> seleccione </option>
                                        <?php
                                            foreach( $tcateg->result() as $row )
                                            {
                                                echo "<option value=$row->Id> $row->Nombre </option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-1"></div>
                            </div>
                            <!-- Select input-->
                            <div class="form-group">
                                <label class="col-md-3 control-label"> <i>Facultad</i> </label>
                                <div class="col-md-8">
                                    <select id="facul" name="facul" class="form-control" onchange="listCboCarrs()" required>
                                        <option value="" disabled selected> seleccione </option>
                                        <?php
                                            foreach( $tfacus->result() as $row )
                                            {
                                                echo "<option value=$row->Id> $row->Nombre </option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-1"></div>
                            </div>
                            <!-- Select input-->
                            <div class="form-group">
                                <label class="col-md-3 control-label"> <i>Escuela Profesional</i> </label>
                                <div class="col-md-8">
                                    <select name="carre" id="carre" class="form-control" required>
                                    </select>
                                </div>
                                <div class="col-md-1"></div>
                            </div>
                            <hr>
                            <!-- Text input-->
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Fecha de Ingreso </label>
								<div class="col-md-8">
									<input name="fechaIn" type="date" class="form-control input-md" value="2017-04-21">
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Fecha de Ascenso </label>
								<div class="col-md-8">
									<input name="fechaAsc" type="date" class="form-control input-md" value="">
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Resolución de Ascenso </label>
								<div class="col-md-8">
									<input name="resolAsc" type="text" class="form-control input-md" value="">
								</div>
								<div class="col-md-1">
								</div>
							</div>

							<hr>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Fecha de CONTRATO </label>
								<div class="col-md-8">
									<input name="fechaCon" type="date" class="form-control input-md" value="2017-04-21">
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Resol. de CONTRATO </label>
								<div class="col-md-8">
									<input name="resolCon" type="text" class="form-control input-md" value="R.R. Nro 1225-2017-R-UNA">
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<hr>

                            <script>
                            function cargaDotax()
                            {
                                $.ajax({
                                    url : "admin/getLeData/"+dni.value,
                                    dataType: "json",
                                    success : function( res ){
                                        console.log( res );
                                        nacim.value = res.FechaNac;
                                        apels.value = res.ApPaterno +" "+ res.ApMaterno;
                                        nomes.value = res.Nombres;
                                    }
                                });
                            }
                            </script>

							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> DNI </label>
								<div class="col-md-5">
									<input id="dni" name="dni" type="number" class="form-control input-md" value="cargaDotax()" required>
								</div>
                                <div class="col-md-3">
                                    <button type="button" class="form-control btn btn-success" onclick="cargaDotax()"> <i class="glyphicon glyphicon-search"></i> BUSCAR </button>
								</div>
								<div class="col-md-1">
								</div>
							</div>

							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Codigo </label>
								<div class="col-md-8">
									<input name="codigo" type="text" class="form-control input-md" value="" required>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Apellidos </label>
								<div class="col-md-8">
									<input id="apels" name="apels" type="text" class="form-control input-md" value="" required>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Nombres </label>
								<div class="col-md-8">
									<input id="nomes" name="nomes" type="text" class="form-control input-md" value="" required>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Nacimiento </label>
								<div class="col-md-6">
									<input name="fechaNac" type="date" class="form-control input-md" value="" required>
								</div>
                                <div class="col-md-2">
									<input id="nacim" name="nacim" type="text" class="form-control input-md" value="" required>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Dirección </label>
								<div class="col-md-8">
									<input name="direcc" type="text" class="form-control input-md" value="" required>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Correo </label>
								<div class="col-md-8">
									<input name="mail" type="email" class="form-control input-md" value="" required>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Celular </label>
								<div class="col-md-8">
									<input name="celu" type="number" class="form-control input-md" value="" required>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-3 control-label"> Contraseña </label>
								<div class="col-md-8">
									<input name="clave" type="password" class="form-control input-md" value="">
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<!-- submit -->
							<div class="form-group">
								<label class="col-md-8 control-label"></label>
								<!-- Button (Double) -->
								<div class="col-md-3">
									<input type="submit" class="btn btn-success col-xs-12" value="Nuevo Docentes">
								</div>
								<div class="col-md-1"></div>
							</div>

                        </fieldset>
                    </form>
                </div>
            </div>
            <!-- end tab 2 -->

            <!-- inicio tab 3 -->
            <div id="dtab3" class="tab-pane fade in" style="border: 1px solid #C0C0FF; padding: 30px">
                <form class="form-horizontal" name="frmcese" method=post onsubmit="oooo(); return false;">
                    <fieldset>
                        <!-- Text input-->
                        <div class="form-group">
                            <label class="col-sm-2 col-md-2 control-label"> Escuela Profesional </label>
                            <div class="col-md-8">
                                <select id="epss" name="epss" class="form-control" onchange="listEdades()" required>
                                    <option value="" disabled selected> seleccione </option>
                                    <?php
                                        foreach( $tfacus->result() as $row )
                                        {
                                            echo "<option value=$row->Id> $row->Nombre </option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <table class="table table-bordered table-striped" style="font-size: 11px">
                    <tr>
                        <th> Nro </th>
                        <th> E.P. </th>
                        <th> Datos Personales </th>
                        <th> Edad </th>
                        <th> Fecha Nac </th>
                    </tr>
                    <?PHP

                    $nro = 1;
                    foreach( $tdocen->result() as $row ){
                        echo "<tr>";
                        echo "<td> $nro </td>";
                        echo "<td>" .substr($row->Facultad,0,32). "</td>";
                        echo "<td> $row->DatosPers ".($row->Activo==1?"<b>(Cesado)</b>":"")." </td>";
                        echo "<td> $row->Edad ".($row->Edad>72?"<<":"")." </td>";
                        echo "<td> $row->FechaNac </td>";
                        echo "</tr>";

                        $nro++;
                    }

                    ?>
                </table>
            </div>
            <!-- end tab 3 -->

        </div>
    </section>

</div> <!--  fin:  div class="col-md-12" -->





<!-- Modal -->
<div class="modal fade" id="dlg" tabindex="-1" role="dialog" aria-labelledby="dlgJurLab" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <span class="label label-default mycaption"> Listado de Grado y Títulos </span>
        <!-- form -->
          <form class="form-horizontal" id="frmDatos" novalidate="novalidate">
          <fieldset>
              <!-- select areas -->
              <div class="form-group form-group-sm">
                  <label class="col-md-2 control-label"> <small>Mensaje</small> </label>
                  <div class="col-md-10">
                      <input id="resu" type="text" class="form-control" value="" readonly>
                      <input name="idtram" type="hidden" class="form-control" value="">
                  </div>
              </div>

              <!-- select areas -->
              <div class="form-group">
                  <label class="col-md-2 control-label"> Nro D.N.I. </label>
                  <div class="col-md-3">
                    <input type="hidden" id="opcion" name="opcion" value="PUB">
                    <input type="hidden" id="token" name="_token" value="oHiSKoJ0Jm4AF5AAxO0fXUWDG0Wwa6fitjV6IJFy">
                    <input type="hidden" id="nombre" name="nombre" class="form-control" placeholder="Ape y Nomb">
                    <input name="doc" id="doc" type="text" class="form-control" placeholder="D.N.I.">
                  </div>
                  <div class="col-md-3">
                    <input name="captcha" id="captcha" type="text" class="form-control" placeholder="Captcha">
                  </div>
                  <div class="col-md-2">
                    <img src="https://enlinea.sunedu.gob.pe/simplecaptcha?date=457&_captcha=AEAEA" height=42>
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-info btn-block" onclick="cargarGrados()"> <i class="glyphicon glyphicon-search"></i> </button>
                  </div>
                  <span class="help-block col-md-offset-2 col-md-10" style="font-size: 12px"> <i class="glyphicon glyphicon-user"></i> Chrome: chrome.exe --user-data-dir="D:/ChromeDevX" --disable-web-security </span>
              </div>

              <!-- select areas -->
              <div class="form-group form-group-sm">
                  <div class="col-md-12">
                      <table class="table table-bordered table-striped" style="font-size: 10px">
                          <th> Graduado </th>
                          <th> Grado </th>
                          <th> Institucion </th>
                          <tbody id="tres"></tbody>
                      </table>
                  </div>
              </div>

          </fieldset>
        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal"> Cancelar </button>
        <button type="button" class="btn btn-info" onclick='$("#dlgJur").modal("hide");frmjur.submit()'> Grabar Cambios </button>
      </div>
    </div>
  </div>
</div>



<script>
function cargarGrados(){

    var total = 0;

    $("#tres").empty();
    $("#resu").val( "Consultando..." );

    $.ajax({
        data : $('#frmDatos').serialize(),
        type : 'POST',
        url  : 'https://enlinea.sunedu.gob.pe/consulta',
        dataType : 'JSON',
    })
    .done(function(data) {

        if( data.response == "error" ){
            $("#resu").val( "Error en Captcha" );
            return;
        }
        $.each( $.parseJSON(data), function(i, item) {
            row  = "<tr>";  total++;
            row += "<td>" + item.NOMBRE +"<br><b>"+ item.DOC_IDENT + "</td>";
            row += "<td>" + item.GRADO +"<br>"+ item.DIPL_FEC + "</td>";
            row += "<td>" + item.UNIV +"<br>"+ item.PAIS + "</td>";
            row += "</tr>";
            $('#tres').append( row );
        });
        $("#resu").val( total + " - Registros" );
    })
    .fail( function(xhr, textStatus, errorThrown) {
        $("#resu").val( "Error Allow-Origin*: "+textStatus+" :: "+ xhr.responseText );
    } );
}
</script>