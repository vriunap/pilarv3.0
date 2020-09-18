<body class="bg-1">
    <!--
    <div class="modal fade" id="msgPosterX" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: gray">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Comunicado</h4>
                </div>
                <div class="modal-body" style="padding: 0px">

                    <div id="myCarousel" class="carousel slide" data-ride="carousel">

                        <div class="carousel-inner">
                            <div class="item active">
                                <a>
                                    <img src="vriadds/vri/web/convocatorias/cierrediciembre20182.jpg"> </a>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                       <button type="button" class="btn btn-danger" data-dismiss="modal"> Cerrar </button>
                </div>

            </div>
        </div>
    </div>
    -->

<!--  fin mensaje a la nación-->


<div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand" rel="home" href="<?=base_url("pilar")?>" title="Universidad Nacional del Altiplano | Vicerrectorado de Investigación">
                <img class="img-responsive" style="max-width:160px; margin-top: -15px;"
                     src="<?=base_url("vriadds/pilar/imag/logos-u-v-p.png");?>">
            </a>

        </div>
        <div id="navbar" class="collapse navbar-collapse navbar-responsive-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="<?=base_url("pilar")?>">Inicio</a></li>
                <li><a href="<?=base_url("pilar/docentes")?>">Docentes</a></li>
                <li><a href="<?=base_url("pilar/tesistas")?>">Tesistas</a></li>
                <li><a href="<?=base_url("pilar/cordinads")?>">Coordinadores</a></li>
                <li><a href="<?=base_url("pilar/sustentas")?>">Sustentaciones</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="container info-pilar margin">
  <img class="img-responsive logo-pilar3" src="<?=base_url("vriadds/pilar/imag/pilar-n.png");?>">
  <h3 id="name-pilar">Plataforma de Investigación Universitaria <br>Integrada a la Labor Académica con Responsabilidad </h3><h4><i>Universidad Nacional del Altiplano - Puno</i></h4>
</div>

<div class="container ">
  <div class="col-md-12 contenido1">
      <div class="col-md-9 bg-white margin">
        <div class="titulo">Presentación</div>
        <p class="description">
            La Universidad Nacional del Altiplano mediante el Vicerrectorado de Investigación y la Plataforma <i>PILAR</i>  
            para Docentes, Tesistas y Coordinadores tienen la información disponible para realizar la
            subida, calificacion, revisión y posterior dictaminación de proyectos de investigación de pregrado
            conducentes a la obtención del título profesional.
        </p>
        <div class="row">
              <div class="col-xs-12 col-md-4 btn-acces-pilar">
                <a id="1tes" onclick="openNav(this.id)" class="btn btn-default btn-user bg-teal"><span class="glyphicon glyphicon-ok-circle"></span> <br/>Tesista</a>
              </div>
              <div class="col-xs-12 col-md-4 btn-acces-pilar">
                <a id="2doc" onclick="openNav(this.id)" class="btn btn-default btn-user bg-green" role="button"><span class="glyphicon glyphicon-list-alt"></span> <br/>Docente</a>
              </div>
              <div class="col-xs-12 col-md-4 btn-acces-pilar">
                <a id="3coord" onclick="openNav(this.id)" class="btn btn-default btn-user bg-red-ligth" role="button"><span class="glyphicon glyphicon-question-sign"></span> <br/>Coordinador</a>
              </div>
        </div>
        <br>
        <div class="row">
              <div class="col-xs-12 col-md-12 btn-acces-pilar">
                <a class="btn btn-preg bg-teal"><span class="glyphicon glyphicon-cog"></span> <br/>Consultas en Linea ( En Construcción ) </a>
              </div>
        </div>
      </div>
      <div class="col-md-3 bg-white margin-bd">
        <div class="titulo">Reglamentos y Manuales</div>
        <div class="list-group">
          <ul class="nav nav-pills bderecha"> 
            <a target="_blank" href="<?php echo base_url("vriadds/pilar/doc/reglamentoPilar2018.pdf");?>" class="list-group-item blink"><span class="glyphicon glyphicon-book"></span> Reglamento Proyectos <span class="label label-warning"> 2018 </span></a>
            <a target="_blank" href="<?php echo base_url("vriadds/pilar/doc/resReglaBorrador.pdf");?>" class="list-group-item blink"><span class="glyphicon glyphicon-book"></span> Reglamento de Borrador</a>
            <a target="_blank" href="<?php echo base_url("web/etica");?>" class="list-group-item blink"><span class="glyphicon glyphicon-book"></span> Procedimientos Ética en Investigación  <span class="label label-warning"> Nuevo </span></a> 
            <hr>
            <a target="_blank" href="<?php echo base_url("vriadds/pilar/doc/Formato-Proy-Tesis-2016.docx");?>" class="list-group-item blink"><span class="glyphicon glyphicon-bookmark"></span> Formato de Proyecto</a>
            
            <a target="_blank" href="<?php echo base_url("vriadds/pilar/doc/Formato-Borrador-Tesis-2017.docx");?>"  class="list-group-item blink"><span class="glyphicon glyphicon-bookmark"></span> Formato de Borrador</a>
            <hr>
            <a href="#" class="list-group-item blink"><span class="glyphicon glyphicon-th-list"></span> Manual para Docentes</a>
            <a target="_blank" href="<?php echo base_url("vriadds/pilar/doc/manual_tesistav31.pdf");?>" class="list-group-item blink"><span class="glyphicon glyphicon-th-list"></span> Manual para Tesistas </a>
            <a href="#" class="list-group-item blink"><span class="glyphicon glyphicon-th-list"></span> Manual para Coordinadores</a>
            <a target="_blank" href="<?php echo base_url("/pilar/web/preguntas");?>" class="list-group-item blink"><span class="glyphicon glyphicon-th-list"></span> Preguntas frecuentes</a>
          </ul>
        </div>
      </div>
      <div class="col-md-12 bg-white">
        <div class="titulo">Herramientas del Investigador</div>
      </div>
      <div class="col-md-12 bg-vino footer">
        Universidad Nacional del Altiplano<br>
        Vicerrectorado de Investigación<br>
        Dirección General de Investigación<br>
        &copy; Plataforma de Investigación y Desarrollo
      </div>
  </div>
