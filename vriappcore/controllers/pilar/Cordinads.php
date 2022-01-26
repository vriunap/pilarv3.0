 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************************
*
*   Project: Integrated VRI - [codename: BoobieMovie]  ::: core 3.1.3
*
*   Software Architects:
*     M.Sc. Ramiro Pedro Laura Murillo
*     Ing. Fred Torres Cruz
*     Ing. Julio Cesar Tisnado Puma
*
*   Begin coding Date: 20 - 03 - 2017 
*
****************************** *********************************************/


include( "absmain/mlLibrary.php" );


define( "PILAR_CORDIS", "AdmCoords" );
define( "ANIO_PILAR", "2021" );


class Cordinads extends CI_Controller { 
/***************************************************************************
* Cambio de Roles de Usuario : 02/05/2018
* Operaciones del Coordinador - PILAR V.3.0
*      (1) Actualizó el estado de un docente.
*      (2) Recepción de Ejemplares de Borrador
*      (3) Notificó a un Docente que tiene proyectos pendientes
*      (3) Revisar formato  y pasar el proyecto al director.
*      (4) Validar Linea de Investigación
*      (5) Rechazar Proyecto de Tesis por FORMATO
*
*      $operaciones=array(
      1   =>'Actualizó el estado de un docente',
      2   =>'Recepcionó Ejemplares', 
      3   =>'Publica Sustentación',
      4   =>'Validar Linea de Investigación',
      5   =>'Acceso a Cuenta'
      6   => 'Genero Acta de Sustentación'
);
*
**************************************************************************/
public function __construct()
{
   parent::__construct();
   $this->load->model('dbPilar');
   $this->load->model('dbRepo');
   $this->load->model('dbFedu');
   $this->load->library("GenSession");
   $this->load->library('GenSexPdf');
   $this->load->library('GenMailer');
   
}

public function linindex()
{
//$table = $this->dbpilar->getTable( 'tesTramites' );

//$this->load->view( "pilar/base", array( 'table'=>$table ) );
}

public function index()
{
   if( mlPoorURL() )
      redirect( mlCorrectURL() );
//
// en caso de admin crear nueva session admin por App
//

   $sess = $this->gensession->GetSessionData(PILAR_CORDIS);
   if( !$sess ){
      //echo "Err no sess";
      redirect( base_url("pilar"), 'refresh');
      return;
   }

   if( $sess->status==0 ){
      $this->logout();
      return;
   }


   // Área de Administración del Administrador
   if($sess->userLevel==1){
      $escuelas=$this->dbRepo->getTable("dicCarreras");
      $this->load->view("pilar/cord/header",array('escuelas' =>$escuelas,'sess'=>$sess ));
//      $this->load->view("pilar/cord/view/proyectos");

   }

   // Área de Administración del Director de Investigación
   if($sess->userLevel==2){
      $escuelas=$this->dbRepo->getTable("dicCarreras","IdFacultad='$sess->IdFacultad'");
      $this->load->view("pilar/cord/header",array('escuelas' =>$escuelas,'sess'=>$sess ));
   }
   // Área de Administración del Sub Director de Investigación
   if($sess->userLevel==3){
      $useri=$this->dbPilar->getSnapRow("tblSecres","Id=$sess->userId");
      $escuelas=$this->dbRepo->getSnapView("dicCarreras","Id=$useri->IdCarrera");
      $this->load->view("pilar/cord/header",array('escuelas' =>$escuelas,'sess'=>$sess ));
   }

   // Área de Administración de la Secretaría de la Direccion de Investigación.
   if($sess->userLevel==4){
      $escuelas=$this->dbRepo->getSnapView("dicCarreras","IdFacultad=$sess->IdFacultad");
      $this->load->view("pilar/cord/header",array('escuelas' =>$escuelas,'sess'=>$sess ));
   }
}




// Cambio de Carrera en el Panel de Coordinador 
public function setCarrera($idCarr){
   $sess = $this->gensession->GetSessionData(PILAR_CORDIS);
   mlSetGlobalVar('IdCarrera',$idCarr);
      // Agente de Acceso 
   $ip = mlClientIP();

   $this->logCordinads("C","5","Acceso a la Cuenta",$ip);
   if(mlGetGlobalVar("IdCarrera")){
      $this->load->view("pilar/cord/menu");
      $this->load->view("pilar/cord/panel",array('sess' =>$sess ));
   }
   else{
      echo "<div class='text-center'> <h4>Estimado $sess->userName, es necesario es necesario seleccionar una escuela profesional para porder observar los cambios.<br><small>Esquina superior derecha.</small></h4></div>";
   }
}
// Vista de Inicio Cordinador
public function vwInicio(){
   $this->load->view('pilar/cord/view/inicio');
}
// Vista de Inicio Director de Investigación
public function vwProy2018(){
   $this->load->view('pilar/cord/direc/vwProy2018');
}
// Vista de Docentes Cordinador
public function vwDocentes(){
   $this->load->view('pilar/cord/view/docentes');
}

// Vista de Docentes Cordinador
public function vwFedu(){
   $this->load->view('pilar/cord/info/fedu');

}

// js Docente Info
public function jsmdlDocInfo($idDoc){
   $this->load->view("pilar/cord/view/docentes_infomdl",array('IdDocente' =>$idDoc));
}
// js Docente Opciones
public function jsmdlDocOpc($idDoc){
   $this->load->view("pilar/cord/view/docentes_opcmdl",array('IdDocente' =>$idDoc));
}

// js Actualizacion de Estado Docente desde este módulo de coordinación
public function jsUpdateEstadoDoc()
{
   $idDoc = mlSecureRequest("idDoc");
   $idStado = mlSecurePost("idStado");
   $just = mlSecurePost("just");
   $detalle = mlSecurePost("detalle");
// Insertamos el Log de Cambio de Estaodo 
   $this->logCordinads("C","1","Cambio estado Docente : $just","($idDoc to $idStado )$detalle ");
// Cambiamos el Estado de Docente
   $this->dbRepo->Update('tblDocentes',array('Activo'=>$idStado),$idDoc);
// Log Estado Docentes
   $this->dbRepo->Insert('tblLogDocentes',array(
      'IdDocente'     =>$idDoc,
      'EstadoAnt'     =>$this->dbRepo->getOneField("tblDocentes","Activo","Id=$idDoc"),
      'EstadoNvo'     =>$idStado,
      'Detalle'       =>$detalle,
      'Documento'     =>$just,
      'Fecha'         =>mlCurrentDate()           
   )); 
// Mensaje de Éxito
   echo "<h5 class='bg-success'>Estado de Docente Actualizado <span class='badge'> Recuerda Actualizar la Lista de Docentes</span></h5>";
}

// Vista de Proyectos de Tesis Coordinador
public function vwProyectos(){
   $this->load->view("pilar/cord/view/proyectos");

    // $this->innerTrams( 1 
}
// Revisar Formato de Proyecto
public function vwRevisaFormatoPy($idPy){
   $this->load->view("pilar/cord/view/revisa_formato",array('IdProyect'=>$idPy));
}
// vista de Memo En Modal
public function vwProyectosMemos($idPy){
   $this->load->view("pilar/cord/view/proyectos_memmdl",array('IdProyect'=>$idPy));
}
// Vista de Proyectos de Tesis Coordinador
public function vwBorradores(){
   $this->load->view("pilar/cord/view/borradores");
}
public function vwInfo($idPy){
   $this->load->view("pilar/cord/view/infopb",array('IdProyect'=>$idPy));
}
// vista de Memo En Modal
public function vwBorradoresMemos($idPy){
   $this->load->view("pilar/cord/view/borradores_memmdl",array('IdProyect'=>$idPy));
}
public function vwRecibirEjemplares($idPy){
   $this->load->view("pilar/cord/view/borradores_ejempmdl",array('IdProyect'=>$idPy));
}

public function recepEjemplares($id){
   $sess = $this->gensession->GetSessionData(PILAR_CORDIS);       
   $row=$this->dbPilar->getSnapRow("tesTramites","Id=$id");
   if($row){
      $this->logCordinads("C","2","Recepcionó Ejemplares de Borrador","($id) La coordinación recepcionó los ejemplares del borrador de tesis");
// Cambiamos el Estado de Docente
      $this->dbPilar->Update('tesTramites',array('Estado'=>13,'FechModif'=>mlCurrentDate()),$id);
// Insertar memos faltaa tiooo
      $this->inGenMemo($row,5);

      $tesist=$this->dbPilar->inCorreo($row->IdTesista1);
      $presid=$this->dbRepo->inCorreo($row->IdJurado1);
      $titulo="Presentación de Ejemplares de Borrador";
      $mensaje="<p align='justify' >Estimado(s) Tesista(s) <b>".$this->dbPilar->inTesistas($id).",</b> <br><br> La Coordinación de Investigación de la Escuela Profesional de <b>".$this->dbRepo->inCarrera($row->IdCarrera)."</b> ha recepcionado 4 ejemplares del borrador de tesis el cual usted realizó las correcciones registradas en PILAR .</p>";

      $titulo1="Dictamen de Borrador de Tesis";
      $mensaje1="<b>$titulo1</b><br><br><p align='justify'>Estimado ".$this->dbRepo->inDocenteEx($row->IdJurado1).", <br><br> Se le 
      comunica que el Bach. ".$this->dbPilar->inTesistas($id)." ha realizado las correcciones del borrador de tesis titulado "
      .$this->dbPilar->inTitulo($id)." y a su vez presentó 4 ejemplares anillados del mismo en la coordinación de 
      investigación de la Escuela Profesional de ".$this->dbRepo->inCarrera($row->IdCarrera).", en cumplimiento del 
      Art. N° 8 del reglamento de presentación de borradores de tesis.<br>
      <br>Por lo que se le informa que usted deberá convocar a una reunión del jurado con presencia del bachiller en 
      un <b>plazo máximo de cinco (05) días hábiles</b> luego de recibida esta notificación a fin de
      realizar las observaciones finales y dictaminar el borrador de tesis.<br> <br> La fecha de
      reunión será informada a la Coordinación de Investigación respectiva, quien a su vez
      citará a los miembros del Jurado e informará a la plataforma PILAR de la misma.La reunión de
      dictamen se llevará a cabo en la Sala de Profesores de la Facultad a la que
      pertenezca el bachiller. <b>Art. N°9</b></p> ";

      $this->logTramites( $sess->userId, $row->Id, "Recepción de Ejemplares", "Pdf Corregido + Memos" );

      $this->logCorreo($row->IdTesista1,0,$tesist,$titulo,$mensaje);


      $this->logCorreo(0,$row->IdJurado1,$presid,$titulo1,$mensaje1);
      echo "<b>NOTIFICACIÓN DE RECEPCIÓN POR EMAIL</b><br><br> Se ha notificado a ".$this->dbRepo->inDocenteEx($row->IdJurado1)." Presidente del borrador de tesis para la citación a reunión de dictamen, de acuerdo al (Artículo N° 8) del reglamento. Así como tambien al tesista ".$this->dbPilar->inTesistas($id)." para la verificación del Trámite. <br> <br> $titulo1 <br> $mensaje1";
   }else{
      echo "<span class='text-danger'> OCURRIÓ ALGUN ERROR, COMUNIQUESE CON EL ADMINISTRADOR.</span>";
   }
}
public function vwSustentac(){
   $this->load->view("pilar/cord/view/sustentaciones");
}

public function vwSustentacVir(){
   $this->load->view("pilar/cord/view/sustentacionesvirt");
}

public function vwPubSusten(){
   $this->load->view("pilar/cord/view/sustentaciones_form");   
}


public function publicaSusten(){
   $id = mlSecurePost("idBorr");
   $codigo = mlSecurePost("codBorr");
   $fechad  = mlSecurePost("fechad");
   $fecha  = mlSecurePost("fechasust");
   $hora   = mlSecurePost("horasust");
   $lugar  = mlSecurePost("lugarsust");
   $tituloNuevo=mlSecurePost("titulo");
   $IdCarrera = mlGetGlobalVar("IdCarrera");
   $datei = date("$fecha $hora");       

   $row = $this->dbPilar->getSnapRow( "tesSustens", "IdBorrador=$id AND Activo > 0" );

   $rowsi = $this->dbPilar->getSnapRow( "tesSustensSolic", "IdTramite=$id" );
   $tipo =1;
   if ($rowsi) {
      $this->dbPilar->Update( 'tesSustensSolic', array(
         'Estado'    => 2,
      ) , $rowsi->Id );
      $tipo =2;
   }

   if( $row ){
      echo "<br> La sustentación ya se encuentra programada ver en : <a  target=_blank href='".base_url('pilar/sustentas')."'> SUSTENTACIONES</a> $row->Codigo , $row->Id , ($id)";
   }else{

      $this->dbPilar->Insert("tesSustens", array(
         'Tipo'=> $tipo,
         'IdBorrador'    => $id,    
         'IdTramite' =>  $id,
         'Codigo'    =>    $codigo,
         'Fecha' =>  $datei,
         'FechaDic'  =>   $fechad,
         'IdCarrera' =>  $IdCarrera,
         'Lugar' => $lugar
      ) );

      $this->dbPilar->Update( 'tesTramites', array(
         'Estado'    => 14,
         'Tipo'      => 3,
         'FechModif' => mlCurrentDate()
      ) , $id );

// Confusiooo de lo snombres 
      $this->dbPilar->Insert( 'tesTramsDet', array(
         'Iteracion' => 5,
         'IdTramite' => $id,
         'Archivo'   => $this->dbPilar->getOneField("tesTramsDet","Archivo","IdTramite='$id' ORDER BY Iteracion DESC"),
         'Titulo'    => $tituloNuevo,
         'vb1'    => -1,
         'vb2'    => -1,
         'vb3'    => -1,
         'vb4'    => -1,
         'Fecha'     => mlCurrentDate(),
         'Obs' =>"Sustentacion"
      ));
// LOG DE COORDINADOR
      $this->logCordinads("C","3","Publico Sustentación","($id) Se programó la sustentación del código $codigo");
      
      echo "<h2></h2> Publicado en Sustentaciones : <a target=_blank href='".base_url('pilar/sustentas')."'> Verificar enlace</a>";
// echo "<br>Preparado para Insertar<br>id:[$id]<br>codigo:[$codigo]<br>fecha:[$fecha]<br>fechadic:[$fechad]<br>hora:[$hora]<br>lugar:[$lugar]<br>IdCarrera:[$IdCarrera]<br>date:[$date]<br>tituloNuevo:[$tituloNuevo] falta solo un poco, esperenme porfavor. <br> Atte Fred.";
   }
}
public function evaluaSusten($cod){
   // $cod=mlSecurePost('cod');
   $tesis=$this->dbPilar->getSnapRow("tesTramites","Codigo='$cod'");
   if(!$tesis){
      echo "$cod :: No corresponde";
      return;
   }
   if($tesis->Estado<13){
      echo "Este codigo no corresponde ::$cod"; 
      return;
   } 
   if($this->dbPilar->getSnapRow("tesSustens","Codigo=$cod")){
      echo "La sustentación ya fue publicada";
      return;
   }

   $idPy=$this->dbPilar->getOneField("tesTramites","Id","Codigo='$cod'");
   $proyecto=$this->dbPilar->inTitulo($idPy);
   $tesistas=$this->dbPilar->inTesistas($idPy);

   echo "<br><hr><form method='POST' class='form-horizontal' name='publSusten' method='post' >
   <label class='control-label'>Tesista :</label>  
   <input class='form-control' type='hidden' id='idBorr'name='idBorr' value='$idPy'>
   <input class='form-control' type='hidden' id='codBorr'name='codBorr' value='$cod'>
   <input class='form-control' type='text' id='nombresust' value='$tesistas' disabled>";

   echo "<label>Sustentación :</label>";

   echo "<label>Fecha de Dictamen:</label>";

   echo "<input type='date' name='fechad' class='form-control'>";

   echo "<label class='control-label'>Titulo de Proyecto :</label>
   <textarea rows='5' name='titulo' class='form-control'>$proyecto</textarea>";

   echo "<label class='control-label'>Fecha de Sustentacion :</label>
   <input class='form-control' type='date' id='fechasust' name='fechasust' required>";

   echo "<label class='control-label'>Hora de Sustentacion :</label>
   <input class='form-control' type='text' id='horasust' name='horasust'value='11:00:00' required>";
   echo "<p class='help-block'>Formato de 24 Horas Ejem(15:00:00 , 09:00:00).</p>";

   echo "<label class='control-label'>Lugar / Sala de Sustentación :</label>
   <input type='text' class='form-control' id='lugarsust' name='lugarsust' placeholder='Auditorio/Sala de Grados de la  EP___' required>";

   echo "<br><div class='col-md-4'></div><button type='button' class='btn btn-info' onclick=\"LoadForm('postSusten','cordinads/publicaSusten',publSusten)\">
   <span class='glyphicon glyphicon-send'></span> Publicar Sustentación
   </button>";
   echo "<form></div>";
   echo "</div>";


}


public function EvaluaActaSusten($idtram){
   // $cod=mlSecurePost('cod');
   $tesis=$this->dbPilar->getSnapRow("tesTramites","Id='$idtram'");
   if(!$tesis){
      echo "$idtram :: No corresponde";
      return;
   }
   if($tesis->Estado<13){
      echo "Este codigo no corresponde ::$idtram"; 
      return;
   } 

   $idPy=$this->dbPilar->getOneField("tesTramites","Id","Id='$idtram'");
   $proyecto=$this->dbPilar->inTitulo($idPy);
   $tesistas=$this->dbPilar->inTesistas($idPy);

   echo "<br><hr><form method='POST' class='form-horizontal' name='evalSusten' method='post' >
   <h4>Generación de Acta de Exposición y Defensa </h4>

   <p>Esta Operación será registrada a su nombre, por cuanto es importante describir la motivación para llevar este procedimiento . </p>

   <label class='control-label'>Tesista :</label>  
   <input class='form-control' type='hidden' id='idBorr'name='idBorr' value='$idPy'>
   <input class='form-control' type='text' id='nombresust' value='$tesistas' disabled>";

   echo "<label>Sustentación :</label>";

   echo "<label class='control-label'> Motivo  / Grabación de Sala:</label>
   <input type='text' class='form-control' id='motiv' name='motiv' placeholder=' El docente PEREZ PEREZ, JUAN  no ha asistido al acto de sustentación , cuya deliveración se encuentra grabada en el siguiente enlace' required>";

   echo "<br><div class='col-md-4'></div><button type='button' class='btn btn-info' onclick=\"LoadForm('postSusten','cordinads/procesaActaSust  ',evalSusten)\">
   <span class='glyphicon glyphicon-send'></span> Generar Acta 
   </button>";
   echo "<form></div>";
   echo "</div>";

   
}

 
public function procesaActaSust(){

   $idtram = mlSecurePost("idBorr");
   $motiv = mlSecurePost("motiv");

   $dets = $this->dbPilar->inTramDetIter( $idtram, 5 );
   $tram = $this->dbPilar->inProyTram( $idtram );
   // $total = $dets->vb1 + $dets->vb2 +$dets->vb3+$dets->vb4;
   $bum1=($dets->vb1!=-1?1:0);
   $bum2=($dets->vb2!=-1?1:0);
   $bum3=($dets->vb3!=-1?1:0);
   $bum4=($dets->vb4!=-1?1:0);
   $total = $bum1+$bum2+$bum3+$bum4;

   if($total >= 3){
      $total=0;
      $boo1=($dets->vb1!=-1?$dets->vb1:0);
      $boo2=($dets->vb2!=-1?$dets->vb2:0);
      $boo3=($dets->vb3!=-1?$dets->vb3:0);
      $boo4=($dets->vb4!=-1?$dets->vb4:0);
      $total=$boo1+$boo2+$boo3+$boo4;

       $ptj=($total>=6?"Aprobado con Distinción":($total>=3?"Aprobado":"Desaprobado"));
       $dict=($total>=6?"2":($total>=3?"1":"0"));
       $sust=$this->dbPilar->getSnaprow('tesSustensSolic',"IdTramite=$idtram");
       $this->dbPilar->Update("tesSustensSolic",array('Estado'=>3),$sust->Id);
       $this->dbPilar->Update("tesTramites",array('Tipo'=>3),$sust->Id);
       $value=$this->dbPilar->getOneField('tesSustenAct',"Num"," IdTramite>0 ORDER BY Num DESC");
       $num=($value?$value:0);
       // 
       $sustentado=$this->dbPilar->getSnapRow("tesSustenAct","IdTramite=$idtram");
       if($sustentado){
           echo "Acta registrada con anterioridad.";
           return;              
       }
       // 
       $this->dbPilar->Insert( "tesSustenAct", array(
           'IdTramite'  => $tram->Id,  ///// 1, 4,
           'IdCarrera'  => $tram->IdCarrera,
           'Dictamen'   => $dict,
           'Fecha'      => mlCurrentDate(),
           'Num'        => $num+1,
           'Obs'        => $ptj,
           'ExtraObs'   => $motiv
       ) );

       $this->logCordinads("C","8","Genero Acta de Exposición y Defensa ","($tram->Id) Se generó el Acta de Sustentación ");
       echo "Registrado en el historial del usuario de la Unidad.";


   }else{
      echo "Confirmar la Asistencia de almenos 03 miembros del jurado dictaminador.";
   }

   echo "Acta de Exposición y defensa generada desde la Unidad de Investigación.";

   echo " <a href='javascript:void(0)' onclick=\"lodPanel('panelCord','cordinads/vwSustentacVir')\"class='btn btn-primary'><span class='glyphicon glyphicon glyphicon-calendar'></span> Listar Sustentaciones Virtuales</a>";

}

// Log de Actividades del Coordinador
private function logCordinads($tipo, $IdOpe, $just, $detalle )
{
    $sess = $this->gensession->GetSessionData(PILAR_CORDIS);

    $this->dbPilar->Insert(
        'logCordinads', array(
            'Tipo'          => $tipo,// N = Notificación  T=Tramite  C=Cambio
            'IdUser'        => $sess->userId,
            'IdCarrera'     => mlGetGlobalVar("IdCarrera"),
            'IdOperacion'   => $IdOpe,
            'Just'          => $just,
            'Detalle'       => $detalle,
            'Fecha'         => mlCurrentDate()
        ) );
}

// Generar Memorandums
private function inGenMemo( $rowTram, $iterMemo )
{
   $anio  = ANIO_PILAR;
   $orden = 1 + $this->dbPilar->getOneField( "tblMemos", "Ordinal", "Anio=$anio ORDER BY Ordinal DESC" );

   $this->dbPilar->Insert( "tblMemos", array(
   'Tipo'      => $iterMemo,   //1 - 4 - 5,
   'IdTramite' => $rowTram->Id,
   'IdCarrera' => $rowTram->IdCarrera,
   'Anio'      => $anio,
   'Ordinal'   => $orden,
   'Fecha'     => mlCurrentDate(),
   ) );
}

// Log Correo 
private function logCorreo( $idTes,$idDoc, $correo, $titulo, $mensaje )
{
// enviamos mail
   $this->genmailer->mailPilar( $correo, $titulo, $mensaje );

// procedemos a grabarlo
   $this->dbPilar->Insert(
      'logCorreos', array(
         'IdDocente' => $idDoc,
         'IdTesista' => $idTes,
         'Fecha'   => mlCurrentDate(),
         'Correo'  => $correo,
         'Titulo'  => $titulo,
         'Mensaje' => $mensaje
      ) );
}
private function logTramites( $idUser, $tram, $accion, $detall )
{
   $this->dbPilar->Insert(
      'logTramites', array(
'Tipo'      => 'C',      // T D C A
'IdUser'    => $idUser,
'IdTramite' => $tram,
'Quien'     => 'Coordinacion',
'Accion'    => $accion,
'Detalle'   => $detall,
'Fecha'     => mlCurrentDate()
) );
}
public function vwLogCordinador(){
    $sess = $this->gensession->GetSessionData(PILAR_CORDIS);
   $carrera=mlGetGlobalVar("IdCarrera");
   $dato=$this->dbPilar->getSnapView("logCordinads","IdCarrera=$carrera AND IdUser =$sess->userId ORDER BY ID DESC");
   echo "<h2><b>Registro de Actividades<small></b></h2><ul>";
   foreach ($dato->result() as  $row) {
      echo "<li>$row->Fecha :: (".$row->IdOperacion.") $row->Just , $row->Detalle<br></li>";
   }
   echo "</ul>";
}

// Function de Memorandums
public function memosGen( $IdTramite )
{
   $pdf = new GenSexPdf();
   $pdf->SetMargins(20, 10 , 170);
   $memo=$this->dbPilar->getSnapRow("tblMemos","IdTramite=$IdTramite ORDER BY Id DESC");
   if( ! $memo ) {
      echo "No hay Memo generado";
      return;
   }

   $tram=$this->dbPilar->getSnapRow("tesTramites","Id=$IdTramite");
   $codigop=$this->dbPilar->getOneField("tesTramites","Codigo","Id=$memo->IdTramite");
   $carrera=$this->dbRepo->inCarrera($memo->IdCarrera);
   $proyecto=$this->dbPilar->getOneField("tesTramsDet","Titulo","IdTramite='$memo->IdTramite' ORDER BY Iteracion DESC");
   $nmemo=$memo->Ordinal;
   $anio=$memo->Anio;
   $fecha=$memo->Fecha;
   if($tram->Estado==4 OR $tram->Estado==12 or $tram->Estado==13){
      if($tram->Estado==4){
         $quienes=array($tram->IdJurado1,$tram->IdJurado2,$tram->IdJurado3);
         $asunto="REVISIÓN DE PROYECTO DE TESIS";
         $str = "Por medio del presente comunicarle que Ud. ha sido sorteado para la revisión "
         . "del Proyecto de Tesis registrado en Plataforma con el còdigo: $codigop, "
         . "presentado y registrado para la Escuela Profesional de: $carrera. "
         . "con fecha $fecha."
         . "La cual deberá dar tràmite revisando y subiendo sus correcciones "
         . "haciendo uso de su cuenta en Plataforma.\n\n"
         . "Ud. tiene un plazo máximo de 10 dias para la revisión via OnLine, "
         . "recuerde que el documento PDF se ha enviado a su cuenta en "
         . "Plataforma ubicando en http://vriunap.pe/pilar"
         . "\n\n"
         . "Atentamente."
         ;
      }
      if($tram->Estado==12){
         $quienes=array($tram->IdJurado1,$tram->IdJurado2,$tram->IdJurado3,$tram->IdJurado4);
         $asunto="REVISIÓN DE BORRADOR DE TESIS";
//  Borrador
         $str = "Por medio del presente comunicarle que Ud. ha sido SORTEADO como jurado revisor "
         . "del Proyecto de Tesis Aprobado con el còdigo: $codigop, "
         . "de la Escuela Profesional de: $carrera."
         . "\n\nCuyo borrador de tesis deberá de revisar en un plazo máximo de 10 dias apartir del día $fecha y enviar sus observaciones via PILAR, "
         . "el documento PDF se ha enviado a su cuenta en "
         . "Plataforma ubicando en http://vriunap.pe/pilar \n\n"
         . "Si transcurrido este tiempo, no exixtiera respuesta alguna PILAR considerará el borrador de tesis apto para su defensa."
         . "(Art.6 Reglamento de Presentación dictamen de borradores y defensa de tesis) Resolución Rectoral N°3011-2016-R-UNA\n\n\n"
         . "Atentamente."
         ;
      }
      if($tram->Estado==13){ 
         $quienes=array($tram->IdJurado1);
         $tesista=$this->dbPilar->InTesista($tram->IdTesista1);
         if($tram->IdTesista2){
            $tesista=$this->dbPilar->InTesista($tram->IdTesista1) ." y el(la) bachiller ".$this->dbPilar->InTesista($tram->IdTesista2);
         }
         $j2m=$this->dbRepo->inDocente($tram->IdJurado2);
         $j3m=$this->dbRepo->inDocente($tram->IdJurado3);
         $j4m=$this->dbRepo->inDocente($tram->IdJurado4);
         $asunto="CITACIÓN A REUNIÓN DE DICTAMEN";
//  Borrador
         $str = "Por medio del presente comunicarle que Ud. ha sido designado como PRESIDENTE del jurado revisor "
         . "del Proyecto de Tesis Aprobado con el còdigo: $codigop, "
         . "de la Escuela Profesional de: $carrera "
         . "\n\nCuyo borrador de tesis ya fue revisado por los jurados via plataforma para lo cual usted deberá convocar a una reunión en un plazo máximo de 05 dias apartir del día $fecha con todos los miembros de jurado, como sigue a continuación: \n"
         ." - Primer Miembro : $j2m\n"
         ." - Segundo Miembro :$j3m\n"
         ." - Tercer Miembro/Director :$j4m\n"
         . "con presencia del bachiller  $tesista , a fin de realizar las observaciones finales  y dictaminar el borrador de tesis. "
         . "(Art.9 Reglamento de Presentación dictamen de borradores y defensa de tesis) Resolución Rectoral N°3011-2016-R-UNA\n\n\n"
         . "Atentamente."; 
      }
// Cargo
      $pdf->AddPage();
      $pdf->SetDrawColor( 170, 170, 170 );
      $pdf->SetFont('Arial','B',13);
      $textmemo = sprintf( "MEMORANDO CIRCULAR Nro %03d-%04d-PILAR-VRI-UNAP", $nmemo, $anio );        
      $pdf->Cell( 170, 2, toUTF($textmemo), 0, 1, 'L' ); 
      $pdf->Cell( 170, 1, "___________________________________________________", 0, 1, 'L' ); 
      $pdf->Ln(5);  
      $pdf->SetFont( "Arial", "", 12 );
      $pdf->Cell( 30, 9, toUTF("PARA"), 0, 0, 'L' );
      $pdf->Cell( 150, 9, toUTF(": CARGO"), 0, 1, 'L' );
      $pdf->Cell( 30, 9, toUTF("ASUNTO"), 0, 0, 'L' );
      $pdf->Cell( 150, 9, toUTF(": $asunto"), 0, 1, 'L' );
      $pdf->Cell( 30, 9, toUTF("FECHA"), 0, 0, 'L' );
      $pdf->Cell( 150, 9, toUTF(": $fecha"), 0, 1, 'L' );
      $pdf->Cell( 170, 9,'_______________________________________________________________________', 0, 1, 'L' );
      $pdf->MultiCell( 170,7,toUTF($str),0,'J');
      $pdf->Ln(50);
      $pdf->SetFont( "Arial", "", 6 );
      $pdf->Cell( 30, 2, toUTF("/ Vicerectorado de Investigación"), 0, 1, 'L' ); 
      $pdf->Cell( 30, 2, toUTF("/ Plataforma PILAR"), 0, 1, 'L' ); 
      $pdf->Cell( 30, 2, toUTF("/ Cordinación de Investigación $carrera"), 0, 1, 'L' ); 
// Memos;
      for ($i=0; $i < count($quienes); $i++) {   
      $grado=$this->dbRepo->inGrado($quienes[$i]);       
         $destinatario=$this->dbRepo->inDocente($quienes[$i]);
         $correo=$this->dbRepo->inCorreo($quienes[$i]);

         $pdf->AddPage();

         $pdf->SetDrawColor( 170, 170, 170 );
         $pdf->SetFont('Arial','B',13);
         $textmemo = sprintf( "MEMORANDO CIRCULAR Nro %03d-%04d-PILAR-VRI-UNAP", $nmemo, $anio );        
         $pdf->Cell( 170, 7, toUTF($textmemo), 0, 1, 'L' );
         $pdf->Cell( 170, 1, "___________________________________________________", 0, 1, 'L' ); 
         $pdf->Ln(5);
         $pdf->SetFont( "Arial", "", 12 );
         $pdf->Cell( 30, 9, toUTF("PARA"), 0, 0, 'L' );
         $pdf->Cell( 150, 9, toUTF(": $grado $destinatario"), 0, 1, 'L' );
         $pdf->Cell( 30, 9, toUTF("ASUNTO"), 0, 0, 'L' );
         $pdf->Cell( 150, 9, toUTF(": $asunto"), 0, 1, 'L' );
         $pdf->Cell( 30, 9, toUTF("FECHA"), 0, 0, 'L' );
         $pdf->Cell( 150, 9, toUTF(": $fecha"), 0, 1, 'L' );
         $pdf->Cell( 170, 9,'_______________________________________________________________________', 0, 1, 'L' );
         $pdf->MultiCell( 170,7,toUTF($str),0,'J');
         $pdf->Ln(60);
         $pdf->SetFont( "Arial", "", 6 );
         $pdf->Cell( 30, 2, toUTF("/ Vicerectorado de Investigación"), 0, 1, 'L' ); 
         $pdf->Cell( 30, 2, toUTF("/ Plataforma PILAR"), 0, 1, 'L' ); 
         $pdf->Cell( 30, 2, toUTF("/ Cordinación de Investigación $carrera"), 0, 1, 'L' ); 
      }
      $pdf->Output();
   }else{
      echo "Algo no está Bien ... Contáctate con el Administrador!";
   }
}
//  Lineas y Docentes
public function lineasReg()
{
// hack para E. Inicial
   $carre = mlGetGlobalVar("IdCarrera");
   if( $carre == 19 )
      $carre = 18;

   $lineas = $this->dbRepo->getTable("tblLineas","IdCarrera='$carre' AND Estado = '1'");
   $this->load->view("pilar/tes/tesLineas",array('lineas'=>$lineas));
}

// Entrar al Admin-Coordinador
public function login()
{
   $user = mlSecurePost("user");
   $pass = mlSecurePost("pass");
   if( !$user ) return;
// verificar existencia de correo
   if( ! $this->dbPilar->getSnapRow( "tblSecres", "Usuario='$user'" ) ) {
      echo '[{"error":true, "msg":"Este usuario no está registrado"}]';
      return;
   } 

// ahora si comprobar cuenta
   $row = $this->dbPilar->loginByUser( "tblSecres", $user, $pass );
   if( ! $row ) {
      echo '[{"error":true, "msg":"Su clave es incorrecta <br> Intente mas tarde porfavor, con el nuevo usuario otorgado."}]';
      return;
   }
//public function SetCordLogin( $sessName, $userId, $userName, $userAlias,$userFacu,$userLevel=0 )
   $this->gensession->SetCordLogin(PILAR_CORDIS,$row->Id,$row->Resp,$row->Id_Facultad,$row->UserLevel,$row->Estado);

   echo '[{"error":false, "msg":"OK, Redirecciónando al panel de trabajo."}]';
   // echo '[{"error":false, "msg":"OK, Estamos redireccionando..."}]';
   //redirect( base_url("pilar3/cordinads") );
}

// Salir de Admin
public function logout()
{
   $this->gensession->SessionDestroy(PILAR_CORDIS);
   redirect( base_url("pilar"), 'refresh');
}

// Función de Prueba
public function ver()
{
   echo "Pueba de sessión: Coords<br>";
   $this->gensession->IsLoggedAccess(PILAR_CORDIS);
   $sess = $this->gensession->GetSessionData(PILAR_CORDIS);
   echo "$sess->userId :: $sess->userAlias :: $sess->userName";
}
// Reportes para coordinadores
public function vwReportes(){
// hack para E. Inicial
   $carre = mlGetGlobalVar("IdCarrera");
   if( $carre == 19 )
      $carre = 18;
   $this->load->view("pilar/cord/report/vwReporte",array(
      'Carrera'=>$this->dbRepo->inCarrera($carre),
   ));
}

public function selecrepo(){ 
   $carre = mlGetGlobalVar("IdCarrera");
   $op=mlSecurePost('option');
   switch ($op) {
      case 1:
         $this->load->view("pilar/cord/report/1rpEvalDocente",array(
            'list'=>$this->dbRepo->getTable("tblDocentes","IdCarrera=$carre AND Activo>0"),
         ));
         break;
      case 2:
         $this->load->view("pilar/cord/report/2rpEstadoCarrer",array(
            'IdCarrera'=>$carre
         ));
         break;
      case 3:
      echo "<h3 style='color: rgba(15,81,117,1);'> <center><b>REPORTE CONSOLIDADO DE PROYECTOS</b></center><small><h3>";
    echo "<center><h4>".$this->dbRepo->inCarrera($carre)."<h4></center>";      
    echo "<div class='table-responsive' style='font-size:11px'><table class='table table-striped'>"
    . "<tr><th>ORD</th>"
    . "<th>COD</th>"
    . "<th>TIP</th>"
    . "<th>NOMBRES</th>"
    . "<th>P2018</th><th>J1</th><th>J2</th><th>D</th><th>Tot2018</th>"
    . "<th>P2019</th><th>J1</th><th>J2</th><th>D</th><th>Tot2019</th>"
    . "<th>P2020</th><th>J1</th><th>J2</th><th>D</th><th>Tot2020</th>"
    . "<th>P2021</th><th>J1</th><th>J2</th><th>D</th><th>Tot2021</th></tr>";
    $table = $this->dbRepo->getSnapView( "vwDocentes", "IdCarrera=$carre AND Activo >=3 " );
    $nro=1;
    foreach ( $table->result() as $row ){
    $conteo1=$this->conteoDoc($row->Id,'2018');
    $conteo2=$this->conteoDoc($row->Id,'2019');  
    $conteo3=$this->conteoDoc($row->Id,'2020');  
    $conteo4=$this->conteoDoc($row->Id,'2021');
    echo "<tr>";
    echo "<td> <b>$nro</b> </td>";
    echo "<td> $row->Codigo     </td>"; ;
    echo "<td> $row->CategAbrev </td>";
    echo "<td> $row->DatosPers  </td>";
    echo " $conteo1";
    echo " $conteo2";
    echo " $conteo3";
    echo " $conteo4";

    echo "</tr>"; 
    $nro++;
    }
    echo "</table>";
    echo "NOTA: El reporte antes del 01 - 08 - 2017 contabiliza los proyectos rechazados, por la plataforma. A partir de esta fecha se quitaron del conteo.";
         break;
      case 4:
         $this->load->view("pilar/cord/report/4rpSustentaciones",array(
            'IdCarrera'=>$carre,
            'list'=>$this->dbPilar->getTable("tesSustens","IdCarrera='$carre' ORDER BY Fecha DESC"),
         ));
         break;      
      default:
         echo "<br> Error .... !";
         break;
   }
}

