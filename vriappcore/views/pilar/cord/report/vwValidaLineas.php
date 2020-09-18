<?php
$IdCarrera=mlGetGlobalVar("IdCarrera");
$Carrera=$this->dbRepo->getOneField("dicCarreras","Nombre","Id=$IdCarrera");
if (!$IdCarrera) {
	$Carrera="No se ha seleccionado ninguna escuela profesional.";
}
$oppcion=" ";
?>

<div class="container-fluid">
	<div class="panel">
		<h2 class="text-center">Reconsideración de Lineas de Investigación <br> <small>ESCUELA PROFESIONAL DE <?php  echo strtoupper($Carrera); ?></small></h2>
		<p>La reestructuración de Lineas de Investigación, corresponte al Oficio N° 0080 del Vicerrectorado de Investigación en el que se deberá validar a los docentes que pertenecen a las lineas de investigación de sus respectivas Escuelas Profesionales, y en el caso de que los docentes no correspondan se deberá de indicar marcando la opción de se requiere validación del Docente. <br> <small>Nota : Este procedimiento deberá realizarse por el Director de la Unidad de Investigación de la Facultad en Coordinación con el Sub Director de Investigación la Escuela Profesional Respectiva a Evaluar.</small></p>                                                                                      
		<div class="table-responsive">          
			<table class="table">
				<?php
				$flag=1;
				foreach($lineas->result() as $row){
					$nom=$this->dbRepo->getOneField("dic_LineasVRI","Nombre","Id=$row->id_lineaV");
					?>
					<thead>
						<tr>
							<th>N°</th>
							<th><?php echo $nom." |<small> ". $row->Nombre ."</small>" ;  ?></th>
							<th>OPCIONES</th>
						</tr>
					</thead>
					<tbody>
						<?php 
							$doci = $this->dbPilar->getTable( "docLineas", "IdLinea='$row->Id' order by IdDocente asc");
							$flaquis = 1;
							foreach( $doci->result() as $rina ) {
								$name=$this->dbRepo->inDocente( $rina->IdDocente );
								$tin="";
								$carrera=$this->dbRepo->inCarreDoc( $rina->IdDocente );
								if ($Carrera!=$carrera) {
									$tin=$carrera;
								}
								$butin="<button class='btn btn-xs btn-warning' onclick=\"jsLoadConfirmLin($rina->Id)\">Validar</button>";
								if($rina->Estado==0){$butin="<B style='color:red'>RECHAZADO</B>";}
								if($rina->Estado==2){$butin="<B style='color:green'>VERIFICADO</B>";}
								echo "<tr style='height: 10px;'>
								<td>$flaquis :: $rina->Id</td>
								<td><a href='javascript:void(0)' onclick=\"jsLoadModalCord($rina->IdDocente,'cordinads/jsmdlDocInfo/')\"class='btn btn-info btn-xs'>Información</a> | $name <small style='color:gray'>$tin</small></td>
								<td id='estado$rina->Id'>
									$butin
								</td>
								</tr>";
								$flaquis++;
							}
						?>
					</tbody>
					<?php 
				}
				?>
			</table>
		</div>
	</div>
	<div class="container-fluid" id="reporte">

	</div>




</div>