<div class="panel panel-info">
    <div class="panel-heading">
        <h2 class="panel-title"> Registro de Borrador de Tesis </h2>
    </div>
    <div class="panel-body" id="plops">

      <div id="plock" style="display: none; z-index: 1000; position: fixed; left: 0; top: 0; width:100%; height:100%; padding: 300px; background: rgba(0,0,0,0.5)">
          <div style="margin: 0 auto; width: 320px; height: 80px; background: white; padding: 15px">
            <center> <b> Enviando datos y borrador, espere ... </b> </center>
            <div class="progress progress-striped active" style="margin-bottom:0;">
            <div class="progress-bar" style="width: 100%"></div></div>
          </div>
      </div>

      <!-- form -->
      <form class="form-horizontal" id="frmborr" method="POST" onsubmit="grabaBorr(); return false"
            accept-charset="utf-8" enctype="multipart/form-data">
          <fieldset>
              <?php
                //
                // local Id(s), IdTramite
                //
                $sess  = $this->gensession->GetData();

                $tram = $this->dbPilar->inTramByTesista($sess->userId);
                $autors = $this->dbPilar->inTesistas( $tram->Id );
                $titulo = $this->dbPilar->inLastTramDet( $tram->Id )->Titulo;
                $lineai = $this->dbRepo->inLineaInv( $tram->IdLinea );
              ?>

              <!-- Text input-->
              <div class="form-group">
                  <div class="col-md-1"></div>
                  <div class="col-md-10 alert alert-warning">
                    <strong> Complete la información y envie a PILAR </strong>
                  </div>
                  <div class="col-md-1"></div>
              </div>

              <!-- area de datos a almacenar en la BD -->
              <!-- select areas -->
              <div class="form-group success">
                  <label class="col-md-4 control-label" style="color:green"> Linea de Investigación </label>
                  <div class="col-md-7" style="padding-top:7px"> <?=$lineai?> </div>
              </div>

              <!-- select areas -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Autor(es) </label>
                  <div class="col-md-7" style="padding-top:7px"> <?=$autors?> </div>
                  <div class="col-md-4"></div>
              </div>
              <hr>

              <!-- Text input-->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Seleccione Archivo </label>
                  <div class="col-md-7">
                      <input name="nomarch" id="nomarch" type="file" class="file form-control input-md" required>
                      <span id="filemsg" class="help-block"> <center>Puede subir un PDF con un máximo de 5MB</center> </small></span>
                  </div>
                  <!--
                  <input id="nomarch" name="nomarch" type="text" class="form-control input-md" required readonly>
                  <div class="col-md-2">
                      <input type="button" class="btn btn-success col-xs-12" value="Buscar" onclick="tesDownFile('pdf')">
                  </div>
                  -->
              </div>

              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Titulo de Borrador </label>
                  <div class="col-md-7">
                      <textarea name="nomproy" type="text" class="form-control" rows="3" style="text-transform: uppercase" required><?=$titulo?></textarea>
                  </div>
              </div>
              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Resumen (Abstract) </label>
                  <div class="col-md-7">
                      <textarea name="resumen" type="text" class="form-control" rows="3" placeholder="acepta varias lineas" required></textarea>
                  </div>
              </div>
              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Palabras clave (keywords) </label>
                  <div class="col-md-7">
                      <input name="pclaves" type="text" class="form-control input-md" placeholder="separadas por coma y acaba en punto" required>
                      <!-- <span class="help-block">  </span> -->
                  </div>
              </div>
              <!-- Text area -->
              <div class="form-group success">
                  <label class="col-md-4 control-label"> Conclusiones </label>
                  <div class="col-md-7">
                      <textarea name="conclus" type="text" class="form-control" rows="3" placeholder="acepta varias lineas" required></textarea>
                  </div>
              </div>

              <!-- Button (Double) -->
              <div class="form-group">
                  <div class="col-md-6"></div>
                  <div class="col-md-5">
                      <button type="submit" class="btn btn-primary col-xs-12">
                          <span class="glyphicon glyphicon-save"></span> &nbsp; Enviar Proyecto
                      </button>
                  </div>
              </div>
          </fieldset>
      </form>
      <!-- form -->
    </div>
</div>
