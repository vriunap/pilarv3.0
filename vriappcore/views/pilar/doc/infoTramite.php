  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th> Nro </th>
        <th class="col-md-1"> Participacion</th>
        <th class="col-md-1"> Codigo </th>
        <th class="col-md-6"> Titulo </th>
        <th class="col-md-1"> Fecha </th>
        <th class="col-md-1"> Tiempo </th>
        <th class="col-md-2"> Opciones </th>
        <th class="col-md-2"> Archivo</th>
      </tr>
    </thead>
    <tbody>
<?php

    $nro = $tproys->num_rows();

    $procesos = array (
        "proy nuevo",
        "para Director",
        "para Sorteo",
        "en Revisión",
        "en Dictámen",
        "P. Aprobado",    // 6
        "tram borr",   // 10
        "borr nuevo",  // 11
        "rev borr",
        "etc"
    );

    $proceclr = array(
        "btn-default",
        "btn-primary",
        "btn-success",
        "btn-success",
        "btn-warning",
        "btn-default"
    );

    $posjurado = array(
        "Presidente",
        "Primer miembro",
        "Segundo miembro",
        "Director/Asesor"
    );



    foreach( $tproys->result() as $row ) {

        echo "<tr>";

		$aut = "";
        $pos = $this->dbPilar->inPosJurado( $row, $sess->userId ); // tesTramite row
        $det = $this->dbPilar->inLastTramDet( $row->Id );

        $fecha = mlFechaNorm( $row->FechModif );


        // popUp con Id Tipo
        $archivo= "<a href='http://vriunap.pe/repositor/docs/$det->Archivo' target='_blank' class='btn btn-xs btn-info'> Archivo</a>";
        $menus = "";
        $estado = "";
		//-----------------------------------------------------------------------------------------------------
        if( $row->Estado >= 1 && $row->Estado <= 6 ) {
            $btnclr = $proceclr[ $row->Estado-1 ];
            $estado = $procesos[ $row->Estado-1 ];
            $estado = "<button class='btn btn-xs $btnclr'> $estado </button>";
        }

		//-----------------------------------------------------------------------------------------------------
		if( $row->Tipo == 1 ) {

			// director/asesor
			if( $row->Estado == 2 && $pos==4 ) {
				// OJO: controlar Jurado no dejar al miembro elegido
				$menus = "<button onclick=\"loadCorrs('docentes/corrProys',$row->Id)\" class='btn btn-sm btn-success'> Aprobación </button>";
			}

			// revision de proyectos
			if( $row->Estado == 4 ) {
				$menus = "<button onclick=\"loadCorrs('docentes/corrProys',$row->Id)\" class='btn btn-sm btn-info'> Revisar PDF </button>";
                if($pos==4){
                    $menus = "<button class='btn btn-sm btn-default'> En Revisión por Jurados</button>";
                }
			}

			// dictaminación de proyecto
			if( $row->Estado == 5 ) {

				$calif = 0;
				if( $pos == 1 ) $calif = $det->vb1;
				if( $pos == 2 ) $calif = $det->vb2;
				if( $pos == 3 ) $calif = $det->vb3;
				if( $pos == 4 ) $calif = $det->vb4;

				if( $calif < 0 ) $tipo = "Desaprobado";
				elseif( $calif > 0 ) $tipo = "Aprobado";

				if( $calif == 0 )
					$menus = "<button onclick=\"loadCorrs('docentes/corrProys',$row->Id)\" class='btn btn-sm btn-warning'> Dictaminar </button>";
				else
					$menus = "<button class='btn btn-sm btn-default'> $tipo </button>";
			}

			// visualizacion de Acta
			if( $row->Estado >= 6 ) {
				$menus .= "<a target=_blank href='../pilar/tesistas/actaProy/$row->Id' class='btn btn-sm btn-default'><span class='glyphicon glyphicon-list-alt'></span>Acta</a>";
			}
		}


		// mostrar los autores
		if( $row->Estado >= 12 )
			$aut = "<p style='font-size:9.5px;font-weight:bold; margin-bottom: 0px'>"
				 . "TESISTA(S): ".$this->dbPilar->inTesistas($row->Id)."</p>";

		//-----------------------------------------------------------------------------------------------------
		if( $row->Tipo == 2 ) {

			$estado = $posjurado[ $pos - 1 ];

			if( $row->Estado == 10 ) {
				$menus = ($row->Estado==10)? "<i>(trámite pendiente)</i>":"";
				$estado = "<button class='btn btn-xs btn-default'> $estado </button>"; // tipo jurado
				//$btnclr = $proceclr[ $row->Estado-10 ];
				//$estado .= "<button class='btn btn-xs $btnclr'>  </button>";
			}

			if( $row->Estado == 11 ) {
				$estado = "<button class='btn btn-xs btn-default'> $estado </button>"; // tipo jurado
				$menus = "<small><b>Borrador en espera de Carga de Archivo</b></small>";
			}

			if( $row->Estado == 12 ) {
				$menus = "<button onclick=\"loadCorrs('docentes/corrBorras',$row->Id)\" class='btn btn-sm btn-info'> Corregir Borrador </button>";
				$estado = "<button class='btn btn-xs btn-success'> $estado </button>"; // tipo jurado
			}

			if( $row->Estado == 13 ) {
				$estado = "<button class='btn btn-xs btn-warning'> $estado </button>"; // tipo jurado
				$menus = "<small><b>Revisión Presencial</b></small>";
			}


		}

		//-----------------------------------------------------------------------------------------------------
        // si ha sustentado poner fecha de sustentacion.
		//-----------------------------------------------------------------------------------------------------
        if( $row->Tipo == 3 ) {
            $fecha = $this->dbPilar->getOneField( 'tesSustens', 'Fecha', "IdTramite=$row->Id" );
            $fecha = "Sustentación<br>" .  mlFechaNorm($fecha);
			$menus = "<button onclick=\"loadCorrs('docentes/constJurado',$row->Id)\" class='btn btn-sm btn-info'> Ver Constancia </button>";
			$virtu = $this->dbPilar->getOneField('tesSustensSolic','Estado',"IdTramite=$row->Id");
			
			if ($virtu==2) {
				$calif = 0;
				if( $pos == 1 ) $calif = $det->vb1;
				if( $pos == 2 ) $calif = $det->vb2;
				if( $pos == 3 ) $calif = $det->vb3;
				if( $pos == 4 ) $calif = $det->vb4;

				if( $calif == 0 ) $tipo = "Desaprobado";
				elseif( $calif == 1 ) $tipo = "Aprobado";
				elseif( $calif == 2 )  $tipo = "Aprobado con Distinción";

				if( $calif == -1 )
						$menus = "<button onclick=\"loadCorrs('docentes/corrProys',$row->Id)\" class='btn btn-sm btn-danger'> Dictamen Sust </button>";
				else
					$menus = "<button class='btn btn-sm btn-default'> $tipo </button>";
				$estado = "<button class='btn btn-xs btn-warning'> Virtual </button>";
			}
			if($virtu==3){
				$menus = "<a target=_blank href='../pilar/tesistas/actaDeliberacion/$row->Id' class='btn btn-sm btn-default'><span class='glyphicon glyphicon-list-alt'></span>Acta</a>";
				$estado = "<button class='btn btn-xs btn-warning'> Virtual </button>";
			}

        }

        $dias='';
        if( $row->Estado==5 ){
            $diasRes=5-mlDiasTranscHoy($row->FechModif);
            $dias = ($diasRes<0)?"<p class='text-danger'>Fuera de Plazo de Dictamen</p>":"<p class='text-success'> $diasRes Días Restantes</p>";
        }

        if( $row->Estado==6 ){
            $dias = "";
        }

        if($row->Estado==12 OR $row->Estado==4){

            $c1 = $this->dbPilar->inNCorrecs($row->Id,$sess->userId,1);
            $c4 = $this->dbPilar->inNCorrecs($row->Id,$sess->userId,4);

            $diasRes = 12 - mlDiasTranscHoy($row->FechModif);
            //$dias = ($diasRes<0)?"<p class='text-danger'> Fuera de Plazo</p>":"<p class='text-success'> $diasRes Días Restantes</p>";
            $dias = ($diasRes<0)? "":"<p class='text-success'> $diasRes Días Restantes</p>";

            if( $row->Estado==4  && $c1>0 ) $dias = "<span class='text-success'> Correcciones realizadas </span>";
            if( $row->Estado==12 && $c4>0 ) $dias = "<span class='text-success'> Correcciones realizadas </span>";

            $dias .= (($det->vb1 + $det->vb2 + $det->vb3)==3)? "<small>Completado" : "";
        }
        // Para tener mejor vizualización y Control del Docente
        $escuela = $this->dbRepo->inCarrera("$row->IdCarrera");

        echo "<td> $nro </td>";
        echo "<td style='font-size:12px;'>".$posjurado[$pos-1]." </td>";
        echo "<td> <b>$row->Codigo</b> <br> $estado </td>";
        echo "<td> $aut <small>$det->Titulo <br><b><i style='font-size:10px;'> $escuela </i><b></small> </td>";
        echo "<td> $fecha </td>";
        echo "<td> <b>$dias</b></td>";
        echo "<td> $menus </td>";
        echo "<td> $archivo </td>";

        echo "</tr>";
        $nro--;
    }

?>
    </tbody>
  </table>


  <!-- MODAL  -->
   <div id="dlgCorrs" class="modal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 99%">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"> Revisión Electrónica </h4>
        </div>
        <div class="modal-body" style="padding: 0px 0px 0px 3px">
			<div class="row" id="vwCorrs">
				<!--
				<div class="col-md-9">
					<iframe id="frmpdf" name="frmpdf" src="" frameborder="0" width="100%"></iframe>
				</div>
				<div class="col-md-3">
					<button type="button" class="btn btn-success"> Aceptar </button>
					<button type="button" class="btn btn-danger"> Rechazar </button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
				</div>
				-->
			</div>
        </div>
		<!--
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        </div> -->
      </div>
    </div>
  </div>
  <!-- /MODAL  -->