   private function conteoDoc($idDoc,$anio){    
       $j1=$this->dbPilar->getSnapView("tesTramites","Anio=$anio AND Estado >=2 AND IdJurado1=$idDoc")->num_rows();
       $j2=$this->dbPilar->getSnapView("tesTramites","Anio=$anio AND Estado >=2 AND IdJurado2=$idDoc")->num_rows();
       $j3=$this->dbPilar->getSnapView("tesTramites","Anio=$anio AND Estado >=2 AND IdJurado3=$idDoc")->num_rows();
       $j4=$this->dbPilar->getSnapView("tesTramites","Anio=$anio AND Estado >=2 AND IdJurado4=$idDoc")->num_rows();
       $tot=$j1+$j2+$j3+$j4;
       return "<td>$j1</td><td>$j2</td><td>$j3</td><td>$j4</td><td> <b>$tot</b> </td>";
    }

public function memoriaAnual(){  
   $this->load->view("pilar/head");
   echo "<div class='col-md-1'> </div>"; 
   echo "<div class='col-md-10'> ";
   echo "<center><img class='img-responsive' style='width:40%' src='".base_url("vriadds/pilar/imag/pilar-head.jpg")."'></img></center>"; 
   $carreras=$this->dbRepo->getSnapView("dicCarreras");
   foreach ($carreras->result() as $pipi) {
      echo " <h4 class='text-center'>REPORTE DE DATOS 2017  ".$this->dbRepo->inCarrera($pipi->Id)."</h4>";
      echo "<table style='width:100%; font-size:7px;' class='table table-striped '>";
      echo "<tr>
      <th>Número</th>
      <th style='width:5%'>Codigo</th>
      <th style='width:15%'>Estudiantes</th>
      <th>Carrera</th>
      <th>Fech - Reg</th>
      <th>AÑO</th>
      <th>Tipo</th>
      <th>Estado Actual</th>
      <th width='30%'>Titulo</th>
      <th width='20%'>LOG DE ACTIVIDADES</th>
      </tr>";
      $tesis=$this->dbPilar->getSnapView("tesTramites", "Anio = 2018 AND IdCarrera=$pipi->Id","ORDER by FechRegProy ASC");
      $flag=1;
      foreach($tesis->result() as $row){
         $chicos=$this->dbPilar->inTesistas("$row->Id");
         $tes= $this->dbPilar->getOneField("tesTramites","IdTesista1","Id=$row->Id");
         $log=$this->dbPilar->getSnapView("logTramites","IdTramite=$row->Id");
         echo "<tr>
         <td>$flag</td>
         <td>$row->Codigo</td>
         <td>".toUTF("$chicos")."</td> 
         <td>".$this->dbRepo->inCarrera($row->IdCarrera)."</td>
         <td> $row->FechModif </td>
         <td>".$row->Anio."</td>
         <td>".$this->dbPilar->inTipo("$row->Id")."</td>
         <td>".$this->dbPilar->inEstado("$row->Id")."</td>
         <td>".$this->dbPilar->inTitulo("$row->Id")."</td>
         <td>"; 
         // foreach($log->result() as $rin) { 
         //    echo "$rin->Fecha : $rin->Accion<br>";
         //    # code...
         // }
         echo "</td>
         </tr>";
         $flag++;
      }
      echo "</table>";
   } 
   echo "<hr>"; 
   echo "</div>";
}

public function EvalDocenteAnio($id,$anio){

   $pdf = new GenSexPdf();
   $pdf->SetMargins(20, 16, 20);
   $pdf->AddPage();
   $pdf->SetFont('Arial','B',14);
   $pdf->Cell(170,7,toUTF("REPORTE DOCENTE EN PILAR"),0,1,'C');
$pdf->Ln(5);// Salto de Lineaaa
$pdf->SetFont('Arial','',11);
$pdf->MultiCell(170,7,toUTF("La Plataforma de Investigación Integrada a la Labor Académica con Responsabilidad"
   . " (PILAR), mediante el presente expide la información registrada en su base de datos del Docente Universitario con la siguiente información:"));
$table = $this->dbRepo->getTable( "vwDocentes", "Id=$id" );
$pdf->SetFont('Arial','',11);
foreach($table->result() as $row){
   $codigo=$row->Codigo;
   $pdf->Cell(60,7,toUTF("Código"),0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Codigo"),0,1,'L');
   $pdf->Cell(60,7,toUTF("DNI"),0,0,'L');$pdf->Cell(100,7,toUTF(": $row->DNI"),0,1,'L');
   $pdf->Cell(60,7,"Apellidos y Nombres",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->DatosPers"),0,1,'L');
   $pdf->Cell(60,7,toUTF("Categoría"),0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Categoria"),0,1,'L');
   $pdf->Cell(60,7,"Facultad",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Facultad"),0,1,'L');
   $pdf->Cell(60,7,"Escuela Profesional",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Carrera"),0,1,'L');
   // $pdf->Cell(60,7,"Ult. Fecha de Ascenso",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->FechaAsc"),0,1,'L');
}
$varconsulta=array(
   4=>"Director",
   1=>"Presidente de Jurado",
   2=>"Primer Miembro de Jurado",
   3=>"Segundo Miembro de Jurado",
);
$state=array(
   -1=>"Observado",
   0=>"Proyecto Rechazado",
   1=>"Proyecto : Revisión de formato",
   2=>"Proyecto : En revisión por el Director",
   3=>"Proyecto : Listo para sorteo",
   4=>"Proyecto : En Revisión por Jurados",
   5=>"Proyecto : En Dictaminación",
   6=>"Proyecto : Proyecto Aprobado",
   10=>"Borrador : En espera a la carga de borrador",
   11=>"Borrador : En revisón de formato de borrador",
   12=>"Borrador : En revisión por Jurados",
   13=>"Borrador : Para Reunión de Dictamen",
   14=>"Borrador : Sustentado",
);


for($i=1;$i<=4;$i++){


   $pdf->Ln(10);
   $pdf->SetFont('Arial','B',11);
   $pdf->Cell(170,7,toUTF("Participación como $varconsulta[$i]"),0,1,'C');
   $pdf->Cell(170,0,"",1,1);
   $pdf->Cell(10,7,toUTF("N°"),0,0,'C');
   $pdf->Cell(40,7,toUTF("Estado"),0,0,'C');
   $pdf->Cell(120,7,toUTF("Titulo"),0,1,'C');
   $n=1;
   $pdf->Cell(170,0,"",1,1);$pdf->SetFont('Arial','',9);

   $pdf->setFontSize(array(8,8,7,8));
   $pdf->SetAligns(array('C','J','J','C'));
   $pdf->SetWidths(array(10,40,100,20));
   $consDirec=$this->dbPilar->getTable("tesTramites","IdJurado$i='$id' AND Anio ='$anio'");
   if($consDirec->num_rows()>0){
      foreach($consDirec->result() as $row){
         if($row->Estado>=1){
            $titulo=$this->dbPilar->getOneField("tesTramsDet","Titulo","IdTramite='$row->Id'");
            $data= array(
               $n,
               toUTF($state[$row->Estado]),
               toUTF("$titulo"),
               $row->FechModif
            );
            $pdf->Row($data);
            $n++;
         }
      }

   }else{
      $pdf->Cell(170,7,toUTF("El docente no es $varconsulta[$i] de  "),1,1,'C');
   }
}


$fecha=getdate();
$pdf->Ln(7);
$pdf->cell(170,5,"$fecha[mday] - $fecha[month] - $fecha[year]",0,1,'L');
$pdf->cell(170,5,toUTF("Plataforma de Investigación y Desarrollo") ,0,1,'L');

$pdf->Output();
}

public function EvalDocente($id){

   $pdf = new GenSexPdf();
   $pdf->SetMargins(20, 16, 20);
   $pdf->AddPage();
   $pdf->SetFont('Arial','B',14);
   $pdf->Cell(170,7,toUTF("REPORTE DOCENTE EN PILAR"),0,1,'C');
$pdf->Ln(5);// Salto de Lineaaa
$pdf->SetFont('Arial','',11);
$pdf->MultiCell(170,7,toUTF("La Plataforma de Investigación Integrada a la Labor Académica con Responsabilidad"
   . " (PILAR), mediante el presente expide la información registrada en su base de datos del Docente Universitario con la siguiente información:"));
$table = $this->dbRepo->getTable( "vwDocentes", "Id=$id" );
$pdf->SetFont('Arial','',11);
foreach($table->result() as $row){
   $codigo=$row->Codigo;
   $pdf->Cell(60,7,toUTF("Código"),0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Codigo"),0,1,'L');
   $pdf->Cell(60,7,toUTF("DNI"),0,0,'L');$pdf->Cell(100,7,toUTF(": $row->DNI"),0,1,'L');
   $pdf->Cell(60,7,"Apellidos y Nombres",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->DatosPers"),0,1,'L');
   $pdf->Cell(60,7,toUTF("Categoría"),0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Categoria"),0,1,'L');
   $pdf->Cell(60,7,"Facultad",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Facultad"),0,1,'L');
   $pdf->Cell(60,7,"Escuela Profesional",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->Carrera"),0,1,'L');
   $pdf->Cell(60,7,"Ult. Fecha de Ascenso",0,0,'L');$pdf->Cell(100,7,toUTF(": $row->FechaAsc"),0,1,'L');
}
$varconsulta=array(
   4=>"Director",
   1=>"Presidente de Jurado",
   2=>"Primer Miembro de Jurado",
   3=>"Segundo Miembro de Jurado",
);
$state=array(
   -1=>"Observado",
   0=>"Proyecto Rechazado",
   1=>"Proyecto : Revisión de formato",
   2=>"Proyecto : En revisión por el Director",
   3=>"Proyecto : Listo para sorteo",
   4=>"Proyecto : En Revisión por Jurados",
   5=>"Proyecto : En Dictaminación",
   6=>"Proyecto : Proyecto Aprobado",
   10=>"Borrador : En espera a la carga de borrador",
   11=>"Borrador : En revisón de formato de borrador",
   12=>"Borrador : En revisión por Jurados",
   13=>"Borrador : Para Reunión de Dictamen",
   14=>"Borrador : Sustentado",
);


for($i=1;$i<=4;$i++){


   $pdf->Ln(10);
   $pdf->SetFont('Arial','B',11);
   $pdf->Cell(170,7,toUTF("Participación como $varconsulta[$i]"),0,1,'C');
   $pdf->Cell(170,0,"",1,1);
   $pdf->Cell(10,7,toUTF("N°"),0,0,'C');
   $pdf->Cell(40,7,toUTF("Estado"),0,0,'C');
   $pdf->Cell(120,7,toUTF("Titulo"),0,1,'C');
   $n=1;
   $pdf->Cell(170,0,"",1,1);$pdf->SetFont('Arial','',9);

   $pdf->setFontSize(array(8,8,7,8));
   $pdf->SetAligns(array('C','J','J','C'));
   $pdf->SetWidths(array(10,40,100,20));
   $consDirec=$this->dbPilar->getSnapView("tesTramites","IdJurado$i=$id ORDER BY Id DESC");
   if($consDirec->num_rows()>0){
      foreach($consDirec->result() as $row){
         if($row->Estado>=1){
            $titulo=$this->dbPilar->getOneField("tesTramsDet","Titulo","IdTramite='$row->Id'");
            $data= array(
               $n,
               toUTF($row->Codigo.":".$state[$row->Estado]),
               toUTF("$titulo"),
               $row->FechModif
            );
            $pdf->Row($data);
            $n++;
         }
      }

   }else{
      $pdf->Cell(170,7,toUTF("El docente no es $varconsulta[$i] de  "),1,1,'C');
   }
}

// FEDUUUU ANTERIOR

$pdf->ln(10);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(170,7,toUTF("Proyectos de Investigación (FEDU) - Hasta 2017 "),0,1,'C');
$pdf->Cell(170,0,"",1,1);$pdf->SetFont('Arial','',9);
$feducon=$this->dbFedu->getTable("integrantes","codDocente=$codigo");

$pdf->SetWidths(array(10,60,100));
$m=1;
foreach($feducon->result() as $row){
   $proy=$this->dbFedu->getSnapRow('proyecto',"id=$row->idProyect");
   $data= array(
      $m,
      toUTF($proy->codigo.":".$proy->estado),
      toUTF("$proy->titulo"),
   );
   $pdf->Row($data);
   $m++;
}

if($feducon->num_rows()>0){
   $pdf->Cell(170,7,toUTF("El Docente SI Realiza Investigación a Nivel Universitario Registrado en http://vriunap.pe/fedu"));
}else{
   $pdf->Cell(170,7,toUTF("El docente no tiene registrado Proyectos de Investigación FEDU en los años 2016  y 2017 ."),1,1,'C');
}

// FEDUUUU ANTERIOR

$pdf->ln(10);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(170,7,toUTF("Proyectos de Investigación (FEDU) - Del 2018 en Adelante "),0,1,'C');
$pdf->Cell(170,0,"",1,1);$pdf->SetFont('Arial','',9);
$feducon2=$this->dbFedu->getTable("docIntegrantes","CodDoc=$codigo");




$pdf->SetWidths(array(10,60,100));
$m=1;
foreach($feducon2->result() as $row){
   $proy=$this->dbFedu->getSnapRow('docProy',"Id=$row->IdProy");
   $proyDet=$this->dbFedu->getSnapRow('docProyDetalle',"IdProy=$row->IdProy");
   $data= array(
      $m,
      toUTF($proy->Codigo.":".$proy->Estado .":".$proy->Anio),
      toUTF("$proyDet->Titulo"),
   );
   $pdf->Row($data);
   $m++;
}


if($feducon2->num_rows()>0){
   $pdf->Cell(170,7,toUTF("El Docente SI Realiza Investigación a Nivel Universitario Registrado en http://vriunap.pe/fedu"));
}else{
   $pdf->Cell(170,7,toUTF("El docente no tiene registrado Proyectos de Investigación FEDU, del 2018 en Adelante."),1,1,'C');
}
$fecha=getdate();
$pdf->Ln(7);
$pdf->cell(170,5,"$fecha[mday] - $fecha[month] - $fecha[year]",0,1,'L');
$pdf->cell(170,5,toUTF("Plataforma de Investigación y Desarrollo") ,0,1,'L');

$pdf->Output();    
}
// ***************************************************** MÓDULOS 2018
public function vwValidaLineas(){

   $this->gensession->IsLoggedAccess(PILAR_CORDIS);
   $sess = $this->gensession->GetSessionData(PILAR_CORDIS);
   if($sess->userLevel>0){
      $carre = mlGetGlobalVar("IdCarrera");
      if( $carre == 19 )
         $carre = 18;
      $lineas = $this->dbRepo->getTable("tblLineas","IdCarrera='$carre' AND Estado = '1'");
      $this->load->view("pilar/cord/report/vwValidaLineas",array('lineas'=>$lineas));
   }else{
      echo "<h3> El módulo se habilitará a las 13:00Hrs.</h3>";
   }
}
public function vwMdlUpdateLin($idLin){
   $a=$this->dbPilar->getSnapRow("docLineas","Id=$idLin");
   $nombre=$this->dbRepo->inDocente("$a->IdDocente");
   $Linea=$this->dbRepo->inLineaInv("$a->IdLinea");
   echo "  
   <div class='modal-content'>
   <div class='modal-header'>
   <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
   <h4 class='modal-title text-primary' id='myModalLabel'>VALIDAR DOCENTE </h4>
   </div>
   <div id='indecisosisi'>
   <div class ='modal-body'>
   <p class='text-justify'>Esta acción <b class='text-success'>CONFIRMA</b> que el docente  $nombre, es especialista en la linea de Investigación $Linea.</p>
   </div>
   <form id='porquee' method='POST'><input name='tipi' id='tipi' class='hidden' value='2'></input></form>
   <div class='modal-footer'>
   <button type='button'class='btn btn-success' id='modal-btn-si' onclick='jsConfirmlLinea($idLin)'>Confirmar</button>
   <button type='button' class='btn btn-warning' id='modal-btn-no' onclick='jsRevalidaLinea($idLin)'>No es Especialista</button>
   <button type='button' class='btn btn-danger' id='modal-btn-no' data-dismiss='modal'>SALIR</button>
   </div>
   </div>
   </div>";
}
public function  vwMdlValidaLinea($idLin){
   $a=$this->dbPilar->getSnapRow("docLineas","Id=$idLin");
   $nombre=$this->dbRepo->inDocente("$a->IdDocente");
   $Linea=$this->dbRepo->inLineaInv("$a->IdLinea");
   echo"
   <div class ='modal-body'>
   <form id='porquee' method='POST'>
   <div class='form-group col-md-12'>
   <input name='tipi' id='tipi' class='hidden' value='0'></input>
   <p class='text-justify'>INDIQUE EL MOTIVO POR EL CUAL EL DOCENTE <b>$nombre</b> <b class='text-danger'>NO PUEDE PERTENECER </b> A LA LINEA DE INVESTIGACIÓN $Linea:</p>
   <textarea class='col-md-12 input-sm' name='txtporque' id='txtporque' placeholder='Ingrese Motivos'></textarea>
   </div>
   </form>
   <br><br><br>
   </div>
   <div class='modal-footer'>
   <button type='button'class='btn btn-success' id='modal-btn-si' onclick='jsConfirmlLinea($idLin)'>GUARDAR</button>
   <button type='button' class='btn btn-danger' id='modal-btn-no' data-dismiss='modal'>CANCELAR</button>
   </div>
   ";
}
public function execUpdateLin($id){
   $a=$this->dbPilar->getSnapRow("docLineas","Id=$id");
   $alvin = mlSecureRequest('txtporque')  ;
   $nombre=$this->dbRepo->inDocente("$a->IdDocente");
   $Linea=$this->dbRepo->inLineaInv("$a->IdLinea");
   $tipi=mlSecureRequest("tipi");   
   if($tipi==2){
      $this->dbPilar->Update('docLineas',array('Estado'=>2,'Obs'=>mlCurrentDate()),$id);
      // Insertamos el Log de Cambio de Estaodo
      $this->logCordinads("C","4","Valida Lineas","Valido la Linea de Investigación $Linea del Docente $nombre.");
      echo "VERIFICADO";
   }else{
      if ($alvin) {
         $this->dbPilar->Update('docLineas',array('Estado'=>0,'Obs'=>mlCurrentDate().":$alvin"),$id);
         $this->logCordinads("C","4","Valida Lineas","Observó la Linea de Investigación $Linea del Docente $nombre.");
         echo "PARA REVALIDAR";
      }else{
         echo "<script>alert('Debe de ingresar un motivo para observar al docente.');</script>";
      }
   }
}

public function innerTrams( $tipo=null )
{
        // $this->gensession->IsLoggedAccess( PILAR_CORDIS );

 $estado = mlSecurePost( "estado" );
 $carrer = mlGetGlobalVar("IdCarrera");;
 $jurado = mlSecurePost( "jurado" );


        // en casi interno con envio de FormData
        //
 if( $tipo==1 && $estado==null && $carrer==null )  $estado = 1;
 if( $tipo==2 && $estado==null && $carrer==null )  $estado = 10;

 $filtro = " Tipo='$tipo' ";
        //----------------------------------------------------------------
 if( $estado >= 1 )
   $filtro .= " AND Estado='$estado' ";
if( $carrer >= 1 )
   $filtro .= " AND IdCarrera='$carrer' ";
if( strlen($jurado) ) {
   $idDocn = $this->dbRepo->inByDatos( $jurado );
   if( ! $idDocn ) $idDocn=-101;
   $filtro = "Tipo='$tipo' AND (IdJurado1=$idDocn OR
   IdJurado2=$idDocn OR
   IdJurado3=$idDocn OR
   IdJurado4=$idDocn) ";
   $estado = $carrer = 0;
}
        //----------------------------------------------------------------
$filtro .= " ORDER BY Estado DESC, FechModif DESC ";


        //
        // por tipo de tramite y fecha de modif la que se controla en cada cambio
        // mas rapido y detallado que obtenerlo de la ultima iteracion
        //
$tproys = $this->dbPilar->getTable( 'tesTramites', $filtro );

$this->load->view( "pilar/admin/verTrams", array (
  'tcarrs' => $this->dbRepo->getTable( "dicCarreras", "1 ORDER BY Nombre" ),
  'tproys' => $tproys,
  'carrer' => $carrer,
  'estado' => $estado,
  'jurado' => $jurado,
  'tipo'   => $tipo
) );
}

    //  FUNCIONES 2018 ACONDICIONADAS

public function execRechaza( $idtram=0 ){
   $tram=$this->dbPilar->getSnapRow("tesTramites","Id=$idtram");
   $msg = "<b>Saludos</b><br><br>\nSu trámite ha sido rechazado, contiene los siguientes errores:\n"
   . "<br><br><ul>\n<li> EL documento no cumple con el formato de la Escuela profesional.\n</ul><br>\nDeberá corregir y subir su proyecto a la brevedad posible.\n"
   . "<br><b>Nota</b>: Revise el <a href='http://vriunap.pe/vriadds/pilar/doc/manual_tesistav3.pdf'>manual de tesista aquí.</a>";

   echo "  
   <div class='modal-content'>
   <div class='modal-header'>
   <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
   <h4 class='modal-title text-primary' id='myModalLabel'>RECHAZAR PROYECTO </h4>
   </div>
   <div id='indecisosisi'>
   <div class ='modal-body' id='popis'>
   <form id='corazon' method='POST'>
   <b>Codigo :</b> $tram->Codigo 
   <br><b>Linea ($tram->IdLinea) :</b> " . $this->dbRepo->inLineaInv($tram->IdLinea)."
   <br><b>Tesista(s) :</b> "             . $this->dbPilar->inTesistas($tram->Id)."
   <hr>
   <div class='form-group col-md-12'>
   <input type=hidden name='idtram' id='idtram' value='$idtram'>
   <label for='comment'>Mensaje a enviar:<small class='help-block'>Indique el o los motivos por los que el proyecto debe de ser rechazado.</small></label>

   <textarea class='col-md-12 input-sm' name='txtporque' id='txtporque'rows='9' placeholder='Ingrese Motivos'>$msg</textarea>
   </div>
   <br><br>
   </div>
   </form>
   <button type='button'class='btn btn-success' id='modal-btn-si' onclick='popExeRechaza(\"$idtram\")'>GUARDAR</button>
   </div>
   <div class='modal-footer'>
   <button type='button' class='btn btn-danger' id='modal-btn-no' data-dismiss='modal'>SALIR</button>
   </div>
   </div>";

}

public function doRechaza($val){
   $this->gensession->IsLoggedAccess( PILAR_CORDIS );
      // $a=$this->dbPilar->getSnapRow("docLineas","Id=$id");
   $idtram = mlSecureRequest('idtram')  ;
   $motivo = mlSecureRequest('txtporque')  ;

   $tram=$this->dbPilar->getSnapRow("tesTramites","Id=$idtram");
   $this->inRechaza($tram,$motivo);
}

private function inRechaza( $rowTram , $msg)
{
   $tram = $this->dbPilar->inProyTram( $rowTram->Id );
   $sess = $this->gensession->GetSessionData(PILAR_CORDIS);
      if( $tram->Estado == 1 or $tram->Estado ==3  or $tram->Estado == 11  ) {
         echo $msg;
            // $this->dbPilar->Delete( "tesTramites", $tram->Id );
            // no borramos pero dejamos para consultas de eliminacion
         if ($tram->Estado==1)$this->dbPilar->Update( "tesTramites", array('Tipo'=>0), $tram->Id );
         if ($tram->Estado==3)$this->dbPilar->Update( "tesTramites", array('Tipo'=>0), $tram->Id );
         if ($tram->Estado==11)$this->dbPilar->Update( "tesTramites", array('Estado'=>10), $tram->Id );

         $mail = $this->dbPilar->inCorreo( $tram->IdTesista1 );
         $this->logCordinads('S', '5', "Retorna Documento : Corregir Formato", $msg );
         $this->logCorreo( $tram->IdTesista1,0, $mail, "Corregir Formato Retornado", $msg );
         $maild = $this->dbRepo->inCorreo( $tram->IdJurado4 );
         $this->logCorreo(0,$tram->IdJurado4 , $maild, "Corregir Formato Retornado", "Dirección de tesis : $tram->Codigo <br>".$msg );
         $this->logTramites($sess->userId , $tram->Id, "Retorna Documento : Corregir Formato", $msg );
         echo "<br><br> <b class='text-danger'>$tram->Codigo</b> fue Retornado...";
          return;
      }
   echo "Error: El trámite no se puede borrar.";
  
}


   //
   // envia que revisen borrador los jurados completos
   //
   public function listBrDire( $idtram=0 )
   {
      $this->gensession->IsLoggedAccess( PILAR_CORDIS );
      if( !$idtram ) return;

      $tram = $this->dbPilar->inProyTram($idtram);
      if(!$tram){ echo "No registro"; return; }

      // solo los que estan en espera pasan
      if( $tram->Estado != 11 ) {
         echo "Error: su estado no era en espera, no enviado.";
         return;
      }

      //
      // pasamos estado a revision de borrador
      //
      $this->dbPilar->Update( "tesTramites", array(
            'Estado'    => 12,
            'FechModif' => mlCurrentDate()
         ), $tram->Id );


        // generamos el memo borrados revis
        $nroMemo = $this->inGenMemo( $tram, 4 );

        echo "Cod de Tramite: <b>$tram->Codigo</b><br>";
        echo "Memo Circular: <b>$nroMemo</b><br>";


      $msg = "<h4>Borrador enviado a Revisión</h4><br>"
          . "Su Borrador de Tesis: <b>$tram->Codigo</b> ha sido enviado a los cuatro miembros de su Jurado. "
          . "El mismo que será revisado mediante la <b>Plataforma PILAR</b>."
          ;

      $this->logTramites($sess->userId , $tram->Id, "Borrador Enviado a Revisión", $msg );
      
      $mail = $this->dbPilar->inCorreo( $tram->IdTesista1 );
      $this->logCorreo( $tram->IdTesista1,0, $mail, "Borrador enviado a revisión", $msg );

      // envio a jurados
      //
      $det = $this->dbPilar->inLastTramDet( $tram->Id );
      $msg = "<h4>Revisión Electrónica</h4><br>"
          . "Por la presente se le comunica que se le ha enviado a su cuenta de Docente en la "
          . "<b>Plataforma PILAR</b> el borrador de tesis con el siguiente detalle:<br><br>   "
          . "Memo Circular: <b>$nroMemo-VRI-UNAP</b><br>"
          . "Tesista(s) : <b>" . $this->dbPilar->inTesistas($tram->Id) . "</b><br>"
          . "Título : <b> $det->Titulo </b><br><br>"
          . "Ud. tiene un plazo de 10 dias hábiles para realizar las revisiones mediante la Plataforma."
          ;

      $corr1 = $this->dbRepo->inCorreo( $tram->IdJurado1 );
      $corr2 = $this->dbRepo->inCorreo( $tram->IdJurado2 );
      $corr3 = $this->dbRepo->inCorreo( $tram->IdJurado3 );
      $corr4 = $this->dbRepo->inCorreo( $tram->IdJurado4 );

      $this->logCorreo( 0,$tram->IdJurado1, $corr1, "Revisión de Borrador de Tesis", $msg );
      $this->logCorreo( 0,$tram->IdJurado2, $corr2, "Revisión de Borrador de Tesis", $msg );
      $this->logCorreo( 0,$tram->IdJurado3, $corr3, "Revisión de Borrador de Tesis", $msg );
      $this->logCorreo( 0,$tram->IdJurado4, $corr4, "Revisión de Borrador de Tesis", $msg );


      //echo $tram->Codigo . " fue Enviado a su Director";
      echo "Correos enviados correctamente<br>";
        echo "El Borrador está en Revisión desde Hoy.<br>";
   }

public function listPyDire( $idtram=0 )
{
   $this->gensession->IsLoggedAccess( PILAR_CORDIS );
   if( !$idtram ) return;

   $tram = $this->dbPilar->inProyTram($idtram);
   if(!$tram){ echo "No registro"; return; }

      // no borramos pero dejamos para consultas de eliminacion
   $this->dbPilar->Update( "tesTramites", array(
      'Estado'    => 2,
      'FechModif' => mlCurrentDate()
   ), $tram->Id );

      // envio de correo
      //
   $msg = "<h4> Enviado al Director </h4><br>"
   . "Su proyecto ha sido enviado a su Director de Proyecto con el "
   . "formato revisado, su Director ya puede revisarlo en la <b>Plataforma PILAR</b>."
   ;

   $mail = $this->dbPilar->inCorreo( $tram->IdTesista1 );
   $this->logCorreo(0,$tram->IdTesista1, $mail, "Proyecto para Asesoria", $msg );
        //------------------------------------------------------------------------------------------------
   $msg = "<h4> Proyecto para Asesoria </h4><br>"
   . "Se le ha remitido el proyecto con código <b>$tram->Codigo</b> "
   . "Ud. ya puede revisarlo y aprobarlo para enviarlo a sorteo en la <b>Plataforma PILAR</b>."
   ;
   $mail = $this->dbRepo->inCorreo( $tram->IdJurado4 );
   $celu = $this->dbRepo->inCelu( $tram->IdJurado4 );
   $this->logCordinads('S', '6 ', "Envia Proyecto a Director", $msg );
   $this->logCorreo( $tram->IdJurado4,0, $mail, "Proyecto para Asesoria", $msg );
   $a=$this->notiCelu($celu,1);
   $msg=$msg.$a;
        //------------------------------------------------------------------------------------------------
   $this->logTramites( 2, $tram->Id, "Enviado al Director", $msg );

   echo "<b class='text-success'>".$tram->Codigo . " fue Enviado a su Director</b>";
}


public function notiCelu($cel,$tip)
{
 $this->load->library('apismss');

 $deviceID = 89014;
 $number   = "0051$cel";
 if($tip==1){
   $mensaje  = "UNAP VRI PILAR \nSr. Docente le llegó un nuevo proyecto en calidad de ASESOR/DIRECTOR de tesis, puede revisarlo en la plataforma PILAR en http://vriunap.pe/pilar,  \n\n".date("d-m-Y")."\nVicerrectorado de Investigación.";
}
if($tip==3){
   $mensaje  = "UNAP VRI PILAR \nSr. Docente usted fué SORTEADO como JURADO de tesis, puede revisarlo en la plataforma PILAR en http://vriunap.pe/pilar,  \n\n".date("d-m-Y")."\nVicerrectorado de Investigación.";
}else{
   $mensaje  = "UNAP VRI PILAR \nSr. Docente se le recuerda revisar, la plataforma PILAR en http://vriunap.pe/pilar y verificar los proyectos y borradores pendientes.\n\n".date("d-m-Y")."\nVicerrectorado de Investigación.";
}
$result   = $this->apismss->sendMessageToNumber($number,$mensaje,$deviceID);

if ($result) {
   return "Mensaje Enviado al $number";
}else{
   return  "Error al enviar mensaje : $number";
}
}

    //---------------------------------------------------------------------------------------
// public function sortPres($lista){

// }  

public function execSorteo( $idtram=0 )
{
    $this->gensession->IsLoggedAccess( PILAR_CORDIS );

    if( !$idtram ) return;

    $tram = $this->dbPilar->inProyTram($idtram);
    if(!$tram){ echo "No registro"; return; }
    $tramDet=$this->dbPilar->getSnapRow("tesTramsDet","IdTramite='$tram->Id' ORDER BY Iteracion desc");
    $intentos=$this->dbPilar->getSnapView("tesJuCambios","IdTramite=$tram->Id")->num_rows()+1;

    echo "  
<div class='modal-content'>
<div class='modal-header'>
<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
<h4 class='modal-title text-primary' id='myModalLabel'>SORTEO DE JURADOS - PILAR </h4>
</div>
<div class ='modal-body' id='sortis'><h3 class='text-right text-danger' style:'margin-top:0px;'> Intento N°  <i id='cntSor'>$intentos</i></h3> <form name='sorT' id='sorT' method='post'>";
    if($intentos>=6){
        echo "El proyecto ya cuenta con $intentos intentos, No puede ser Sorteado";
        exit(0);
    }
    echo "<b>Codigo :</b> $tram->Codigo ";
    echo "<br><b>Linea ($tram->IdLinea) :</b> " . $this->dbRepo->inLineaInv($tram->IdLinea);
    // echo "<br><b>Tesista(s) :</b> "             . $this->dbPilar->inTesistas($tram->Id);
    echo "<br><b>Director :</b> "             . $this->dbRepo->inDocenteEx($tram->IdJurado4);
    $archi = "/repositor/docs/$tramDet->Archivo";
    echo "<br><b>Archivo de Tesis :</b><a href='$archi' class='btn btn-xs btn-info no-print' target=_blank> Ver PDF Click Aquí</a>";
    echo "<br><b>Jurado :</b> [ $tram->IdJurado1 / $tram->IdJurado2 / $tram->IdJurado3 / $tram->IdJurado4 ]";


    if( $tram->IdJurado1+$tram->IdJurado2+$tram->IdJurado3 > 0 ) {
        echo "<br><b>No se puede sortear.";
        return;
    }

    // detallaremos evento interno Ev31
    echo "<input type=hidden name=evt value='31'>";
    echo "<input type=hidden name=idtram value='$idtram'>";
    // *************************************PRESIS
    $presis= array();
    $sumpres=0;

    //  Excepción Arte
    if($tram->IdCarrera!= 11){
        // SELECT * FROM vriunap_pilar3.vxDocInLinXX WHERE TipoDoc='N' AND Activo=6 AND LinEstado=2 AND IdLinea='182' AND IdCarrera='1'
        $tpres = $this->dbPilar->getSnapView( 'vxDocInLin', "TipoDoc='N' AND Activo=6 AND LinEstado=2 AND IdLinea='$tram->IdLinea' AND IdCarrera='$tram->IdCarrera' " );
    }else{

        $tpres = $this->dbPilar->getSnapView( 'vxDocInLin', "Activo=6 AND LinEstado=2 AND IdLinea='$tram->IdLinea' AND IdCarrera='$tram->IdCarrera' " );
    }

    if($tpres->num_rows() < 1 ){
        echo "<h3>Debe validar a los docentes de la Linea</h3>";
        return;
    }

    if($tpres->num_rows() < 1 ){
        echo "<h3>Pocos docentes en Linea </h3>";
        return;
    }

    //echo "N: " . $tpres->num_rows();
    //return;

    foreach($tpres->result() as $rino){

        if($tram->IdJurado4!=$rino->IdDocente )
        {
            $val = (int)$this->dbPilar->totProys( $rino->IdDocente );
            $presis[ $rino->IdDocente ] = $val;
            $sumpres += $val;
        }
    }
    $totalpres= count($presis);
    $mediapres= $sumpres/$totalpres;
    // echo "Presis : $totalpres - $mediapres <br>";
    $pmenors = array();
    $pmayors = array();
    // $mediapres=13;

    foreach( $presis as $k => $v) { // id
        if( $v<$mediapres ) $pmenors[] = $k;
        else            $pmayors[] = $k;
    }
    // al ser muy pocos ponerlos a todos los weyes de eMe.
    if( count($pmenors) <= 2 )
        $pmenors = array_merge($pmenors,$pmayors);

    // retomar el conteo del array general
    $totalpres = count( $pmenors );

    srand( time() ); 
    $j1 = rand( 0, $totalpres-1 );

    $presi=$pmenors[$j1];
    // ************************************************************NORMAL

    $tdocs = $this->dbPilar->getSnapView( 'vxDocInLin', "Activo=6 AND LinEstado=2 AND IdLinea=$tram->IdLinea AND IdDocente<>$presi" );
    if($tdocs->num_rows()<1){
        echo "<h3>Debe validar a los docentes de la Linea</h3>";
        return;
    }
    $lista = array();
    $suma  = 0;
    foreach( $tdocs->result() as $row ){
        // echo "$row->DatosPers <br>"; 
        if($tram->IdJurado4!=$row->IdDocente )
        {
            $val = (int)$this->dbPilar->totProys( $row->IdDocente );
            $lista[ $row->IdDocente ] = $val;
            $suma += $val;
        }
    }
    $total = count( $lista );
    $media = $suma / $total;

    echo sprintf("<br>Carrera : $tram->IdCarrera <b>Docentes en la linea:</b> (%d)  |  <b>Media:</b> (%.3f)", $total, $media );

    $menors = array();
    $mayors = array();

    foreach( $lista as $k => $v) { // id
        if( $v<$media ) $menors[] = $k;
        else            $mayors[] = $k;
        // $var=$var+1/count($docentes)*(($v-$media)*($v-$media));
    }
    // al ser muy pocos ponerlos a todos los weyes de eMe.
    if( count($menors) <= 3 )
        $menors = array_merge($menors,$mayors);

    // retomar el conteo del array general
    $total = count( $menors );

    // echo " | <b class='text-success'>N - poca carga: </b> ($total)";

    // semilla, nunca se repetiran los indices
    srand( time() );

    do {
        $j2= rand( 0, $total-1 );
        $j3 = rand( 0, $total-1 );

    }while( $j2 == $j3 );


    $idDocs = array($presi, $menors[$j2], $menors[$j3], $tram->IdJurado4);


    $arrRes = array();

    $strsor = "<table class='table table-bordered' cellPadding=0>";
    for( $i=0; $i<4; $i++ ) {

        $idDocente = $idDocs[$i];

        $nombe = $this->dbRepo->inDocenteEx($idDocente);

        $grado = $this->dbPilar->getOneField( "docEstudios", "IdGrado", "IdDocente=$idDocente ORDER BY IdGrado" );
        $categ = $this->dbRepo->getOneField( "vwDocentes", "IdCategoria", "Id=$idDocente" );
        $antig = $this->dbRepo->getOneField( "vwDocentes", "Antiguedad", "Id=$idDocente" );
        $carr=$this->dbRepo->getOneField( "tblDocentes", "IdCarrera", "Id=$idDocente" );

        // grado = 0 poner grado alto hasta registrar
        if( !$grado ) $grado  = 7;

        $ponAn = sprintf( "%.3f", 1 - ($antig/15000) );
        $ponde = (($categ*10) + $grado)*10 + $ponAn;

        $arrRes[$i] = array( $idDocente, $ponde,$carr);


        $strsor .= "<tr>";
        $strsor .= "<td> $nombe </td>";
        //echo "<td> <b>$doc->TipoDoc</b> <br><small>$doc->CategAbrev</small> </td>";
        $strsor .= "<td> $categ </td>";
        $strsor .= "<td> $grado </td>";
        $strsor .= "<td> $antig </td>";
        $strsor .= "<td> $ponAn </td>";
        $strsor .= "<td> $ponde </td>";
        $strsor .= "</tr>";
    }
    $strsor .= "</table>";
    // echo"Docinti:";
    //  print_r($arrRes[0][2]);


    //-----------------------------------------------------------------------------------
    for( $i=0; $i<3; $i++ ) for( $j=$i+1; $j<3; $j++ ){
        if( $arrRes[$i][1] > $arrRes[$j][1] )
        {
            $temp = $arrRes[$i];
            $arrRes[$i] = $arrRes[$j];
            $arrRes[$j] = $temp;
        }
    }

    if($arrRes[0][2]!=$tram->IdCarrera){
        $eliza=$arrRes[0];
        $arrRes[0]=$arrRes[1];
        $arrRes[1]=$eliza;
    }

    if($arrRes[0][2]!=$tram->IdCarrera){
        $eliza=$arrRes[0];
        $arrRes[0]=$arrRes[2];
        $arrRes[2]=$eliza;
    }

    //-----------------------------------------------------------------------------------
    $arrRes[3] = array( $tram->IdJurado4, 0 );

    // $idDocP = $arrRes[1-1][0];

    // $ejne=$this->dbRepo->getOneField( "tblDocentes", "IdCategoria", "Id=$idDocP" );
    //   echo "IdCat Jurado : $ejne| Carrera : $ej1<br>";
    //   if($tram->IdCarrera!= $ej1){
    //    goto againplease; 
    // }
    // if ($ejne>10) {
    //    $media = $media +1;
    //    goto againplease;
    // }
    // echo "<br>:::".$idDocP." / $ej1 / $tram->IdCarrera<br>";
    //GUARDAR REGISTRO
    // Insertar Intentos de sorteos en Historial de Jurados
    $this->dbPilar->Insert("tesJuCambios", array(
        'Referens'  => "Coords",        // Mejorar tipo doc
        'IdTramite' => $tram->Id,    // guia de Tramite
        'Tipo'      => $tram->Tipo,  // en el momento
        'IdJurado1' => $arrRes[0][0],
        'IdJurado2' => $arrRes[1][0],
        'IdJurado3' => $arrRes[2][0],
        'IdJurado4' => $tram->IdJurado4,
        'Motivo'    => "Intento $intentos",
        'Fecha'     => mlCurrentDate()
    ) );
    //FIN GUARDAR 
    echo "<table class='table table-bordered' cellPadding=0>";
    for( $i=1; $i<=4; $i++ ) {

        $idDoc = $arrRes[$i-1][0];
        $posis = "<input type='hidden' name='j$i' value='$idDoc'>";

        $conte = sprintf( "%02d", $this->dbPilar->totProys($idDoc) );
        $nombe = $this->dbRepo->inDocenteEx($idDoc);
        $carre = $this->dbRepo->inCarreDoc($idDoc);
        $carre = "<br><p style='font-size:9px'> $carre </p>";

        $doc = $this->dbRepo->inDocenteRow($idDoc);

        if( $tram->IdJurado4 == $idDoc )
            $nombe = "<b>$nombe (D) </b>";

        echo "<tr>";
        echo "<td> $idDoc $posis </td>";
        echo "<td> $nombe $carre </td>";
        echo "<td> <small>$doc->Antiguedad</small> </td>";
        echo "<td> ($doc->Tipo) <small>$doc->CategAbrev</small> </td>";
        echo "<td> <b>$conte</b> </td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</form>";

    echo "<b>Declaración Jurada :</b>";
    echo "<br><input type='checkbox' class='form-check-input' id='linC' onclick='enableSave()'>";
    echo "<label class='form-check-label'>El proyecto corresponde a la <b class='text-warning'>Linea de Investigación</b>? </label>";

    echo "<br><input type='checkbox' class='form-check-input' id='directC' onclick='enableSave()'>";
     echo "<label class='form-check-label'>El Director es idoneo para el proyecto de tesis ? </label>";

     echo "<br><input type='checkbox' class='form-check-input' id='cumpleC' onclick='enableSave()'>";
     echo " <label class='form-check-label'>El proyecto de tesis cumple con lo establecido por la Escuela Profesional ?</label>";

     echo "<br><input type='checkbox' class='form-check-input' id='aceptoC'  onclick='enableSave()'>";
     echo " <label class='form-check-label'>Estoy deacuerdo con el proyecto de tesis para su calificación por los jurados.</label>";

     echo "<button type='button'class='btn btn-success' disabled='' id='modal-btn-si' onclick='popSaveSort(\"$idtram\")'>GUARDAR</button>";
      //echo "<BR>Varianza =".$var;
      // echo "<div id='fred'></div>";

     echo "</div>";
  }

public function inDoSorteoX( $rowTram ){
   $j1 = mlSecurePost( "j1" );
   $j2 = mlSecurePost( "j2" );
   $j3 = mlSecurePost( "j3" );
   $j4 = mlSecurePost( "j4" );

   echo "$j1 / $j2 /$j3 /$j4  ";
}

public function inDoSorteo($idTram){
   $rowTram=$this->dbPilar->getSnapRow("tesTramites","Id=$idTram");
   $sess=$this->gensession->GetSessionData(PILAR_CORDIS);
   $j1 = mlSecurePost( "j1" );
   $j2 = mlSecurePost( "j2" );
   $j3 = mlSecurePost( "j3" );
   $j4 = mlSecurePost( "j4" );

        // revisar que no haya sido ya enviado y modificado
   if( $this->dbPilar->getSnapRow("tesTramites","Id=$rowTram->Id AND Estado=4") ) {
      echo "Este trámite ya se actualizo.";
      return;
   }

   $this->dbPilar->Update( "tesTramites", array(
                'Estado'    => 4, // sorteado enviado a revisar 1
                "IdJurado1" => $j1,
                "IdJurado2" => $j2,
                "IdJurado3" => $j3,
                "FechModif" => mlCurrentDate()
             ), $rowTram->Id );

        // una vez guardado recargamos por los nuevos datos...
   $rowTram = $this->dbPilar->inProyTram( $rowTram->Id );


        // memos revisiòn 1 proyecto

   $nroMemo = $this->inGenMemo( $rowTram, 1 );
   echo "Cod de Tramite: <b>$rowTram->Codigo</b><br>";
   echo "Memo Circular: <b>$nroMemo</b><br>";


   $msg = "<h4>Proyecto enviado a Revisión</h4><br>"
   . "Su Proyecto de Tesis: <b>$rowTram->Codigo</b> ha sido enviado a los miembros de su Jurado. "
   . "Será revisado en un plazo de 10 dias habiles mediante la <b>Plataforma PILAR</b>."
   ;

   $mail = $this->dbPilar->inCorreo( $rowTram->IdTesista1 );

   $this->logCorreo( $rowTram->IdTesista1,0, $mail, "Proyecto enviado a Revisión", $msg );

   $msg = "Sorteo y Envio a Revisión\n"
   . "Proyecto: $rowTram->Codigo  -- Linea: $rowTram->IdLinea\n"
   . "- Presidente: ($j1) \n- Primer Miembro: ($j2) \n- Segundo Miembro: ($j3) \n- Director: ($j4)"
   ;
   $this->logTramites( $sess->userId, $rowTram->Id, "Proyecto enviado a Revisión", $msg );
        // correo a tesista
        // correo a Jurados 4
      // envio a jurados
      //
   $det = $this->dbPilar->inLastTramDet( $rowTram->Id );
   $msg = "<h4>Revisión Electrónica</h4><br>"
   . "Por la presente se le comunica que se le ha enviado a su cuenta de Docente en la "
   . "<b>Plataforma PILAR</b> el proyecto de tesis con el siguiente detalle:<br><br>   "
   . "Memo Circular: <b>$nroMemo-VRI-UNAP</b><br>"
   . "Codigo : <b> $rowTram->Codigo </b><br>"
   . "Título : <b> $det->Titulo </b><br><br>"
   . "Ud. tiene un plazo de 10 dias hábiles para realizar las revisiones mediante la Plataforma."
   . "<br><br>NOTA : La tesis <b>no se envia al correo</b>, se envia a su cuenta en <b>PILAR</b>."
   ;

   $corr1 = $this->dbRepo->inCorreo( $rowTram->IdJurado1 );
   $corr2 = $this->dbRepo->inCorreo( $rowTram->IdJurado2 );
   $corr3 = $this->dbRepo->inCorreo( $rowTram->IdJurado3 );
      // $corr4 = $this->dbRepo->inCorreo( $rowTram->IdJurado4 );
      // logCorreo( $idTes,$idDoc, $correo, $titulo, $mensaje )
   $celu1 = $this->dbRepo->inCelu( $rowTram->IdJurado1 );
   $celu2 = $this->dbRepo->inCelu( $rowTram->IdJurado2 );
   $celu3 = $this->dbRepo->inCelu( $rowTram->IdJurado3 );
   $celu4 = $this->dbRepo->inCelu( $rowTram->IdJurado4 );
   $this->notiCelu($celu1,3);
   $this->notiCelu($celu2,3);
   $this->notiCelu($celu3,3);
   $this->notiCelu($celu4,3);

   $this->logCorreo( 0,$rowTram->IdJurado1, $corr1, "Revisión de Proyecto de Tesis", $msg );
   $this->logCorreo( 0,$rowTram->IdJurado2, $corr2,"Revisión de Proyecto de Tesis", $msg );
   $this->logCorreo( 0,$rowTram->IdJurado3, $corr3,"Revisión de Proyecto de Tesis", $msg );
      //$this->logCorreo( $rowTram->Id, $corr4, "Revisión de Proyecto de Tesis", $msg );
   $this->logCordinads('T', '7', "Sorteo de Jurados", "$rowTram->IdJurado1/$rowTram->IdJurado2/$rowTram->IdJurado3/$rowTram->IdJurado4");
   echo "Correos enviados correctamente<br>";
   echo "El Proyecto está en Revisión desde Hoy.<br>";
        ////echo "DBGmails: $corr1 - $corr2 - $corr3 - $corr4";


        // Finalmente
        //-----------
        //
        // Insertar nuevos sorteos en Historial de Jurados
   $this->dbPilar->Insert("tesJuCambios", array(
            'Referens'  => "Coords",        // Mejorar tipo doc
            'IdTramite' => $rowTram->Id,    // guia de Tramite
            'Tipo'      => $rowTram->Tipo,  // en el momento
            'IdJurado1' => $rowTram->IdJurado1,
            'IdJurado2' => $rowTram->IdJurado2,
            'IdJurado3' => $rowTram->IdJurado3,
            'IdJurado4' => $rowTram->IdJurado4,
            'Motivo'    => "Sorteo",
            'Fecha'     => mlCurrentDate()
         ) );
} 
// Modulos 2019 Inclusión de Información, Validación y Busqueda

   function vwinfoCoord(){
      $this->load->view('pilar/cord/info/infoCord');   
   }

   function vwReporteCarreraActivis(){
      $carrera=mlGetGlobalVar("IdCarrera");
      $dato=$this->dbPilar->getSnapView("logCordinads","IdOperacion=1 AND IdCarrera=$carrera ORDER BY ID DESC");
      echo "<h2><b>Registro de Actividades<small></b></h2><ul>";
      foreach ($dato->result() as  $row) {
         echo "<li>$row->Fecha/$row->IdUser/$row->IdOperacion/$row->Just/$row->Detalle</li>";
      }
      echo "</ul>";
   }

   public function vwbusq(){
      $this->load->view("pilar/cord/info/bsqtesista");
   }

   public function listBusqTesis(){
      // mostrar ampliación si tiene
      // busqueda detallada del estado de tesistas
      $this->gensession->IsLoggedAccess( PILAR_CORDIS );
      $idCarr=mlGetGlobalVar('IdCarrera');
      $dat = mlSecurePost( "dni" );
      $datas=0;
      if( $dat ) {
         $trams = $this->dbPilar->inTramByCodigo( $dat );
         if( $trams ){
            $datas = $this->dbPilar->getSnapRow( "vxDatTesistas", "Id=$trams->IdTesista1" );
            $idtes = $trams->IdTesista1;
         }
         else {
            if( strlen($dat)==6 ){
               $dati=is_integer($dat);
                $datas = $this->dbPilar->getSnapRow( "vxDatTesistas", "Codigo LIKE '$dati'" );
                $idtes = ($datas)? $datas->Id : 0;
            }else {
                $filto = (is_numeric($dat))? "DNI LIKE '$dat%'" : "DatosPers LIKE '%$dat%'";
                $datas = $this->dbPilar->getSnapRow( "vxDatTesistas", $filto );
                $idtes = ($datas)? $datas->Id : 0;
            }
         }
         if ($datas) {
            $trams = $this->dbPilar->inTramByTesista( $datas->Id );
         }
      }
      // if( !$trams ) {
      //    echo "Sin registros";
      //    return;
      // }

      // verificar que exista un trámite
      $idTram = ($trams)? $trams->Id : 0;

      $idecarera=$this->dbPilar->getOneField('tblTesistas','IdCarrera',"Id=$idtes");
   // 
      if ($idCarr != $idecarera ) { 

         $carrera= $this->dbRepo->inCarrera($idCarr);
         echo "<div class='alert alert-warning'><b class='text-danger'>NOTA:</b> <p>El código que ha buscado pertenece a la escuela <b> $carrera</b>, es necesario es necesario seleccionar una escuela profesional correcta para porder visualiazar los datos .<br><small>Esquina superior derecha.</small></p></div>";
         return;
      }
      
      // renderizamos los resultados
      $this->load->view( "pilar/cord/info/rslttesista", array(
            'idtes' => $idtes,
            'tdata' => $datas,
            'ttram' => $trams,
            'proyA' => $this->dbPilar->inTramDetIter($idTram,3),
            'tamps' => $this->dbPilar->inAmpliacion($idTram),
            'tdets' => $this->dbPilar->inProyDetail( $trams? $trams->Id : 0 )
         ) );
    
}
   public function vwFormatos(){
      $this->load->helper(array('form', 'url'));
      $carrera=mlGetGlobalVar("IdCarrera");
      $this->load->view("pilar/cord/info/formatos",array('error' => ''));
   }

   public function do_upload(){
      $config = array(
         'upload_path' => "./repositor/formatos/",
         'allowed_types' => "pdf",
         'overwrite' => TRUE,
         'max_size' => "2048000", // Can be set to particular file size , here it is 2 MB(2048 Kb)

      );
      $this->load->library('upload', $config);
      if($this->upload->do_upload())
      {
         $data = array('upload_data' => $this->upload->data());
         // echo $this->load->view('upload_success',$data);
         echo "Ok Subido";
      }
      else
      {
         $error = array('error' => $this->upload->display_errors());
         $this->load->view('pilar/cord/info/formatos', $error);
         // echo "Error subiendo";
      }
   }

   public function  ReporteAnual($id){
    echo "<table style='width:100%' class='table table-striped '>";
    $lin=$this->dbRepo->getSnapView("dicCarreras","Id=");
    $flag=1;
    foreach($lin->result() as $row){
        $aler6 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 0 AND Anio = 2016" );
        $aler7 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 0 AND Anio = 2017" );
        $aler8 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 0 AND Anio = 2018" );
        $alep6 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 1 AND Anio = 2016" );
        $aleb6 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 2 AND Anio = 2016" );
        $ales6 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 3 AND Anio = 2016" );
        $alep7 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 1 AND Anio = 2017" );
        $aleb7 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 2 AND Anio = 2017" );
        $ales7 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 3 AND Anio = 2017" );
        $alep8 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 1 AND Anio = 2018" );
        $aleb8 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 2 AND Anio = 2018" );
        $ales8 = $this->dbPilar->getTotalRows("tesTramites","IdCarrera=$row->Id AND Tipo = 3 AND Anio = 2018" );

        $tot=$alep6 +$aleb6 +$ales6 +$alep7 +$aleb7 +$ales7 +$alep8 +$aleb8 +$ales8+$aler6+$aler7+$aler8;
    }


    echo "  <tr>
       <th>ID</th>
       <th>CARRERA</th>
    </tr>
    <tr>
    <th>Proyectos Presentados 2016</th>
    <th>$alep6</th>
    </tr>
<tr>
    <th>TOTAL</th>
    <th>$tot</th>
    </tr>
    </tr>";

/*        <th>N° 2016 Susten</th>
    <th>N° 2016 RECHA</th>
    <th>N° 2017 Py</th>
    <th>N° 2017 Bor</th>
    <th>N° 2017 Susten</th>
    <th>N° 2017 RECHA</th>
    <th>N° 2018 Py</th>
    <th>N° 2018 Bor</th>
    <th>N° 2018 Susten</th>
    <th>N° 2018 RECHA</th>*/
    echo "</table>";
    echo "</div>";
}

}
//- EO

