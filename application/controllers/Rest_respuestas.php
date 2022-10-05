<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require(APPPATH.'libraries/Rest_Controller.php');
require(APPPATH.'libraries/Format.php');

class Rest_respuestas extends REST_Controller
{
	
    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, authorization, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		
	}

	
	function guardarRespuestas_post(){
		$this->load->model('preguntas_model', 'pre');
		$this->load->model('respuestas_model', 'res');
		$this->load->model('examenes_model', 'exa');
		
		$data = $this->post('data');
		$user = $this->post('user');
		$idexamen = $this->post('idexamen');
	
		$status = true;
		$mensaje = 'Guardado correctamente';
		foreach($data as $value){
			if($value == ''){
				$status = false;
				$mensaje = 'Debe diligenciar todos los campos';
			}
		}

	

		if($status){
			
			foreach($data as $key=>$value){
				$aux = explode('_',$key);
				$arrayData[$aux[1]][$aux[0]] = $value;
			}

		
			$this->res->delete_by(array(
				'fk_examen'=>$idexamen,
				
			));
			
			foreach($arrayData as $k=>$value){
				$pregunta = $this->pre->get_by(array(
					'id_pregunta' => $k
				));
			
				foreach ($value as $key => $val) {
					$this->res->insert(array(
						'fk_pregunta'=> $k,
						'fk_examen'=>$idexamen,
						'texto_respuesta'=>$val,
						'orden'=>$key,
						'fk_tipo_preg'=>$pregunta->tipo_pregunta, 
						'fk_estado'=>1
					));
				}
				
			}	
		}

		$respuestas = $this->res->get_many_by(array(
			'fk_examen'=>$idexamen,
		));
	
		$resp['mensaje'] = $mensaje;
		$resp['respuestas'] = $respuestas;
		$resp['sucess'] = $status;
		$this->response($resp);
	}

	function list_respuestas_post(){
		$this->load->model('examenes_model', 'exa');
		$this->load->model('respuestas_model', 'res');
		$idexamen = $this->post('id');
	
	
		$respuestas = $this->res->get_many_by(array(
			'fk_examen' => $idexamen
		));

	
		if(count($respuestas) == 0){
			$status = false;
			$mensaje = 'false';
		}else{
			$status = true;
			$mensaje = 'exitoso';
			$arraux = array();
			foreach($respuestas as $value){
				$arraux[$value->fk_pregunta][]= $value;
			}
			$resp['respuestas'] = $arraux;
		}

		
		$resp['mensaje'] = $mensaje;
		$resp['sucess'] = $status;
		$this->response($resp);
	}

	function list_respuestas_prese_post(){
		$this->load->model('examenes_model', 'exa');
		$this->load->model('respuestas_model', 'res');
		$idexamen = $this->post('id');
	
		$respuestas = $this->res->get_many_by(array(
			'fk_examen' => $idexamen,
		));
		
	
		if(count($respuestas) == 0){
			$status = false;
			$mensaje = 'false';
		}else{
			$status = true;
			$mensaje = 'exitoso';
			$arraux = array();
			foreach($respuestas as $value){
				$arraux[$value->fk_pregunta][]= $value;
			}
			$resp['respuestas'] = $arraux;
		}
		$resp['mensaje'] = $mensaje;
		$resp['sucess'] = $status;
		$this->response($resp);
	}

	}
