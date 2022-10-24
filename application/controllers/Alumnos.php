<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require(APPPATH . 'libraries/Rest_Controller.php');
require(APPPATH . 'libraries/Format.php');

class Alumnos extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
	}

	function getall_get()
	{
		$this->load->model('Alumnos_model', 'alum');
		$alumnos = $this->alum->get_all();
		$data['success'] = true;
		$data['data'] = $alumnos;
		$this->response($data);
	}


	function guardar_post()
	{
		$this->load->model('Alumnos_model', 'alum');
		$this->load->library('mail_library');
		$data = json_decode($this->post('data'));
		$id_usu = json_decode($this->post('id'));
		$alumno = json_decode($this->post('profesor'));

		

		//validamos que no exista empleado registrado con esa identificacion
		$valididen = $this->alum->count_by(array(
			'alum_identificacion' => $data->data->identificacion
		));

		if($valididen > 0){

			$resp['ok'] = false;
			$resp['mensaje'] = 'identificación ya registrada';
	
		}else{
	
			//validamos que no exista empleado registrado con este correo
		$validmail = $this->alum->count_by(array(
			'alum_email' => $data->data->email
		));

		if($validmail > 0){

			$resp['ok'] = false;
			$resp['mensaje'] = 'Email ya registrado';
	
		}else{
	
			if ($id_usu == 0) {
				$id = $this->alum->insert(array(
					'alum_identificacion' => $data->data->identificacion,
					'alum_nombres' => $data->data->nombre,
					'alum_apellidos' => $data->data->apellidos,
					'alum_email' => $data->data->email,
					'alum_telefono' => $data->data->telefono,
					'alum_estado' => $data->data->estado,
					'fecha_creacion' => date('y-m-d'),
					'alum_perfil' => 3,
					'alum_pais' => $data->data->pais,
					'alum_password'=>md5($data->data->identificacion)
				));

				$resp['data'] = $this->alum->get_by(array("alum_id" => $id));;
				$resp['ok'] = true;
				$resp['mensaje'] = 'Alumno creado exitosamente';

					//crear link contraseña para redireccionar al cambio de passw
				$this->mail_library->email_registro($data->data->email,2);

			} else {
				$id = $id_usu;
				$this->alum->update_by(array(
					'alum_id' => $id
				), array(
					'alum_nombres' => $data->data->nombre,
					'alum_apellidos' => $data->data->apellidos,
					'alum_email' => $data->data->email,
					'alum_telefono' => $data->data->telefono,
					'alum_estado' => $alumno->estado,
					'alum_perfil' => 3,
					'alum_pais' => $alumno->pais
	
				));

				$resp['data'] = $this->alum->get_by(array("alum_id" => $id));;
				$resp['ok'] = true;
			}
		}

		}
		
		$this->response($resp);
	}

	function getAlumno_post()
	{
		$this->load->model('Alumnos_model', 'alum');
		$id_usu = $this->post('id');
		$profesor = $this->alum->get_by(array('alum_id' => $id_usu));
		$data['success'] = true;
		$data['data'] = $profesor;
		$this->response($data);
	}

	function deleteAlumno_post()
	{
		$this->load->model('Alumnos_model', 'alum');
		$id_usu = $this->post('id');

		$this->alum->delete_by(array(
			'alum_id' => $id_usu
		));

		$alumnos = $this->alum->get_all();
		$resp['mensaje'] = 'Profesor eliminada correctamente';
		$resp['data'] = $alumnos;
		$resp['sucess'] = true;
		$this->response($resp);
	}

	function cargarAlumnos_post()
	{
		$this->load->model('Alumnos_model', 'alum');
		$this->load->library('upload');
		$usuario = json_decode($this->post('usuario'));

		//carga de archivos
		$carpeta = 'documentos/upload/';
		if (!empty($_FILES)) {

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

					$file  = "documentos/upload/" . $mi_archivo . "";
					$file = fopen($file, "r");
					$data = array();

					while (!feof($file)) {
						//pasamos csv a array 
						$infoAlumn[] = fgetcsv($file, null, ';');
					}
					$error = array();

					//recorremos para validacion
					foreach ($infoAlumn as $k => $data) {
						if ($k > 0) {
							//validamos que vengan campos correctos
							$linea =  $k + 1;
							if (count($data) != 6) {

								$error[] = "Favor verificar la información de las columnas, linea: $linea.  ";
							}
							//validamos que sean correos
							if (!filter_var($data[3], FILTER_VALIDATE_EMAIL)) {
								$error[] = "Esta dirección de correo ($data[3]) es invalida linea: $linea.  ";
							}
							//validamos que no haya sido inscrito identificacion

							$idenvalid = $this->alum->count_by(array(
								'alum_identificacion' => $data[0]
							));
							if ($idenvalid > 0) {
								$error[] = "La identificación $data[0] ingresada ya se encuentra registrada  linea: $linea.  ";
							}

							//validamos que no haya sido inscrito correo

							$idenvalid = $this->alum->count_by(array(
								'alum_email' => $data[3]
							));
							if ($idenvalid > 0) {
								$error[] = "El correo $data[3] ingresado ya se encuentra registrado  linea: $linea.  ";
							}
						}
					}

					if (count($error) == 0) {
						
						foreach ($infoAlumn as $k => $data) {
							if ($k > 0) {
							$this->alum->insert(array(
								'alum_identificacion' => $data[0],
								'alum_nombres' => $data[1],
								'alum_apellidos' => $data[2],
								'alum_email' => $data[3],
								'alum_telefono' => $data[4],
								'alum_estado' => 1,
								'fecha_creacion' => date('y-m-d'),
								'alum_perfil' => 3,
								'alum_pais' => $data[5],
								'usu_creacion' => $usuario,
								'alum_textoclaro'=>$data[0],
								'alum_password'=>md5($data[0])
							));
						}
					}
						$resp['error'] = $error;
						$resp['sucess'] = true;
						$resp['mensaje'] = 'Alumnos creados exitosamente.';
					} else {
						$aux_error = '';
						foreach($error as $er){
							$aux_error .= $er;
							
						}
						$resp['error'] = $aux_error;
						$resp['sucess'] = false;
						$resp['mensaje'] = 'Por favor verificar la información diligenciada en el archivo.';
					}
				}
			}
		}

		$alumnos = $this->alum->get_all();
		
		$resp['alumnos'] = $alumnos;
		$this->response($resp);
	}

	function Alum_carrera_post(){
		$this->load->model('Rel_alum_progra_model', 'relp');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');
		$this->load->model('Alumnos_model', 'alum');
		
		$alumnos = $this->relp->alumnos_carrera($this->post('tipo'),$this->post('id_prog'),$this->post('profesor'));
	
		$data['success'] = true;
		$data['mensaje'] = 'OK';
		$data['alumnos'] = $alumnos;
		
		$this->response($data);
	}

	function Inscri_Alumno_prog_post(){
		$this->load->model('Rel_alum_progra_model', 'relp');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');
		$this->load->model('Alumnos_model', 'alum');
		 //validar que el correo este registrado
		 $data = json_decode($this->post('data'));
		 $id_alumno = json_decode($this->post('id_alumno'));
		
		
				$valid= $this->relp->count_by(array(
					'fk_profesor' => $data->data->profesor,
					'fk_alumno' => $id_alumno,
					'fk_programa' => $data->data->programa,
				));
  
				if($valid == 0){
					$this->relp->insert(array(
						'fk_profesor' => $data->data->profesor,
						'fk_alumno' => $id_alumno,
						'fk_programa' => $data->data->programa,
						'fk_tipo_programa' =>  $data->data->tipo_programa,
						'fecha_inscrip' => date('y-m-d'),
						'estado' => 1
					));
					$response['success'] = true;
					$response['mensaje'] = 'Alumno inscrito exitosamente';
				}else{
					$response['success'] = false;
					$response['mensaje'] = 'Alumno ya inscrito en este programa y profesor.';
				}
		
		$this->response($response);
	
	}

	function cant_programasalum_post(){

		$this->load->model('Rel_alum_progra_model', 'rela');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');

		$relcar = $this->rela->get_many_by(array(
			'fk_alumno' => $this->post('alumno'),
			'estado' => 1
		));

		$cantfinal = array();
		$cant_carrer = 0;
		$cant_cur = 0;
		$cant_diplo = 0;
		foreach($relcar as $value){
			if($value->fk_tipo_programa == 1){
				$cantfinal[$value->fk_tipo_programa] = $cant_carrer;
				$cant_carrer++;
			}

			if($value->fk_tipo_programa == 2){
				$cantfinal[$value->fk_tipo_programa] = $cant_cur;
				$cant_cur++;
			}
			
			if($value->fk_tipo_programa == 3){
				$cantfinal[$value->fk_tipo_programa] = $cant_diplo;
				$cant_diplo++;
			}
		}

		

		$data['success'] = true;
		$data['cant_carrer'] = $cant_carrer;
		$data['cant_cur'] = $cant_cur;
		$data['cant_diplo'] = $cant_diplo;
		$this->response($data);

	}

	function getCarrerasAlum_post(){
		$this->load->model('Rel_alum_progra_model', 'relp');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');
		$this->load->model('Alumnos_model', 'alum');
	
		$respons = $this->alum->programasprofactivos($this->post('tipo'),$this->post('alumno'));
	
		$data['success'] = true;
		$data['data'] = $respons;
		$this->response($data);
	}

	function cambiarestado_rel_post(){
		$this->load->model('Rel_alum_progra_model', 'relp');
		$this->load->model('Alumnos_model', 'alum');

		$id_rel = $this->post('id_rel');
		$estado = $this->post('estado');
		$id_alumno = $this->post('alumno');
	
		if($estado == 0){

			$updateestado = 1;
			

		}else if($estado == 1){

			$updateestado = 0;

		}
		$this->relp->update_by(array(
			'id'=>$id_rel
		),array(
			'estado'=>$updateestado
		));

		//consultamos carreras
		$carreras = $this->alum->programasprof(1,$id_alumno);
		//consultamos cursos	
		$cursos = $this->alum->programasprof(2,$id_alumno);
		//consultamos diplomados
		$diplomados= $this->alum->programasprof(2,$id_alumno);

		$data['carreras'] = $carreras;
		$data['cursos'] = $cursos;
		$data['diplomados'] = $diplomados;
		$data['success'] = true;
	
		$this->response($data);

	}

	function getprogramasAlumno_post(){
		$this->load->model('Alumnos_model', 'alum');
		$id_alumno = $this->post('id');
		//consultamos carreras
		$carreras = $this->alum->programasprof(1,$id_alumno);
		//consultamos cursos	
		$cursos = $this->alum->programasprof(2,$id_alumno);
		//consultamos diplomados
		$diplomados= $this->alum->programasprof(2,$id_alumno);

		$data['carreras'] = $carreras;
		$data['cursos'] = $cursos;
		$data['diplomados'] = $diplomados;
		
		if(count($carreras)==0 and count($cursos)==0 and count($diplomados)==0){
			$data['success'] = false;
		}else{
			$data['success'] = true;
		}

		
		
		$this->response($data);
	}


	
