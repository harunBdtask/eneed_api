<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Customer_api_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function customer_signup($data)
    {
        $exist_customer = $this->db->select('*')
            ->from('customer_login')
            ->where('email', $data['customer_email'])
            ->where('email !=', null)
            ->or_where('phone', $data['customer_mobile'])
            ->get()
            ->num_rows();

        if ($exist_customer > 0) {
            return false;
        } else {
            $customer_login = array(
                'customer_id' => $data['customer_id'],
                'email'      => $data['customer_email'],
                'phone'      => $data['customer_mobile'],
                'password'      => md5("gef" . filter_input_data($this->input->post('password', TRUE))),
            );
            $this->db->insert('customer_information', $data);
            $this->db->insert('customer_login', $customer_login);
            return true;
        }
    }

    public function retrieve_company_editdata()
    {
        $this->db->select('*');
        $this->db->from('company_information');
        //$this->db->where('company_id',$company_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Retruve profile data
    public function profile_edit_data()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $this->db->select('*');
        $this->db->from('customer_information');
        $this->db->where('customer_id', $customer_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }
    //Change Password
    public function change_password($email, $old_password, $new_password)
    {
        $user_name     = md5("gef" . $new_password);
        $password     = md5("gef" . $old_password);
        $this->db->where(array('email' => $email, 'password' => $password));
        $query = $this->db->get('customer_login');
        $result =  $query->result_array();

        if (count($result) == 1) {
            $this->db->set('password', $user_name);
            $this->db->where('password', $password);
            $this->db->where('email', $email);
            $this->db->update('customer_login');

            return true;
        }
        return false;
    }
    //Retrieve Template
    public function retrieve_template($status)
    {

        if ($status == 7) {
            $status = 13;
        }

        $this->db->select('*');
        $this->db->from('email_template');
        $this->db->where('status', $status);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }
    //Retrive Email Data
    public function retrieve_email_editdata()
    {
        $this->db->select('*');
        $this->db->from('email_configuration');
        $this->db->where('email_id', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
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
    //Retrieve currency info
    public function retrieve_currency_info()
    {
        $this->db->select('*');
        $this->db->from('currency_info');
        $this->db->where('default_status', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Customer email existing check
    public function customer_email_exists($email = null)
    {

        $customer_exists = $this->db->select('*')
            ->from('customer_login')
            ->where('email', $email)
            ->where('email !=', null)
            ->get()
            ->num_rows();
        if ($customer_exists > 0) {
            return true;
        } else {
            return false;
        }
    }
    //Temporary reset password
    public function temp_reset_password($temp_pass = null, $email = null)
    {
        $result = $this->db->set('reset_pass', $temp_pass)
            ->where('email', $email)
            ->update('customer_login');
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    //order List
    public function order_list($per_page = null, $page = null, $order_no = null, $date = null, $order_status = null)
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        //echo var_dump($customer_id);exit;
        $this->db->select('a.*,b.customer_name');
        $this->db->from('order a');
        $this->db->join('customer_information b', 'b.customer_id = a.customer_id');
        $this->db->where('b.customer_id', $customer_id);

        if ($order_no) {
            $this->db->where('a.order_no', $order_no);
        }

        if ($order_status) {
            $this->db->where('a.order_status', $order_status);
        }

        if ($date) {
            $a = explode("---", $date);
            if (count($a) == 1) {
                $from_date =  $a[0];
                $this->db->where('a.date', $from_date);
            } else {
                $from_date = $a[0];
                $this->db->where('a.date >=', $from_date);
                $to_date   = $a[1];
                $this->db->where('a.date <=', $to_date);
            }
        }
        $this->db->order_by('a.id', 'desc');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return 0;
    }
}
