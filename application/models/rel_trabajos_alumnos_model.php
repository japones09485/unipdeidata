 <?php
class Rel_trabajos_alumnos_model extends MY_Model {
	
	protected $_table = 'rel_trabajos_alumnos';
	function __construct(){
        // Call the Model constructor
        parent::__construct();
	}

	
	function cargados_alumnos($idtrabajo){
		$this->db->select('t_trabajos.id as idtrabajo,t_trabajos.nombre as nombretrabajo,CONCAT(profesores.pro_nombres," ", profesores.pro_apellidos) as nomprofesor,CONCAT(alumnos.alum_nombres," ", alumnos.alum_apellidos) as nomalumno , alumnos.alum_email as correoalumno,rel_trabajos_alumnos.ruta_arch');
		$this->db->from('rel_trabajos_alumnos');
		$this->db->join('alumnos', 'rel_trabajos_alumnos.fk_alumno = alumnos.alum_id');
		$this->db->join('t_trabajos', 'rel_trabajos_alumnos.fk_trabajo = t_trabajos.id');
		$this->db->join('profesores', 'rel_trabajos_alumnos.fk_profesor = profesores.pro_id');
		$this->db->where(array(
			'rel_trabajos_alumnos.fk_trabajo'=>$idtrabajo,
		)); 
		$result = $this->db->get();
		return $result->result();	
	}

	
	
	}
