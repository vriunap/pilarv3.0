<!-- Menu de Docente  -->
  <div class="col-md-2 col-sm-3 sidemenu">
    <div class="docente-info">
        <h4 class="docente-titulo"> BIENVENIDO A <span>PILAR</span></h4>
         <!-- <img class="img-responsive  img-circle docente-img" src="<?= base_url("vriadds/pilar/imag/pilar-user.png");?>" alt="Docente - PILAR"> -->

        <center>
        <?PHP

            if( $media = $this->genapi->getDataPer($sess->userDNI) )
                echo "<img width=110 src='$media->foto' class='img-responsive'><hr style='margin: 8px; border: 1px dotted gray'>";
            else
                echo ">>Sin imagen<< <hr>";

        ?>
        <h3 class="docente-name"> <?php echo $this->dbPilar->after(",","$sess->userName"); echo "<br>".$sess->userMail; ?> </h3> <h5 class="docente-cargo">Docente Universitario</h5>
    </div>

    <div class="list-group">
      <br><br>
      <ul class="nav nav-pills bderecha">
        <a href="<?=base_url("pilar/docentes");?>" class="list-group-item"><span class="glyphicon glyphicon-home"></span> Inicio </a>
        <a onclick="$('#panelView').load('docentes/infoDocente')"  href="javascript:void(0)" class="list-group-item"><span class="glyphicon glyphicon-user"></span> Mis Datos y Pérfil </a>
        <a onclick="lodPanel('panelView','docentes/infoTrams/1')"  href="javascript:void(0)" class="list-group-item"><span class="glyphicon glyphicon-th-list"></span> Proyectos de Tesis</a>
        <a onclick="lodPanel('panelView','docentes/infoTrams/2')"  href="javascript:void(0)" class="list-group-item"><span class="glyphicon glyphicon-book"></span> Borradores de Tesis</a>
        
        <a onclick="lodPanel('panelView','docentes/infoTrams/3')" href="javascript:void(0)" class="list-group-item" style="background-color: #93cdff"><span class="glyphicon glyphicon-camera"></span> Sustentaciones</a>
        <hr>
        <!-- lodPanel('panelView','docentes/infoConsta') -->
        <a onclick="lodPanel('panelView','docentes/infoTrams/3')" href="javascript:void(0)" class="list-group-item"><span class="glyphicon glyphicon-book"></span> Constancias</a>


        <a  href="<?=base_url('tramiteonline');?>" class="list-group-item"><span class="glyphicon glyphicon-book"></span> Nuevos Reglamentos </a>
        <hr>
    <!--     <a onclick="lodPanel('panelView','docentes/programaLaspau')" href="javascript:void(0)" class="list-group-item"><center></span>Convocatoria <br> LASPAU </center>
            <img class="img-responsive pull-left vri-logo-small" src= "<?php //echo base_url("vriadds/vri/web/laspau.png");?>" >
        </a> -->
      </ul>
    </div>
  </div>
<!-- /Menu de Docente  -->
