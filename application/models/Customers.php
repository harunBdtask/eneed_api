<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Customers extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	//Count customer
	public function count_customer()
	{
		return $this->db->count_all("customer_information");
	}
	//Customer List
	public function customer_list($per_page = null, $page = null, $mobile = null, $email = null, $customer = null)
	{
		$this->db->select('*');
		$this->db->from('customer_information');

		if ($mobile) {
			$this->db->like('customer_information.customer_mobile', $mobile, 'both');
		}

		if ($customer) {
			$this->db->like('customer_information.customer_name', $customer, 'both');
		}

		if ($email) {
			$this->db->like('customer_information.customer_email', $email, 'both');
		}

		$this->db->order_by('customer_code', 'desc');
		$this->db->limit($per_page, $page);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}
	public function customer_list_all($mobile = null, $email = null, $customer = null)
	{
		$this->db->select('*');
		$this->db->from('customer_information');

		if ($mobile) {
			$this->db->like('customer_information.customer_mobile', $mobile, 'both');
		}

		if ($customer) {
			$this->db->like('customer_information.customer_name', $customer, 'both');
		}

		if ($email) {
			$this->db->like('customer_information.customer_email', $email, 'both');
		}

		$this->db->order_by('customer_code', 'desc');
		//$this->db->limit($per_page,$page);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}
	//Customer Count
	public function customer_count($mobile = null, $email = null, $customer = null)
	{
		$this->db->select('*');
		$this->db->from('customer_information');

		if ($mobile) {
			$this->db->like('customer_information.customer_mobile', $mobile, 'both');
		}

		if ($email) {
			$this->db->like('customer_information.customer_email', $email, 'both');
		}

		if ($customer) {
			$this->db->like('customer_information.customer_name', $customer, 'both');
		}

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}
		return false;
	}

	//Country List
	public function country_list()
	{
		$this->db->select('*');
		$this->db->from('countries');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}

	//Select City By Country ID List
	public function select_city_country_id($country_id)
	{
		$this->db->select('*');
		$this->db->from('states');
		$this->db->where('country_id', $country_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}
	//Select Country Name 
	public function select_county_name($country_id)
	{
		$this->db->select('*');
		$this->db->from('countries');
		$this->db->where('id', $country_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();
		}
		return false;
	}

	//Customer Search List
	public function customer_search_item($customer_id)
	{
		$this->db->select('customer_information.*,sum(customer_transection_summary.amount) as customer_balance');
		$this->db->from('customer_information');
		$this->db->join('customer_transection_summary', 'customer_transection_summary.customer_id= customer_information.customer_id');
		$this->db->where('customer_information.customer_id', $customer_id);
		$this->db->group_by('customer_transection_summary.customer_id');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}
	//Customer entry
	public function customer_entry($data)
	{
		$customer_password = filter_input_post('password');
		$this->db->select('*');
		$this->db->from('customer_information');
		$this->db->where('customer_mobile', $data['customer_mobile']);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return false;
		} else {
			$this->db->insert('customer_information', $data);
			if (!empty($customer_password)) {
				// var_dump($customer_password);exit;
				$customer_login = array(
					'customer_id'=> $data['customer_id'], 
					'email' 	 => $data['customer_email'], 
					'phone' 	 => $data['customer_mobile'], 
					'password' 	 => md5("gef".$customer_password), 
				);
				$this->db->insert('customer_login',$customer_login);
			}

			$this->db->select('*');
			$this->db->from('customer_information');
			$query = $this->db->get();
			foreach ($query->result() as $row) {
				$json_customer[] = array('label' => $row->customer_mobile, 'value' => $row->customer_id);
			}
			$cache_file = './my-assets/js/admin_js/json/customer.json';
			$customerList = json_encode($json_customer);
			file_put_contents($cache_file, $customerList);
			return TRUE;
		}
	}

	//Retrieve company Edit Data
	public function retrieve_company()
	{
		$this->db->select('*');
		$this->db->from('company_information');
		$this->db->limit('1');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}

	//Retrieve customer Edit Data
	public function retrieve_customer_editdata($customer_id)
	{
		$this->db->select('*');
		$this->db->from('customer_information');
		$this->db->where('customer_id', $customer_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}

	//Retrieve customer Personal Data 
	public function customer_personal_data($customer_id = null, $customer_mobile = null, $email = null)
	{
		$this->db->select('customer_information.*,countries.name as country_name');
		$this->db->from('customer_information');
		$this->db->join('countries', 'countries.id = customer_information.country', 'left');
		$this->db->join('customer_login', 'customer_login.customer_id = customer_information.customer_id', 'left');
		if (!empty($customer_id)) {
			$this->db->where('customer_information.customer_id', $customer_id);
		}

		if (!empty($customer_mobile)) {
			$this->db->where('customer_information.customer_mobile', $customer_mobile);
		}

		if (!empty($email)) {
			$this->db->where('customer_information.customer_email', $email);
		}

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}

	// Invoice Data for specific data
	public function invoice_data($customer_id)
	{
		$this->db->select('a.*,c.customer_name');
		$this->db->from('invoice a');
		$this->db->join('customer_information c', 'c.customer_id = a.customer_id');
		$this->db->where('c.customer_id', $customer_id);
		$this->db->group_by('a.id');
		$this->db->order_by('a.id', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}

	//Update Customer
	public function update_customer($data, $customer_id)
	{
		
		$this->db->where('customer_id', $customer_id);
		$this->db->update('customer_information', $data);
		
		//customer_login table update
		$this->db->set('b.email', $data['customer_email']);
		$this->db->set('b.phone', $data['customer_mobile']);
		$this->db->where('b.customer_id', $customer_id);
		$this->db->update('customer_login as b');
		//
		$old_password = filter_input_post('old_password');
		$new_password = filter_input_post('new_password');

		if (!empty($old_password) && !empty($new_password)) {
			$this->db->where(array('email'=>$data['customer_email'],'password'=>md5("gef".$old_password) ));
			$query = $this->db->get('customer_login');
			$result =  $query->result_array();
			if (count($result) == 1)
			{
				$this->db->set('password', md5("gef".$new_password));
				$this->db->where('password', md5("gef".$old_password));
				$this->db->where('email', $data['customer_email']);
				$this->db->update('customer_login');

				$this->session->set_flashdata('error_message', 'Password Updated');
				return true;
			}	
			$this->session->set_flashdata('error_message', 'Something is Wrong, Password Not Updated');
			return false;
		}else{
			$this->session->set_flashdata('error_message', 'Password Not Updated');
		}

		$this->db->select('*');
		$this->db->from('customer_information');
		$query = $this->db->get();
		foreach ($query->result() as $row) {
			$json_customer[] = array('label' => $row->customer_mobile, 'value' => $row->customer_id);
		}
		$cache_file = './my-assets/js/admin_js/json/customer.json';
		$customerList = json_encode($json_customer);
		file_put_contents($cache_file, $customerList);
		return true;
	}

	// Delete customer
	public function delete_customer($customer_id)
	{
		$result = $this->db->select('*')
			->from('invoice')
			->where('customer_id', $customer_id)
			->get()
			->num_rows();
		if ($result > 0) {
			$this->session->set_userdata(array('error_message' => display('you_cant_delete_this_customer')));
			redirect('manage_customer');
		} else {
			$this->db->where('customer_id', $customer_id);
			$this->db->delete('customer_information');

			$this->db->select('*');
			$this->db->from('customer_information');
			$query = $this->db->get();
			foreach ($query->result() as $row) {
				$json_customer[] = array('label' => $row->customer_mobile, 'value' => $row->customer_id);
			}
			$cache_file = './my-assets/js/admin_js/json/customer.json';
			$customerList = json_encode($json_customer);
			file_put_contents($cache_file, $customerList);
			return true;
		}
	}

	//Customer search list
	public function customer_search_list($cat_id, $company_id)
	{
		$this->db->select('a.*,b.sub_category_name,c.category_name');
		$this->db->from('customers a');
		$this->db->join('customer_sub_category b', 'b.sub_category_id = a.sub_category_id');
		$this->db->join('customer_category c', 'c.category_id = b.category_id');
		$this->db->where('a.sister_company_id', $company_id);
		$this->db->where('c.category_id', $cat_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}

	//Customer search
	public function customer_search($customer_info)
	{
		$result = $this->db->select('*')
			->from('customer_information')
			->like('customer_name', $customer_info, 'both')
			->or_like('customer_email', $customer_info, 'both')
			->or_like('customer_mobile', $customer_info, 'both')
			->or_like('customer_short_address', $customer_info, 'both')
			->get()
			->result_array();

		return $result;
	}
}
