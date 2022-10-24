<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require(APPPATH . 'libraries/Rest_Controller.php');
require(APPPATH . 'libraries/Format.php');

class Cursos extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
	}

	function getall_get()
	{
		$this->load->model('Cursos_model', 'cur');
		$cursos = $this->cur->get_all();
		$data['success'] = true;
		$data['data'] = $cursos;
		$this->response($data);
	}

	function deletecurso_post()
	{
		$this->load->model('Cursos_model', 'cur');
		$id_cur = $this->post('id');

		$curso = $this->cur->get_by(array(
			'id' => $id_cur
		));

		//eliminamos archivos
		$carpeta = 'imagenes/cursos/' . $id_cur . ''; //obtenemos todos los nombres de los ficheros
		$files = glob('imagenes/cursos/' . $id_cur . '/*');

		foreach ($files as $file) {
			if (is_file($file))
				unlink($file); //elimino el fichero
		}

		if (is_dir($carpeta)) {
			rmdir($carpeta);
		}
		$this->cur->delete_by(array(
			'id' => $id_cur
		));

		$cursos = $this->cur->get_all();
		$resp['mensaje'] = 'Curso eliminada correctamente';
		$resp['data'] = $cursos;
		$resp['sucess'] = true;
		$this->response($resp);
	}

	function guardar_post()
	{
		$this->load->model('Cursos_model', 'cur');
		$this->load->library('upload');
		$data = json_decode($this->post('data'));
		$id_curso = json_decode($this->post('id'));
		$usuario = json_decode($this->post('usuario'));

		if ($id_curso == 0) {
			$id = $this->cur->insert(array(
				'nombre' => $data->data->nombre,
				'descripcion' => $data->data->descripcion,
				'estado' => $data->data->estado,
				'link_argentina' => $data->data->link_arg,
				'link_pago' => $data->data->link_pago,
				'fecha_creacion' => date('y-m-d'),
				'usuario_creacion' => $usuario->usu_id
			));
		} else {
			$id = $id_curso;
			$this->cur->update_by(array(
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
			$carpeta = 'imagenes/cursos/' . $id;

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

					$this->cur->update_by(array('id' => $id), array('foto' . $k => $carpeta . '/' . $fil->file_name));
					$resp['imagenes' . $k] = true;
				}
			}
		}
		$resp['data'] = $this->cur->get_by(array("id" => $id));;
		$resp['ok'] = true;
		$this->response($resp);
	}
	function getcurso_post()
	{
		$this->load->model('Cursos_model', 'cur');
		$id_cur = $this->post('id');
		$curso = $this->cur->get_by(array('id' => $id_cur));
		$data['success'] = true;
		$data['data'] = $curso;
		$this->response($data);
	}
}
