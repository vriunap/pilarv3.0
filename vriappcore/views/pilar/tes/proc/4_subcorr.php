<div class="page-header">
	<h4 class="titulo"> ¿Subida de Correcciones de Proyectos? </h4>
</div>

<div class="contenido">
    
	<p>
       Antes de iniciar este procedimiento le recordamos que debe poner en texto
       rojo con fondo blanco los parrafos o elementos que su jurado le ha indicado
	   corregir, por ello revise bien su <b>proyecto de tesis</b> asi evitar
       el rechazo del mismo.
    </p>
    
<!--<div class="alert alert-danger">
        Las subida de correcciones para dictaminacion se realizara a partir de inicio de semestre 2020-I, desde el 30 de marzo.
    </div> -->
	<p>
		<button class="btn btn-primary" onclick="lodShifs(1)">
            <span class="glyphicon glyphicon-folder-open" ></span>
			&nbsp;&nbsp; Ver mis Correcciones
        </button>

        <?php

            // MEDICINA HUMANA = 32
            //
            //if( $sess->IdCarrera == 32 ):
            if( true ):
        ?>

		<button class="btn btn-warning" onclick="lodShifs(2)">
            <span class="glyphicon glyphicon-upload" ></span>
			&nbsp;&nbsp; Subir PDF con Correcciones
        </button>

         <?php endif ?>
	</p>




	<div id="blq1" class="col-md-12" style="background: #FFFFFA">
		<ul class='nav nav-tabs'>
  			<li class='active'><a data-toggle='tab' href='#tab1'> Presidente </a></li>
  			<li><a data-toggle='tab' href='#tab2'> Primer Miembro </a></li>
  			<li><a data-toggle='tab' href='#tab3'> Segundo Miembro </a></li>
		</ul>
		<div class='tab-content'>
		<?php
		for( $i=1; $i<4; $i++ ) {

			$extra = ($i==1) ? "in active" : "";
			echo "<div id='tab$i' class='tab-pane fade $extra pre-scrollable' style='height: 320px'>";
            //unuv1.0 agregado en caso el docente haya realizo la aprobacion del proyecto
            if($i==1)
            {
                if($detTram->vb1==2)
                {
                    echo "<p><b>Aprobo, sin observaciones</b> </p>";
                }
            }
            if($i==2)
            {
                if($detTram->vb2==2)
                {
                    echo "<p><b>Aprobo, sin observaciones</b> </p>";
                }
            }
            if($i==3)
            {
                if($detTram->vb3==2)
                {
                    echo "<p><b>Aprobo, sin observaciones</b> </p>";
                }
            }

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

		// controlar vb
		if( $detTram->vb1 >0 && $detTram->vb2 >0 && $detTram->vb3 >0 ) {

		} else {

			// si falta de alguno, muestra y termina
			if(  $totCorrs1==0 or $totCorrs2==0 or $totCorrs3==0 )
			{
				echo "<br> <b>Verificación de Correcciones:</b>";
				echo "<br>Presidente      : <b> " .($detTram->vb1==0? '' : ($detTram->vb1==1? 'Observado' : ($detTram->vb1==2?'Aprobado' : -1))). "</b>";
				echo "<br>Primer Miembro  : <b> " .($detTram->vb2==0? '' : ($detTram->vb2==1? 'Observado' : ($detTram->vb2==2?'Aprobado' : -1)))."</b>";
				echo "<br>Segundo Miembro : <b> " .($detTram->vb3==0? '' : ($detTram->vb3==1? 'Observado' : ($detTram->vb3==2?'Aprobado' : -1)))."</b>";    

				return;
			}
		}
	?>
	<!-- area de subida de correcciones -->
    <div class="panel-heading">
        <h2 class="panel-title"> Correcciones de Proyecto de Tesis </h2>
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
      <form class="form-horizontal" id="frmborr" method="POST" onsubmit="grabaCorr(); return false"
            accept-charset="utf-8" enctype="multipart/form-data">
          <fieldset>
              <?php
                //
                // local Id(s), IdTramite
                //
                $sess  = $this->gensession->GetData();

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

              <!-- Button (Double) -->
              <div class="form-group">
                  <div class="col-md-6"></div>
                  <div class="col-md-5">
                      <button type="submit" class="btn btn-primary col-xs-12">
                          <span class="glyphicon glyphicon-save"></span> &nbsp; Subir Corrección
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
