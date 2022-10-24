<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require(APPPATH . 'libraries/Rest_Controller.php');
require(APPPATH . 'libraries/Format.php');

class Profesores extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
	}


	function getall_get()
	{
		$this->load->model('Profesores_model', 'pro');
		$profesores = $this->pro->get_all();
		$data['success'] = true;
		$data['data'] = $profesores;
		$this->response($data);
	}


	function guardar_post()
	{
		$this->load->model('Profesores_model', 'pro');
		$this->load->library('mail_library');
		$data = json_decode($this->post('data'));
		$id_usu = json_decode($this->post('id'));
		$profesor = json_decode($this->post('profesor'));

		if ($id_usu == 0) {
			$id = $this->pro->insert(array(
				'pro_identificacion'=>$data->data->identificacion,
				'pro_nombres' => $data->data->nombre,
				'pro_apellidos' => $data->data->apellidos,
				'pro_email' => $data->data->email,
				'pro_telefono' => $data->data->telefono,
				'pro_estado' => $data->data->estado,
				'fecha_creacion' => date('y-m-d'),
				'pro_perfil' => 2,
				'pro_pais' => $data->data->pais,
				'pro_cod_verificacion'=>md5($data->data->email),
				'pro_estado_verificacion'=>0,
				'pro_textoclaro'=>$data->data->identificacion,
				'pro_password'=>md5($data->data->identificacion)
			));

	//crear link contraseña para redireccionar al cambio de passw
	$this->mail_library->email_registro($data->data->email,1);

		} else {
			$id = $id_usu;
			$this->pro->update_by(array(
				'pro_id' => $id
			), array(
				'pro_nombres' => $data->data->nombre,
				'pro_apellidos' => $data->data->apellidos,
				'pro_email' => $data->data->email,
				'pro_telefono' => $data->data->telefono,
				'pro_estado' => $data->data->estado,
				'pro_perfil' => 2,
				'pro_pais' => $data->data->pais

			));
		}
		$resp['data'] = $this->pro->get_by(array("pro_id" => $id));;
		$resp['ok'] = true;

		$this->response($resp);
	}

	function getProfesor_post()
	{
		$this->load->model('Profesores_model', 'pro');
		$id_usu = $this->post('id');
		$profesor = $this->pro->get_by(array('pro_id' => $id_usu));
		$data['success'] = true;
		$data['data'] = $profesor;
		$this->response($data);
	}

	function deleteProfesor_post()
	{
		$this->load->model('Profesores_model', 'pro');
		$id_usu = $this->post('id');

		$this->pro->delete_by(array(
			'pro_id' => $id_usu
		));

		$profesores = $this->pro->get_all();
		$resp['mensaje'] = 'Profesor eliminada correctamente';
		$resp['data'] = $profesores;
		$resp['sucess'] = true;
		$this->response($resp);
	}

	function deleteprofesor_program_post(){
		$this->load->model('Rel_prof_progra_model', 'relp');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');

		$programa = $this->post('programa');
		$tipo = $this->post('tipo');
		$profesor = $this->post('profesor');
	
		//eliminamos profesor inscrito
		$this->relp->delete_by(array(
			'fk_programa' => $programa,
			'fk_tipo_programa' => $tipo,
			'fk_profesor' => $profesor
		));
		
	

		if($tipo ==1){
			$profesores = $this->relp->profesores_carrera($programa,$tipo);
			$carrera = $this->car->get_by(array(
				'id' => $programa
			));

			

			$name_program = $carrera->nombre;
		}elseif($tipo ==2){
			$profesores = $this->relp->profesores_curso($programa,$tipo);
		
			$curso = $this->cur->get_by(array(
				'id' => $programa
			));

			$name_program = $curso->nombre;
		}elseif($tipo ==3){

			$profesores = $this->relp->profesores_diplomado($programa,$tipo);
		
			$diplo = $this->dip->get_by(array(
				'id' => $programa
			));

			$name_program = $diplo->nombre;
		}
		$data['success'] = true;
		$data['mensaje'] = 'Profesor eliminado exitosamente';
		$data['profesores'] = $profesores;
		$data['name_program'] = $name_program;
		$this->response($data);

	}

	function InscripcionProfesor_post()
	{
		$this->load->model('Profesores_model', 'pro');
		$this->load->model('Rel_prof_progra_model', 'relp');
		$tipo = $this->post('tipo');
		$id = $this->post('id');
		$data = $this->post('data');
		$correo = $data['correo'];
		//validamos que el profesor exista y este activo
		$profesor = $this->pro->get_by(array(
			'pro_email' => $correo
		));

		if (!$profesor) {
			$response['sucess'] = false;
			$response['mensaje'] = "No existe un profesor registrado con el correo: " . $correo . " ";
		} else {

			$val_ins = $this->relp->count_by(array(
				'fk_profesor' => $profesor->pro_id,
				'fk_programa' => $id,
				'fk_tipo_programa' => $tipo
			));

			if ($val_ins == 0) {
				$this->relp->insert(array(
					'fk_profesor' => $profesor->pro_id,
					'fk_programa' => $id,
					'fk_tipo_programa' => $tipo,
					'fecha_ins' => date('y-m-d')
				));
				$response['sucess'] = true;
				$response['mensaje'] = "Profesor inscrito correctamente";
			} else {
				$response['sucess'] = false;
				$response['mensaje'] = "Ya se encuentra inscrito este profesor en este programa.";
			}
		}
		$this->response($response);
	}



	function profesoresPrograma_post(){
		$this->load->model('Rel_prof_progra_model', 'relp');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');

		$programa = $this->post('programa');
		$tipo = $this->post('tipo');
		
		
		
		if($tipo ==1){
			$profesores = $this->relp->profesores_carrera($programa,$tipo);
			$carrera = $this->car->get_by(array(
				'id' => $programa
			));

			$name_program = $carrera->nombre;
		}elseif($tipo ==2){
			$profesores = $this->relp->profesores_curso($programa,$tipo);
		
			$curso = $this->cur->get_by(array(
				'id' => $programa
			));

			$name_program = $curso->nombre;
		}elseif($tipo ==3){

			$profesores = $this->relp->profesores_diplomado($programa,$tipo);
		
			$diplo = $this->dip->get_by(array(
				'id' => $programa
			));

			$name_program = $diplo->nombre;
		}
		$data['success'] = true;
		$data['profesores'] = $profesores;
		$data['name_program'] = $name_program;
		$this->response($data);
		
	}

	function cant_programas_post(){
		$this->load->model('Rel_prof_progra_model', 'relp');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');

		$relcar = $this->relp->get_many_by(array(
			'fk_profesor' => $this->post('profesor')
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

	function carrerasProf_post(){
		$this->load->model('Rel_prof_progra_model', 'relp');
		$this->load->model('Carreras_model', 'car');
		$this->load->model('Cursos_model', 'cur');
		$this->load->model('Diplomados_model', 'dip');
		

		$relcar = $this->relp->order_by('fk_tipo_programa')->get_many_by(array(
			'fk_profesor' => $this->post('profesor')

		));
		$respons = array();
		$respons ['carreras'] = array();
		$respons ['cursos'] = array();
		$respons ['diplomados'] = array();
		foreach($relcar as $value){
			if($value->fk_tipo_programa == 1){
				$respons['carreras'][] = $this->car->get_by(array(
					'id' => $value->fk_programa
				));
			}

			if($value->fk_tipo_programa == 2){
				$respons['cursos'][] = $this->cur->get_by(array(
					'id' => $value->fk_programa
				));
			}

			if($value->fk_tipo_programa == 3){
				$respons['diplomados'][] = $this->dip->get_by(array(
					'id' => $value->fk_programa
				));
			}

		}
		
	
		
		$data['success'] = true;
		$data['data'] = $respons;
		$this->response($data);
	}

	function listarTrabajos_post(){
		$this->load->model('Trabajos_model', 'tra');
		$this->load->model('Profesores_model', 'pro');

		
		$profesor = $this->post('profesor');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$tipo_doc = $this->post('tipo_doc');

	
		$trabajos = $this->tra->get_many_by(array(
			'fk_programa'=>$programa,
			'fk_tipo_programa'=>$tipo,
			'tipo_doc'=>$tipo_doc,
			'fk_profesor'=>$profesor
		));

		if(count($trabajos)>0){
			$profesor = $this->pro->get_by(array(
				'pro_id' =>$trabajos[0]->fk_profesor
			));
	
			$data['trabajos'] = $trabajos;
			$data['profesor'] = $profesor;
			$data['sucess'] = true;
			$data['mensaje'] = 'ok';

		}else{
			$data['sucess'] = false;
			$data['mensaje'] = 'ok';
	

		}

		$this->response($data);		
	}

	public function crearTrabajo_post()
	{

		$this->load->model('Trabajos_model', 'tra');
		$this->load->library('upload');

		$data1 = json_decode($this->post('data'));
		
		$id_trabajo = json_decode($this->post('id_trabajo'));
		$tipo = json_decode($this->post('tipo'));
		$profesor = json_decode($this->post('profesor'));
		$programa = json_decode($this->post('programa'));
		$tipo_doc = json_decode($this->post('tipo_doc'));
		
		$id = $this->tra->insert(array(
			"nombre" => $data1->data->nombre,
			"descripcion" =>  $data1->data->descrip,
			"fecha_creacion" =>  date('Y-m-d H:i:s'),
			"fecha_inicio" =>  $data1->data->fechaini,
			"fecha_fin" => $data1->data->fechafin,
			"fk_tipo_programa" => $tipo,
			"fk_programa" => $programa,
			"fk_profesor" => $profesor,
			'estado' => 1,
			'tipo_doc' => $tipo_doc
			
		));

	

				//carga de archivos
				if (!empty($_FILES)) {
					echo 'Si';
					exit;
					$carpeta = 'imagenes/trabajos/' . $id;
		
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
		
							$this->tra->update_by(array('id' => $id), array('ruta_arch' => $carpeta . '/' . $fil->file_name));
							$resp['imagenes' . $k] = true;
						}
					}
				}
		$resp['data'] = $this->tra->get_by(array("id" => $id));;
		$resp['ok'] = true;

		$this->response($resp);
	}

	
	function TrabajoId_post()
	{
		$this->load->model('Trabajos_model', 'tra');
		$id = $this->post('id');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');
		
		$examen = $this->tra->get_by(array(
			'id'=>$id
		));
		
		$data['ok'] = true;
		$data['trabajo'] = $examen;
		$this->response($data);
	}

	public function editarTrabajo_post()
	{
		$this->load->model('Trabajos_model', 'tra');
		$this->load->library('upload');
	
		$data1 = json_decode($this->post('data'));
		$id = json_decode($this->post('id_trabajo'));
		$tipo = json_decode($this->post('tipo'));
		$profesor = json_decode($this->post('profesor'));
		$programa = json_decode($this->post('programa'));
		$tipo_doc = json_decode($this->post('tipo_doc'));

		$this->tra->update_by(array('id' => $id), array(
			"nombre" => $data1->data->nombre,
			"descripcion" =>  $data1->data->descrip,
			"fecha_creacion" =>  date('Y-m-d H:i:s'),
			"fecha_inicio" =>  $data1->data->fechaini,
			"fecha_fin" => $data1->data->fechafin,
			"fk_tipo_programa" => $tipo,
			"fk_programa" => $programa,
			"fk_profesor" => $profesor,
			'estado' => 1,
			'tipo_doc'=>$tipo_doc
			
		));

		
				//carga de archivos
				if (!empty($_FILES)) {
					$carpeta = 'imagenes/trabajos/' . $id;
		
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
		
							$this->tra->update_by(array('id' => $id), array('ruta_arch' => $carpeta . '/' . $fil->file_name));
							$resp['imagenes' . $k] = true;
						}
					}
				}

		

		$resp['resp'] = $this->tra->get_by(array('id' => $id));
		$resp['mensaje'] = 'Examen editado exitosamente';
		$resp['ok'] = true;

	
		$this->response($resp);
	}

	public function eliminarTrabajo_post(){
		$this->load->model('Trabajos_model', 'tra');
		$id = $this->post('id');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');
		$tipo_doc = $this->post('tipo_doc');
			
		 $this->tra->delete_by(array(
			'id'=>$id
		));

		
		$trabajos = $this->tra->get_many_by(array(
			'fk_profesor'=>$profesor,
			'fk_programa'=>$programa,
			'fk_tipo_programa'=>$tipo,
			'tipo_doc'=>$tipo_doc
		));


		$data['trabajos'] = $trabajos;
		$data['ok'] = true;
		$this->response($data);
	}

	function resultadosExamen_post(){
		$this->load->model('examenes_model', 'exa');
		$resultados = $this->exa->resultados_examenes($this->post('id_examen'));
		
		if($resultados){
			$resp['sucess']  = true;
			$resp['mensaje']  = 'OK';
			$resp['resultados'] = $resultados;
		}else{
			$resp['sucess']  = false;
			$resp['mensaje']  = 'No hay resultados presentados disponibles.';
			$resultados['resultados'] = '';
		}
		$this->response($resp);
	}

	function verResultado_post(){
		
		$this->load->model('examenes_model', 'exa');
		$this->load->model('Respuestas_alum_model', 'res');
		$this->load->model('respuestas_model', 'resp');

		$id_pres = $this->post('id_pres');
		$info = $this->exa->info_examen($id_pres);
		

		
		$preguntas = $this->exa->preg_presen($id_pres);
		
		foreach($preguntas as $k => $value){
			

			$resp_alum = $this->res->get_by(array(
				'fk_presentacion' => $id_pres,
				'fk_pregunta'=>$value->id_pregunta
			));

	
			if($value->tipo_pregunta==1){
				
				$tipo_preg = 'Opción multiple';
				$contestada = $this->resp->get_by(array(
					'fk_pregunta' => $value->id_pregunta,
					'orden' => $resp_alum->respuesta
				));

				$correcta = $this->resp->get_by(array(
					'fk_pregunta' => $value->id_pregunta,
					'orden' => $resp_alum->respuesta_true
				));

				$contestada = $contestada->texto_respuesta;
				$correcta = $correcta->texto_respuesta;
				
			}else if($value->tipo_pregunta==2){
			
				$tipo_preg = 'Verdadero o falso';
				$contestada = $resp_alum->respuesta;
				$correcta = $resp_alum->respuesta_true;
				
			}elseif($value->tipo_pregunta==3){
				
				$tipo_preg = 'Abierta';
			
			}

			if($resp_alum->result_respuesta==1){
				$calificacion = 'Correcta';
			}else{
				$calificacion = 'Incorrecta';
			}
			
			$value->tipo_pregunta = $tipo_preg;
			$value->resp_alumno = $contestada;
			$value->resp_true = $correcta;
			$value->calificacion = $calificacion;
	

		}

		$data['info'] = $info;
		$data['preguntas'] = $preguntas;
		$data['sucess'] = true;

		
		$this->response($data);
		

		
	}


	function listarVideos_post(){
		$this->load->model('Videos_model', 'vi');
		$this->load->model('Profesores_model', 'pro');

		
		$profesor = $this->post('profesor');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$tipo_doc = $this->post('tipo_doc');

	
		$videos = $this->vi->get_many_by(array(
			'vid_fk_profesor'=>$profesor,
			'vid_fk_programa'=>$programa,
			'vid_fk_tipo_programa'=>$tipo
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

	function VideoId_post()
	{
		$this->load->model('Videos_model', 'vi');
		$id = $this->post('id');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');
		
		$video = $this->vi->get_by(array(
			'vid_id'=>$id
		));
		
		$data['ok'] = true;
		$data['video'] = $video;
		$this->response($data);
	}

	function crearVideo_post(){
		$this->load->model('Videos_model', 'vi');
		$data = $this->post('data');
		$id_video = $this->post('id_video');
		$tipo_prog = $this->post('tipo');
		$profesor = $this->post('profesor');
		$programa = $this->post('programa');



		if($id_video == 0){

			$id = $this->vi->insert(array(
				"vid_titulo" => $data['titulo'],
				"vid_descripcion" =>  $data['descrip'],
				"vid_fk_profesor" =>  $profesor,
				"vid_fk_programa" =>  $programa,
				"vid_fk_tipo_programa" => $tipo_prog,
				"vid_fecha_creacion" =>  date('Y-m-d H:i:s'),
				"vid_ruta" =>  $data['ruta'],
				'vid_estado' => 0
				
			));

		}else{
		
			$id = $this->vi->update_by(array(
				"vid_id" => $id_video
			),array(
				
				"vid_descripcion" =>  $data['descrip'],
				"vid_ruta" =>  $data['ruta'],
				'vid_estado' => 0
				
			));

		}

		$resp['ok'] = true;

		$this->response($resp);
	}

	function eliminarVideo_post(){

		$this->load->model('Videos_model', 'vi');
		$id = $this->post('id_video');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');
		
			
		 $this->vi->delete_by(array(
			'vid_id'=>$id
		));

		
		$videos = $this->vi->get_many_by(array(
			'vid_fk_profesor'=>$profesor,
			'vid_fk_programa'=>$programa,
			'vid_fk_tipo_programa'=>$tipo
		));


		$data['videos'] = $videos;
		$data['ok'] = true;
		$this->response($data);
	}

	function cambiarestadoVideo_post(){

		$this->load->model('Videos_model', 'vi');
		$id_video = $this->post('id_video');
		$estado = $this->post('estado');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');

		if($estado == 0){
			$cambio = 1;
		}elseif($estado == 1){
			$cambio = 0;
		}

		$id = $this->vi->update_by(array(
			"vid_id" => $id_video
		),array(
			
			'vid_estado' => $cambio
			
		));

		$videos = $this->vi->get_many_by(array(
			'vid_fk_profesor'=>$profesor,
			'vid_fk_programa'=>$programa,
			'vid_fk_tipo_programa'=>$tipo
		));


		$data['videos'] = $videos;
		$data['ok'] = true;
		$this->response($data);

		
	}

	function cargadosTrabajo_post(){
		$this->load->model('rel_trabajos_alumnos_model', 'rel');
		$this->load->model('Trabajos_model', 'tra');
		$cargados = $this->rel->cargados_alumnos($this->post('idtrabajo'));
		if(count( $cargados)>0){
			$data['cargados'] = $cargados;
			$data['success'] = true;
		}else{
			$data['cargados'] = '';
			$data['success'] = false;
		}
		
		$this->response($data);
	}

	function listarclases_post(){
		$this->load->model('Clases_model', 'cla');
		$this->load->model('Profesores_model', 'pro');
	
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');

	
		$clases = $this->cla->get_many_by(array(
			'fk_profesor'=>$profesor,
			'fk_programa'=>$programa,
			'fk_tipo_programa'=>$tipo
		));

		$profesor_data = $this->pro->get_by(array("pro_id" => $profesor));

		if(count($clases)>0){
			$data['success'] = true;
			$data['clases'] = $clases;
			$data['profesor_data'] = $profesor_data;
		}else{
			$data['success'] = false;
			$data['clases'] = '';
			$data['profesor_data'] = '';
		}
		
		$this->response($data);
		
	}

	function crearClase_post(){
		$this->load->model('Clases_model', 'cla');
		$this->load->model('Profesores_model', 'pro');
	
		$data = $this->post('data');
		$id_clase = $this->post('id_clase');
		$tipo_prog = $this->post('tipo');
		$profesor = $this->post('profesor');
		$programa = $this->post('programa');
		
		if($id_clase == 0){

			$id = $this->cla->insert(array(
				"clas_nombre" => $data['nombre'],
				"clas_descripcion" =>  $data['descrip'],
				"clas_fecha_inicio" =>   $data['fechaini'],
				"clas_hora" =>  $data['hora'],
				"clas_fecha_creacion" =>  date('Y-m-d H:i:s'),
				"fk_tipo_programa" => $tipo_prog,
				"fk_programa" =>  $programa,
				'fk_profesor' => $profesor,
				'estado' => 0,
			));

		}else{
		
			$id = $this->cla->update_by(array(
				"clas_id" => $id_clase
			),array(
				
				"clas_nombre" => $data['nombre'],
				"clas_descripcion" =>  $data['descrip'],
				"clas_fecha_inicio" =>   $data['fechaini'],
				"clas_hora" =>  $data['hora'],
				"clas_fecha_creacion" =>  date('Y-m-d H:i:s'),
				"fk_tipo_programa" => $tipo_prog,
				"fk_programa" =>  $programa,
				'fk_profesor' => $profesor,
				'estado' => 0,
				
			));

		}

		$profesor_data = $this->pro->get_by(array("pro_id" => $profesor));
		$data['profesor_data'] = $profesor_data;
		$resp['sucess'] = true;

		$this->response($resp);

	}

	function ClaseId_post(){
		$this->load->model('Clases_model', 'cla');
		$id = $this->post('id');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');
		
		$clase = $this->cla->get_by(array(
			'clas_id'=>$id
		));
		
		$data['ok'] = true;
		$data['clase'] = $clase;
		$this->response($data);
	}

	function eliminarClase_post(){
		$this->load->model('Clases_model', 'cla');

		$id = $this->post('id');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');

			
		 $this->cla->delete_by(array(
			'clas_id'=>$id
		));



		
		$clases = $this->cla->get_many_by(array(
			'fk_profesor'=>$profesor,
			'fk_programa'=>$programa,
			'fk_tipo_programa'=>$tipo
		));

	
		$data['clases'] = $clases;
		$data['ok'] = true;
		$this->response($data);
	}

	function estadoClases_post(){
		$this->load->model('Clases_model', 'cla');
		$this->load->model('Profesores_model', 'pro');
		$estado = $this->post('estado');
		$idclase = $this->post('id_clase');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');

		if($estado == 0){
			$newestado = 1;
		}else if($estado == 1){
			$newestado = 0;
		}

		$this->cla->update_by(array(
			'clas_id'=>$idclase
		),array(
			'estado' => $newestado
		));

		$clases = $this->cla->get_many_by(array(
			'fk_profesor'=>$profesor,
			'fk_programa'=>$programa,
			'fk_tipo_programa'=>$tipo
		));

		
		$clases = $this->cla->get_many_by(array(
			'fk_profesor'=>$profesor,
			'fk_programa'=>$programa,
			'fk_tipo_programa'=>$tipo
		));

		$profesor_data = $this->pro->get_by(array("pro_id" => $profesor));

		if(count($clases)>0){
			$data['success'] = true;
			$data['clases'] = $clases;
			$data['profesor_data'] = $profesor_data;
		}else{
			$data['success'] = false;
			$data['clases'] = '';
			$data['profesor_data'] = '';
		}
		
		$this->response($data);
		

	}
}
