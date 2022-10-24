<?php
ob_start();
defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require(APPPATH.'libraries/Rest_Controller.php');
require(APPPATH.'libraries/Format.php');

class Usuarios extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
		
    }

	function guardar_post()
	{
		$this->load->model('Usuarios_model','usu');
		$data = json_decode($this->post('data'));
		$id_usu = json_decode($this->post('id'));
		$usuario = json_decode($this->post('usuario'));

		$valididen = $this->usu->count_by(array(
			'usu_identificacion'=> $data->data->identificacion
		));

		if($valididen > 0){
			$resp['ok'] = false;
			$resp['mensaje'] = 'Identificacion ingresada ya registrada';
		}else{

			$validusu = $this->usu->count_by(array(
				'usu_email'=> $data->data->email
			));

			

			if($validusu >0){
				$resp['ok'] = false;
				$resp['mensaje'] = 'Email ingresado ya registrado';
			}else{
				if ($id_usu == 0) {
					$id = $this->usu->insert(array(
						'usu_identificacion' => $data->data->identificacion,
						'usu_nombres' => $data->data->nombre,
						'usu_apellidos' => $data->data->apellidos,
						'usu_email' => $data->data->email,
						'usu_telefono' => $data->data->telefono,
						'usu_estado' => $data->data->estado,
						'fecha_creacion' => date('y-m-d'),
						'usu_perfil' => 1,
						'usu_pais' => $data->data->pais
					));

					echo 'Creado';
					exit;
				} else {
					$id = $id_usu;
					$this->usu->update_by(array(
						'usu_id' => $id
					), array(
						'usu_nombres' => $data->data->nombre,
						'usu_apellidos' => $data->data->apellidos,
						'usu_email' => $data->data->email,
						'usu_telefono' => $data->data->telefono,
						'usu_estado' =>  $data->data->estado,
						'usu_perfil' => 1,
						'usu_pais' =>  $data->data->pais
		
					));
				}
	
				$resp['data'] = $this->usu->get_by(array("usu_id" => $id));;
				$resp['ok'] = true;
				$resp['mensaje'] = 'Usuario creado exitosamante';
			}
		}

		$this->response($resp);
	}

	function getall_get(){
		$this->load->model('Usuarios_model','usu');
		$usuarios = $this->usu->get_all();
		$data['success'] = true;
		$data['data'] = $usuarios;
		$this->response($data);
	}	

	
	function getusuario_post()
	{
		$this->load->model('Usuarios_model', 'usu');
		$id_usu = $this->post('id');
		$usuario = $this->usu->get_by(array('usu_id' => $id_usu));
		$data['success'] = true;
		$data['data'] = $usuario;
		$this->response($data);
	}

	function deleteusuario_post(){
		$this->load->model('Usuarios_model', 'usu');
		$id_usu = $this->post('id');
		
		$this->usu->delete_by(array(
			'usu_id'=>$id_usu
		));

		$usuarios = $this->usu->get_all();
		$resp['mensaje'] ='Usuario eliminada correctamente';
		$resp['data'] = $usuarios;
		$resp['sucess'] = true;
		$this->response($resp);
	}


    }