</div>

  <!-- Inicio Login Tesista-->
  <div id="Tesistas" class="overlay">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="overlay-content">
        <div class="login-page">
          <div class="form">
            <img class="img-responsive login-logo" src="<?php echo base_url("vriadds/pilar/imag/pilar-tes.png");?>">
            <h4 class="login-title-tes">Área de Tesistas</h4>
              <div id="pmsg" class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span>
              </div>
              <!--Ud., no está registrado.<br><small>Apersonarse a las oficinas del VRI con una copia de su Ficha y/o Bachiller</small>-->
              <!-- *** -->
            <form class="register-form" name="frmoti" onsubmit="callSave(); return false">
              <div id="pdta" style="color: black">
                <input id="cod" name="cod" type="number" placeholder="Codigo de Matricula" required="" />
                <input id="dni" name="dni" type="number" placeholder="Número de D.N.I." required="" />
                <button onclick="callOTI()" class="login-btn-tesista" type="button"> Verificar mis Datos </button>
              </div>
              <p class="message">Ya estas Registrado? <a rel="nofollow" onclick="register()" rel="noreferrer">Ingresar</a></p>
            </form>
              <!-- *** -->
            <form class="login-form" name="logtes" onsubmit="callLoginTes(); return false" method="post">
              <input type="email" name="mail" placeholder="su correo personal" required="" />
              <input type="password" name="pass" placeholder="contraseña" required="" />
              <button type="submit" class="login-btn-tesista"> Ingresar </button>
              <p class="message"><i>Usted es Tesista Nuevo?</i> <a  rel="nofollow" onclick="register()" href="javascript:void(0)">Crear una Cuenta Nueva </a></p>
            </form>
              <a class="text-center" onclick="closeNav()"><span class="glyphicon glyphicon-remove-circle gi-1x"></span></a>
          </div>
        </div>
    </div>
  </div>
  <!-- Finaliza Login Tesista -->



  <!-- Inicio Login Docente-->
  <div id="Docentes" class="overlay">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="overlay-content">
        <div class="login-page">
          <div class="form">
            <img class="img-responsive login-logo" src="<?php echo base_url("vriadds/pilar/imag/pilar-doc.png");?>">
            <h4 class="login-title-doc">Área de Docente</h4>
            <div id="qmsg" class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> </div>

            <form class="register-form">
              <!--
              <input type="number" max="999999" placeholder="Codigo de Docente"/>
              <input type="email" placeholder="correo registrado" required=""/>
              <input type="text" placeholder="Numero Celular" required=""/>
              <input type="text" placeholder="Fecha Nacimiento" required=""/>
              <input type="text" placeholder="Direccion" required=""/>
              <input type="password" placeholder="contraseña" required=""/>
              <input type="password" placeholder="repite la contraseña" required=""/>
              <button class="login-btn-tesista">Crear</button>
              -->
              <p class="message"> El Registro se realiza en Plataforma con una copia de su resolución de contrato. <a href=""> Cerrar </a></p>
            </form>

            <form name="logdoc" class="login-form" onsubmit="callLoginDoc(); return false" method="post">
              <input name="mail" type="text" placeholder="su correo personal" required="" />
              <input name="pass" type="password" placeholder="contraseña" required="" />
              <button class="login-btn-docente">Ingresar</button>
              <p class="message"><i>Es su Primera Vez Aqui?</i>
                  <a rel="nofollow" onclick="register()" href="javascript:void(0)"> Crear una Cuenta Nueva </a>
              </p>

            </form>
            <a class="text-center" onclick="closeNav()"><span class="glyphicon glyphicon-remove-circle gi-1x"></span></a>
          </div>
        </div>
    </div>
  </div>
  <!-- Finaliza Login Docente -->
  <!-- Inicio Login Coordinador-->
  <div id="Coordinadores" class="overlay">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="overlay-content">
        <div class="login-page">
          <div class="form">
            <img class="img-responsive login-logo" src="<?php echo base_url("vriadds/pilar/imag/pilar-cord.png");?>">
            <h4 class="login-title-cord">Área de Coordinadores</h4>
            <div id="cmsg" class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> </div>
            <form class="register-form">
              <input type="email" placeholder="correo@unap.edu.pe" required=""/>
              <input type="password" placeholder="contraseña" required="" />
              <input type="text" placeholder="email address" required="" />
              <button class="login-btn-coord">Crear</button>
              <p class="message">Ya estas Registrado? <a rel="nofollow" onclick="register()" rel="noreferrer">Ingresar</a></p>
            </form>

            <form name="logcor" class="login-form" onsubmit="callLoginCor(); return false" method="post">
              <input name="user" type="text" placeholder="usuario(a)" required=""/>
              <input name="pass" type="password" placeholder="contraseña" required=""/>
              <button class="login-btn-coord">Ingresar</button>
              <p class="message "><i>Coordinadoor Nuevo?</i> <a  rel="nofollow" onclick="register()" href="#">Crear una Cuenta Nueva </a></p>
            </form>
            <a class="text-center" onclick="closeNav()"><span class="glyphicon glyphicon-remove-circle gi-1x"></span></a>
          </div>
        </div>
    </div>
  </div>
  <!-- Finaliza Login Docente -->


  <!-- MODAL  -->
   <div class="modal" id="myModal3" role="dialog">
    <div class="modal-dialog">
      <br><br><br>
      <!-- Modal content-->
      <div class="modal-content modal-pilar">
        <div class="modal-header modal-pilar-title">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">COMUNICADO</h4>
        </div>
        <div id="game" style="padding: 15px; text-align: center"> <!-- remove -->
            <div class="modal-body modal-pilar modal-pilar-content">

              <img class="img-responsive" src="/vriadds/vri/img/comunicadoCOVID.jpeg">

            </div>
            <div class="modal-footer">
                <button class="btn btn-xs btn-default pull-left" onclick="loadGame()"> Me siento con suerte, haré click! </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
            </div>
        </div> <!-- remove -->
      </div>

    </div>
  </div>
  <!-- /MODAL  -->