function listarVideos_post(){
	$this->load->model('Videos_model', 'vi');
		$this->load->model('Profesores_model', 'pro');

		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');
	
		$videos = $this->vi->get_many_by(array(
			'vid_fk_programa'=>$programa,
			'vid_fk_tipo_programa'=>$tipo,
			'vid_fk_profesor'=>$profesor
		));
		
	
		if(count($videos)>0){
			$profesor = $this->pro->get_by(array(
				'pro_id' =>$videos[0]->vid_fk_profesor
			));
	
			$data['videos'] = $videos;
			$data['profesor'] = $profesor;
			$data['sucess'] = true;
			$data['mensaje'] = 'ok';

		}else{
			$data['sucess'] = false;
			$data['mensaje'] = 'ok';
	

		}

	
		$this->response($data);		

}

function listarClases_post(){
	$this->load->model('Clases_model', 'cla');
	$this->load->model('Profesores_model', 'pro');

		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');

		//tipo alumno 
		$tipoalum =  $this->post('tipoalum');
		
		if($tipoalum == 1){
			$clases = $this->cla->get_many_by(array(
				'fk_programa'=>$programa,
				'fk_tipo_programa'=>$tipo,
				'fk_profesor'=>$profesor,
				'estado' => 1
				
			));
		}else{
			$clases = $this->cla->get_many_by(array(
				'fk_programa'=>$programa,
				'fk_tipo_programa'=>$tipo,
				'fk_profesor'=>$profesor,
				
			));
		}
		

	
	
		if(count($clases)>0){

			
			$profesor = $this->pro->get_by(array(
				'pro_id' =>$clases[0]->fk_profesor
			));
	
			$data['clases'] = $clases;
			$data['profesor'] = $profesor;
			$data['sucess'] = true;
			$data['mensaje'] = 'ok';

		}else{
			$data['sucess'] = false;
			$data['mensaje'] = 'ok';
	

		}

		
		$this->response($data);		

}



