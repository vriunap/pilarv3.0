<div class="container-fluid">
	<h3>Reportes la Escuela Profesional:: <small><?php  echo $Carrera; ?></small></h3>
	<div class="panel">

		<div class="form-group hidden-print">
			<form name="selRepo" id="selRepo" method='POST' accept-charset="utf-8">
			<label class="col-md-4 control-label" for="selectbasic">Seleccione el Reporte:</label>
			<div class="col-md-5">
				<select id="option" name="option" class="form-control">
					<option value="1">Evaluación Docente</option>
					<option value="2">Estado Actual de la Carrera</option>
					<option value="3">Resumen</option>
				</select>
			</div>
			<div class="col-md-3">
				<button class="form-control btn btn-sm btn-info" type="button" onclick="LoadForm('reporte','cordinads/selecrepo',selRepo)"> BUSCAR</button>
			</div>
			</form>
		</div>
		
	</div>
	<div class="container-fluid" id="reporte">
		
	</div>
</div>