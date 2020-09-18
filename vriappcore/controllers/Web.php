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

include ("absmain/mlLibrary.php");
define  ("SessApp", "i:VRI");



class Web extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        //$this->load->model('dbPilar');
        //$this->load->model('dbFedu');
        //$this->load->model('dbRepo');
    }

    public function index()
    {
        $this->load->view( "web20/demo" );
    }


    //-------------------------------------
    public function construccion()
    {
        //header("Location: http://www.vriunap.pe/home");
        $this->load->view( "web/inicio" );
    }
}

//- EOF