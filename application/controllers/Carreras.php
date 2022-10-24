<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require(APPPATH . 'libraries/Rest_Controller.php');
require(APPPATH . 'libraries/Format.php');

class Carreras extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
	}

	function getFront_get(){
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');


		$carreras = $this->car->get_many_by(array(
			'estado'=>1
		));

		$cursos = $this->cur->get_many_by(array(
			'estado'=>1
		));

		$diplomados = $this->dip->get_many_by(array(
			'estado'=>1
		));

		$programas = array();

		foreach ($carreras as $car){
			$programas[] = $car;
		}

		foreach ($cursos as $cur){
			$programas[] = $cur;
		}

		foreach ($diplomados as $dip){
			$programas[] = $dip;
		}
		
		if(count($programas)< 6){
			$cant = count($programas);
		}else{
			$cant = 9;
		}

		$claves_aleatorias = array_rand($programas, $cant);
		
		foreach($claves_aleatorias as $indice){
			$info[] = $programas[$indice];
		}

		$data['data'] = $info;
		
		$this->response($data);

	}


	function getallProgramas_get(){
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');


		$carreras = $this->car->get_many_by(array(
			'estado'=>1
		));

		$cursos = $this->cur->get_many_by(array(
			'estado'=>1
		));

		$diplomados = $this->dip->get_many_by(array(
			'estado'=>1
		));

		$programas = array();

		foreach ($carreras as $car){
			$car->tipo_programa = 1;
			$programas[] = $car;
		}

		foreach ($cursos as $cur){
			$cur->tipo_programa = 2;
			$programas[] = $cur;
		}

		foreach ($diplomados as $dip){
			$dip->tipo_programa = 3;
			$programas[] = $dip;
		}

		
		$data['data'] = $programas;
		
		$this->response($data);

	}

	function getall_get()
	{
		$this->load->model('Carreras_model', 'car');
		$carreras = $this->car->get_all();
		$data['success'] = true;
		$data['data'] = $carreras;
		$this->response($data);
	}

	function getcarrera_post()
	{
		$this->load->model('Carreras_model', 'car');
		$id_car = $this->post('id');
		$carrera = $this->car->get_by(array('id' => $id_car));
		$data['success'] = true;
		$data['data'] = $carrera;
		$this->response($data);
	}

	function guardar_post()
	{
		$this->load->model('Carreras_model', 'car');
		$this->load->library('upload');
		$data = json_decode($this->post('data'));

		$id_carrera = json_decode($this->post('id'));
		$usuario = json_decode($this->post('usuario'));

		if ($id_carrera == 0) {
			$id = $this->car->insert(array(
				'nombre' => $data->data->nombre,
				'descripcion' => $data->data->descripcion,
				'estado' => $data->data->estado,
				'link_argentina' => $data->data->link_arg,
				'link_pago' => $data->data->link_pago,
				'fecha_creacion' => date('y-m-d'),
				'usuario_creacion' => $usuario->usu_id
			));
		} else {
			$id = $id_carrera;
			$this->car->update_by(array(
				'id' => $id
			), array(
				'nombre' => $data->data->nombre,
				'descripcion' => $data->data->descripcion,
				'estado' => $data->data->estado,
				'link_argentina' => $data->data->link_arg,
				'link_pago' => $data->data->link_pago,
				'fecha_creacion' => date('y-m-d'),
				'usuario_creacion' => $usuario->usu_id

			));
		}

		//carga de archivos
		if (!empty($_FILES)) {
			$carpeta = 'imagenes/carreras/' . $id;

		
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

					$this->car->update_by(array('id' => $id), array('foto' . $k => $carpeta . '/' . $fil->file_name));
					$resp['imagenes' . $k] = true;
				}
			}
		}
		$resp['data'] = $this->car->get_by(array("id" => $id));;
		$resp['ok'] = true;

		$this->response($resp);
	}

	function deletecarrera_post(){
		$this->load->model('Carreras_model', 'car');
		$id_car = $this->post('id');

		$carrera = $this->car->get_by(array(
			'id' => $id_car	
		));

		//eliminamos archivos
		$carpeta = 'imagenes/carreras/'.$id_car.''; //obtenemos todos los nombres de los ficheros
		$files = glob('imagenes/carreras/'.$id_car.'/*'); 

		foreach($files as $file){
			if(is_file($file))
			unlink($file); //elimino el fichero
		}
		if(is_dir($carpeta)){
			rmdir($carpeta);
		}
	
		$this->car->delete_by(array(
			'id'=>$id_car
		));

		$carreras = $this->car->get_all();
		$resp['mensaje'] ='Carrera eliminada correctamente';
		$resp['data'] = $carreras;
		$resp['sucess'] = true;
		$this->response($resp);
	}
}
