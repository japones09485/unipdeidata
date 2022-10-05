 <?php
class Perfiles_model extends MY_Model {
	
	protected $_table = 'perfiles';
	function __construct(){
        // Call the Model constructor
        parent::__construct();
	}

	function getnameperfiles($id){
		$this->db->select('nombre');
		$this->db->from('perfiles');
		$this->db->where('id',$id); 
		$result = $this->db->get();
		return $result->row();
	}
	
	}
