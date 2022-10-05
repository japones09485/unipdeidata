<?php
class Preguntas_model extends MY_Model {
	
	protected $_table = 't_preguntas';
	function __construct(){
        // Call the Model constructor
        parent::__construct();
	}

	function maxpreg($id_exam){

		
		$this->db->select("MAX(numero_pregunta) as max");
		$this->db->from('t_preguntas');
	
		$this->db->where(array(
			'fk_examen'=>$id_exam
		)); 
		$result = $this->db->get();
		return $result->row();
	}

	function maxid(){

		
		$this->db->select("MAX(id_pregunta) as max");
		$this->db->from('t_preguntas');
		$result = $this->db->get();
		return $result->row();
	}

}
