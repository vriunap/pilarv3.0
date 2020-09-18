<?php 
	$tram=$this->dbPilar->getSnapRow("tesTramites","Id=$IdProyect");
	$det=$this->dbPilar->getSnapRow("tesTramsDet","IdTramite=$tram->Id");
	// <a href='mundo' target=_blank class='btn btn-info btn-xs'><span class='glyphicon glyphicon-book'></span> Memo</a>
?>
<!-- Modal content-->
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<center><h4 class="modal-title">Información del Proyecto <br> <small> <?php echo $det->Titulo; ?></small></h4></center>
	</div>
	<div class="modal-body">
		<div>
			<?php 
				if ($tram->Tipo==1) {
					$doc1=$this->dbRepo->inDocente("$tram->IdJurado4");
					echo "Director de Tesis: $doc1 <br>";
					if($tram->IdTesista2)echo "<br><b class='text-danger'> NOTA : Este proyecto tiene 2 Tesistas</b>";
				}
				if ($tram->Tipo>=2){
					$doc1=$this->dbRepo->getSnapRow('tblDocentes',"Id=$tram->IdJurado1");
					$doc2=$this->dbRepo->getSnapRow('tblDocentes',"Id=$tram->IdJurado2");
					$doc3=$this->dbRepo->getSnapRow('tblDocentes',"Id=$tram->IdJurado3");
					$doc4=$this->dbRepo->getSnapRow('tblDocentes',"Id=$tram->IdJurado4");
					echo "<br>Presidente :  $doc1->Apellidos, $doc1->Nombres |Tel. $doc1->NroCelular | $doc1->Correo";
					echo "<br>Jurado 1 :  $doc2->Apellidos, $doc2->Nombres |Tel. $doc2->NroCelular | $doc2->Correo";
					echo "<br>Jurado 2 :  $doc3->Apellidos, $doc3->Nombres |Tel. $doc3->NroCelular | $doc3->Correo";
					echo "<br>Jurado 3 :  $doc4->Apellidos, $doc4->Nombres |Tel. $doc4->NroCelular | $doc4->Correo <br>";
				}
				if($tram->IdTesista2)echo "<br><b class='text-danger'> NOTA : Este proyecto tiene 2 Tesistas</b>";

				echo "Linea de Investigación:".$this->dbRepo->inLineaInv($tram->IdLinea);
			 ?>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-danger" data-dismiss="modal"> Cerrar</button>
	</div>
</div>
<!-- //Modal content-->