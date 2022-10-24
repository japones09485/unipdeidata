<?php
class mail_library {
	//funciones que queremos implementar en Miclase.
	function email_registro($email, $tipo)
{

	$CI =& get_instance();
	$CI->load->model('Profesores_model', 'pro');
	$codigo = md5($email);


	if ($tipo == 1) {
		$cabecera = 'Bienvenido a UNIPDEI ANDINA para validar su cuenta como PROFESOR en nuestra plataforma por favor dar click al siguiente enlace';
		//$body = 'https://cityfitnessworld.com/fitnes/inicio/verifycodigo/' . $codigo;
		
	} elseif($tipo == 2){
		$cabecera = 'Bienvenido a UNIPDEI ANDINA para validar su cuenta como ALUMNO en nuestra plataforma por favor dar click al siguiente enlace';
	}
	$body = 'https://unipdeiandina.com//rest_passw/'.$codigo;
	$profesor = $CI->pro->get_by(array('pro_email' => $email));

	$config['protocol'] = 'mail';
	$config['mailtype'] = 'html';
	$config['charset']  = 'utf-8';
	$config['smtp_host']  = "mail.unipdeiandina.com";
	$config['smtp_port']  = "465";
	$config['smtp_user']  = "contactounipdei";
	$config['smtp_pass']  = "contactounipdei***";
	$CI->email->initialize($config);
	$CI->email->from('Contacto@unipdeiandina.com','Unipdei Andina');
	$CI->email->to($email);
	$CI->email->subject($cabecera);
	$CI->email->message($body);
	$CI->email->send();
	
	}

 }
