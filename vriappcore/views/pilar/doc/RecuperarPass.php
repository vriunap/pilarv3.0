<?php 
	 $sess = $this->gensession->GetData();
 ?>
<div class="col-md-12" style="background: #FFFFFF;">
	<div class="col-md-3">
	</div>
	<div class="col-md-6" style="">	
		
		<form class="form-horizontal" id="frmproy"  style="margin: 10px;" onsubmit="EnviarCambio(); return false">
			<fieldset>
				<div class="form-group" style="background: #96D1EB; color:white">
					<center>
						 <h4 class="modal-title"> Cambiar Contraseña </h4>
					</center>			
				</div>
				<div class="form-group">
					<label class="col-md-5 control-label">Nueva Contraseña :</label>
					<div class="col-md-7">
						<input id="passCambio" name="passCambio" type="password" class="form-control input-md"  value="" required="" >
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-5 control-label">Confirmar Contraseña :</label>
					<div class="col-md-7">
						<input id="passCambio2" name="passCambio2" type="password" class="form-control input-md"  value="" required="">
					</div>
				</div>
				<div id="mos"  class="form-group" style="display: none" >
		            <span id="demo" class="label label-danger"></span>		            
		        </div> 
		        <div id="mos2"  class="form-group" style="display: none" >		            
		            <span id="demo2" class="label label-primary"></span>		            
		        </div> 
		        <div id="cargando" style="display:none; color: green;"><span id="demo" class="label label-success">Cargando...</span></div>
				<div class="form-group">
					<label class="col-md-8 control-label"> </label>
					<div class="col-md-3">
						<button id ='guardar' class="form-control btn-success" value="Guardar">Cambiar</button>					
						<input id ='salir' style="display: none" type="button" onclick="cerrar();" class="form-control btn-danger" value="Salir"
						>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="col-md-3">
	</div>
</div>

<script type="text/javascript">

	function EnviarCambio()
	{
		var img = '<?php echo base_url("docentes/logout");?>';
		document.getElementById('mos').style.display='none';
		document.getElementById('mos2').style.display='none';
		$("#cargando").css("display", "inline");

		 jVRI.ajax({
            type:'POST', 
            url: "Docentes/CambiarPass",
             data : $('#frmproy').serialize(),
            success:function(arg){

            	$("#cargando").css("display", "none");       	
	        	if(arg=='')
	        	{
	        		document.getElementById('mos2').style.display='block';
	        		document.getElementById("demo2").innerHTML='Se realizo exitosamente el cambio de contraseña';
	        		document.getElementById("passCambio").value = '';	        		
	        		document.getElementById("passCambio2").value = '';
	        		document.getElementById('guardar').style.display='none';
	        		document.getElementById('salir').style.display='block';
	        		//img;
	        	}
	        	else
	        	{
	        		setTimeout(document.getElementById('mos').style.display='block',5000);
	        		document.getElementById("demo").innerHTML=arg;
	        	}
	            console.log(arg);
             
             //$('#modal_exito').modal('show');
               //document.getElementById("mensaje").innerHTML = "Se restableció la contraseña del tesista";
           },
           error:function(data){
            alert(data);
           }
         });	
	}
function cerrar()
{
	m_href = "Docentes/logout";

	 location.href = m_href;
	
}

</script>