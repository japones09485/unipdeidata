 <?php
class Alumnos_model extends MY_Model {
	
	protected $_table = 'alumnos';
	function __construct(){
        // Call the Model constructor
        parent::__construct();
	}

	function programasprof($tipo,$alumno){
		$this->db->select('prog.*,pro.pro_id,pro.pro_nombres,pro.pro_apellidos');
		$this->db->from('rel_alumnos_programas rel');
		
		if($tipo == 1){
			$this->db->join('carreras prog', 'prog.id = rel.fk_programa');
		}else if($tipo == 2){
			$this->db->join('cursos prog', 'prog.id = rel.fk_programa');
		}else if($tipo == 3){
			$this->db->join('diplomados prog', 'prog.id = rel.fk_programa');
		}
		
		$this->db->join('profesores pro', 'rel.fk_profesor = pro.pro_id');
		
		$this->db->where(array(
		
			'rel.fk_tipo_programa'=>$tipo,
			'rel.fk_alumno'=>$alumno,
			
		)); 
		$result = $this->db->get();
	    return $result->result();
	}
	
	}
