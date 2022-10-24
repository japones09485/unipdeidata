<?php
ob_start();
defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require(APPPATH.'libraries/Rest_Controller.php');
require(APPPATH.'libraries/Format.php');

class Rest_preguntas extends REST_Controller
{
	

    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, authorization, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		
	}

	function guardarPreguntas_post(){
		$this->load->model('preguntas_model', 'pre');
		$this->load->model('respuestas_model', 'res');
		$this->load->model('examenes_model', 'exa');
		$data = $this->post('data');
		$user = $this->post('user');
		$idexamen = $this->post('idexamen');

		$examen = $this->exa->get_by(array(
			'id_examen' => $idexamen
		));

		
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

			$preguntas = $this->pre->get_many_by(array(
				'fk_examen' => $idexamen,
			));
			
			if(count($preguntas)==0){
				$this->pre->delete_by(array(
					'fk_examen'=>$idexamen,
					
				));

			
	
				foreach($arrayData as $k=>$value){
					
				
	
					$id_preg = $this->pre->insert(array(
						'fk_examen'=> $idexamen,
						'numero_pregunta'=>$k,
						'enunciado'=>$value['pregunta'],
						'tipo_pregunta'=>$value['respuesta'],
						'fk_estado'=>1
					));

					
				
				if($value['respuesta'] == 1){
					for ($i=1; $i <=5 ; $i++) { 
						$this->res->insert(array(
							'fk_pregunta'=> $id_preg,
							'fk_examen'=>$idexamen,
							'texto_respuesta'=>'',
							'orden'=>$i,
							'fk_tipo_preg'=>$value['respuesta'],
							'fk_estado'=>0
						));
						
					}
					
				}else if($value['respuesta'] == 2){
					$this->res->insert(array(
						'fk_pregunta'=> $id_preg,
						'fk_examen'=>$idexamen,
						'texto_respuesta'=>'',
						'orden'=>0,
						'fk_tipo_preg'=>$value['respuesta'],
						'fk_estado'=>0
					));
				}else if($value['respuesta'] == 3){
					$this->res->insert(array(
						'fk_pregunta'=> $id_preg,
						'fk_examen'=>$idexamen,
						'texto_respuesta'=>'',
						'orden'=>0,
						'fk_tipo_preg'=>$value['respuesta'],
						'fk_estado'=>0
					));
				}
	

					
				}	
			}else{
				foreach($arrayData as $k=>$value){
					
					$cant = $this->pre->count_by(array(
						'fk_examen'=>$idexamen,
						'numero_pregunta'=>$k
					));

					$this->pre->update_by(array(
						'fk_examen' => $idexamen,
						'numero_pregunta' =>$k
						
					
					),array(
						'fk_examen' => $idexamen,
						'numero_pregunta' =>$k,
						'enunciado'=>$value['pregunta']
					));
					
				}	
			}
		
			$this->exa->update_by(array(
				'id_examen'=>$idexamen
			),array(
				'status_edit'=>1
			));
		}
	
		$resp['mensaje'] = $mensaje;
		$resp['sucess'] = $status;

	
		$this->response($resp);
	}

	function list_preguntas_post(){
		$this->load->model('preguntas_model', 'pre');
		$this->load->model('examenes_model', 'exa');
		
		$idexamen = $this->post('id');

	
		$preguntas = $this->pre->get_many_by(array(
			'fk_examen' => $idexamen
		));
		
	
		if(count($preguntas) == 0){
			$status = false;
			$mensaje = 'false';
		}else{
			$status = true;
			$mensaje = 'exitoso';
			$resp['preguntas'] = $preguntas;
		}
		$resp['mensaje'] = $mensaje;
		$resp['sucess'] = $status;
		$this->response($resp);
	}

	function add_preg_post(){
		$this->load->model('preguntas_model', 'pre');
		$this->load->model('examenes_model', 'exa');
		$this->load->model('respuestas_model', 'res');
		$this->load->model('presen_examen_model', 'pres');

		$data = $this->post('data');
		$idexamen = $this->post('idexamen');
		$examen = $this->exa->get_by(array(
			'id_examen' => $idexamen
		));

		
		//validar que no existan presentados
		$validal = $this->pres->count_by(array(
			'fk_examen'=>$idexamen
		));
		

		if($validal==0){
			$max_preg = $this->pre->maxid();

		

		
			$id_preg = $this->pre->insert(array(
				'fk_examen'=> $idexamen,
				'numero_pregunta'=>$max_preg->max+1,
				'enunciado'=>$data['pregunta'],
				'tipo_pregunta'=>$data['respuesta'],
				'fk_estado' => 1
			));
	
			if($data['respuesta'] == 1){
				for ($i=1; $i <=5 ; $i++) { 
					$this->res->insert(array(
						'fk_pregunta'=> $id_preg,
						'fk_examen'=>$idexamen,
						'texto_respuesta'=>'',
						'orden'=>$i,
						'fk_tipo_preg'=>$data['respuesta'],
						'fk_estado'=>0
					));
				}
				
			}else if($data['respuesta'] == 2){
				$this->res->insert(array(
					'fk_pregunta'=> $id_preg,
					'fk_examen'=>$idexamen,
					'texto_respuesta'=>'',
					'orden'=>0,
					'fk_tipo_preg'=>$data['respuesta'],
					'fk_estado'=>0
				));
			}else if($data['respuesta'] == 3){
				$this->res->insert(array(
					'fk_pregunta'=> $id_preg,
					'fk_examen'=>$idexamen,
					'texto_respuesta'=>'',
					'orden'=>0,
					'fk_tipo_preg'=>$data['respuesta'],
					'fk_estado'=>0
				));
			}
		
	
			$this->exa->update_by(array(
				'id_examen'=>$idexamen
			),array(
				'numero_preguntas' => $examen-> numero_preguntas + 1
			));
	
			$resp['mensaje'] = 'Pregunta agregada exitosamente';
			$resp['sucess'] = true;
		
		}else{
			$resp['mensaje'] = 'No se puede agregar pregunta al examen ya que ha sido presentado por mas de 1 alumno';
			$resp['sucess'] = false;
		}


		$preguntas = $this->pre->get_many_by(array(
			'fk_examen' => $idexamen,
		));

		$resp['preguntas'] = $preguntas;

		$this->response($resp);

	}

	function eliminar_preg_post(){
		$this->load->model('preguntas_model', 'pre');
		$this->load->model('examenes_model', 'exa');
		$this->load->model('respuestas_model', 'res');
		$this->load->model('presen_examen_model', 'pres');

		$idpregunta = $this->post('id_pres');
		$idexa = $this->post('id_exa');
		$examen = $this->exa->get_by(array(
			'id_examen' => $idexa
		));

		//validar que no existan presentados
		$validal = $this->pres->count_by(array(
			'fk_examen'=> $idexa
		));
		

		if($validal == 0){
			if($examen->numero_preguntas == 1){
				$preguntas = $this->pre->get_many_by(array(
					'fk_examen' => $idexa
				));
				$mensaje = 'Para eliminar la ultima pregunta, elimine el examen.';
				$resp['mensaje'] = $mensaje;
				$resp['preguntas'] = $preguntas;
				$resp['sucess'] = false;
		
				}else{
						
				$this->pre->delete_by(array(
					'id_pregunta' => $idpregunta
				));
		
				$this->res->delete_by(array(
					'fk_pregunta' => $idpregunta
				));
		
				$this->exa->update_by(array(
					'id_examen'=>$idexa
				),array(
					'numero_preguntas' => $examen-> numero_preguntas - 1
				));
				$resp['mensaje'] = 'Pregunta eliminada exitosamente';
				$resp['sucess'] = true;
				}
		}else{
			
			$resp['mensaje'] = 'No se puede eliminar la pregunta del examen ya que ha sido presentado por mas de 1 alumno';
			$resp['sucess'] = true;
		}	

		$preguntas = $this->pre->get_many_by(array(
			'fk_examen' => $idexa
		));
		$resp['preguntas'] = $preguntas;
		$this->response($resp);
	}
	
}