<!-- MODAL ENERO 2018 -->




<!-- FIN MODAL ENERO 2018 -->





<!-- modal steps -->
<div class="modal fade bs-example-modal-lg" tabindex="-4" role="dialog"  id="tutorial" >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
          <!-- Wrapper for slides -->
          <div class="carousel-inner">
              <div class="item active">
                  <img class="img-responsive" src="<?=base_url("vriadds/pilar/imag/img3mt/paso0.jpg");?>" alt="...">
              </div>
              <div class="item">
                  <img class="img-responsive" src="<?=base_url("vriadds/pilar/imag/img3mt/paso1.jpg");?>" alt="...">
              </div>
              <div class="item">
                  <img class="img-responsive" src="<?=base_url("vriadds/pilar/imag/img3mt/paso2.jpg");?>" alt="...">
              </div>
              <div class="item">
                  <img class="img-responsive" src="<?=base_url("vriadds/pilar/imag/img3mt/paso3.jpg");?>" alt="...">
              </div>
              <div class="item">
                  <img class="img-responsive" src="<?=base_url("vriadds/pilar/imag/img3mt/paso4.jpg");?>" alt="...">
              </div>
          </div>
          <!-- Controls -->
          <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
              <span class="glyphicon glyphicon-chevron-left"></span>
          </a>
          <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
              <span class="glyphicon glyphicon-chevron-right"></span>
          </a>
        </div>
      </div>
    </div>
</div> <!-- modal steps -->


<script>


$(document).ready(function(){
  $("#myModal3").modal('show');
});


</script>




</body>
</html>
