<?php 
	$dat=$this->dbRepo->getSnapRow("tblDocentes","Id=$IdDocente");
 ?>
<!-- Modal content-->
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<center><h4 class="modal-title">Informacion Docente</h4></center>
	</div>
	<div class="modal-body" id='chDoc'>
		<h5><b>Señor Coordinador,</b> recuerde que toda modificación es bajo su responsabilidad, y con la debida documentación del caso.</h5>
		<form class="form-horizontal" method="post" id="formUpStaDoc" name="formUpStaDoc" >
		<fieldset>
			<div class="form-group">
				<label class="col-md-4 control-label" >Docente</label>  
				<div class="col-md-6">
					<input type="hidden"  name="idDoc" value="<?php echo $IdDocente; ?>">
					<input type="text"  class="form-control input-md" disabled name="doc" value="<?php echo "$dat->Apellidos, $dat->Nombres";?>">
				</div>
			</div> 
			<div class="form-group">
				<label class="col-md-4 control-label" >Cambiar de Estado </label>  
				<div class="col-md-4">
					<select id="idStado" name="idStado" class="form-control">
						<?php 	$estadoc=$this->dbRepo->getTable("dicEstadosDoc");
								foreach($estadoc->result() as $est){
									echo "<option value='$est->Id'>$est->Nombre</option>";
								}
						?>
					</select>
				</div>
			</div> 
			<div class="form-group"> 
				<label class="col-md-4 control-label" >Documento</label>  
				<div class="col-md-6">
					<input id="just" name="just" type="text" placeholder="R.D. N° 0123 - F - UNAP" class="form-control input-md" required>
					<span class="help-block">Motivo del Cambio</span>  
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label" for="textarea">Detalle</label>
				<div class="col-md-8">                     
					<input class="form-control" id="detalle" name="detalle" placeholder="Justificación del cambio de Estado al Docente." required></input>
					<span class="help-block">Ejem. El docente fue elegido Director de Investigación de la escuela profesional.</span>  
				</div>
			</div>
			<!-- Button -->
			<div class="form-group">
			  <label class="col-md-4 control-label" for="singlebutton">Registro</label>
			  <div class="col-md-4">
			    <button type="button" class="btn btn-success" onclick="LoadForm('chDoc','cordinads/jsUpdateEstadoDoc',formUpStaDoc)"> <span class="glyphicon glyphicon-send"></span> Cambiar Estado</button>
			  </div>
			</div>
		</fieldset>
		</form>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-danger" data-dismiss="modal" onclick="lodPanel('panelCord','cordinads/vwDocentes')"> Cerrar</button>
	</div>
</div>
<!-- //Modal content-->	