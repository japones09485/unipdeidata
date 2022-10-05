<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require(APPPATH.'libraries/Rest_Controller.php');
require(APPPATH.'libraries/Format.php');

class Paises extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
		
    }
    public function getPaises_get(){
	
		$this->load->model('Paises_model','pa');
		$paisesaux = $this->pa->get_all();
        $paises = array();
        foreach($paisesaux as $k => $value){
            $paises[$value->iso]= $value;
        }
		$data['data'] = $paises;
        $data['ok'] = true;
        $this->response($data);
		
	}

    public function getPaisesList_get(){
	
	
		$this->load->model('Paises_model','pa');
		$paisesaux = $this->pa->get_all();
        $paises = array();
		$data['data'] = $paisesaux;
        $data['ok'] = true;
        $this->response($data);
		
	}
	


    }
