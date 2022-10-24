<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require(APPPATH . 'libraries/Rest_Controller.php');
require(APPPATH . 'libraries/Format.php');

class Rest_examenes extends REST_Controller
{
	private $campos = array(
		'nombre' => 'nombre'
	);

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, authorization, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
	}

	public function crearExamen_post()
	{

		$this->load->model('examenes_model', 'exa');
		
		$data = $this->post('data');
		$profesor = $this->post('profesor');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');

		$id = $this->exa->insert(array(
			"nombre" => $data['nombre'],
			"descripcion" => $data['descrip'],
			"fecha_creacion" =>  date('Y-m-d H:i:s'),
			"fk_profesor" =>  $profesor,
			'fk_tipo_programa' =>$tipo,
			'fk_programa' =>$programa,
			"fecha_inicio" =>  $data['fechaini'],
			"fecha_fin" =>  $data['fechafin'],
			"numero_preguntas" =>  $data['numpreg'],
			"num_preg_aprobar" =>  $data['numpregaprob'],
			'status_edit' => 0,
			'duracion' => $data['duracion'],
			'tipo_examen' => $data['tipo_examen']
			
		));

	
	
		$resp['resp'] = $this->exa->get_by(array('id_examen' => $id));
		$resp['mensaje'] = 'Examen creado exitosamente';
		$resp['ok'] = true;
		$this->response($resp);
	}


	public function editarExamen_post()
	{
		$this->load->model('examenes_model', 'exa');
		$id = $this->post('id_examen');
		$data = $this->post('data');
		$profesor = $this->post('profesor');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
	
		$this->exa->update_by(array('id_examen' => $id), array(
			"nombre" => $data['nombre'],
			"descripcion" =>  $data['descrip'],
			"fecha_creacion" =>  date('Y-m-d H:i:s'),
			"fk_profesor" => $profesor,
			"fecha_inicio" =>  $data['fechaini'],
			"fecha_fin" => $data['fechafin'],
			"numero_preguntas" => $data['numpreg'],
			"num_preg_aprobar" =>  $data['numpregaprob'],
			'status_edit' => 0,
			'duracion' => $data['duracion'],
			'tipo_examen' => $data['tipo_examen']
		));

		$resp['resp'] = $this->exa->get_by(array('id_examen' => $id));
		$resp['mensaje'] = 'Examen editado exitosamente';
		$resp['ok'] = true;
		$this->response($resp);
	}


	function listarActivos_get()
	{
		$this->load->model('examenes_model', 'exa');
		$pag = $this->get('pagina');

		if (empty($pag)) {
			$pag = 1;
		}
		$ini = ($pag - 1) * 20;
		$cantdat = count($this->exa->get_All());
		$cantdat = ceil($cantdat / 20);
		$data = $this->exa->limit(20, $ini)->get_All();
		$resp['lista'] = $data;
		$resp['ok'] = true;
		$resp['pag_actual'] = $pag;
		$resp['cant_pag'] = $cantdat;
		$this->response($resp);
	}


	function listarAll_post()
	{
		$this->load->model('Examenes_model', 'exa');
		
		$tipo = $this->post('tipo');
		$profesor = $this->post('profesor');
		$programa = $this->post('programa');
		$result = $this->exa->get_Examenes($tipo,$programa,$profesor,1);
		
		$resp['data'] = $result;
		$resp['sucess'] = true;
		
		$this->response($resp);
	}

	function listarExamenesAlum_post(){
		$this->load->model('Examenes_model', 'exa');
			
		$tipo = $this->post('tipo');
		$profesor = $this->post('profesor');
		$programa = $this->post('programa');
		$result = $this->exa->get_Examenes($tipo,$programa,$profesor,2);
		
		$resp['data'] = $result;
		$resp['sucess'] = true;
		$this->response($resp);
	}



	function misExamenes_post()
	{

		$this->load->model('examenes_model', 'exa');
		$usuario = $this->post('usuario');
		$pag = $this->get('pagina');
		if (empty($pag)) {
			$pag = 1;
		}
		$ini = ($pag - 1) * 20;
		$cantdat = $this->exa->count_by(array(
			'usuario_creacion' => $usuario
		));

		$cantdat = ceil($cantdat / 20);
		$data = $this->exa->limit(20, $ini)->get_many_by(array(
			'usuario_creacion' => $usuario
		));
		$resp['lista'] = $data;
		$resp['ok'] = true;
		$resp['pag_actual'] = $pag;
		$resp['cant_pag'] = $cantdat;
		$this->response($resp);
	}


	function eliminarExamen_post()
	{
		$this->load->model('examenes_model', 'exa');
		$this->load->model('preguntas_model', 'pre');
		$this->load->model('respuestas_model', 'res');
		$this->load->model('presen_examen_model', 'pres');
	
		$tipo = $this->post('tipo');
		$profesor = $this->post('profesor');
		$programa = $this->post('programa');
	
		$id_exa = $this->post('id_examen');
		$valid_prese = $this->pres->count_by(array(
			'fk_examen'=>$id_exa
		));
		
		if($valid_prese==0){
		$this->exa->delete_by(array('id_examen' => $id_exa));
		$this->pre->delete_by(array('fk_examen' => $id_exa));
		$this->res->delete_by(array('fk_examen' => $id_exa));
		$resp['sucess']  = true;
		$resp['mensaje'] = 'Examen eliminado exitosamente';
		}else{
			$resp['sucess']  = false;
			$resp['mensaje'] = 'No se puede eliminar el examen ya que ha sido presentado por mas de 1 alumno.';	
		}
		
		$examenes = $this->exa->get_Examenes($tipo,$programa,$profesor,1);
		$resp['examenes']  = $examenes;
		$this->response($resp);
	}


	function traerId_post()
	{
		$this->load->model('examenes_model', 'exa');
		$id = $this->post('id');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
	
		$examen = $this->exa->traerId($id);
		
		$data['ok'] = true;
		$data['examen'] = $examen;
		$this->response($data);
	}

	function estadoExamen_post()
	{
		$this->load->model('examenes_model', 'exa');
		$this->load->model('preguntas_model', 'pre');
		$this->load->model('respuestas_model', 'res');

		$id_exa = $this->post('id_examen');
		$estado = $this->post('estado');
		$tipo = $this->post('tipo');
		$programa = $this->post('programa');
		$profesor = $this->post('profesor');

		$valid = true;
		$contp = $this->pre->count_by(array(
			'fk_examen' => $id_exa,
			'fk_estado' => 0,
		));

		
		if ($contp > 0) {
			$valid = false;
		}
		//Validar que existan respuestas ya creadas
		$validpreg = $this->res->count_by(array(
			'fk_examen' => $id_exa,
		));

		if($validpreg == 0 ){
			$valid = false;

		}else{
			//Validar que las respuestas este ya configuradas
		
			$contr = $this->res->count_by(array(
				'fk_examen' => $id_exa,
				'fk_estado' => 0
			));
	
			if ($contr > 0) {
				$valid = false;
			}
		}
		

		if ($valid == true) {
			$this->exa->update_by(array(
				'id_examen' => $id_exa
			), array(
				'estado' => $estado

			));
			$resp['sucess']  = true;
		} else {
			$resp['sucess']  = false;
		}

		$examenes = $this->exa->get_many_by(array(
			'fk_profesor'=>$profesor,
			'fk_programa' =>$programa
		));

		$resp['examenes'] = $examenes;

	
		$this->response($resp);
	}

	function iniciarExamen_post()
	{
		$this->load->model('presen_examen_model', 'pre');
		$this->load->model('examenes_model', 'exa');

		$examen = $this->exa->get_by(array(
			'id_examen' => $this->post('idexamen')
		));
		$duracion = $examen->duracion;
		$duracion = $duracion . " minutes";

		$presen = $this->pre->count_by(array(
			'fk_examen' => $this->post('idexamen'),
			'fk_alumno' => $this->post('alumno'),
			'estado' => 0
		));

		if ($presen == 0) {

			$fechaAuxiliar  = strtotime($duracion, strtotime(date('Y-m-d h:i:s')));
			$fechaSalida   = date('Y-m-d H:i:s', $fechaAuxiliar);

			$this->pre->insert(array(
				'fk_examen' => $this->post('idexamen'),
				'fk_alumno' => $this->post('alumno'),
				'fecha_presentacion' => date('Y-m-d h:i:s'),
				'fecha_inicio' => date('Y-m-d h:i:s'),
				'fecha_fin' => $fechaSalida,
				'estado' => 0
			));
		}

		$resp['sucess']  = true;
		$this->response($resp);
	}




	function validar_prese_post()
	{
		$this->load->model('examenes_model', 'exa');
		$this->load->model('presen_examen_model', 'pre');
		$this->load->model('inscrip_recupera_model','ins');

		$fecha_actual = date('Y-m-d h:i:s');
		
		$examen = $this->exa->get_by(
			array(
				'id_examen' => $this->post('idexamen')
			)
		);

		$presen = $this->pre->get_by(array(
			'fk_examen' => $this->post('idexamen'),
			'fk_alumno' => $this->post('alumno'),
		));	
		
	//validamos examens regular

		if (!$presen) {
			$resp['sucess']  = true;
			$resp['mensaje']  = 'Por favor responda todas las preguntas, el tiempo limite para contestar el examen: ' . $examen->duracion . 'minutos.';
			$resp['estado']  = 0; //pendiente para presentar
		} else {

			if($examen->tipo_examen == 2){
				
				$valid = $this->ins->count_by(array(
					'fk_alumno'=> $this->post('alumno'),
					'fk_examen'=> $this->post('idexamen')
				));
			

				if($valid == 0){
				$resp['sucess']  = false;
				$resp['mensaje']  = 'Para presentar este examen de recuperación debe estar inscrito, Por favor ponerse en contacto con su instructor.';
				$resp['estado']  = 2; //finalizado y presentado
				$this->response($resp);
				}
			}

			if ($presen->estado == 0) {
				if ($fecha_actual >= $presen->fecha_inicio and $fecha_actual <= $presen->fecha_fin) {
					$resp['sucess']  = true;
					$resp['mensaje']  = 'Por favor responda todas las preguntas, la fecha y hora de caducidad es: ' . $presen->fecha_fin;
					$resp['estado']  = 0; //pendiente para presentar	
				} else {
					$this->pre->update_by(array(
						'id_presentacion' => $presen->id_presentacion
					), array(
						'estado' => 2
					));
					$resp['sucess']  = false;
					$resp['mensaje']  = 'El tiempo del examen a caducado.';
					$resp['estado']  = 2; //finalizado y presentad
				}
			} else if ($presen->estado == 1) {
				$result = $this->result_examen($presen->id_presentacion, $this->post('idexamen'));
				$resp['sucess']  = false;
				$resp['mensaje']  = $result['mensaje'];
				$resp['estado']  = 1; //finalizado y presentado		
			}else if($presen->estado == 2){
				$resp['sucess']  = false;
				$resp['mensaje']  = 'Ha vencido el tiempo para presentar el examen.';
				$resp['estado']  = 2; //finalizado y presentado
			}
		}
	
		$resp['presentacion'] = $presen;

		$this->response($resp);
	}

	function result_examen($id_pres, $id_examen)
	{
		$this->load->model('Respuestas_alum_model', 'res');
		$this->load->model('examenes_model', 'exa');
		$this->load->model('presen_examen_model', 'pre');

		$examen = $this->exa->get_by(array(
			'id_examen' => $id_examen
		));

		$respuestas = $this->res->get_many_by(
			array(
				'fk_examen' => $id_examen,
				'fk_presentacion' => $id_pres
			)
		);

		$totalpre = 0;
		$contok = 0;
		$contfalse = 0;
		foreach ($respuestas as $r) {
			$totalpre++;
			if ($r->result_respuesta == false and $r->fk_tipo_pregunta != 3) {
				$contfalse++;
			} else if ($r->result_respuesta == true and $r->fk_tipo_pregunta != 3) {
				$contok++;
			}
		}
		if ($contok >= $examen->num_preg_aprobar) {
			$resp['sucess']  = true;
			$resp['mensaje']  = 'Examen aprobado, calificación: ' . $contok . '/' . $totalpre;
		} else {
			$resp['sucess']  = false;
			$resp['mensaje']  = 'Examen reprobado, calificación: ' . $contok . '/' . $totalpre;
		}
		return $resp;
	}

	function Inscri_alum_examen_post(){
		$this->load->model('Alumnos_model', 'alum');
		$this->load->model('inscrip_recupera_model','ins');

		
		$data = $this->post('data');
		$idAlid = $this->post('aliado');
		$idExamen = $this->post('examen');
		$alumno  = $this->alum->get_by(array(
			'alum_email' => $data['correo'],
		));

		if ($alumno) {
			//validamos que no haya sido registrado
			$inscripcion = $this->ins->get_by(array(
				'fk_alumno' => $alumno->alum_id,
				'fk_profesor' => $idAlid,
				'fk_examen' => $idExamen
			));
			
			if ($inscripcion) {
			
					$data['sucess'] = false;
					$data['mensaje'] = 'El alumno ya se encuentra registrado.';
			
				
			} else {
				$this->ins->insert(array(
					'fk_examen' => $idExamen,
					'fk_alumno' => $alumno->alum_id,
					'fk_profesor' => $idAlid,
					'fecha_inscrip' => date('Y-m-d')
				));

			
				
				$data['sucess'] = true;
			
				$data['mensaje'] = "Alumno inscrito correctamente";
			}

		} else {
			$mensaje = 'Correo no registrado.';
			$data['sucess'] = false;
			$data['alumno'] = '';
			$data['mensaje'] = $mensaje;
		}

		$alumnos = $this->ins->alum_recuperacion($idExamen);
		$data['alumnos'] = $alumnos;

		$this->response($data);
	}

	function List_alum_examen_post(){
		$this->load->model('inscrip_recupera_model','ins');
		$alumnos = $this->ins->alum_recuperacion($this->post('id_examen'));
		$data['alumnos'] = $alumnos;
		$data['sucess'] = true;
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

	function eliminar_alum_examen_post(){
		$this->load->model('inscrip_recupera_model','ins');
		$this->ins->delete_by(array(
			'id_recupera'=>$this->post('id_rec')
		));
		$alumnos = $this->ins->alum_recuperacion($this->post('id_examen'));
		$data['alumnos'] = $alumnos;
		$data['sucess'] = true;
		$data['mensaje']  = 'Alumno eliminado exitosamente';
		$this->response($data);
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



	function filtrar_get()
	{
		$this->load->model('examenes_model', 'exa');
		$param = $this->get();

		foreach ($param as $w => $val) {
			$flag = array_search($w, $this->campos);
			if ($flag !== FALSE) {
				$where[$flag . ' LIKE'] = '%' . $val . '%';
			}
		}
		$sd = $this->exa->filtrar($where);

		if (!empty($sd)) {
			$resp['ok'] = true;
			$resp['lista'] = $sd;
		} else {
			$resp['ok'] = false;
			$resp['lista'] = $sd;
		}
		$this->response($resp);
	}
}
