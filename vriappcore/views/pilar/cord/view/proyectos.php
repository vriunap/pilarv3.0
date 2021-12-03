<?php
    $IdCarrera=mlGetGlobalVar("IdCarrera");
    $Carrera=$this->dbRepo->getOneField("dicCarreras","Nombre","Id=$IdCarrera");
    if (!$IdCarrera) {
    	$Carrera="No se ha seleccionado ninguna escuela profesional.";
	}
	$sess = $this->gensession->GetSessionData(PILAR_CORDIS); //agregado unuv1.0 - estado sorteo
?>
<h3>Proyectos de Tesis :: <small><?php  echo $Carrera; ?></small></h3>
<div class="col-md-12">
	<h4>Listado de Proyectos</h4>
	<small id='respcord'></small>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Num</th>
				<th align="center">Código</th>
				<th>Fecha</th>
				<th align="center">Estado</th>
				<th align="center">Revisiones</th>
				<th align="center">Opciones</th>
			</tr>
		</thead>
		<tbody>
		<?php 
			$pyts=$this->dbPilar->getTable("tesTramites","IdCarrera='$IdCarrera' AND  Tipo='1' ORDER BY Estado ASC , FechModif DESC ");
			$i=1;  
			// popUp con Id Tipo
				$nro = $pyts->num_rows();
			foreach($pyts->result() as $row){
				$rowi=$this->dbPilar->getSnapRow("tesTramsDet","IdTramite='$row->Id' ORDER BY Iteracion desc"); 
		        $estado = "";
				$archi = base_url("/repositor/docs/$rowi->Archivo");
				$actap = base_url("pilar/tesistas/actaProy/$rowi->Id");
				$opt = "<a href='$archi' class='btn btn-xs btn-info no-print' target=_blank> ver PDF </a>";
				switch ($row->Estado) {
					case 1:
						// $opt="<a href='$archi' class='btn btn-xs btn-info no-print' target=_blank> ver PDF </a> | ";
						//$opt .= " |  <button onclick=\"pyDirect($nro,$row->Id)\" class='btn btn-xs btn-warning'> Enviar al Asesor</button> ";
						$opt .= " |  <button  onclick=\"jsLoadModalCord($row->Id,'cordinads/execEnvia/')\" class='btn btn-warning btn-xs'> Enviar al Asesor</button> "; //Modificacion unuv1.0 - Estado enviar proyecto al Asesor
						$opt.="<a href='javascript:void(0)' onclick=\"jsLoadModalCord($row->Id,'cordinads/execRechaza/')\" class='btn btn-danger btn-xs'>Rechazar</a>";	//Modificacion unuv1.0 - Estado rechazar proyecto por formato
						$estado="Revisión de Formato";
						break;
					case 2:
						$opt="";
						$estado="En revisión por el Asesor";
						break;
					case 3:
						//Agregado unuv1.0 - Estado sorteo de jurados
						if($sess->userLevel==4)
						{ 
							$opt.=" |  <a href='javascript:void(0)' onclick=popLoad(\"cordinads/execSorteo/$row->Id\",$nro) class='btn btn-xs btn-warning'> Sorteo</a>";
						}
						// $opt .= " | <button onclick='popLoad(\"admin/execSorteo/$row->Id\",$nro)' class='btn btn-xs btn-warning'> Sorteo </button>";
						$estado="Sorteo de Jurados"; 
						break;
					case 4:
						$opt="<a href='javascript:void(0)' onclick=\"jsLoadModalCord($row->Id,'cordinads/vwProyectosMemos/')\" class='btn btn-info btn-xs'>Memo</a>";
						if($sess->userLevel==4){
							$opt.= " |  <button onclick='popLoad(\"cordinads/execAprobPy/$row->Id\",$nro)' class='btn btn-xs btn-warning'> Dictaminar </button>";
						}	//agregado unuv1.0 - Estado revision 1			 
						$estado="Revisón por Jurados (1)";
						break;
					case 5: //agregado unuv1.0 - estado revision 2
						$opt="<a href='javascript:void(0)' onclick=\"jsLoadModalCord($row->Id,'cordinads/vwProyectosMemos/')\" class='btn btn-info btn-xs'>Memo</a>";
						if($sess->userLevel==4){
							$opt.= " |  <button onclick='popLoad(\"cordinads/execAprobPy/$row->Id\",$nro)' class='btn btn-xs btn-warning'> Dictaminar </button>";
						}	//agregado unuv1.0 - Estado revision 2			 
						$estado="Revisón por Jurados (2)";
						break;	
					case 6: //agregado unuv1.0 - estado revision 3
						$opt="<a href='javascript:void(0)' onclick=\"jsLoadModalCord($row->Id,'cordinads/vwProyectosMemos/')\" class='btn btn-info btn-xs'>Memo</a>";
						if($sess->userLevel==4){
							$opt.= " |  <button onclick='popLoad(\"cordinads/execAprobPy/$row->Id\",$nro)' class='btn btn-xs btn-warning'> Dictaminar </button>";
						}	//agregado unuv1.0 - Estado revision 3			 
						$estado="Revisón por Jurados (3)";
						break;
					case 7: //agregado unuv1.0 - estado dictamen
						if($sess->userLevel==4){
						$opt.= " |  <button onclick='popLoad(\"cordinads/execAprobPy/$row->Id\",$nro)' class='btn btn-xs btn-warning'> Dictaminar </button>"; 
									//$opt = "  <button onclick='popLoad(\"cordinads/execCancelPy/$row->Id\",$nro)' class='btn btn-xs btn-danger'> Rechazar </button>";  
						}
						$estado="En Dictamación";
						break;
					case 8:
						$opt="<a href='".base_url("pilar/tesistas/actaProy/$row->Id")."' target=_blank class='btn btn-success btn-xs'>Acta de Aprobación</a>";
						$estado="Proyecto Aprobado";
						break;
					default:
						$opt="";
						$estado=" Eroor.....! Comunicar!";
						break;
				}
				$fech=strtotime($row->FechModif);
				$fech= date("Y-m-d",$fech);
				echo "<tr id='item".$nro."'>
						<td align='center'>$nro</td>
						<td align='center' style='font-size:18px;'><a href='javascript:void(0)' 
						onclick=\"jsLoadModalCord($row->Id,'cordinads/vwInfo/')\">$row->Codigo</a> </td>
						<td align='center'>$fech</td>
						<td align='center'>$estado</td>
						<td align='center'>[ $rowi->vb1 / $rowi->vb2 / $rowi->vb3 ] </td>
						<td align='center'>
							$opt
						</td>
					  </tr>";
				$nro--;
			}  
		?>
		</tbody>
	</table>
</div>

<!--Agregado unuv1.0 - sorteo de jurado------->
<div id="dlgPan" class="modal" role="dialog">
<div class="modal-dialog modal-md">
  <br><br><br><br><br>
  <div class="modal-content">
	<div class="modal-header" style="background: #920738; color:white">
	  <button class="close" data-dismiss="modal" style="color:white">&times;</button>
	  <h4 class="modal-title"> Coordinador </h4>
	</div>
  <form name="fX" id="fX" method="post">
	<div class="modal-body" id="vwCorrs" style="font-size:13px">
		<!-- <div class="row"></div> -->
	</div>
  </form>
	<div class="modal-footer">
		<button class="btn btn-success" id="popOk" onclick="popProcede('cordinads/popExec',new FormData(fX))"> Procesar </button>
		<button onclick="lodPanel('panelCord','cordinads/vwProyectos')" class="btn btn-danger" data-dismiss="modal"> Cerrar </button>
	</div>
  </div>
</div>
</div>
<!-- /MODAL  -->