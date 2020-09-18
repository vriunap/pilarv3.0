<div class="col-md-3">
	<div class="page-header">
        <h2 id="timeline"> Opciones </h2>
    </div>
	<div class="list-group">
		<ul class="nav nav-pills bderecha">
			<a href="<?= base_url("pilar/tesistas");?>"  class="list-group-item"><span class="glyphicon glyphicon-home"></span> Inicio</a>
			<a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/lineasTes')" class="list-group-item" ><i class="glyphicon glyphicon-th-list"></i> Lineas de Investigación</a>
			<a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/tesHerramientas')"class="list-group-item"><span class="glyphicon glyphicon-bookmark"></span> Herramientas del Tesista</a>
			<a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/tesContacto')"class="list-group-item"><span class="glyphicon glyphicon-eye-open"></span> Contacto <span class="label label-info">Nuevo</span></a>
			<hr>
			
			<a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/tesProyecto')" class="list-group-item"><span class="glyphicon glyphicon-book"></span> Proyecto de Tesis</a>

			<a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/tesBorrador')" class="list-group-item"><span class="glyphicon glyphicon-th-large"></span> Borrador de Tesis</a>

			<a href="javascript:void(0)" style='background: #93cdff' onclick="lodPanel('panelTesis','tesistas/vwSolictaSust')" class="list-group-item" disabled=""><span class="glyphicon glyphicon-th-large"></span> Sustentación No Presencial</a>
			
			<a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/mails')" class="list-group-item"><span class="glyphicon glyphicon-th-large"></span> Notificaciones <span class="label label-info">Nuevo</span> </a>
			<!-- <a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/sorry')" class="list-group-item"><span class="glyphicon glyphicon glyphicon-calendar"></span> Sustentaciones</a> -->
			<!-- <a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/sorry')" class="list-group-item"><span class="glyphicon glyphicon-align-justify"></span> Reportes</a> -->
			<!-- <hr> -->
			<!-- <a href="javascript:void(0)" onclick="lodPanel('panelTesis','tesistas/sorry')" class="list-group-item"><span class="glyphicon glyphicon-retweet"></span> Documentos</a> -->
			<hr>

			<center><b><H4 style=' color: #1a55bf'> FORMATOS </H4></b></center>
			<?php 
				// $carr=$this->dbRepo->getSanapView('dicCarreras',"formato","Id=$sess->IdCarrera");
				$sess = $this->gensession->GetData();
				$form=$this->dbRepo->getOneField("dicCarreras","RutaArchivo","Id=$sess->IdCarrera");
			 ?>
			<a href="<?php echo $form; ?>" 
				class="list-group-item" 
				style="color:white;background-color: #1a55bf;text-align: center; font-size: 16px;">
				<span class="glyphicon glyphicon-plus-sign"></span> Descargar Formato: Proyecto de Tesis </a>
			<br>
            <?php  if( $sess->IdCarrera == 10 ): ?>
			<a href="http://vriunap.pe/vriadds/pilar/doc/formatos/Formato de tesis - Antropologia.docx"
				class="list-group-item" 
				style="color:gray;background-color: #109bc2;text-align: center; font-size: 18px;">
				<span class="glyphicon glyphicon-plus-sign"></span> Descargar Formato: Borrador de Tesis <br></a>
            <?php  endif  ?>

            <!--
			<center><b><H4 style=' color: #1a55bf'> CONVOCATORIAS ABIERTAS 2018</H4></b></center>
			<a href="javascript:void(0)" 
				onclick="lodPanel('panelTesis','tesistas/vwInsqPoster')"
				class="list-group-item" 
				style="color:white;background-color:#fb3c00;text-align: center; font-size: 18px;">
				<span class="glyphicon glyphicon-plus-sign"> Inscripción <br> <span style="color:white;">MI TESIS EN UN POSTER</span></a>
			<br>
			<a href="javascript:void(0)" 
				onclick="lodPanel('panelTesis','tesistas/vwInsq3mt')" 
				class="list-group-item" 
				style="color:white;background-color: #1a55bf;text-align: center; font-size: 18px;">
				<span class="glyphicon glyphicon-plus-sign"></span> Inscripción <br> <span style="color:#96ea58;">TESIS 3 MINUTOS</span></a>
            -->
		</ul>
    </div>
</div>
