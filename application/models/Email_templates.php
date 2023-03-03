<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Email_templates extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	//Template List
	public function template_list()
	{
		$this->db->select('*');
		$this->db->from('email_template');
		$this->db->order_by('id','desc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Template entry
	public function template_entry($data)
	{
		$exs_te = $this->db->select('*')
				->from('email_template')
				->where('status',$data['status'])
				->get()
				->num_rows();

		if ($exs_te > 0) {
			return false;
		}else{
			$this->db->insert('email_template',$data);
			return TRUE;
		}
	}
	//Retrieve Template Edit Data
	public function retrieve_template_editdata($template_id)
	{
		$this->db->select('*');
		$this->db->from('email_template');
		$this->db->where('id',$template_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();	
		}
		return false;
	}	
	//Retrieve Template
	public function retrieve_template($status)
	{
		
		$this->db->select('*');
		$this->db->from('email_template');
		$this->db->where('status',$status);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();	
		}
		return false;
	}
	
	//Update Template
	public function update_template($data,$template_id)
	{

		$exs_te = $this->db->select('*')
				->from('email_template')
				->where('id !=',$template_id)
				->where('status',$data['status'])
				->get()
				->num_rows();
		if ($exs_te > 0) {
			return false;
		}else{
			$this->db->where('id',$template_id)
					->update('email_template',$data);
			return TRUE;
		}
	}

	// Delete Template item
	public function delete_template($template_id)
	{
		$this->db->where('id',$template_id);
		$this->db->delete('email_template'); 	
		return true;
	}

	public function get_template_names()
	{
		$this->db->select('*');
		$this->db->from('email_template');
		$this->db->order_by('name','asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
}