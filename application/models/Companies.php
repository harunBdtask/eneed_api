<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Companies extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    #============Count Company=============#
    public function count_company()
    {
        return $this->db->count_all("company_information");
    }

    #=============Company List=============#
    public function company_list()
    {
        $this->db->select('*');
        $this->db->from('company_information');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    #==============Company search list==============#
    public function company_search_item($company_id)
    {
        $this->db->select('*');
        $this->db->from('company_information');
        $this->db->where('company_id', $company_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    #==============Company edit data===============#
    public function retrieve_company_editdata()
    {
        $this->db->select('*');
        $this->db->from('company_information');
//		$this->db->where('company_id',$company_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    #==============Update company==================#
    public function update_company($data, $company_id)
    {
        $this->db->select('*');
        $this->db->from('company_information');
        $company_info = $this->db->get()->result();
        if ($company_info) {
            $this->db->where('company_id', $company_id);
            $this->db->update('company_information', $data);
        } else {
            $this->db->insert('company_information', $data);
        }

        $this->db->select('*');
        $this->db->from('company_information');
        $this->db->where('status', 1);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $json_product[] = array('label' => $row->company_name, 'value' => $row->company_id);
        }
        $cache_file = './my-assets/js/admin_js/json/company.json';
        $productList = json_encode($json_product);
        file_put_contents($cache_file, $productList);
        return true;
    }
}