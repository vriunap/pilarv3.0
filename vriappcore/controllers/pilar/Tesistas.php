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
 ***************************************************************************/


include( "absmain/mlLibrary.php" );
include( "absmain/mlotiapi.php" );


// tesBorrador
// Edicion 2018.a
define( "ANIO_PILAR", "2020" );


class Tesistas extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('dbPilar');
        $this->load->model('dbRepo');
        $this->load->library("GenSession");
        $this->load->library("GenMailer");
        $this->load->library("GenSexPdf");
    }

    // Entrar al Admin 
    public function login() {

        $mail = mlSecurePost("mail");
        $pass = mlSecurePost("pass");
        if( !$mail ) return;

        // por DNI profesores $filto = is_numeric($dat)? "DNI LIKE '$dat%'" : "DatosPers LIKE '%$dat%'";

        // verificar existencia de correo
        if( ! $this->dbPilar->getSnapRow( "vxDatTesistas", "Correo='$mail'" ) ) {
            echo '[{"error":true, "msg":"Este Correo no está registrado"}]';
            return;
        }

        // ahora si comprobar cuenta
        $row = $this->dbPilar->loginByMail( "vxDatTesistas", $mail, sqlPassword($pass) );
        if( ! $row ) {
            $IdTesista = $this->dbPilar->getOneField( "vxDatTesistas", "Id", "Correo='$mail'"  );
            $this->logLogin( $IdTesista, "Clave incorrecta" );
            echo '[{"error":true, "msg":"Su clave es incorrecta"}]';
            return;
        }

        //----------------------------------------------------------------
        // como todo esta correcto creamos la sesion usuario general
        //----------------------------------------------------------------
        /*
            'IdService' => 0x10
            'servName'  => 'utf8'
            'userLevel' => 0
            'userType'  => 0
            'userId'    => $userId,
            'userCod'   => $userCod
            'userDesc'  => $userDesc
            'userName'  => $userName
            'userMail'  => $userMail
            'userDNI'   => $userDNI
            'islogged'  => true
        */

        $this->gensession->SetUserLogin(
            'tesistas',
            $row->Id,
            $row->DatosPers,
            $row->Correo,
            $row->DNI,
            $row->Codigo,
            $row->IdCarrera
        );

        $this->logLogin( $row->Id, "Ingreso" );

        echo '[{"error":false, "msg":"OK, Estamos redireccionando..."}]';
    }

    // Salir de Tesistas
    public function logout() {

        $this->gensession->SessionDestroy();
        redirect( base_url("pilar"), 'refresh');
    }


    public function logLogin( $idUser, $obs )
    {
        $this->load->library('user_agent');

        $agent = 'Unknowed UA';
        if ($this->agent->is_browser())
        {
            $agent = $this->agent->browser().' '.$this->agent->version();
        }
        elseif ($this->agent->is_robot())
        {
            $agent = $this->agent->robot();
        }
        elseif ($this->agent->is_mobile())
        {
            $agent = $this->agent->mobile();
        }

        //-----------------------------------------------------
        // echo $agent ." // ". $this->agent->platform();
        //-----------------------------------------------------
        $this->dbPilar->Insert( "logLogins", array(
                'Tipo'    => 'T',
                'IdUser'  => $idUser,
                'Accion'  => $obs,
                'IP'      => mlClientIP(),
                'OS'      => $this->agent->platform(),
                'Browser' => $agent,
                'Fecha'   => mlCurrentDate()
            ) );
    }

    private function logCorreo( $idUser, $correo, $titulo, $mensaje )
    {
        // enviamos mail
        $this->genmailer->mailPilar( $correo, $titulo, $mensaje );

		// procedemos a grabarlo
        $this->dbPilar->Insert(
            'logCorreos', array(
            'IdDocente' => 0,
            'IdTesista' => $idUser,
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
                'Tipo'      => 'T',      // T D C A
                'IdUser'    => $idUser,
                'IdTramite' => $tram,
                'Quien'     => 'Tesista',
                'Accion'    => $accion,
                'Detalle'   => $detall,
                'Fecha'     => mlCurrentDate()
        ) );
    }
    //-----------------------------------------------------------------------------


    /*
    public function verx()
    {
        $sess = $this->gensession->GetData();
        print_r( $sess );
    }
    */

    public function index()
    {
        if( mlPoorURL() )
            redirect( mlCorrectURL() );

        //
        // session usuario simple terminable
        //
        $sess = $this->gensession->GetData();

        if( !$sess ){
            redirect( base_url("pilar"), 'refresh');
            return;
        }

        // otro que no sea tesista kill
        if( $sess->userDesc != "tesistas" ) {
            $this->logout();
            return;
        }

        $this->inicia();
    }

    //------------------------------------------------------------------------------
    public function inicia()
    {
        $this->gensession->IsLoggedAccess();
        $sess = $this->gensession->GetData();

        $this->load->view("pilar/tes/header", array('sess'=>$sess) );
        $this->load->view("pilar/tes/menu");
        $this->load->view("pilar/tes/panelWork");
        $this->load->view("pilar/tes/footer");
    }

    public function lineasTes()
    {
        $this->gensession->IsLoggedAccess();
        $sess = $this->gensession->GetData();

		// hack para E. Inicial
		$carre = $sess->IdCarrera;
		if( $sess->IdCarrera == 19 )
			$carre = 18;

        $lineas = $this->dbRepo->getTable("tblLineas","IdCarrera='$carre' AND Estado = '1'");
        $this->load->view("pilar/tes/tesLineas",array('lineas'=>$lineas));
    }

    public function tesHerramientas()
    {
        $this->gensession->IsLoggedAccess();
        $this->load->view("pilar/tes/tesHerramientas");
    }

    public function tesContacto()
    {
        $this->gensession->IsLoggedAccess();
        $sess = $this->gensession->GetData();
        $tram = $this->dbPilar->inRowTesista( $sess->userId );

        $contacto=$this->dbPilar->getSnapRow('tblSecres',"Id_Facultad=$tram->IdFacultad AND UserLevel=4");
        $this->load->view("pilar/tes/contactoCords",array(
            'nombre'=>$contacto->Resp,
            'mail'=>$contacto->Correo,
            'celular'=>$contacto->Celular,
        ));
    }

    // evaluar el proyecto y cada estado
    //
    public function tesProyecto()
    {
        $this->gensession->IsLoggedAccess();

        $sess = $this->gensession->GetData();
        $tram = $this->dbPilar->inTramByTesista( $sess->userId ); // Tipo > 0

        // no hay tramite disponble nuevo tramite
        if( ! $tram ) {

            $prev = $this->dbPilar->getTable( "tesTramites", "Tipo<='0' AND (IdTesista1=$sess->userId OR IdTesista2=$sess->userId)" );
            $this->load->view( "pilar/tes/proc/0_regproy", ['prev'=>$prev] );

            //echo "<h3> Se  esta preparando un módulo de acuerdo al nuevo reglamento en debate el 20-03-18 en Consejo Universitario.</h3>";
            // echo "Desde este punto si los proyectos no cumplen el nuevo formato serán rechazados. Los que cumplen seguirán su tramite hasta concluir el semestre.";
            return;
        }


		// si existe ver Iteraciones
		$dets = $this->dbPilar->inLastTramDet( $tram->Id );

		// sumar 3 revisiones para correcciones
		if( $tram->Estado == 4 ) {
            $link = base_url( "repositor/docs/$dets->Archivo" );
            echo "Aqui puedes ver tu <b>proyecto</b> en Revisión: <a href='$link' target=_blank class='btn btn-warning'> Ver/Descargar Proyecto de Tesis </a><br>";
            // echo "<br><img class='img-responsive' src='http://vriunap.pe/vriadds/vri/web/convocatorias/comunicadoenero.png'</h4>";

			$this->load->view( "pilar/tes/proc/4_subcorr", array(
                            'sess'    => $sess,
			 		        'detTram' => $dets,
			 		        'arrCorr' => array(
			 				// enviamos un array organizado de correcciones
			 				1 => $this->dbPilar->inCorrecs( $tram->Id, 1 ),
			 				2 => $this->dbPilar->inCorrecs( $tram->Id, 2 ),
			 				3 => $this->dbPilar->inCorrecs( $tram->Id, 3 ),
                            4 => $this->dbPilar->inCorrecs( $tram->Id, 4 )
			 	    ) ) );

			return;
		}

        // mostrar acte de aprobación
        if( $tram->Estado >= 6 ) {
            $link2 = base_url( "repositor/docs/$dets->Archivo" );
			$link = base_url( "pilar/tesistas/actaProyIn" );
            $det = $this->dbPilar->inLastTramDet( $tram->Id );
            $dias = mlDiasTranscHoy( $det->Fecha );
            echo "<div class='text-center'>";
            echo "<center><img class='img-responsive' style='height:70px;' src='".base_url('vriadds/pilar/imag/pilar-tes.png')."'/> </center>";
			echo "<h1>Felicitaciones !</h1>";
            echo "<h4>Su Proyecto de Tesis ha sido <b class='text-success'>Aprobado</b></h4> Puede descargar su Acta de aprobación de Proyecto de Tesis. </h4>";
			echo "<hr> <a href='$link' target=_blank class='btn btn-info'> Ver/Descargar Acta </a>";
            echo " | <a href='$link2' target=_blank class='btn btn-success'> Ver Proyecto de Tesis </a> ";
            echo "</div>";
            return;
        }

        // ya existe como tramite
        switch ( $tram->Estado ) {
            case '1':
                echo "En espera de validación de formato";
                break;
            case '2':
                echo "En la bandeja del Director/Asesor";
                break;
            case '3':
                echo "Listo para sorteo diario";
                break;
            case '4':
                echo "En Revisión (E: $tram->Estado)";
                break;
            case '5':
				echo "En Dictaminación";
                break;
            case '6':
                break;
            default:
                break;
        }
    }

    public function tesBorrador()
    {
        $this->gensession->IsLoggedAccess();

        $sess = $this->gensession->GetSessionData();
        $tram = $this->dbPilar->inTramByTesista( $sess->userId );

        // no hay tramite disponble nuevo tramite
        if( $tram == null ) return;

        // Anuncio para tesistas sin activacion de tram Borr
        if( $tram->Tipo == 1 && $tram->Estado == 6 ) {

            // $det = $this->dbPilar->inLastTramDet( $tram->Id );
            $det = $this->dbPilar->inTramDetIter($tram->Id, 3);

            $dias  = mlDiasTranscHoy( $det->Fecha );
            // $dias  = mlDiasTranscHoy( $tram->FechModif );
            echo "<center><img class='img-responsive' style='height:70px;' src='".base_url('vriadds/pilar/imag/pilar-tes.png')."'/> </center>";
            echo "<center><h2 class='text'>¿Presentación de Borrador de Tesis?</h2>";
            echo "<h4> Su proyecto tiene $dias dia(s) de Ejecución de un total de 90 mínimos. </h4> </center>";
            echo "<p>Antes de continuar con el proceso usted deberá : (a) Completar el tiempo mínimo.(b) Poseer el grado académico de Bachiller. Si cumple con los requisitos (a) y (b) está apto para proseguir con su trámite, de lo contrario deberá esperar hasta cumplir lo estipulado. <br> <div class='alert alert-warning'><b>Nota :</b> La información registrada será responsabilidad del usuario y tienen caracter de <b>Declaración Jurada</b>, de lo contrario estará sujeto a las sanciones que determine la Universidad Nacional del Altiplano de Puno. </p></div>";

            $consulta=$this->dbPilar->getOneField('tesTramsBach',"Id","Estado=1 AND IdTesista=$sess->userId");

            if ($dias>=90 AND !$consulta) {
            echo "<center><br>
                    <a  class='btn btn-lg btn-success'
                        href='javascript:void(0)' 
                        onclick=\"lodPanel('panelTesis','tesistas/uploadBachiller')\" '>
                        <span class='glyphicon glyphicon-upload'></span>
                        Cargar Bachiller
                    </a>
                 </center";
            }
        }
        //-------------------------------------------------------------

        // ahora si los estados una vez activados
		$dets = $this->dbPilar->inLastTramDet( $tram->Id );

        // sustentado y ejemplar entregado
        if( $tram->Tipo == 3 ) {
			echo "<h4>Felicitaciones</h4> Su trámite ha concluido en la Plataforma PILAR del Vicerrectorado de Investigación.";
        }

        if( $tram->Tipo != 2 ) return;

        if( $tram->Estado == 10 ) {        
            $this->load->view( "pilar/tes/proc/0_regborr",array(
                'doc' => array(
                    1=>$this->dbRepo->inDocenteRow($tram->IdJurado1),
                    2=>$this->dbRepo->inDocenteRow($tram->IdJurado2),
                    3=>$this->dbRepo->inDocenteRow($tram->IdJurado3),
                    4=>$this->dbRepo->inDocenteRow($tram->IdJurado4),
                    ),
            ));
        }

        if( $tram->Estado == 11 ) {
              echo "<b>Revisión de Composición de Jurado :</b>";
                echo "<ul>";
                $count=0;
                $doc = array(
                    1=>$this->dbRepo->inDocenteRow($tram->IdJurado1),
                    2=>$this->dbRepo->inDocenteRow($tram->IdJurado2),
                    3=>$this->dbRepo->inDocenteRow($tram->IdJurado3),
                    4=>$this->dbRepo->inDocenteRow($tram->IdJurado4),
                );
                for ($i=1; $i <=4 ; $i++) { 
                    if($doc[$i]){
                        $status=($doc[$i]->Activo >= 5)?"(Docente Habilitado)":"(<b>OBSERVADO</b>Necesita Cambio)";
                        $kind=($doc[$i]->Activo >= 5)?"success":"danger";
                        echo "<li class='text-$kind'> $status | ".$doc[$i]->DatosPers ."  </li>";
                    }
                }
                echo "</ul>";

            echo "Borrador Subido a PILAR, a la espera de la validación de Formato y Composición de Jurados.";
        }

        if( $tram->Estado == 12 ) {

            // $tram->IdJurado1
            // $tram->IdJurado2
            // $tram->IdJurado3
            // $tram->IdJurado4
            // echo "$tram->IdJurado1 :: $tram->IdJurado2 :: $tram->IdJurado3 :: $tram->IdJurado4 ";
            // if()
            // echo "sI YA CImprimir 4 ejemplares y llevar a la coordinación de Investigación"

			$this->load->view( "pilar/tes/proc/12_subcorr", array(
                    'tram'    => $tram,
					'detTram' => $dets,
					'arrCorr' => array(
							// enviamos un array organizado de correcciones borrador
							//
							1 => $this->dbPilar->inCorrecs( $tram->Id, 1, 4 ),
							2 => $this->dbPilar->inCorrecs( $tram->Id, 2, 4 ),
							3 => $this->dbPilar->inCorrecs( $tram->Id, 3, 4 ),
							4 => $this->dbPilar->inCorrecs( $tram->Id, 4, 4 )
				) ) );
			return;
        }

        if( $tram->Estado >= 13 ) {
            $link = base_url( "repositor/docs/$dets->Archivo" );
            $link2 = base_url( "vriadds/vri/reglamentos/ReglamentoDefensaNP.pdf" );
            echo "<center><img class='img-responsive' style='height:70px;' src='".base_url('vriadds/pilar/imag/pilar-tes.png')."'/> </center>";
            echo "<center><h2 class='text'>Exposición y Defensa de Tesis</h2>";
            echo "<h4> Usted ha cargado el documento Final del Trabajo de Investigación.</h4></center> ";
            echo "<br><p> Si prefiere puede solicitar la Exposición y Defensa <b>NO PRESENCIAL</b> para lo cual deberá seguir los procedimientos descritos en el <i>REGLAMENTO DEL PROCESO DE EXPOSICIÓN Y DEFENSA DE LA TESIS EN FORMA NO PRESENCIAL, COMO UNO DE LOS REQUISITOS A CUMPLIR DURANTE EL PROCESO DE OBTENCIÓN DEL RESPECTIVO TÍTULO PROFESIONAL </i>. Para lo cual deberá preparar sus diapositivas y cargar este documento al Repositorio institucional. </p> ";
            echo "<center>
                    <a href='$link' target=_blank class='btn btn-success'> Ver el Borrador Final</a> 
                    <a href='$link2' target=_blank class='btn btn-info'> Ver el Reglamento</a> 
                  <center>";

        }



    }

    public function sorry()
    {
        echo "...";
    }


    public function loadRegBorr()
    {
        $this->gensession->IsLoggedAccess();
        $this->load->view( "pilar/tes/regBorr" );
    }


	public function actaProyIn()
	{
		$this->gensession->IsLoggedAccess();
		$sess = $this->gensession->GetData();
		$tram = $this->dbPilar->inTramByTesista( $sess->userId );
		$this->actaProy( $tram->Id );
	}

    public function constanciaSorteokkkkk( $idTram=0 )
    {
        // libre nada de sesiones
        if( !$idTram ) return;

        $tram = $this->dbPilar->inProyTram($idTram);
        if( !$tram ){ echo "Inexistente"; return;}
        if( $tram->Estado >=4 ){ 
            $dets = $this->dbPilar->inLastTramDet($idTram);
            $pdf = new GenSexPdf();

            //$pdf->AddPage();
            $pdf->AddPageEx( 'P', '', 2 );
            $pdf->SetMargins( 18, 40, 20 );

            $pdf->Ln( 25 );
            $pdf->SetFont( "Times", 'B', 15 );

            $pdf->Cell( 2,  9, "" );
            $pdf->Cell( 28, 9, $tram->Codigo, 1, 0, 'C' );
            $pdf->BarCode39( 150, 34, $tram->Codigo );
            mlQrRotulo( $pdf, 19, 220, $tram->Codigo );



            $pdf->Ln( 19 );
            $pdf->SetFont( "Arial", 'B', 14 );
            $pdf->Cell( 174, 5, toUTF("CONSTANCIA"), 0, 1, 'C' );


            $dia = (int) substr( $dets->Fecha, 8, 2 );
            $mes = mlNombreMes( substr($dets->Fecha,5,2) );
            $ano = (int) substr( $dets->Fecha, 0, 4 );
            $hor = substr( $dets->Fecha, 11, 8 );

            $jurado4 = $this->dbRepo->inDocenteEx( $tram->IdJurado4 );
            $tes = $this->dbPilar->inTesista($tram->IdTesista1, true);
            if( $tram->IdTesista2 != null ){
                $str = "Presentado por los Bachilleres:";
                $tes = $tes .",". $this->dbPilar->inTesista($tram->IdTesista2, true);
                // revisa modo de aprobacion
                $strConst = "La presente es la contancia que los tesistas: $tes respectivamente. Han iniciado de forma grupal el trámite electrónico "
                      . "para la presentación y revisión de su Proyecto de Tesis en la Plataforma de Investigación. Este proyecto ha sido aprobado por el director de tesis  $jurado4 y se realizó la asignación de jurados correspondiente con fecha $tram->FechModif, el mismo que se encuentra en revisión.";
            }
            $strConst = "La presente es la contancia que: $tes  ha iniciado el trámite electrónico para la presentación "
                      . "y revisión de su Proyecto de Tesis en la Plataforma de Investigación. Este proyecto ha sido aprobado por el director de tesis  $jurado4 y se realizó la asignación de jurados correspondiente  con fecha $tram->FechModif, el mismo que se encuentra en revisión."
                      . ""
                      ;
        
            $pdf->Ln(5);
            $pdf->SetFont( "Arial", "", 12 );
            $pdf->MultiCell( 174, 5.5, toUTF($strConst), 0, 'J' );

            $pdf->Ln(8);
            $pdf->SetFont( "Arial", "B", 11 );
            $pdf->MultiCell( 174, 5.5, toUTF("Puno, $mes de $ano"), 0, 'R' );

            $pdf->Image( 'vriadds/pilar/imag/aprofirma.jpg', 75, 230, 80 );

            $pdf->Output();
        }else{
            echo "No puede tener constancia";
        }
    }

    // acta borr
    public function actaBorr( $idTram=0 )
    {
        // libre nada de sesiones
        if( !$idTram ) return;

        $tram = $this->dbPilar->inProyTram($idTram);
        if( !$tram ){ echo "Inexistente"; return;}
        if( $tram->Estado < 6 ){ echo "No Aprobado"; return;}

		// Borr iteracion N :: la ultima.
		//
        $dets = $this->dbPilar->inLastTramDet($idTram);

        $pdf = new GenSexPdf();

        //$pdf->AddPage();
        $pdf->AddPageEx( 'P', '', 2 );
        $pdf->SetMargins( 18, 40, 20 );

        $pdf->Ln( 25 );
        $pdf->SetFont( "Times", 'B', 15 );

        $pdf->Cell( 2,  9, "" );
        $pdf->Cell( 28, 9, $tram->Codigo, 1, 0, 'C' );
        $pdf->BarCode39( 150, 34, $tram->Codigo );
        mlQrRotulo( $pdf, 19, 220, $tram->Codigo );



        $pdf->Ln( 19 );
        $pdf->SetFont( "Arial", 'B', 14 );
        //$pdf->Cell( 174, 5, toUTF("ACTA  DE BORRADOR DE TESIS (_DEVELOP_)"), 0, 1, 'C' );
        $pdf->Cell( 174, 5, toUTF("ACTA DE APROBACIÓN BORRADOR DE TESIS"), 0, 1, 'C' );


        $dia = (int) substr( $dets->Fecha, 8, 2 );
        $mes = mlNombreMes( substr($dets->Fecha,5,2) );
        $ano = (int) substr( $dets->Fecha, 0, 4 );
        $hor = substr( $dets->Fecha, 11, 8 );


        // revisa modo de aprobacion
        //
        $modo = (($dets->vb1+$dets->vb2+$dets->vb3)==3)?"UNANIMIDAD":"MAYORIA";
        if( ($dets->vb1 + $dets->vb2 + $dets->vb3) <= 1 )
            $modo = "REGLAMENTO";


        $str = "En la Ciudad Universitaria, a los $dia dias del mes $mes del $ano "
             . "siendo horas $hor. Se presentó el Borrador de tesis titulado:";

        $pdf->Ln( 7 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF($str) );


        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", 'B', 10 );
        $pdf->MultiCell( 174, 6, toUTF($dets->Titulo), 1, 'C' );

        $str = "Presentado por el(la) Bachiller:";
        $tes = $this->dbPilar->inTesista($tram->IdTesista1, true);
        if( $tram->IdTesista2 ){
            $str = "Presentado por los Bachilleres:";
            $tes = $tes ."\n". $this->dbPilar->inTesista($tram->IdTesista2, true);
        }

        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF($str) );

        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", 'B', 10 );
        $pdf->MultiCell( 174, 6, toUTF($tes), 1, 'C' );

		// carrera
        $pdf->Ln(4);
        $pdf->SetFont( "Arial", "", 10 );
        $pdf->MultiCell( 174, 5, toUTF("De la Escuela Profesional de:"), 0, 'L' );

		$Carrera = $this->dbRepo->inCarrera($tram->IdCarrera);

        $pdf->Ln(4);
        $pdf->SetFont( "Arial", "B", 10 );
        $pdf->MultiCell( 174, 5, toUTF($Carrera), 1, 'C' );


        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF("Siendo el Jurado Dictaminador, conformado por:") );


        $jurado1 = $this->dbRepo->inDocenteEx( $tram->IdJurado1 );
        $jurado2 = $this->dbRepo->inDocenteEx( $tram->IdJurado2 );
        $jurado3 = $this->dbRepo->inDocenteEx( $tram->IdJurado3 );
        $jurado4 = $this->dbRepo->inDocenteEx( $tram->IdJurado4 );

        $pdf->Ln(4);
        $pdf->Cell( 50, 6, "Presidente", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado1), 0, 1, "L" );

        $pdf->Cell( 50, 6, "Primer Miembro", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado2), 0, 1, "L" );

        $pdf->Cell( 50, 6, "Segundo Miembro", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado3), 0, 1, "L" );

        $pdf->Cell( 50, 6, "Director/Asesor", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado4), 0, 1, "L" );


        $strBloq = "Para dar fe de este proceso electrónico, el Vicerrectorado de Investigación de la Universidad "
                 . "Nacional del Altiplano - Puno, mediante la Plataforma de Investigación se le asigna la presente "
                 . "constancia y a partir de la presente fecha queda expedito para la ejecución de su PROYECTO DE INVESTIGACIÓN DE TESIS.";

        $pdf->Ln(5);
        $pdf->SetFont( "Arial", "", 10 );
        $pdf->MultiCell( 174, 5.5, toUTF($strBloq), 0, 'J' );

        $pdf->Ln(8);
        $pdf->SetFont( "Arial", "B", 11 );
        $pdf->MultiCell( 174, 5.5, toUTF("Puno, $mes de $ano"), 0, 'R' );

        $pdf->Image( 'vriadds/pilar/imag/aprofirma.jpg', 75, 230, 80 );

        $pdf->Output();
    }


    // ver acta proy
    public function actaProy( $idTram=0 )
    {
        // libre nada de sesiones
        if( !$idTram ) return;

        $tram = $this->dbPilar->inProyTram($idTram);
        if( !$tram ){ echo "Inexistente"; return;}
        if( $tram->Estado < 6 ){ echo "No Aprobado"; return;}

		// ACTA iteracion 3 :: no la ultima.
		//

        $dets = $this->dbPilar->inTramDetIter($idTram, 3);

        // ni se te ocurra cambiarlo, por la fecha en la iteracion 3

        $pdf = new GenSexPdf();

        //$pdf->AddPage();
        $pdf->AddPageEx( 'P', '', 2 );
        $pdf->SetMargins( 18, 40, 20 );

        $pdf->Ln( 25 );
        $pdf->SetFont( "Times", 'B', 15 );

        $pdf->Cell( 2,  9, "" );
        $pdf->Cell( 28, 9, $tram->Codigo, 1, 0, 'C' );
        $pdf->BarCode39( 150, 34, $tram->Codigo );
        mlQrRotulo( $pdf, 19, 220, $tram->Codigo );



        $pdf->Ln( 19 );
        $pdf->SetFont( "Arial", 'B', 14 );
        $pdf->Cell( 174, 5, toUTF("ACTA DE APROBACIÓN DE PROYECTO DE TESIS"), 0, 1, 'C' );


        $dia = (int) substr( $dets->Fecha, 8, 2 );
        $mes = mlNombreMes( substr($dets->Fecha,5,2) );
        $ano = (int) substr( $dets->Fecha, 0, 4 );
        $hor = substr( $dets->Fecha, 11, 8 );


        // revisa modo de aprobacion
        //
        $modo = (($dets->vb1+$dets->vb2+$dets->vb3)==3)?"UNANIMIDAD":"MAYORIA";
        if( ($dets->vb1 + $dets->vb2 + $dets->vb3) <= 1 )
            $modo = "REGLAMENTO";


        $str = "En la Ciudad Universitaria, a los $dia dias del mes $mes del $ano "
             . "siendo horas $hor. Los miembros del Jurado, declaran APROBADO POR $modo "
             . "el PROYECTO DE INVESTIGACIÓN DE TESIS titulado:";

        $pdf->Ln( 7 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF($str) );


        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", 'B', 10 );
        $pdf->MultiCell( 174, 6, toUTF($dets->Titulo), 1, 'C' );

        $str = "Presentado por el(la) Bachiller:";
        $tes = $this->dbPilar->inTesista($tram->IdTesista1, true);
        if( $tram->IdTesista2 ){
            $str = "Presentado por los Bachilleres:";
            $tes = $tes ."\n". $this->dbPilar->inTesista($tram->IdTesista2, true);
        }

        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF($str) );

        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", 'B', 10 );
        $pdf->MultiCell( 174, 6, toUTF($tes), 1, 'C' );

		// carrera
        $pdf->Ln(4);
        $pdf->SetFont( "Arial", "", 10 );
        $pdf->MultiCell( 174, 5, toUTF("De la Escuela Profesional de:"), 0, 'L' );

		$Carrera = $this->dbRepo->inCarrera($tram->IdCarrera);

        $pdf->Ln(4);
        $pdf->SetFont( "Arial", "B", 10 );
        $pdf->MultiCell( 174, 5, toUTF($Carrera), 1, 'C' );


        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF("Siendo el Jurado Dictaminador, conformado por:") );


        $jurado1 = $this->dbRepo->inDocenteEx( $tram->IdJurado1 );
        $jurado2 = $this->dbRepo->inDocenteEx( $tram->IdJurado2 );
        $jurado3 = $this->dbRepo->inDocenteEx( $tram->IdJurado3 );
        $jurado4 = $this->dbRepo->inDocenteEx( $tram->IdJurado4 );

        $pdf->Ln(4);
        $pdf->Cell( 50, 6, "Presidente", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado1), 0, 1, "L" );

        $pdf->Cell( 50, 6, "Primer Miembro", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado2), 0, 1, "L" );

        $pdf->Cell( 50, 6, "Segundo Miembro", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado3), 0, 1, "L" );

        $pdf->Cell( 50, 6, "Director/Asesor", 0, 0, "L" );
        $pdf->Cell( 100, 6, ": " .toUTF($jurado4), 0, 1, "L" );


        $strBloq = "Para dar fe de este proceso electrónico, el Vicerrectorado de Investigación de la Universidad "
                 . "Nacional del Altiplano - Puno, mediante la Plataforma de Investigación se le asigna la presente "
                 . "constancia y a partir de la presente fecha queda expedito para la ejecución de su PROYECTO DE INVESTIGACIÓN DE TESIS.";

        $pdf->Ln(5);
        $pdf->SetFont( "Arial", "", 10 );
        $pdf->MultiCell( 174, 5.5, toUTF($strBloq), 0, 'J' );

        $pdf->Ln(8);
        $pdf->SetFont( "Arial", "B", 11 );
        $pdf->MultiCell( 174, 5.5, toUTF("Puno, $mes de $ano"), 0, 'R' );

        $pdf->Image( 'vriadds/pilar/imag/aprofirma.jpg', 75, 230, 80 );

        $pdf->Output();
    }



    // get aware with reescribe data...
    //
    public function execInBorr()
    {
        $this->gensession->IsLoggedAccess();

        $sess  = $this->gensession->GetData();


        // si falla al subir Borrador termina
        $archi = $this->subirArchevo( 2 );
        if( ! $archi ) return;


        //$archi = mlSecurePost( "nomarch" );  siempre es NULL arriba lo asignaremos
        $resum = mlSecurePost( "resumen" );
        $clave = mlSecurePost( "pclaves" );
        $concl = mlSecurePost( "conclus" );
        $titul = mb_strtoupper( mlSecurePost( "nomproy" ) );



        // 1. verificar previo
        // 2. insertar Tramite
        // 3. insertar detTramite
        // 4. insertar TramiteDoc  mining
        // 5. log de tramites
        // 6. enviar correo...
        // 7. log de correos

        $tram = $this->dbPilar->inTramByTesista($sess->userId);


        // check if we had a prevoius activation
        if( $tram->Tipo == 2 && $tram->Estado >= 12 ) return;

        ///echo  "t:$titul  r:$resum   k:$clave  c:$concl <br>";
        ///return;


        $this->dbPilar->Update( 'tesTramites', array(
                'Tipo'   => 2,
                'Estado' => 11,
                'FechModif' => mlCurrentDate()
            ), $tram->Id );


        $this->dbPilar->Insert( 'tesTramsDet', array(
            'Iteracion' => 4,
            'IdTramite' => $tram->Id,
            'Archivo'   => $archi,
            'Titulo'    => $titul,
            'Fecha'     => mlCurrentDate()
        ));

        // para mineria de datos
        $this->dbPilar->Insert( 'tesTramDoc', array(
            'Tipo'      => 2,
            'IdTramite' => $tram->Id,
            'Title'     => $titul,
            'Abstract'  => $resum,
            'Conclus'   => $concl,
            'Keywords'  => $clave
        ));


        $msg = "<br>Se ha actualizado el trámite: <b>$tram->Codigo</b><br><br> "
             . "Título de Borrador de Tesis: <b>$titul</b> <br><br>       "
             . "Ud. debe apersonarse a Plataforma para revisar el formato "
             . "y la conformación de su <b>Jurado Evaluador</b> "
             . "de lo contrario no se procede con el envio para "
             . "que el tramite de su borrador continue."  ;


        // agregar tramite
        $this->logTramites( $sess->userId, $tram->Id, "Subida de Borrador", $msg );

        // grabar y enviar en LOG de correos.
        $this->logCorreo( $tram->Id, $sess->userMail, "Subida de Borrador", $msg );

        // finalmente
        echo $msg . "<br><b>hecho !</b>";
    }


    //
    // real carga proyecto de tesis
    //
    public function loadRegProy( $extCod=0 )
    {
        $this->gensession->IsLoggedAccess();
        $sess = $this->gensession->GetData();

        // buscamos los records de cada uno
        $tes1 = $this->dbPilar->inTesistByCod( $sess->userCod );
        $tes2 = $this->dbPilar->inTesistByCod( secureString($extCod) );

        // antes era:
        // SELECT abc
        //   union
        // SELECT bcd


        $errorMsg = null;

        if( $extCod && !$tes2 )
            { $errorMsg = "el $extCod no esta registrado aún"; }

        if( $tes2 ) if( $tes1->IdCarrera != $tes2->IdCarrera )
            { $errorMsg = "No son de la misma carrera"; $tes2 = null; }

        if( $sess->userCod == $extCod )
            { $errorMsg = "No debe repetir su Código"; $tes2 = null;  }


		// hack para E. Inicial
		$carre = $sess->IdCarrera;
		if( $sess->IdCarrera == 19 )
			$carre = 18;

        $args = array(
            // 'tlineas' => $this->dbPilar->getSnapView( 'vxLineas', "IdCarrera=$carre" ),
                'tlineas' => $this->dbRepo->getTable("tblLineas","IdCarrera='$carre' AND Estado = '1'"),
                'tbltes' => (!$tes2)?array($tes1):array($tes1,$tes2),
                'errmsg' => $errorMsg,
            );

        // no hiddens : sessVars sí
        mlSetGlobalVar( 'datTest', array(
                'user1' => ($tes1)? $tes1->Id : 0,
                'user2' => ($tes2)? $tes2->Id : 0,
                'carre' => $tes1->IdCarrera
            ) );

        $this->load->view( "pilar/tes/regProy", $args );
    }


    // procedimiento externo Upload
    public function subirArchevo( $tipo=1, $kind="pdf" )
    {
        $sess = $this->gensession->GetData();

        if( $kind=="pdf" ){
            $config['upload_path']   = './repositor/docs/';
        } else {
            $config['upload_path']   = './repositor/foto/';
        }

        // generamos el nombre Aleatorio: 5 Caracteres - Aleatorizados + 3 DNI
        //$str = mlRandomStr(12);

        $config['allowed_types'] = 'jpg|png|pdf';  // ext
        $config['max_size']      = '6144';         // KB
        $config['overwrite']     = TRUE;

        $config['file_name']     = sprintf("d%08s-Proy.pdf", $sess->userId );
        //$config['max_width']  = '2024';
        //$config['max_height'] = '2008';


        if( $tipo == 2 )
            $config['file_name']     = sprintf("d%08s-Borr.pdf", $sess->userId );
        // Carga de Bachiller como requisito.
        if( $tipo == 3 ){
            $config['upload_path']   = './repositor/bach/';
            $config['file_name']     = sprintf("d%08s-Bach.pdf", $sess->userId );
        }
        // Carga Correcciones de Borrador
        if( $tipo == 4 )
            $config['file_name']     = sprintf("d%08s-Final.pdf", $sess->userId );

        // Carga Correcciones de Borrador
        if( $tipo == 5 )
            $config['file_name']     = sprintf("d%08s-Diapo.pdf", $sess->userId );

        // finalmente subir archivo
        $this->load->library('upload', $config);
        if ( !$this->upload->do_upload("nomarch") ) { // input field

            $data['uploadError'] = $this->upload->display_errors();
            echo "Error: " . $this->upload->display_errors();
            return null;

        } else {
            $file_info = $this->upload->data();
            echo "Archivo Subido <br>";
        }

        // devolvemos el nombre del archivo
        return  $config['file_name'];
    }

    public function execInProy()
    {
        $this->gensession->IsLoggedAccess();


        //
        // AÑO LECTIVO
        //
        $anio = ANIO_PILAR;


        $sess  = $this->gensession->GetData();
        $users = mlGetGlobalVar( 'datTest' );


        // si falla al subir termina
        $archi = $this->subirArchevo( 1 );
        if( ! $archi ) return;


        // buscamos ultimo registro de tramite del año y procedemos
        $orden = $this->dbPilar->getOneField( "tesTramites", "Orden", "Anio=$anio ORDER BY Orden DESC" );
        $codigo = sprintf("%04d-%03d", $anio, $orden + 1 );


        $tesi1 = $users['user1'];
        $tesi2 = $users['user2'];
        $carre = $users['carre'];

        $linea = mlSecurePost( "cbolin" );
        $jura4 = mlSecurePost( "jurado4" );
        //$jura3 = mlSecurePost( "jurado3" );
        //$archi = mlSecurePost( "nomarch" );  siempre es NULL arriba lo asignaremos
        $resum = mlSecurePost( "resumen" );
        $clave = mlSecurePost( "pclaves" );
        $titul = mb_strtoupper( mlSecurePost( "nomproy" ) );


        // 1. verificar previo
        // 2. insertar Tramite
        // 3. insertar detTramite
        // 4. insertar TramiteDoc  mining
        // 5. log de tramites
        // 6. enviar correo...
        // 7. log de correos


        // el control de 0 el model devuelve null
        if( $rwtes = $this->dbPilar->inTramByTesista($tesi1) )
        {
            echo "Error : El primer tesista, ya integra el trámite: <b>$rwtes->Codigo</b>";
            return;
        }

        // el control de 0 el model devuelve null
        if( $rwtes = $this->dbPilar->inTramByTesista($tesi2) )
        {
            echo "Error : El segundo tesista, ya integra el trámite: <b>$rwtes->Codigo</b>";
            return;
        }


        // guardar trámite
        $idTram = $this->dbPilar->Insert( 'tesTramites', array(
            'Tipo'       => 1,        // Proys
            'Estado'     => 1,        // Inciamos
            'Anio'       => $anio,    // lectivo
            'Orden'      => $orden+1,
            'Codigo'     => $codigo,
            'IdCarrera'  => $carre,
            'IdTesista1' => $tesi1,
            'IdTesista2' => $tesi2,
            'IdLinea'    => $linea,
            'IdLinAlte'  => 0,
            //'IdJurado3'  => $jura3,
            'IdJurado4'  => $jura4,
            'FechRegProy' => mlCurrentDate(),
            'FechModif'   => mlCurrentDate()
        ));

        //$idTram = mysql_insert_id();
        $this->dbPilar->Insert( 'tesTramsDet', array(
            'Iteracion' => 1,
            'IdTramite' => $idTram,
            'Archivo'   => $archi,
            'Titulo'    => $titul,
            'Fecha'     => mlCurrentDate()
        ));

        // para mineria de datos
        $this->dbPilar->Insert( 'tesTramDoc', array(
            'Tipo'      => 1,
            'IdTramite' => $idTram,
            'Title'     => $titul,
            'Abstract'  => $resum,
            'Conclus'   => "",
            'Keywords'  => $clave
        ));


        //
        // $mail = $this->dbPilar->getOneField( 'tblTesistas', 'Correo', "Id=$tesi1" );
        //
        $msg = "<br>Se ha registrado el proyecto: <b>$codigo</b><br><br> "
             . "Título de Proyecto: <b>$titul</b> <br><br>"
             . "Ud. debe comunicarse con su Director/Asesor para "
             . "que su proyecto sea evaluado."  ;


        // agregar tramite
        $this->logTramites( $tesi1, $idTram, "Subida de Proyecto", $msg );

        // grabar y enviamos mail en LOG
        $this->logCorreo( $idTram, $sess->userMail, "Subida de Proyecto", $msg );

        // finalmente
        echo $msg . "<br><b>hecho !</b>";
    }


	// correcciones Proyecto
    public function execInCorr()
    {
        $this->gensession->IsLoggedAccess();

        $sess  = $this->gensession->GetData();


        // si falla al subir Borrador termina
        $archi = $this->subirArchevo( 1 );
        if( ! $archi ) return;


        //$archi = mlSecurePost( "nomarch" );  siempre es NULL arriba lo asignaremos
        $resum = mlSecurePost( "resumen" );
        $clave = mlSecurePost( "pclaves" );
        //$concl = mlSecurePost( "conclus" );
        $titul = mb_strtoupper( mlSecurePost( "nomproy" ) );



        // 1. verificar previo
        // 2. insertar Tramite
        // 3. insertar detTramite
        // 4. insertar TramiteDoc  mining
        // 5. log de tramites
        // 6. enviar correo...
        // 7. log de correos

        $tram = $this->dbPilar->inTramByTesista($sess->userId);


        // check if we had a prevoius activation
        if( $tram->Tipo == 1 && $tram->Estado >= 5 ) return;


        $this->dbPilar->Update( 'tesTramites', array(
                'Estado'    => 5,
                'FechModif' => mlCurrentDate()
            ), $tram->Id );


        $this->dbPilar->Insert( 'tesTramsDet', array(
            'Iteracion' => 2,
            'IdTramite' => $tram->Id,
            'Archivo'   => $archi,
            'Titulo'    => $titul,
            'Fecha'     => mlCurrentDate()
        ));

        // para mineria de datos
        $this->dbPilar->Insert( 'tesTramDoc', array(
            'Tipo'      => 1,
            'IdTramite' => $tram->Id,
            'Title'     => $titul,
            'Abstract'  => $resum,
            'Conclus'   => "*",
            'Keywords'  => $clave
        ));


        $msg = "<br>El tesista ha subido Correcciones en el trámite:<br><br>"
             . "Codigo: <b>$tram->Codigo</b><br> "
             . "Título de Proyecto : <b>$titul</b> <br><br>  "
             . "A partir de la fecha en un plazo de 5 días hábiles (sin feriados) "
             . "se realizará la <b>Dictaminación del Jurado Evaluador</b>. "
             . "Se procede con el registro y envio de las notificaciones."  ;


        // agregar tramite
        $this->logTramites( $sess->userId, $tram->Id, "Subida de Corrección", $msg );

        // grabar y enviamos mail en LOG correos
        $this->logCorreo( $tram->Id, $sess->userMail, "Subida de Corrección", $msg );


		// enviar correos a profesores OJO
		/// $this->correoProfes($tram);
		$corr1 = $this->dbRepo->inCorreo( $tram->IdJurado1 );
		$corr2 = $this->dbRepo->inCorreo( $tram->IdJurado2 );
		$corr3 = $this->dbRepo->inCorreo( $tram->IdJurado3 );
		$corr4 = $this->dbRepo->inCorreo( $tram->IdJurado4 );

		$this->logCorreo( $tram->Id, $corr1, "Dictaminación de Proyecto de Tesis", $msg );
		$this->logCorreo( $tram->Id, $corr2, "Dictaminación de Proyecto de Tesis", $msg );
		$this->logCorreo( $tram->Id, $corr3, "Dictaminación de Proyecto de Tesis", $msg );
		$this->logCorreo( $tram->Id, $corr4, "Dictaminación de Proyecto de Tesis", $msg );

        // finalmente
        echo $msg . "<br><b>hecho !</b>";
    }

    // Correcciones Borrador
    public function execInCorrBorr()
    {
        $this->gensession->IsLoggedAccess();

        $sess  = $this->gensession->GetData();


        // si falla al subir Borrador termina
        $archi = $this->subirArchevo( 4 );
        if( ! $archi ) return;

        //$archi = mlSecurePost( "nomarch" );  siempre es NULL arriba lo asignaremos
        $resum = mlSecurePost( "resumen" );
        $clave = mlSecurePost( "pclaves" );
        $concl = mlSecurePost( "conclus" );
        $titul = mb_strtoupper( mlSecurePost( "nomproy" ) );

        // 1. verificar previo
        // 2. insertar Tramite
        // 3. insertar detTramite
        // 4. insertar TramiteDoc  mining
        // 5. log de tramites
        // 6. enviar correo...
        // 7. log de correos

        $tram = $this->dbPilar->inTramByTesista($sess->userId);


        // check if we had a prevoius activation
        if( $tram->Tipo != 2 && $tram->Estado >= 13 ) return;


        $this->dbPilar->Update( 'tesTramites', array(
                'Estado'    => 13,
                'FechModif' => mlCurrentDate()
            ), $tram->Id );


        // $this->dbPilar->Insert( 'tesTramsDet', array(
        //     'Iteracion' => 5,
        //     'IdTramite' => $tram->Id,
        //     'Archivo'   => $archi,
        //     'Titulo'    => $titul,
        //     'Fecha'     => mlCurrentDate()
        // ));

        // para mineria de datos
        $this->dbPilar->Insert( 'tesTramDoc', array(
            'Tipo'      => 3,
            'IdTramite' => $tram->Id,
            'Title'     => $titul,
            'Abstract'  => $resum,
            'Conclus'   => $concl,
            'Keywords'  => $clave
        ));


        $msg = "<br>El tesista ha subido el Borrador Final en el trámite:<br><br>"
             . "Codigo: <b>$tram->Codigo</b><br> "
             . "Título de Proyecto : <b>$titul</b> <br><br>  "
             . "Se realizará la verificación de este proceso con el <b>Jurado Evaluador</b> y el <b>Repositorio Institucional</b>. "
             . "Se procede con el registro y envio de las notificaciones."  ;


        // agregar tramite
        $this->logTramites( $sess->userId, $tram->Id, "Subida de Borrador Final", $msg );

        // grabar y enviamos mail en LOG correos
        $this->logCorreo( $tram->Id, $sess->userMail, "Subida de Borrador Final", $msg );


        // enviar correos a profesores OJO
        /// $this->correoProfes($tram);
        $corr1 = $this->dbRepo->inCorreo( $tram->IdJurado1 );
        $corr2 = $this->dbRepo->inCorreo( $tram->IdJurado2 );
        $corr3 = $this->dbRepo->inCorreo( $tram->IdJurado3 );
        $corr4 = $this->dbRepo->inCorreo( $tram->IdJurado4 );

        $this->logCorreo( $tram->Id, $corr1, "Borrador Final ", $msg );
        $this->logCorreo( $tram->Id, $corr2, "Borrador Final", $msg );
        $this->logCorreo( $tram->Id, $corr3, "Borrador Final", $msg );
        $this->logCorreo( $tram->Id, $corr4, "Borrador Final", $msg );

        // finalmente
        echo $msg . "<br><b>hecho !</b>";
    }


	function correoProfes( $tram )
	{
		//
		// $tram = $this->dbPilar->inTramByTesista(1);
		//
		//$this->gensession->IsLoggedAccess();

		$correo1 = $this->dbRepo->inCorreo( $tram->IdJurado1 );
		$correo2 = $this->dbRepo->inCorreo( $tram->IdJurado2 );
		$correo3 = $this->dbRepo->inCorreo( $tram->IdJurado3 );
		$correo4 = $this->dbRepo->inCorreo( $tram->IdJurado4 );

        $msg = "<br>Se ha actualizado el trámite: <b>$tram->Codigo</b><br><br> "
             . "Título de Proyecto de Tesis: <b>$titul</b> <br><br> "
             . "A partir de la fecha en un plazo de 5 días hábiles (sin feriados) "
             . "Ud. podrá realizar la Dictaminación del <b>Proyecto de Tesis</b>. "
             . "Una vez que se ha procedido con realizar el envio de las notificaciones.";


        // grabar y enviamos mail en LOG correos
		//
        $this->logCorreo( $tram->Id, $correo1, "Subida de Corrección", $msg );
		$this->logCorreo( $tram->Id, $correo2, "Subida de Corrección", $msg );
		$this->logCorreo( $tram->Id, $correo3, "Subida de Corrección", $msg );
		$this->logCorreo( $tram->Id, $correo4, "Subida de Corrección", $msg );
	}



    //
    // argumentos por URL : Combo Lineas Docentes
    //
    public function loadLinCbo( $tipjur, $linea )
    {
        $this->gensession->IsLoggedAccess();
        $sess = $this->gensession->GetData();

        // nombre BD estricto
        $dbrep = "vriunap_absmain";
        $dbpil = "vriunap_pilar3";

        // first item
        echo "<option value='' disabled selected> seleccione </option>";

        // aliementamos los nombrados
        if( $tipjur == 4 )
        {
            // ojo tiene que ser vwJurados no repositorio
            // autoridades elegibles
            //  
			/*
            $table = $this->dbPilar->getQuery (
                "SELECT  * FROM  $dbpil.vxDocInLin
                  WHERE  IdCategoria <= '9'
                    AND  Activo >= 5
                    AND  IdLinea='$linea' ORDER BY DatosPers" );
			*/

            $table = $this->dbPilar->getSnapView(
						  "vxDocInLin",
						  "IdCategoria<='9' AND Activo>=5 AND IdLinea='$linea' AND IdCarrera=$sess->IdCarrera AND LinEstado = 2",
						  "ORDER BY DatosPers" );

            //
            // Rectores, Decanos NO pero si Directores
            //
            foreach( $table->result() as $row ) {
                echo "<option value=$row->IdDocente> $row->DatosPers </option>";
            }
        }

        if( $tipjur == 3 )
        {
			// PRONTO A QUITAR TBLAUTORIDADES

            // ojo tiene que ser vwJurados no repositorio
            //

			/*
            $table = $this->dbPilar->getQuery(
              "SELECT  * FROM  $dbpil.vxDocInLin
                WHERE  IdDocente NOT IN(SELECT IdDocente FROM $dbrep.tblAutoridades)
                  AND  Activo = 6
                  AND  IdLinea = $linea ORDER BY DatosPers" );
		  	*/
            $table = $this->dbPilar->getSnapView(
						  "vxDocInLin",
						  "Activo=6 AND IdLinea='$linea'",
						  "ORDER BY DatosPers" );


            // Rectores, Decanos NO pero si Directores
            //
            foreach( $table->result() as $row ){
                echo "<option value=$row->IdDocente> $row->DatosPers </option>";
            }
        }
    }


    //------------------------------------
    // external function area AJAX
    //------------------------------------

    
    //
    // grabar nuevo tesista verificado con OTI
    //
    public function execInNew()
    {
        // validacion de datos interna
        $data = mlGetGlobalVar( "proRec" );
        if( !$data ){
            echo "Sin acceso autorizado.";
            return;
        }

        // procedemos:  array : { } : json
        $data = json_decode( json_encode ($data) );


        //
        // Super Importante : registrar evitando duplicados
        //
        if( ! $this->dbPilar->getSnaprow( "tblTesistas", "Codigo='$data->Codigo'" ) ) {

            $pass = mlSecurePost("pass1");
            $mail = mlSecurePost("mail");

            // mb_strtoupper
            $myId = $this->dbPilar->Insert( 'tblTesistas', array(
                'Activo'     => 2, // desde ya
                'DNI'        => $data->DNI,
                'Codigo'     => $data->Codigo,
                'IdFacultad' => $data->IdFacu,
                'IdCarrera'  => $data->IdCarr,
                'IdEspec'    => $data->IdEspec,
                'SemReg'     => $data->SemReg,
                'FechaReg'   => mlCurrentDate(),
                'NroCelular' => mlSecurePost("celu"),
                'Direccion'  => mlSecurePost("dire"),
                'Correo'     => mlSecurePost("mail"),
                'Nombres'    => $data->Nombres,
                'Apellidos'  => $data->Apellis,
                'Clave'      => sqlPassword($pass)
            ));



            $msg = "<h3>Bienvenido</h3>"
                 . "Sr(rta): <b>$data->Nombres $data->Apellis</b>.<br>"
                 . "Ud. ha concluido satisfactoriamente su inscripción en la  "
                 . "Plataforma PILAR para el trámite electrónico de su "
                 . "proyecto y borrador de tesis, en calidad de estudiante "
                 . "egresado de la <b>UNA - Puno</b>."
                 . "<br><br><b>Datos de su Cuenta:</b><br>"
                 . "  * usuario: $mail<br>"
                 . "  * contraseña: $pass<br>"
                 . "<br><br>Gracias."
                 ;

            // grabar en LOG de correos y enviamos mail
            $this->logCorreo( $myId, $mail, "Inscripción", $msg );

            echo "Registro completo, revise su <b>e-mail</b>.";

        } else {

            echo "Se guardo previamente";
        }

        //print_r( $data );
        mlSetGlobalVar( "proRec", null );
    }


    //
    // verificacion con OTI, dni, semestre, carrera, session
    //
    public function jsBusqTes()
    {
        mlSetGlobalVar( "proRec", array() );

        // no logueado
        $codigo = mlSecurePost("cod");
        $numdni = mlSecurePost("dni");
        if( ! $codigo ) return;

        if( $row = $this->dbPilar->getSnapRow("tblTesistas","Codigo='$codigo'") ) {
            echo "<b>$row->Nombres</b> Ha sido registrado el <b>$row->FechaReg</b>";
            return;
        }

        $alumno = otiGetData($codigo);
        if( $alumno == null ) {
            echo "<b> Can't connect to: unap.edu.pe </b>";
            return;
        }

        if( $alumno->success == false )
        {
            echo "<b> Datos incompletos </b>";
            return;
        }

        // copiar datos y verificacion de DNI
        $data = $alumno->items[0];
        if( $data->documento_numero != $numdni ){
            echo "<b> Los datos no coinciden </b>";
            return;
        }


        // solo los semestres
        $arrSemes = array(
                   "OCTAVO", "NOVENO", "DECIMO",
                   "DECIMO PRIMERO", "DECIMO SEGUNDO",
                   "DECIMO TERCERO", "DECIMO CUARTO"
             );

        if( !in_array($data->matricula->semestre, $arrSemes) ) {
            echo "<b>Solo estudiantes de 2 últimos semestres</b> <br><small>"
               . "Ud. está en: " .$data->matricula->semestre. "</small>" ;
            return;
        }

        // codigo 15 no, 14 agosto no
        // if( $codigo >= 142000 && $codigo <= 700000 ){
        //     echo "Ley 30220-SUNEDU, Reglamento en desarrollo apersonese al VRI";
        //     return;
        // }

        // revisar carreras permitidas
        $carres = $this->dbRepo->getSnapRow( "dicCarreras", "Nombre = '$data->escuela'" );
        if( $carres == null ) {
            echo "Error.05 : Carrera no indexada";
            return;
        }

        // 20 Secundaria
        // 11 Arte
        // 29 fismat
        // 16 biologia

        $idEspec = 0;
        if( $carres->Id==20 or $carres->Id==11 or $carres->Id==29 or $carres->Id==16) {

            // buscar la especialidad y grabarla
            //---------------------------------------------------------
            $arrEsp = array(
                ""                                              => 0,
                "CARRERA PURA"                                  => 0,
                //------------------------------------------------------
                "CIENCIAS SOCIALES"                             => 1,
                "BIOLOGIA, FISICA, QUIMICA Y LABORATORIOS"      => 2,
                "BIOLOGIA, FISICA, QUIMICA Y LABORATORIO"       => 2,
                "LENGUA, LITERATURA, PSICOLOGIA Y FILOSOFIA"    => 3,
                "MATEMATICA E INFORMATICA"                      => 4,
                "MATEMATICA, COMPUTACION E INFORMATICA"         => 4,
                "MATEMATICA FISICA COMPUTACION E INFORMATICA"   => 4,
                //------------------------------------------------------
                "ARTES PLASTICAS"                               => 11,
                "MUSICA"                                        => 12,
                "DANZA"                                         => 13,
                "TEATRO"                                        => 14,
                //------------------------------------------------------
                "PESQUERIA"                           => 21,
                "ECOLOGIA"                            => 22,
                "MICROBIOLOGIA Y LABORATORIO CLINICO" => 23,
                //------------------------------------------------------
                "MENCION MATEMATICA"                  => 31,
                "MENCION FISICA"                      => 32

            );

            $idEspec = $arrEsp[ $data->matricula->especialidad ];
        }


        // mlSetGlobalVar( "proRec", Facultad, Carrera, DNI txt, Codigo, Datos )
        mlSetGlobalVar( "proRec", array(
                'IdCarr'  => $carres->Id,
                'IdFacu'  => $carres->IdFacultad,
                'IdEspec' => $idEspec,
                'SemReg'  => $data->matricula->anio."-".$data->matricula->periodo." (".$data->matricula->semestre.")",
                'DNI'     => $data->documento_numero,
                'Apellis' => $data->apellidos,
                'Nombres' => $data->nombres,
                'Codigo'  => $data->codigo
            ) );


        // finalmente a mostrar los datos
        $this->load->view( "pilar/tes/regNvoTes", array(
                'data' => $data  // Json in array
            ) );
    }

    function listarEsp( $esp=1 )
    {
        echo "<style> body{ padding: 50px; font-family: Arial; font-size: 14px } </style>";
        echo "<table cellPadding=5 cellSpacing=0 border=1 style='font-size: 12px' width=800px>";

        $carr = $this->dbRepo->getSnapRow( "vwLstCarreras", "IdEspec=$esp" );
        echo "E.P.: $carr->Carrera : $carr->Especialidad";

        $nro = 1;
        $tes = $this->dbPilar->getSnapView( "vxDatTesistas", "IdCarrera=20 AND IdEspec=$esp ORDER BY DatosPers" );
        foreach( $tes->result() as $row ) {
            echo "<tr>";
            echo "<td> $nro </td>";
            echo "<td> $row->Codigo </td>";
            echo "<td> $row->DatosPers </td>";
            echo "<td> $row->SemReg </td>";
            echo "<td> " .mlFechaNorm($row->FechaReg). " </td>";
            echo "</tr>";
            $nro++;
        }

        echo "</table>";
    }

    public function mails(){

        $this->gensession->IsLoggedAccess();

        $sess = $this->gensession->GetData();
        $tram = $this->dbPilar->inTramByTesista( $sess->userId ); // Tipo > 0

        // no hay tramite disponble nuevo tramite
        if( $sess ) {
            $mail=$this->dbPilar->getOneField('tblTesistas',"Correo","Id=$sess->userId");
            $prev = $this->dbPilar->getTable( "logCorreos", "Correo LIKE '$mail' ORDER BY Id DESC" );
            $this->load->view( "pilar/tes/proc/0_mails", ['prev'=>$prev] );

            //echo "<h3> Se  esta preparando un módulo de acuerdo al nuevo reglamento en debate el 20-03-18 en Consejo Universitario.</h3>";
            // echo "Desde este punto si los proyectos no cumplen el nuevo formato serán rechazados. Los que cumplen seguirán su tramite hasta concluir el semestre.";
            return;
        }

    }

    // Módulo complementario para realizar las sustentaciones en PILAR en el Periodo del COVID-19
    public function uploadBachiller(){
        $sess = $this->gensession->GetData();
        $tram = 
        $this->load->view('pilar/tes/proc/10_subbach',array('sess'=>$sess));
    }

    public function execInBachi(){
        $sess  = $this->gensession->GetSessionData();

        // si falla al subir termina
        $archi = $this->subirArchevo( 3 );
        if( ! $archi ) return;

        $rrec  = mlSecurePost( "rrec" );
        $dater = mlSecurePost( "dater" );
        $anio = mlSecurePost( "anio" );
        $tram=$this->dbPilar->inTramByTesista($sess->userId);


        $this->dbPilar->Insert( 'tesTramsBach', array(
            'Estado' => 1,//(1)Subido (2)Aprobado
            'IdTramite' => $tram->Id,
            'IdTesista' => $sess->userId,
            'IdCarrera' => $tram->IdCarrera,
            'NroRes'    => $rrec,
            'AnioRes'    => $anio,
            'DateRes'   => $dater,
            'File'   => $archi,
            'Obs'   => '-',            
        ));

        $msg = "El tesista ha cargado su bachiller en el trámite:<br>"
             . "Codigo: <b>$tram->Codigo </b><br> "
             . "Este proceso tiene caracter de declaración jurada bajo la responsabilidad del usuario de esta cuenta. Se habilitará la opción de cargar el borrador de tesis.<br>"
             . "Se procede con el registro y envio de las notificaciones."  ;

        if ($tram->IdTesista2==0) {
            $this->dbPilar->Update( 'tesTramites', array(
                'Tipo'    => 2,
                'Estado'    => 10,
                'FechModif' => mlCurrentDate(),
                'FechActBorr'=> mlCurrentDate()
            ), $tram->Id );
        }else{
            $querytes1=$this->dbPilar->getOneField('tesTramsBach',"Id","IdTesista=$tram->IdTesista1");
            $querytes2=$this->dbPilar->getOneField('tesTramsBach',"Id","IdTesista=$tram->IdTesista2");
            if ($querytes1 && $querytes2) {
                $this->dbPilar->Update( 'tesTramites', array(
                    'Tipo'    => 2,
                    'Estado'    => 10,
                    'FechModif' => mlCurrentDate(),
                    'FechActBorr'=> mlCurrentDate()
                ), $tram->Id );
            }else{
                $msg= $msg."<br><b>Nota : </b>Para completar la habilitación ambos tesistas deberán cargar su bachiler en PILAR.";
            }
        }

        // agregar tramite
        $this->logTramites( $sess->userId, $tram->Id, "Subida de Bachiller", $msg );

        // grabar y enviamos mail en LOG correos
        $this->logCorreo( $sess->userId, $sess->userMail, "Subida de Bachiller", $msg );

        echo $msg."<br> Hecho!";
    }

    // Módulo de carga final de Borrador de tesis
    public function vwSolictaSust(){
        $this->gensession->IsLoggedAccess();

        $sess = $this->gensession->GetData();
        $tram = $this->dbPilar->inTramByTesista( $sess->userId );
        $det = $this->dbPilar->inTramDetIter($tram->Id, 5);

        $solic=$this->dbPilar->getSnapRow("tesSustensSolic","IdTramite=$tram->Id");
        if( $solic ) {
            if ($solic->Estado==1) {
                # code...
                echo "<center><img class='img-responsive' style='height:70px;' src='".base_url('vriadds/pilar/imag/pilar-tes.png')."'/> </center>";
                echo "<center><h2 class='text'>Solicitud de Enviada </h2>";
            }

            // Si aún no se publicó 
            elseif ($solic->Estado==3) {
            
            $link = base_url("pilar/tesistas/actaDeliberacion/$tram->Id");

            echo "<div class='text-center'>";
            echo "<center><img class='img-responsive' style='height:70px;' src='".base_url('vriadds/pilar/imag/pilar-tes.png')."'/> </center>";
            echo "<h1>Felicitaciones !</h1>";
            echo "<h4>Su Trabajo de Investigación de Tesis ha sido <b class='text-success'>Aprobado</b></h4> Puede descargar su Acta de aprobación de Proyecto de Tesis. </h4>";
            echo "<hr> <a href='$link' target=_blank class='btn btn-info'> Ver/Descargar Acta </a>";
            echo "</div>";

            }
            elseif ($solic->Estado==2) {

                echo "<h4> Esperando el Dictamen del Jurado Evaluador .... </center> "; 
                echo "<br>Presidente      : <b> " .($det->vb1!=0? "Ok":"En Dictamen . . ."). "</b>";
                echo "<br>Primer Miembro  : <b> " .($det->vb2!=0? "Ok":"En Dictamen . . ."). "</b>";
                echo "<br>Segundo Miembro : <b> " .($det->vb3!=0? "Ok":"En Dictamen . . ."). "</b>";
                echo "<br>Director/Asesor : <b> " .($det->vb4!=0? "Ok":"En Dictamen . . ."). "</b>";

            }else{
                echo "<h4> Esperando la Verificación y Publicación de la Sustentación ";
            }
        }
        elseif ($tram->Tipo == 2 && $tram->Estado == 13 ) {
            $this->load->view('pilar/tes/proc/13_sustvirtual',array('sess'=>$sess));
        }
        else{
            echo "<br><br><center><h3>  Lo sentimos ! <br> Usted aún no cumple los requisitos para este proceso. </h3></center>";
        }
    }

    public function execSolSusten(){
        $sess  = $this->gensession->GetSessionData();

        // si falla al subir termina
        $archi = $this->subirArchevo( 5 );
        if( ! $archi ) return;

        $dated=mlSecurePost("dated");
        $dates=mlSecurePost("dates");
        $enlarepo=mlSecurePost("enlarepo");

        $tram=$this->dbPilar->inTramByTesista($sess->userId);

        $this->dbPilar->Insert( 'tesSustensSolic', array(
            'Estado' => 1,
            'IdTramite' => $tram->Id,
            'IdTesista' => $sess->userId,
            'IdCarrera' => $tram->IdCarrera,
            'UrlRepo'    => $enlarepo,
            'FechDic'    => $dated,
            'FechSusten'   => $dates,
            'FileDiapo'   => $archi,
            'DateSolic'   => mlCurrentDate(),
            'Obs'   => '-',            
        ));


        $msg = "Se ha registrado la solicitud de exposición y defensa no presencial con el trámite:<br>"
             . "Codigo: <b>$tram->Codigo </b><br> "
             . "Se notificará a la Unidad de Investigación, para verificación de la información y programación de la sustentación en el panel de PILAR.<br>"
             . "Se procede con el registro y envio de las notificaciones."  ;

        // Agregar log del Trámite
        $this->logTramites( $sess->userId, $tram->Id, "Solicitud No Presencial", $msg );

        // Grabar y enviamos mail en LOG correos
        $this->logCorreo( $sess->userId, $sess->userMail, "Solicitud No Presencial", $msg );
        

        echo $msg."<br> Hecho!";

    }



    public function actaDeliberacion( $idTram=0 )
    {
                
        if( !$idTram ) return;

        $tram = $this->dbPilar->inProyTram($idTram);
        if( !$tram ){ echo "Inexistente"; return;}
        if( $tram->Estado < 13 ){ echo "No Aprobado"; return;}

        $dets = $this->dbPilar->inTramDetIter($idTram, 5);
        if( !$dets ) return;
        // iteración 4 presenta borrador
        // iteración 5 sustenta
               
        $acta = $this->dbPilar->getSnapRow("tesSustenAct","IdTramite=$idTram");
        if( !$acta ) { echo "No hay Acta "; return;}

        $pdf = new GenSexPdf();

        //$pdf->AddPage();
        $pdf->AddPageEx( 'P', '', 2 );
        $pdf->SetMargins( 18, 40, 20 );

        $pdf->Ln( 25 );
        //$pdf->SetFont( "Times", 'B', 15 );


        //$pdf->Cell( 28, 9, $tram->Codigo, 1, 0, 'C' ); el código ya no va acá
        $pdf->BarCode39( 150, 34, $tram->Codigo );
        mlQrRotulo( $pdf, 19, 240, $tram->Codigo );


        $txtFacultadPerse=toUTF($this->dbRepo->inFacultad($tram->IdCarrera));
        $txtEscuelaPerse=toUTF($this->dbRepo->inCarrera($tram->IdCarrera));

        $txtFacultad="FACULTAD DE ".$txtFacultadPerse;
        $txtEscuela="ESCUELA PROFESIONAL DE".$txtEscuelaPerse;
        $pdf->Ln( 10 );
        $pdf->SetFont( "Arial", 'B', 11 );
        $pdf->Cell( 174, 5, toUTF(strtoupper($txtFacultad)), 0, 1, 'C' );
        $pdf->Cell( 174, 5, toUTF(strtoupper($txtEscuela)), 0, 1, 'C' );
        $pdf->Ln(5);


        // agregar ruta en la BD de la imagen

        // $codCarrera= $tram->IdCarrera;
        // $rutaEscudo=$this->dbRepo->getOneField("dicCarreras","RutaEscudo","Id=".$codCarrera); 

        // $pdf->Cell(70,40, "",0);
        // if ($rutaEscudo) {
        //     $pdf->Cell(46,40, $pdf->Image($rutaEscudo, $pdf->GetX(), $pdf->GetY(),30),0, 0,'R');
        // }
        $pdf->Cell(58,40, "",0);
        $pdf->Ln(5);
        
        


        $cadTitulo="ACTA DE EVALUACIÓN DE TESIS Nº ";
        // $acta="001"; // agregar función que lleve la cuenta de actas por escuela
        $pdf->Ln( 20 );
        $pdf->SetFont( "Arial", 'B', 14 );
        $pdf->Cell( 174, 5, toUTF($cadTitulo).$acta->Num, 0, 1, 'C' );


        $dia = (int) substr( $dets->Fecha, 8, 2 );
        $mes = mlNombreMes( substr($dets->Fecha,5,2) );
        $ano = (int) substr( $dets->Fecha, 0, 4 );
        $hor = substr( $dets->Fecha, 11, 8 );


        


        $str = "El jurado revisor ha calificado el trabajo de tesis titulado:";

        $pdf->Ln( 7 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF($str) );


        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", 'B', 10 );
        $pdf->MultiCell( 174, 6, toUTF($dets->Titulo), 0, 'C' );

        $strBachiller = "Presentado por el(la) Bachiller:";
        $tes = $this->dbPilar->inTesista($tram->IdTesista1, true);
        if( $tram->IdTesista2 != 0 ){
            $strBachiller= "Presentado por los Bachilleres:";
            $tes = $tes ."\n". $this->dbPilar->inTesista($tram->IdTesista2, true);
        }

        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->MultiCell( 174, 5, toUTF($strBachiller),0 );

        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", 'B', 10 );
        $pdf->MultiCell( 174, 6, toUTF($tes), 0, 'C' );

        
        
        $strCod=$this->dbPilar->getOneField("tblTesistas","Codigo","Id=".$tram->IdTesista1);
        $strCod1=$this->dbPilar->getOneField("tblTesistas","Codigo","Id=".$tram->IdTesista2);

        $strCodPY= $tram->Codigo;
        $strTexto = "Con código de matrícula ".$strCod;
        $strTextoCodPY = " y código de proyecto : ".$strCodPY;
        if( $tram->IdTesista2 != 0 ){
            $strTexto= $strTexto." y $strCod1 ";
        }

        $strCodPY= $tram->Codigo;
        $strTexto = "Con código de matrícula ".$strCod;
        $strTextoCodPY = " y código de proyecto : ".$strCodPY;
        //obtener código de matrícula
        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", '', 10 );
        $pdf->Cell( 174, 5, toUTF($strTexto.$strTextoCodPY),0);

        // carrera
        $txtCarrera = $this->dbRepo->inCarrera($tram->IdCarrera);
        $pdf->Ln(6);
        $pdf->SetFont( "Arial", "", 10 );
        $pdf->Cell( 50, 5, toUTF("de la Escuela Profesional de"),0, 'L' );
        $pdf->SetFont( "Arial", "B", 10 );
        $pdf->Cell( 40, 5, toUTF(": ".$txtCarrera), 0, '' );



        $asesor = $this->dbRepo->inDocenteEx( $tram->IdJurado4 );

        $pdf->Ln(6);
        $pdf->SetFont( "Arial", "", 10 );
        $pdf->Cell( 50, 5, "Director / asesor ", 0, 0, "L" );
        $pdf->Cell( 100, 5, ": " .toUTF($asesor), 0, 0, "L" );

                
        $resEvaluacion= "$acta->Obs"; // función de resultado de evaluación
       
        $codCarrera= $tram->IdCarrera;
        $denominacion=$this->dbRepo->getOneField("dicCarreras","Titulo","Id=".$codCarrera); 

        $pdf->Ln(6);
        $pdf->Cell( 60, 5, toUTF("Siendo el resultado de la evaluación"), 0, 0, "L" );
        $pdf->Cell( 80, 5, ": " .toUTF($resEvaluacion), 0, 0, "L" );


        $pdf->Ln(10);
        $pdf->Cell( 50, 5, "Por lo expuesto, el(la) bachiller ", 0, 0, "L" );
        $pdf->Cell( 100, 5, ": " .toUTF($tes), 0, 0, "L" );

        $pdf->Ln(6);
        $pdf->Cell( 83, 5, toUTF("queda expedito para recibir el Título Profesional de: "), 0, 0, "L" );
        $pdf->SetFont( "Arial", 'B', 10 );
        $pdf->Cell( 70, 5, toUTF($denominacion), 0, 0, "L" ); 
        
               

        $cadenaFe = "Para dar fe de ello, queda asentada la presente acta ";

        $pdf->Ln(10);
        $pdf->SetFont( "Arial", "", 10 );
        $pdf->Cell( 174, 5, toUTF($cadenaFe), 0, 'J' );

                 
        $pdf->Ln( 4 );
        $pdf->SetFont( "Arial", '', 10 );
                

        $jurado1 = $this->dbRepo->inDocenteEx( $tram->IdJurado1 );
        $jurado2 = $this->dbRepo->inDocenteEx( $tram->IdJurado2 );
        $jurado3 = $this->dbRepo->inDocenteEx( $tram->IdJurado3 );
        $jurado4 = $this->dbRepo->inDocenteEx( $tram->IdJurado4 );

        $pdf->Ln(4);
        $pdf->Cell( 50, 5, "Presidente", 0, 0, "L" );
        $pdf->Cell( 100, 5, ": " .toUTF($jurado1), 0, 1, "L" );

        $pdf->Cell( 50, 5, "Primer Miembro", 0, 0, "L" );
        $pdf->Cell( 100, 5, ": " .toUTF($jurado2), 0, 1, "L" );

        $pdf->Cell( 50, 5, "Segundo Miembro", 0, 0, "L" );
        $pdf->Cell( 100, 5, ": " .toUTF($jurado3), 0, 1, "L" );

        $pdf->Cell( 50, 5, "Director/Asesor", 0, 0, "L" );
        $pdf->Cell( 100, 5, ": " .toUTF($jurado4), 0, 1, "L" );
       
        
        $pdf->Ln(8);
        $pdf->SetFont( "Arial", "B", 11 );
        $pdf->MultiCell( 174, 5.5, toUTF("Puno, $mes de $ano"), 0, 'R' );


        $pdf->Output();
    }

    public function coo()
    {
        /*
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.1and1.com',
            'smtp_port' => 25,
            'smtp_user' => 'certificacion@gmail.com',
            'smtp_pass' => 'mapa4violeta',
            'charset' => 'utf-8',
            'priority' => 1
        );

        $this->load->helper('url');
        $this->load->library('email' );

        $this->email->initialize($config);
        $this->email->set_mailtype("html");
        $this->email->set_newline("\r\n");

        //Email content
        $htmlContent = '<h1>Sending email via SMTP server</h1>';
        $htmlContent .= '<p>This email has sent via SMTP server from CodeIgniter application.</p>';

        $this->email->to('rplm.mx@gmail.com');
        $this->email->from('certificacion@gmail.com','My email');
        $this->email->subject('How to send email via SMTP server in CodeIgniter');
        $this->email->message($htmlContent);

        //Send email
        $this->email->send();
        */



        /*
        //echo CI_VERSION;
        //exit;

        //$CI = & get_instance();

        $this->load->helper('url');
        $this->load->library('session');

        $this->config->item('base_url');
        $this->load->library('email' );

        //$this->email->initialize($config);
        /*
        $config['protocol'] = 'sendmail';
        $config['mailpath'] = '/usr/sbin/sendmail';
        $config['charset'] = 'iso-8859-1';
        $config['wordwrap'] = TRUE;
        $this->email->initialize($config);
        */

        /*
        $subject = 'Bienvenido a mi app';

        $msg = 'Mensaje de prueba xxx';

        $this->email
            ->from('certificacion@gmail.com')
            ->to($email)
            ->subject($subject)
            ->message($msg)
            ->send();
        */
    }    


    /*
    //  Mi tesis en UN PosTER
    public function vwInsqPoster()
    {
        $sess = $this->gensession->GetSessionData();
        $this->load->view("pilar/tes/poster/inscripcion",array('sess'=>$sess));
        // $this->load->view("pilar/tes/poster/fin");
    }
    
    public function execPostulaPoster()
    {
        $sess = $this->gensession->GetSessionData();

        $resum = mlSecurePost( "resumen" );
        $titulo = mlSecurePost( "titulo" );

        $tram=$this->dbPilar->inTramByTesista($sess->userId);
        $ord=$this->dbPilar->getOneField("2posTer","Ord","Id>0 ORDER BY Id DESC")+1;

        $codigo=sprintf("POS%03s", $ord );

        if(!$this->dbPilar->getSnaprow("2posTer","IdProyecto=$tram->Id"))
        {
            $this->dbPilar->Insert("2posTer", array(
                'IdProyecto'=>$tram->Id,
                'IdCarrera'=>$sess->IdCarrera,
                'Ord'=> $ord,
                'Codigo'=>$codigo,
                'Titulo'=>$titulo,
                'Resumen'=>$resum,
                // 'Poster'=>"$codigo.pptx",
                'Fecha'=>mlCurrentDate(),
            ));

            $msg= "<center><img  width='250px'src='http://vriunap.pe/vriadds/vri/web/logo_footer.png'></img></center><b>Postulación Aceptada</b><br><br>Estimado(a), Tesista Bienvenido al concurso MI PROYECTO DE TESIS EN UN POSTER.<br><br>Recuerde que en la
                presentación oral usted deberá presentar un poster teniendo en cuenta las pautas de la capacitación la cual se publicará en la página web del vicerrectorado de investigación. Usted puede verificar su inscripción en la web de la convocatoria : <a href='http://vriunap.pe/poster'><i> Ver Inscritos</i></a> ";
            $this->logCorreo( $tram->Id, $sess->userMail, "Inscripcion MI TESIS EN UN POSTER ", $msg );
            // $this->logCorreo( $tram->Id, "torresfrd@gmail.com", "Inscripcion TESIS EN UN POSTER ", $msg );

            echo "<div class='alert alert-success text-center'>
                  <h2><strong>Inscripción Finalizada</strong></h2> <h5>Estimado tesista tu postulación ha sido registrada con éxito.</h5>.
                </div>";
        }else{
                echo "<div class='alert alert-danger text-center'>ERROR :<br> Tienes Inconvenientes para la inscripción Intenta Nuevamente</h3> </div>";
        }

    }

    //  Tesis 3 Minutos
    public function vwInsq3mt()
    {
        $sess = $this->gensession->GetSessionData();
        echo "Inscripción Finalizada";
        // $this->load->view("pilar/tes/3mt/inscripcion",array('sess'=>$sess));
    }

    public function execPostula3MT()
    {
        $sess  = $this->gensession->GetSessionData();
        $resum  = mlSecurePost( "resumen" );
        $titulo = mlSecurePost( "titulo" );

        $tram = $this->dbPilar->inTramByTesista($sess->userId);
        $ord  = $this->dbPilar->getOneField("3mtPostul","Ord","Id>0 ORDER BY Id DESC")+1;

        $codigo  = sprintf("T%03s", $ord );
        $codigo1 = sprintf("LP%03s", $ord );

        $ppita = $this->uploaddf($codigo);

        // $potito=$this->uploadfoto($codigo1);
        if($ppita!=null)
        {
            if(!$this->dbPilar->getSnaprow("3mtPostul","IdTesista=$sess->userId"))
            {
                $this->dbPilar->Insert("3mtPostul", array(
                        'IdTesista'=>$sess->userId,
                        'IdCarrera'=>$sess->IdCarrera,
                        'Ord'=> $ord,
                        'Codigo'=>$codigo,
                        'Titulo'=>$titulo,
                        'Resumen'=>$resum,
                        'Archivo'=>"$codigo.pptx",
                        'Fecha'=>mlCurrentDate(),
                    ));
            }

            $msg= "<center><img  width='250px'src='http://vriunap.pe/vriadds/vri/web/convocatorias/curso1-3mt.jpg'></img></center><b>Postulación Aceptada</b><br><br>Señor Tesista Bienvenido a Tesis en Tres Minutos UNA-Puno (3MT®).<br><br>Recuerde que en la
                    presentación oral usted deberá explicar de forma convincente, concisa y clara su investigación. Usted puede verificar su inscripción en la web de la convocatoria : <a href='http://vriunap.pe/tesis3minutos'><i> Ver Inscritos</i></a> ";
            $this->logCorreo( $tram->Id, $sess->userMail, "Inscripcion 3MT ", $msg );
                // $this->logCorreo( $tram->Id, "torresfrd@gmail.com", "Inscripcion 3MT ", $msg );

            echo "<div class='alert alert-success text-center'>
                  <h2><strong>Inscripción Finalizada</strong></h2> <h5>Estimado tesista tu postulación ha sido registrada con éxito.</h5>.
                 </div>";
        }else{
                echo "<div class='alert alert-danger text-center'>ERROR :<br> Tienes Inconvenientes para la inscripción Intenta Nuevamente</h3> </div>";
        }


    }

    public function uploaddf($nombre)
    {
        $sess = $this->gensession->GetData();
        $config['upload_path']   = './repositor/tesis3m/';
        $config['allowed_types'] = 'ppt|pptx';  // ext
        $config['max_size']      = '6144';         // KB
        $config['overwrite']     = TRUE;
        $config['file_name']     = "$nombre";

        // finalmente subir archivo
        $this->load->library('upload', $config);
        if ( !$this->upload->do_upload("nomarch") ) { // input field

            $data['uploadError'] = $this->upload->display_errors();
            //echo "<div class='alert alert-danger text-center'><h2>Error:</h2> " . $this->upload->display_errors()."<h3><br> Tienes Inconvenientes para la inscripción Intenta Nuevamente</h3> </div>";
            return null;

        } else {
            $file_info = $this->upload->data();
            echo "Archivo Subido <br>";
            return $file_info;

        }

        // devolvemos el nombre del archivo
        return  $config['file_name'];
    }

    public function uploadfotoS($nombre)
    {
        $sess = $this->gensession->GetData();
        $config2['upload_path']   = './repositor/tesis3m/';
        $config2['max_size']      = '9144';         // KB
        $config2['allowed_types'] = 'png';  // ext
        $config2['overwrite']     = TRUE;
        $config2['max_width'] = '1024';
        $config2['max_height'] = '768';
        $config2['file_name']     = "$nombre.png";

        // finalmente subir archivo
        $this->load->library('upload', $config2);
        if ( !$this->upload->do_upload("nomphot") ) { // input field

            $data['uploadError'] = $this->upload->display_errors();
            // echo "<div class='alert alert-danger text-center'><h2>Error:</h2> " . $this->upload->display_errors()."<h3><br> Tienes Inconvenientes para la inscripción Intenta Nuevamente</h3> </div>";
            return null;

        } else {
            $this->upload->data();
            echo "Archivo Subido <br>";
            return $file_info;
        }

        // devolvemos el nombre del archivo
        // return  $config['file_name'];
    }
    */
}

//- EOF

