 <?php
class Rel_prof_progra_model extends MY_Model {
	
	protected $_table = 'rel_prof_programas';
	function __construct(){
        // Call the Model constructor
        parent::__construct();
	}

	function profesores_carrera($programa, $tipo){
		$this->db->select('pro.pro_id AS id , pro.pro_identificacion AS identifi , CONCAT(pro.pro_nombres, " ", pro.pro_apellidos) AS nombre , pro.pro_email AS email , pro.pro_telefono AS telefono , pro.pro_estado AS estado');
		$this->db->from('rel_prof_programas rel');
		$this->db->join('profesores pro', 'rel.fk_profesor = pro.pro_id');
		$this->db->join('carreras car', 'car.id = rel.fk_programa');
		$this->db->where(array(
			'rel.fk_programa'=>$programa,
			'rel.fk_tipo_programa'=>$tipo
		)); 
		$result = $this->db->get();
	    return $result->result();
	}

	function profesores_curso($programa, $tipo){
		$this->db->select('pro.pro_id AS id , pro.pro_identificacion AS identifi , CONCAT(pro.pro_nombres, " ", pro.pro_apellidos) AS nombre , pro.pro_email AS email , pro.pro_telefono AS telefono , pro.pro_estado AS estado');
		$this->db->from('rel_prof_programas rel');
		$this->db->join('profesores pro', 'rel.fk_profesor = pro.pro_id');
		$this->db->join('cursos car', 'car.id = rel.fk_programa');
		$this->db->where(array(
			'rel.fk_programa'=>$programa,
			'rel.fk_tipo_programa'=>$tipo
		)); 
		$result = $this->db->get();
	    return $result->result();
	}

	function profesores_diplomado($programa, $tipo){
		$this->db->select('pro.pro_id AS id , pro.pro_identificacion AS identifi , CONCAT(pro.pro_nombres, " ", pro.pro_apellidos) AS nombre , pro.pro_email AS email , pro.pro_telefono AS telefono , pro.pro_estado AS estado');
		$this->db->from('rel_prof_programas rel');
		$this->db->join('profesores pro', 'rel.fk_profesor = pro.pro_id');
		$this->db->join('diplomados car', 'car.id = rel.fk_programa');
		$this->db->where(array(
			'rel.fk_programa'=>$programa,
			'rel.fk_tipo_programa'=>$tipo
		)); 
		$result = $this->db->get();
	    return $result->result();
	}
	
	}