function cargarTrabajoAlum_post(){
	$this->load->model('rel_trabajos_alumnos_model', 'rel');
	$this->load->library('upload');
	$this->load->model('Trabajos_model', 'tra');

	$data1 = json_decode($this->post('data'));
	$id_trabajo = json_decode($this->post('id_trabajo'));
	$alumno = json_decode($this->post('alumno'));
	$comentario = $data1->data->comentario;

	$trabajo = $this->tra->get_by(array(
		'id'=>$id_trabajo
	));

	$this->rel->delete_by(array(
		'fk_trabajo'=>$id_trabajo,
		'fk_alumno'=>$alumno,
		'fk_programa'=> $trabajo->fk_programa,
		'fk_tipo_programa'=>$trabajo->fk_tipo_programa,
		'fk_profesor'=>$trabajo->fk_profesor,
	));

	$id = $this->rel->insert(array(
		'comentario'=>$comentario,
		'fk_trabajo'=>$id_trabajo,
		'fk_alumno'=>$alumno,
		'fk_programa'=> $trabajo->fk_programa,
		'fk_tipo_programa'=>$trabajo->fk_tipo_programa,
		'fk_profesor'=>$trabajo->fk_profesor,
		'fecha'=>date('y-m-d')
	));

	
			//carga de archivos
			if (!empty($_FILES)) {

			
				$carpeta = 'imagenes/trabajosAlumnos/'.$id_trabajo.'/'.$alumno;
				$files = glob($carpeta.'/*'); //obtenemos todos los nombres de los ficheros
				foreach($files as $file){
					if(is_file($file))
					unlink($file); //elimino el fichero
				}

				
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
	
						$this->rel->update_by(array('id' => $id), array('ruta_arch' => $carpeta . '/' . $fil->file_name));
						$resp['success'] = true;
						$resp['mensaje'] = 'Trabajo cargado exitosamente';
						$resp['tipo_programa'] = $trabajo->fk_tipo_programa;
						$resp['programa'] = $trabajo->fk_programa;
						$resp['profesor'] = $trabajo->fk_profesor;
						
					}
				}
			}else{
				$resp['success'] = false;
				$resp['mensaje'] = 'Error al cargar archivo';
				$resp['tipo_programa'] = '';
				$resp['programa'] = '';
				$resp['profesor'] = '';
						
			}

	$this->response($resp);
}

function ValidarcargueTrabajo_post(){
	$this->load->model('rel_trabajos_alumnos_model', 'rel');
	$this->load->model('Trabajos_model', 'tra');

	$id_trabajo = json_decode($this->post('idtrabajo'));
	$alumno = json_decode($this->post('alumno'));
	
	$trabajo = $this->tra->get_by(array(
		'id'=>$id_trabajo
	));

	if($trabajo){

		$data =  $this->rel->get_by(array(
			'fk_trabajo' =>$id_trabajo,
			'fk_alumno' =>$alumno,
			'fk_programa' =>$trabajo->fk_programa,
			'fk_tipo_programa' =>$trabajo->fk_tipo_programa,
			'fk_profesor' =>$trabajo->fk_profesor
		));
		

		$namearchivo = explode('/',$data->ruta_arch);
		
		$resp['success']= true;
		$resp['fk_programa']= $trabajo->fk_programa;
		$resp['fk_tipo_programa']= $trabajo->fk_tipo_programa;
		$resp['fk_profesor']= $trabajo->fk_profesor;
		$resp['data'] = $data;
		$resp['namearchivo']=$namearchivo[4];
	}else{
		$resp['success']= false;
		$resp['data'] = '';
	}

	$this->response($resp);
}
}
