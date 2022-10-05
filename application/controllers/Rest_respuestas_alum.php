<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require(APPPATH . 'libraries/Rest_Controller.php');
require(APPPATH . 'libraries/Format.php');

class Rest_respuestas_alum extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, authorization, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
	}


	function guardarRespuestas_post()
	{
		$this->load->model('Respuestas_alum_model', 'res');
		$this->load->model('respuestas_model', 're');
		$this->load->model('presen_examen_model', 'pre');
		$this->load->model('examenes_model', 'exa');
		$this->load->model('resultados_examen_model', 'resul');

		$data = $this->post('data');
		$alumno = $this->post('alumno');

		$idexamen = $this->post('idexamen');
		$respuestas = array();
		$valid = true;

		$examen = $this->exa->get_by(array(
			'id_examen' => $idexamen
		));

		$paratrue = $examen->num_preg_aprobar;

		foreach ($data as $key => $value) {
			$indice = explode('_', $key);

			if ($indice[0] == 'respuesta') {
				if (!$value) {
					$valid = false;
				}
				$respuestas[$indice[1]]['tipo_pregunta'] = $indice[2];
				$respuestas[$indice[1]]['valor'] = $value;
			}
		}

		if ($valid == true) {

			//validar tiempo examen
			$present = $this->pre->get_by(array(
				'fk_examen' => $idexamen,
				'fk_alumno' => $alumno,
				'estado' => 0
			));

			$fecha_actual = date('Y-m-d h:i:s');

			if ($present->fecha_inicio < $fecha_actual and $present->fecha_fin > $fecha_actual) {
				$this->res->delete_by(array(
					'fk_examen' => $idexamen,
					'fk_presentacion' => $present->id_presentacion,
					'fk_alumno' => $alumno
				));
				foreach ($respuestas as $k => $v) {
					if ($v['tipo_pregunta'] == 1) {
						$aux = 5;
					} else {
						$aux = 0;
					}

					$respuesta = $this->re->get_by(array(
						'fk_examen' => $idexamen,
						'fk_pregunta' => $k,
						'orden' => $aux
					));

					if ($v['tipo_pregunta'] != 3) {
						if ($respuesta->texto_respuesta == $v['valor']) {
							$result = true;
						} else {
							$result = false;
						}
					} else {
						$result = true;
					}

					$this->res->insert(array(
						'fk_examen' => $idexamen,
						'fk_presentacion' => $present->id_presentacion,
						'fk_pregunta' => $k,
						'fk_tipo_pregunta' => $v['tipo_pregunta'],
						'fk_alumno' => $alumno,
						'respuesta' => $v['valor'],
						'respuesta_true' => $respuesta->texto_respuesta,
						'result_respuesta' => $result
					));
				}
				$this->pre->update_by(array(
					'id_presentacion' => $present->id_presentacion
				), array(
					'estado' => 1,
					'fecha_finalizacion' => $fecha_actual
				));

				$res_fin = $this->res->get_many_by(array(
					'fk_examen' => $idexamen,
					'fk_presentacion' => $present->id_presentacion
				));

				$totalpre = 0;
				$contok = 0;
				$contfalse = 0;
				foreach ($res_fin as $r) {
					$totalpre++;
					if ($r->result_respuesta == false and $r->fk_tipo_pregunta != 3) {
						$contfalse++;
					} else if ($r->result_respuesta == true and $r->fk_tipo_pregunta != 3) {
						$contok++;
					}
				}

				$cualita = 'Su resultado es: ' . $contok . '/' . $totalpre;
				if ($paratrue > $contok) {
					$nota = false;
				} else {
					$nota = true;
				}


				$this->resul->insert(array(
					'resul_fk_examen' => $idexamen,
					'resul_fk_presen' => $present->id_presentacion,
					'resul_fk_alumno' => $alumno,
					'resul_nota' => $nota,
					'result_cuantitativa' => $cualita
				));

				//envio de resultado a email
				$this->enviarResultados($alumno, $examen, $nota, $cualita);
				

				$mensaje = 'Examen guardado exitosamente';
			} else {
				$mensaje = 'Ha caducado el tiempo para responder el examen.';
				$this->pre->update_by(array(
					'fk_examen' => $idexamen,
					'fk_alumno' => $alumno
				), array(
					'estado' => '1'
				));
			}
		} else {
			$mensaje = 'Debe reponder todas las preguntas del examen';
		}


		$resp['mensaje'] = $mensaje;
		$resp['respuestas'] = $respuestas;
		$resp['sucess'] = $valid;
		$this->response($resp);
	}

	function enviarResultados($alumno, $examen, $nota, $cualita)
	{

		$this->load->model('usuarios_model', 'usu');
		$dataalumno = $this->usu->get_by(array(
			'usu_id' => $alumno
		));

		$nombre_alumno = $dataalumno->usu_nombres . ' ' . $dataalumno->usu_apellidos;
		$email_alumno = $dataalumno->usu_email;
		//validamos nota


		if ($nota == 1) {
			$nota = 'APROBADO';
		} else {
			$nota = 'REPROBADO';
		}

		$cabecera = 'City Fitness World resultado de evaluacion de : ' . $nombre_alumno;
		$body = 'Ha finalizado la presentación del examen: ' . $examen->nombre . ' con resultado:	' . $nota . ' - ' . $cualita;

		$config['protocol'] = 'sendmail';
		$config['mailtype'] = 'html';
		$config['charset']  = 'utf-8';
		$config['newline']  = "\r\n";


		$this->email->clear(TRUE);
		$this->email->initialize($config);
		$this->email->set_mailtype("html");
		$this->email->from('cityfitnessworld.contacto@cityfitnessworld.com', 'City Fitness World');
		$this->email->cc($email_alumno);
		$this->email->bcc('cityfitnessworld.contacto@cityfitnessworld.com');
		//$this->email->to($email_alumno);
		$this->email->subject($cabecera);
		$this->email->message($body);
		
		
		if ($this->email->send()) {

			return TRUE;
		
		} else {
			//echo $this->email->print_debugger();
			return FALSE;
		}
	
	}
}
