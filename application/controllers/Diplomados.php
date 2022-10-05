<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require(APPPATH.'libraries/Rest_Controller.php');
require(APPPATH.'libraries/Format.php');

class Diplomados extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
		
    }

	function getall_get(){
		$this->load->model('Diplomados_model','dip');
		$diplomados = $this->dip->get_all();
		$data['success'] = true;
		$data['data'] = $diplomados;
		$this->response($data);
	}

	
	function getdiplomado_post()
	{
		$this->load->model('Diplomados_model', 'dip');
		$id_dip = $this->post('id');
		$diplomado = $this->dip->get_by(array('id' => $id_dip));
		$data['success'] = true;
		$data['data'] = $diplomado;
		$this->response($data);
	}

	function guardar_post()
	{
		$this->load->model('Diplomados_model', 'dip');
		$this->load->library('upload');
		$data = json_decode($this->post('data'));
		$id_dip = json_decode($this->post('id'));
		$usuario = json_decode($this->post('usuario'));

		if ($id_dip == 0) {
			$id = $this->dip->insert(array(
				'nombre' => $data->data->nombre,
				'descripcion' => $data->data->descripcion,
				'estado' => $data->data->estado,
				'link_argentina' => $data->data->link_arg,
				'link_pago' => $data->data->link_pago,
				'fecha_creacion' => date('y-m-d'),
				'usuario_creacion' => $usuario->usu_id
			));
		} else {
			$id = $id_dip;
			$this->dip->update_by(array(
				'id' => $id
			), array(
				'nombre' => $data->data->nombre,
				'descripcion' => $data->data->descripcion,
				'link_argentina' => $data->data->link_arg,
				'link_pago' => $data->data->link_pago,
				'estado' => $data->data->estado,
				'fecha_creacion' => date('y-m-d'),
				'usuario_creacion' => $usuario->usu_id

			));
		}

		//carga de archivos
		if (!empty($_FILES)) {
			$carpeta = 'imagenes/diplomados/' . $id;
		
			foreach ($_FILES as $k => $values) {

				
				
				if (!file_exists($carpeta)) {
					mkdir($carpeta, 0777, true);
				}
				$mi_archivo = $values['name'];
				$config['upload_path'] = $carpeta;
				$config['file_name'] = $mi_archivo;
				$config['overwrite'] = true;
				$config['allowed_types'] = "*";
				$fil = $this->upload->initialize($config, false);
				if (!$this->upload->do_upload($k)) {
					//*** ocurrio un error
					$resp['imagenes' . $k] = 'Error al cargar la foto' . $k;
				} else {
				
					$this->dip->update_by(array('id' => $id), array('foto' . $k => $carpeta . '/' . $fil->file_name));
					$resp['imagenes' . $k] = true;
				}
			}
		}
		$resp['data'] = $this->dip->get_by(array("id" => $id));;
		$resp['ok'] = true;

		$this->response($resp);
	}

	function deletediplomado_post(){
		$this->load->model('Diplomados_model', 'dip');
		$id_dip = $this->post('id');
		//eliminamos archivos
		$carpeta = 'imagenes/diplomados/'.$id_dip.''; //obtenemos todos los nombres de los ficheros
		$files = glob('imagenes/diplomados/'.$id_dip.'/*'); 

		foreach($files as $file){
			if(is_file($file))
			unlink($file); //elimino el fichero
		}
		if(is_dir($carpeta)){
			rmdir($carpeta);
		}
	
		$this->dip->delete_by(array(
			'id'=>$id_dip
		));

		$diplomados = $this->dip->get_all();
		$resp['mensaje'] ='Diplomado eliminada correctamente';
		$resp['data'] = $diplomados;
		$resp['sucess'] = true;
		$this->response($resp);
	}

    }
