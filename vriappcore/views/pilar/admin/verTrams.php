<ol class="breadcrumb">
    <small class="text-right" id="ixp">
        <?php //  $this->benchmark->elapsed_time(); ?>
        Tiempo de carga: <strong> {elapsed_time} s</strong>
    </small>
</ol>

<?php

    if( $tipo <= 2 ) {

        $ini = ($tipo==1)? 1 : 10;
        $fin = ($tipo==1)? 6 : 13;
        // OJO :
        // Estado = 14 para los sustentados completos

        $onsubm = "sndLoad('admin/innerTrams/$tipo', new FormData(fsee) )";
?>

<div class="col-md-12">
    <form id="fsee" class="form-horizontal" onsubmit="<?=$onsubm?>; return false">
        <fieldset>
            <!-- Select Basic -->
            <div class="form-group no-print">
                <input type="hidden" name="tipo" value="<?=$tipo?>"> <!-- Kind of view -->
                <label class="col-md-1 control-label" for="selectbasic"> ESTADO </label>
                <div class="col-md-2">
                    <select id="estado" name="estado" class="form-control" onchange="<?=$onsubm?>" autofocus> <!-- required -->
                        <option value="0">(todos)</option>
                        <?php
                        for( $Id=$ini; $Id<=$fin ; $Id++  )
                        {
                            $issel = ($Id==$estado)? "selected" : "";
                            echo "<option value=$Id $issel> estado $Id </option>";
                        }
                        ?>
                    </select>
                </div>
                <label class="col-md-1 control-label" for="selectbasic"> CARRERA </label>
                <div class="col-md-3">
                    <select id="carrer" name="carrer" class="form-control" onchange="<?=$onsubm?>"> <!-- this.form.submit() -->
                        <option value="0">( todos )</option>
                        <?php
                        foreach( $tcarrs->result() as $row)
                        {
                            $issel = ($row->Id==$carrer)? "selected" : "";
                            echo "<option value=$row->Id $issel> $row->Nombre </option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <input id="jurado" name="jurado" type="text" class="form-control input-md" placeholder="Nombre de Jurado">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success btn-block"> <span class="glyphicon glyphicon-search"></span> Buscar </button>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<?php } ?>

<!-- ============================================================================ -->
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th> Nro </th>
        <th class="col-md-1"> Codigo </th>
        <th class="col-md-2"> Tesista </th>
        <th class="col-md-5"> Titulo </th>
        <th class="col-md-1"> Fecha </th>
        <th class="col-md-2"> Opciones </th>
      </tr>
    </thead>
    <tbody>
<?php

    $nro = $tproys->num_rows();

    $procesos = array (
        0 => "",
        1 => "proy nuevo",
        2 => "en Asesor",
        3 => "en espera",
        4 => "en Revisión",
        5 => "en Dictámen",     // 05
        6 => "P. Aprobado",     // 06
        10 => "tram borr",      // 10
        11 => "borr nuevo",     // 11
        12 => "Revis borr",
        13 => "Gen Memos",
        14 => "Susten"
    );

    $proceclr = array(
        0 => "",
        1 => "btn-success",
        2 => "btn-primary",
        3 => "btn-danger",
        4 => "btn-success",
        5 => "btn-warning",
        6 => "btn-default",
        10 => "btn-danger",
        11 => "btn-warning",
        12 => "btn-success",
        13 => "btn-info",
        14 => "btn-default"
    );

    //-------------------------------------------------------------------
    // filtrado de acciones dependiento del tipo de tramite
    //-------------------------------------------------------------------
    foreach( $tproys->result() as $row ) {

        echo "<tr id='nr$nro'>";

        $det    = $this->dbPilar->inLastTramDet( $row->Id );
		if( ! $det ){ echo "Error detail ($row->Id)"; continue; }

        $fecha  = mlFechaNorm( $row->FechModif );
        $diasp  = mlDiasTranscHoy( $row->FechModif );
        $autors = $this->dbPilar->inTesistas( $row->Id );
        $carrer = $this->dbRepo->inCarrera( $row->IdCarrera );


        // popUp con Id Tipo
        $estado = "";
		$archi = "/repositor/docs/$det->Archivo";
		$actap = base_url("pilar/tesistas/actaProy/$row->Id");
		$menus = "<a href='$archi' class='btn btn-xs btn-info no-print' target=_blank> ver PDF </a>";

        // Estado >= 1 && <= 6 : Proyectos
        if( $row->Tipo == 1 ) {

            $btnclr = $proceclr[ ($row->Estado>15 or $row->Estado<0)? 0:$row->Estado ];
            $estado = $procesos[ ($row->Estado>15 or $row->Estado<0)? 0:$row->Estado ];
            $estado = "<button class='btn btn-xs $btnclr'> $estado </button>";
            $estado = $estado . " <br><small> (E: $row->Estado) </small> ";

			// rechazar proyecto y grabar historia
			if( $row->Estado == 1 AND $diasp>=0 )
				///$menus .= "<br> <button onclick='pyRetorna($nro,$row->Id)' class='btn btn-xs btn-danger'> Devolver </button> "
                $menus .= "<br> <button onclick='popLoad(\"admin/execRechaza/$row->Id\",$nro)' class='btn btn-xs btn-danger'> Rechazar </button> "
				        . "<button onclick='pyDirect($nro,$row->Id)' class='btn btn-xs btn-warning'> Al Asesor </button>" ;

			if( $row->Estado == 2 )
				$menus .= " | <button onclick='popLoad(\"admin/execNoDirec/$row->Id\",$nro)' class='btn btn-xs btn-danger'> Rechazar </button>";

			if( $row->Estado == 3 AND $diasp>=0 ){
				$menus .= " | <button onclick='popLoad(\"admin/execSorteo/$row->Id\",$nro)' class='btn btn-xs btn-warning'> Sorteo </button>";
			}

            // revisiones
			if( $row->Estado == 4 ) {

                $menus .= " | <button onclick='popLoad(\"admin/execCorrec/$row->Id\",$nro)' class='btn btn-xs btn-primary'> Correcs </button>";
                $menus .=  "<button onclick='popLoad(\"admin/execCancelPy/$row->Id\",$nro)' class='btn btn-xs btn-danger'> Archivar </button>" ;
                if( $diasp > 60 ) {

                    $menus .= " | <button onclick='popLoad(\"admin/execRech4/$row->Id\",$nro)' class='btn btn-xs btn-danger'> Cancelar </button>";
                    $menus .= "<br> <p style='color:red'> <b>Exceso de tiempo</b> <br> [ $det->vb1 / $det->vb2 / $det->vb3 ] </p>";
                }
                else {
				    $menus .= "<br>[ $det->vb1 / $det->vb2 / $det->vb3 ]";
                }
            }

			// dictaminaciones
			if( $row->Estado == 5  ) {

                $cance = ($det->vb1 + $det->vb2 + $det->vb3)<0? "<button onclick='popLoad(\"admin/execCancelPy/$row->Id\",$nro)' class='btn btn-xs btn-danger'> Cancelar </button>" : "";

                $menus .= " | <button onclick='popLoad(\"admin/execAprobPy/$row->Id\",$nro)' class='btn btn-xs btn-warning'> Aprobar </button> $cance";
                 $menus .= " | <button onclick='popLoad(\"admin/execCorrec/$row->Id\",$nro)' class='btn btn-xs btn-primary'> Correcs </button>";
				$menus .= "<br>[ $det->vb1 / $det->vb2 / $det->vb3 ]";

            }

			// ver Actas
			if( $row->Estado == 6 ) {
				$menus .= " | <a href='$actap' class='btn btn-xs btn-primary no-print' target=_blank> ACTA </a>";
            }


            $cont = 0;
            if( $row->IdJurado1 == $row->IdJurado2 ) $cont++;
            if( $row->IdJurado1 == $row->IdJurado3 ) $cont++;
            if( $row->IdJurado1 == $row->IdJurado4 ) $cont++;

            if( $row->IdJurado2 == $row->IdJurado1 ) $cont++;
            if( $row->IdJurado2 == $row->IdJurado3 ) $cont++;
            if( $row->IdJurado2 == $row->IdJurado4 ) $cont++;

            if( $row->IdJurado3 == $row->IdJurado1 ) $cont++;
            if( $row->IdJurado3 == $row->IdJurado2 ) $cont++;
            if( $row->IdJurado3 == $row->IdJurado4 ) $cont++;

            // alerta jurados repetidos
            if( $cont >= 1 && $row->Estado >= 4 ) {
                $menus .= "<br> <p style='color:red'> <b>Alerta: Jurado Repite</b> </p>";
            }
        }

        // Estado >= 10 && <= 14 : Borradores
        if( $row->Tipo == 2 ) {

            $btnclr = $proceclr[ $row->Estado ];
            $estado = "<button class='btn btn-xs $btnclr'> Borr (E: $row->Estado) </button>";

			if( $row->Estado==10 ) {
				$fecha = mlFechaNorm( $row->FechActBorr );
				$menus = ($row->Estado==10)? "<i>(trámite pendiente)</i>":"";
				$diasp = mlDiasTranscHoy( $row->FechActBorr );
			}

			if( $row->Estado==11 ) {
				$menus .= " | <button onclick='borDirect($nro,$row->Id)' class='btn btn-xs btn-warning'>Envia a Revisión</button>";
			}
        }

        // Con programacion de sustent y pasados
        if( $row->Tipo == 3 ) {

            // fecha de susten.
            $fechSu = $this->dbPilar->inFechSustent( $row->Id );

            $estado = ($row->Estado==13)? "Programado" : "Concluido";
            $btnclr = $proceclr[ $row->Estado ];
            $estado = "<button class='btn btn-xs $btnclr'> $estado </button>";

            $fecha =  "<small><b>Sustentación: ".mlFechaNorm($fechSu)."</b></small>";
        }


        echo "<td> $nro <br><span style='color:red;font-size:10px'>::$row->Id</span> </td>";
        echo "<td> <b>$row->Codigo</b> <br> $estado </td>";
        echo "<td> <span style='color:blue;font-size:9px'>$carrer<br></span> <small>$autors</small> </td>";
        echo "<td> <small> $det->Titulo </small> </td>";
        echo "<td> $fecha <br> <b>$diasp dia(s)</b> </td>";
        ///if( $row->Id == 5142 ) $menus = " <small>Error xD o.O  </small>";
        echo "<td> $menus </td>";

        echo "</tr>";
        $nro--;
    }

?>
    </tbody>
  </table>


<!-- ============================================================================ -->
<!-- End of Rendering area -->
<!-- ============================================================================ -->

<!-- MODAL  -->
<div id="dlgPan" class="modal" role="dialog">
<div class="modal-dialog modal-md">
  <br><br><br><br><br>
  <div class="modal-content">
	<div class="modal-header" style="background: #920738; color:white">
	  <button class="close" data-dismiss="modal" style="color:white">&times;</button>
	  <h4 class="modal-title"> MiniVentana Admin </h4>
	</div>
  <form name="fX" id="fX" method="post">
	<div class="modal-body" id="vwCorrs" style="font-size:13px">
		<!-- <div class="row"></div> -->
	</div>
  </form>
	<div class="modal-footer">
		<button class="btn btn-success" id="popOk" onclick="popProcede('admin/popExec',new FormData(fX))"> Procesar en Tiempo Record, OK ! </button>
		<button class="btn btn-danger" data-dismiss="modal"> Cerrad la Ventana, tio ! </button>
	</div>
  </div>
</div>
</div>
<!-- /MODAL  -->

