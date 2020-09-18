<?php 
	$tram=$this->dbPilar->getSnapRow("tesTramites","Id=$IdProyect");
	$det=$this->dbPilar->getSnapRow("tesTramsDet","IdTramite=$tram->Id ORDER BY Iteracion DESC");
	$opciones=  
	"
		<a href='javascript:void(0)' target=_blank class='btn btn-success btn-xs'><span class='glyphicon glyphicon-send'></span> Mensaje</a>
	";
	$docu="<a href='".base_url("pilar/cordinads/memosGen/$tram->Id")."' target=_blank class='btn btn-primary btn-sm'><span class='glyphicon glyphicon-print'></span> Imprimir Memo</a>";
	// <a href='mundo' target=_blank class='btn btn-info btn-xs'><span class='glyphicon glyphicon-book'></span> Memo</a>
?>
<!-- Modal content-->
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Borrador de Tesis <?php echo $tram->Codigo; ?></h4>
	</div>
	<div class="modal-body">
		<h5> Miembros de Jurado | Documento de Referencia : <?php echo $docu;?></h5>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th width="5%">Ord</th>
					<th width="60%">Docente</th>
					<th width="10%">V°B</th>
					<th width="25%">Opciones</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>1</td>
					<td><?php echo $this->dbRepo->inDocente($tram->IdJurado1);?></td>
					<td><?php echo "[ $det->vb1 ]";?></td>
					<td><?php echo $opciones;?></td>
				</tr>
				<tr>
					<td>2</td>
					<td><?php echo $this->dbRepo->inDocente($tram->IdJurado2);?></td>
					<td><?php echo "[ $det->vb2 ]";?></td>
					<td><?php echo $opciones;?></td>
				</tr>
				<tr>
					<td>3</td>
					<td><?php echo $this->dbRepo->inDocente($tram->IdJurado3);?></td>
					<td><?php echo "[ $det->vb3 ]";?></td>
					<td><?php echo $opciones;?></td>
				</tr>
				<tr>
					<td>4</td>
					<td><?php echo $this->dbRepo->inDocente($tram->IdJurado4);?></td>
					<td><?php echo "[ $det->vb4 ]";?></td>
					<td><?php echo $opciones;?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-danger" data-dismiss="modal"> Cerrar</button>
	</div>
</div>
<!-- //Modal content-->