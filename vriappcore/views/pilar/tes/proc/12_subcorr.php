<div class="page-header">
	<h4 class="titulo"> ¿Subida de Archivo Final? </h4>
</div>

<div class="contenido">
	<p>
       Antes de iniciar este procedimiento le recordamos que debe poner en texto de color
       <b class='text-danger'>rojo</b> con fondo blanco los parrafos o elementos que su jurado le ha indicado corregir, por ello revise bien su <b>Borrador de Tesis</b> asi evitar
       el rechazo del mismo. Por otro lado deberá coordinar
    </p>
    <p>
        Para realizar las correcciónes deberá coordinar con sus jurados 
    </p>
	<p>
		<button class="btn btn-primary" onclick="lodShifs(1)">
            <span class="glyphicon glyphicon-folder-open" ></span>
			&nbsp;&nbsp; Ver mis Correcciones
        </button>

	<!-- 	<button class="btn btn-warning" onclick="alert('Presentar 4 Ejemplares en Coordinación')">
            <span class="glyphicon glyphicon-upload" ></span>
			&nbsp;&nbsp; Presentar Ejemplares en Coordinación de Investigación
        </button> -->

        <button class="btn btn-warning" onclick="lodShifs(2)">
            <span class="glyphicon glyphicon-upload" ></span>
            &nbsp;&nbsp; Subir PDF con Correcciones 
        </button>
        <span class="label label-info"> <span class="glyphicon glyphicon-info-sign" ></span> Solo si ya no tiene observaciones.</span>
	</p>
	<div id="blq1" class="col-md-12" style="background: #FFFFFA">
        <p>Si usted tiene consultas sobre sus observaciones, contáctese directamente con el jurado evaluador utilizando los correos electrónicos, en la sección de <b>Líneas de Investigación</b> .</p>
		<ul class='nav nav-tabs'>
  			<li class='active'><a data-toggle='tab' href='#tab1'> Presidente </a></li>
  			<li><a data-toggle='tab' href='#tab2'> Primer Miembro </a></li>
  			<li><a data-toggle='tab' href='#tab3'> Segundo Miembro </a></li>
			<li><a data-toggle='tab' href='#tab4'> Director/Asesor </a></li>
		</ul>
		<div class='tab-content'>

		<?php
		for( $i=1; $i<=4; $i++ ) {

			$extra = ($i==1) ? "in active" : "";
			echo "<div id='tab$i' class='tab-pane fade $extra pre-scrollable' style='height: 320px'>";
            // echo "<p>Nombres y Apellidos $i</p>";
			foreach( $arrCorr[$i]->result() as $row ) {
				$fecha = mlFechaNorm( $row->Fecha );
				echo "<p><b>[ $fecha ]</b> : $row->Mensaje </p>";
			}
			echo "</div>";
		}
		?>
		</div>
	</div>
	<!-- ----------------------------------------------------------------------------------------- -->
	<div id="blq2" class="col-md-12" style="display: none;">
	<?php

		// comprobamos que haya correcciones sin VB
		$totCorrs1 = $arrCorr[1]->num_rows();
		$totCorrs2 = $arrCorr[2]->num_rows();
		$totCorrs3 = $arrCorr[3]->num_rows();
		$totCorrs4 = $arrCorr[4]->num_rows();

		// controlar vb
		if( $detTram->vb1 + $detTram->vb2 + $detTram->vb3 + $detTram->vb4 == 4 ) {

		} else {

			// si falta de alguno, muestra y termina
			if(  $totCorrs1==0 or $totCorrs2==0 or $totCorrs3==0 or $totCorrs4==0 )
			{   
                echo "<h4><span class='label label-danger'> <span class='glyphicon glyphicon-info-sign' ></span> Contáctese con sus jurados para completar las revisiones.</span></h4>";
				echo "<br> <b>Verificación de Correcciones:</b>";
				echo "<br>Presidente      : <b> " .($totCorrs1? "Ok":""). "</b>";
				echo "<br>Primer Miembro  : <b> " .($totCorrs2? "Ok":""). "</b>";
				echo "<br>Segundo Miembro : <b> " .($totCorrs3? "Ok":""). "</b>";
				echo "<br>Director/Asesor : <b> " .($totCorrs4? "Ok":""). "</b>";

				return;
			}
		}
	?> 
	<!-- area de subida de correcciones -->
    <div class="panel-heading">
        <h2 class="panel-title"> Correcciones Finales de Borrador de Tesis </h2>
    </div>
    <div class="panel-body" id="plops">

      <div id="plock" style="display: none; z-index: 1000; position: fixed; left: 0; top: 0; width:100%; height:100%; padding: 300px; background: rgba(0,0,0,0.5)">
          <div style="margin: 0 auto; width: 320px; height: 80px; background: white; padding: 15px">
            <center> <b> Enviando datos y borrador, espere ... </b> </center>
            <div class="progress progress-striped active" style="margin-bottom:0;">
            <div class="progress-bar" style="width: 100%"></div></div>
          </div>
      </div>

      <!-- form -->
      <h4><span class='label label-danger'> <span class='glyphicon glyphicon-info-sign' ></span> Solo realiza este procedimiento si es el documento FINAL.</span></h4>
      <p> Una vez registrado el borrador no hay manera de corregir el documento, usted deberá estar seguro de que el jurado ha aprobado el borrador, de lo contrario será rechazado e iniciará una nuevo trámite en 60 días reglamentarios.</p>
      <form class="form-horizontal" id="frmborr" method="POST" onsubmit="grabaCorrBorr(); return false"
            accept-charset="utf-8" enctype="multipart/form-data">
          <fieldset>
              <?php
                //
                // local Id(s), IdTramite
                //
                $sess = $this->gensession->GetData();

                $tram = $this->dbPilar->inTramByTesista($sess->userId);
                $autors = $this->dbPilar->inTesistas( $tram->Id );
                $titulo = $this->dbPilar->inLastTramDet( $tram->Id )->Titulo;
                $lineai = $this->dbRepo->inLineaInv( $tram->IdLinea );
              ?>

              <!-- area de datos a almacenar en la BD -->
              <!-- select areas -->
              <div class="form-group success">
                  <label class="col-md-4 control-label" style="color:green"> Linea de Investigación </label>
                  <div class="col-md-7" style="padding-top:7px"> <?=$lineai?> </div>
              </div>


              <!-- select areas -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Autor(es) </label>
                  <div class="col-md-7" style="padding-top:7px"> <?=$autors?> </div>
                  <div class="col-md-4"></div>
              </div>
              <hr>

              <!-- Text input-->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Seleccione Archivo </label>
                  <div class="col-md-7">
                      <input name="nomarch" id="nomarch" type="file" class="file form-control input-md" required>
                      <span id="filemsg" class="help-block"> <center>Puede subir un PDF con un máximo de 2MB</center> </small></span>
                  </div>
                  <!--
                  <input id="nomarch" name="nomarch" type="text" class="form-control input-md" required readonly>
                  <div class="col-md-2">
                      <input type="button" class="btn btn-success col-xs-12" value="Buscar" onclick="tesDownFile('pdf')">
                  </div>
                  -->
              </div>

              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Titulo de Proyecto </label>
                  <div class="col-md-7">
                      <textarea name="nomproy" type="text" class="form-control" rows="3" style="text-transform: uppercase" required><?=$titulo?></textarea>
                  </div>
              </div>
              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Resumen (Abstract) </label>
                  <div class="col-md-7">
                      <textarea name="resumen" type="text" class="form-control" rows="3" placeholder="acepta varias lineas" required></textarea>
                  </div>
              </div>
              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Palabras clave (keywords) </label>
                  <div class="col-md-7">
                      <input name="pclaves" type="text" class="form-control input-md" placeholder="separadas por coma y acaba en punto" required>
                      <!-- <span class="help-block">  </span> -->
                  </div>
              </div>

              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Conclusiones : </label>
                  <div class="col-md-7">

                        <textarea name="conclus" type="text" class="form-control" rows="3" placeholder="acepta varias lineas" required></textarea>
                      <!-- <span class="help-block">  </span> -->
                  </div>
              </div>


                   <!-- date area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label">Fecha de Dictamen :</label>
                  <div class="col-md-7">
                      <input name="dated" type="date" class="form-control input-md" required>
                  </div>
              </div>
          
              <!-- date area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label">Fecha de Sustentación :</label>
                  <div class="col-md-7">
                      <input name="dates" type="date" class="form-control input-md" required>
                  </div>
              </div>

              <span class='label label-danger'> <span class='glyphicon glyphicon-info-sign' ></span> Asegúrese de que el dictamen esté incluido en el archivo.</span>

              <!-- Button (Double) -->
              <div class="form-group">
                  <div class="col-md-6"></div>
                  <div class="col-md-5">
                      <button type="submit" class="btn btn-primary col-xs-12" >
                          <span class="glyphicon glyphicon-save"></span> &nbsp; Subir Archivo Final
                      </button>
                  </div>
              </div>
          </fieldset>
      </form>
      <!-- form -->
    </div>


	<!-- fin de Tab sub Correcs -->
	</div>
</div>
