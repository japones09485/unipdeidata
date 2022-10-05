<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require(APPPATH.'libraries/Rest_Controller.php');
require(APPPATH.'libraries/Format.php');

class Auth extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
		header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		
    }

    function getperfiles_get(){
        $this->load->model('perfiles_model','perf');
        $perfiles = $this->perf->get_all();
        $data['sucess'] = true;
        $data['data'] = $perfiles;
        $this->response($data);
    }

	function getnameperfiles_get(){
		$this->load->model('perfiles_model','perf');
		$id = $this->post('id');
        $nombre = $this->perf->getnameperfiles($id);
        $data['sucess'] = true;
        $data['data'] = $nombre;
        $this->response($data);
	}

    public function login_post()
    {
        $this->load->model('Usuarios_model', 'u');
		$this->load->model('Profesores_model', 'pro');
		$this->load->model('Alumnos_model', 'alum');

        $usuario = $this->post('usuario');
        $data = array();
        $contrasenia = $this->post('password');
        $perfil = $this->post('perfil');
		
		if($perfil == 1){
			$cont_user = $this->u->count_by(array(
				'usu_email' => $usuario,
				'usu_estado_verificacion'=>1,
				'usu_textoclaro' => $contrasenia,
				'usu_perfil'=>$perfil,
				'usu_estado'=>1
			));
		}elseif($perfil == 2){
			$cont_user = $this->pro->count_by(array(
				'pro_email' => $usuario,
				'pro_estado_verificacion'=>1,
				'pro_textoclaro' => $contrasenia,
				'pro_perfil'=>$perfil,
				'pro_estado'=>1
			));
            
		}elseif($perfil == 3){
			$cont_user = $this->alum->count_by(array(
				'alum_email' => $usuario,
				'alum_estado_verificacion'=>1,
				'alum_textoclaro' => $contrasenia,
				'alum_perfil'=>$perfil,
				'alum_estado'=>1
			));

            
		}


        if($cont_user>0){

			if($perfil == 1){
				$user = $this->u->get_by(array(
					'usu_email' => $usuario,
					'usu_estado_verificacion'=>1,
					'usu_perfil'=>$perfil,
					'usu_estado'=>1 
				));

				$estado_v = $user->usu_estado;

			}elseif($perfil == 2){
				$user = $this->pro->get_by(array(
					'pro_email' => $usuario,
					'pro_estado_verificacion'=>1,
					'pro_perfil'=>$perfil,
					'pro_estado'=>1 
				));
				$estado_v = $user->pro_estado;

			}elseif($perfil == 3){
				$user = $this->alum->get_by(array(
					'alum_email' => $usuario,
					'alum_estado_verificacion'=>1,
					'alum_perfil'=>$perfil,
					'alum_textoclaro' => $contrasenia,
				));
				
				$estado_v = $user->alum_estado;
			}
          
           

            //validar activo
		
            if($estado_v == 0){
                $data['status'] = false;
                $data['mensaje'] = 'Usuario Inactivo por favor ponerse en contacto con unipdei.com';

            }else{
				if($perfil == 1){

                $data['usu_id'] = $user->usu_id;
                $data['usu_nombres'] = $user->usu_nombres;
                $data['usu_apellidos'] = $user->usu_apellidos;
                $data['usu_email'] = $user->usu_email;
                $data['usu_perfil'] = $user->usu_perfil;
				$usu_password = $user->usu_password;
				$idUsu = $user->usu_id;
				$nomUsu = $user->usu_nombres . ' ' . $user->usu_apellidos;
				$perfilUsu = $user->usu_perfil;
               
				}elseif($perfil == 2){
				$data['usu_id'] = $user->pro_id;
                $data['usu_nombres'] = $user->pro_nombres;
                $data['usu_apellidos'] = $user->pro_apellidos;
                $data['usu_email'] = $user->pro_email;
                $data['usu_perfil'] = $user->pro_perfil;
				$usu_password = $user->pro_password;
				$idUsu = $user->pro_id;
				$nomUsu = $user->pro_nombres . ' ' . $user->pro_apellidos;
				$perfilUsu = $user->pro_perfil;
               
				}elseif($perfil == 3){
					$data['usu_id'] = $user->alum_id;
					$data['usu_nombres'] = $user->alum_nombres;
					$data['usu_apellidos'] = $user->alum_apellidos;
					$data['usu_email'] = $user->alum_email;
					$data['usu_perfil'] = $user->alum_perfil;
					$usu_password = $user->alum_password;
					$idUsu = $user->alum_id;
					$nomUsu = $user->alum_nombres . ' ' . $user->alum_apellidos;
					$perfilUsu = $user->alum_perfil;
				   
				}
			
                $passSYSTEM = md5($contrasenia);
				
				
                if (is_object($user) and  $usu_password=== $passSYSTEM) {
                    unset($usu_password);
                    $creatorJWT = new CreatorJWT();
                    $tokenData['X-API-KEY'] = 'japones';
                    $tokenData['nombre'] = $nomUsu;
                    $tokenData['perfil'] = $perfilUsu;
                    $tokenData['timeStamp'] = Date('Y-m-d h:i:s');
                    $jwtToken = $creatorJWT->GenerateToken($tokenData);
                    $data['status'] = true;
                    $data['mensaje'] = 'Credenciales Correctas';
                    $data['token'] = $jwtToken;
                    $data['user'] = $data;
                    
                } else {
                    $data['status'] = false;
                    $data['mensaje'] = 'Credenciales Incorrectas';
                }
            }

        }else{
            $data['status'] = false;
            $data['mensaje'] = 'Credenciales Incorrectas';
        }
		
        $this->response($data);
    }

	 public function verifyToken_get()
    {
        $creatorJWT = new CreatorJWT();
        $received_Token = $this->input->request_headers();
		echo '<pre>';
		print_r($received_Token);
		exit;
		
        if (!isset($received_Token['X-API-KEY'])) {
            http_response_code('401');
            $this->response(array('status' => false, "message" => 'NO ESTA'));
            exit;
        }

        try {
            $rkAr = explode(' ', $received_Token['X-API-KEY']);
            $jwtData = $creatorJWT->DecodeToken($rkAr[1]);
            $this->response(array(
                'status' => true,
                'data' => $jwtData
            ));
        } catch (Exception $e) {
            http_response_code('401');
            $this->response(array('status' => false, "message" => $e->getMessage()));
            exit;
        }
    }

	
       
}
