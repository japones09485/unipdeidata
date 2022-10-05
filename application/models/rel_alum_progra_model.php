 <?php
class Rel_alum_progra_model extends MY_Model {
	
	protected $_table = 'rel_alumnos_programas';
	function __construct(){
        // Call the Model constructor
        parent::__construct();
	}

	function alumnos_carrera($tipo,$programa,$profesor){
		$this->db->select('*');
		$this->db->from('rel_alumnos_programas rel');
		$this->db->join('alumnos alu', 'rel.fk_alumno = alu.alum_id');
		if($tipo == 1){
			$this->db->join('carreras car', 'car.id = rel.fk_programa');
		}else if($tipo == 2){
			$this->db->join('cursos car', 'car.id = rel.fk_programa');
		}else if($tipo == 3){
			$this->db->join('diplomados car', 'car.id = rel.fk_programa');
		}
		
		$this->db->where(array(
			'rel.fk_programa'=>$programa,
			'rel.fk_tipo_programa'=>$tipo,
			'rel.fk_profesor'=>$profesor
		)); 
		$result = $this->db->get();
	    return $result->result();
	}


	
	}
