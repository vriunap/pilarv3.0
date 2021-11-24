<div class="panel panel-success">
    <div class="panel-heading">
        <h2 class="panel-title">Formulario de Bachiller</h2>
    </div>
    <div class="panel-body" id="plops">

      <div id="plock" style="display: none; z-index: 1000; position: fixed; left: 0; top: 0; width:100%; height:100%; padding: 300px; background: rgba(0,0,0,0.5)">
          <div style="margin: 0 auto; width: 320px; height: 80px; background: white; padding: 15px">
            <center> <b> Enviando Bachiller, espere ... </b> </center>
            <div class="progress progress-striped active" style="margin-bottom:0;">
            <div class="progress-bar" style="width: 100%"></div></div>
          </div>
      </div>

      <!-- form -->
      <form class="form-horizontal" id="frmbach" method="POST" onsubmit="subBatch(); return false"
            accept-charset="utf-8" enctype="multipart/form-data">
          <fieldset>
              <div class="alert alert-info col-md-offset-1 col-md-10">
                <?php 
                    $name=$this->dbPilar->inTesista($sess->userId);
                ?>
                  <center><b>DECLARACIÓN JURADA</b><br></center>
                  Yo, <?=$name;?>, en amparo de lo dispuesto en el Artículo N° 41 de la Ley N° 27444, a efectos de cumplir con los requisitos y proseguir los trámites para presentar los resultados de mi investigación y defender mi borrador de tesis  <b>DECLARO BAJO JURAMENTO </b>, que los datos que adjunto en el presente formulario son auténticos y me sujeto a la normativa vigente de demostrarse lo contrario.
              </div>
              <!-- Info Área-->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Tesista : </label>
                  <div class="col-md-7">
                      <input name="tesista" type="text" class="form-control input-md" disabled="" value="<?=$name;?>">
                  </div>
                  <div class="col-md-4"></div>
                  <!-- <span class="help-block col-md-7">El Asesor de Proyecto deberá ser un docente Nombrado</span> -->
              </div>

              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label">Resolución Rectoral N°:</label>
                  <div class="col-md-7">
                      <input name="rrec" type="number" title="Debe poner solo números" class="form-control" required></input>
                  </div>
              </div>
              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label">Año de Resolución :</label>
                  <div class="col-md-7">
                      <input name="anio" type="number" min="1900" max="2020" step="1" value="2020" title="Debe poner un año válido."class="form-control" required></input>
                  </div>
              </div>
              <!-- date area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label">Fecha de Aprobación :</label>
                  <div class="col-md-7">
                      <input name="dater" type="date" class="form-control input-md" required>
                  </div>
              </div>

            <!-- File input-->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Seleccione Archivo :</label>
                  <div class="col-md-7">
                      <input name="nomarch" id="nomarch" type="file" class="file form-control input-md" required>
                      <span id="filemsg" class="help-block"> <center>Puede subir un PDF con un máximo de 2MB</center> </small></span>
                  </div>
              </div>

              <!-- Button (Double) -->
              <div class="form-group">
                  <div class="col-md-6"></div>
                  <div class="col-md-5">
                      <button type="submit" class="btn btn-primary col-xs-12">
                          <span class="glyphicon glyphicon-save"></span> &nbsp; Enviar Mi Bachiller
                      </button>
                  </div>
              </div>
          </fieldset>
      </form> 
      <!-- form -->
    </div>
</div>
