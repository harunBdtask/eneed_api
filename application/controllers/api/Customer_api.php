<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customer_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //header
        header('Content-Type: application/json');
        //customer_api_model load
        $this->load->model('api/customer_api_model');
    }

    /*
	|---------------------------------------------------
	|	number_generator for customer_information table
	|---------------------------------------------------
	*/
    public function number_generator()
    {
        $this->db->select_max('customer_code');
        $query = $this->db->get('customer_information');
        $result = $query->result_array();
        $customer_code = $result[0]['customer_code'];
        if ($customer_code != '') {
            $customer_code = $customer_code + 1;
        } else {
            $customer_code = 1000;
        }
        return $customer_code;
    }
    /*
	|-------------------------------
	|	Check valid user
	|-------------------------------
	*/
    public function check_valid_user($email, $password)
    {
        $password     = md5("gef" . $password);
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        $this->db->or_where('phone', $email);
        $query         = $this->db->get('customer_login');
        $result     = $query->result_array();
        if (count($result) == 1) {
            $this->db->select('*');
            $this->db->from('customer_information');
            $this->db->where('customer_id', $result[0]['customer_id']);
            $query = $this->db->get();
            $data   =  $query->result();
            if (!empty($data)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    /*
	|---------------------------------------------------
	|	customer registration
	|	post method 
	|	route = api/customer_api/customer_information
	|---------------------------------------------------
	*/
    public function customer_information()
    {
        //api_response_history();
        //api key checking
        // if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean');
            $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean');
            $this->form_validation->set_rules('customer_mobile', 'Mobile', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
            $this->form_validation->set_rules('customer_email', 'Email', 'trim|required|max_length[100]|valid_email|xss_clean');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[32]|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['first_name'])) {
                    $errors_data = $errors['first_name'];
                }
                if (!empty($errors['last_name'])) {
                    $errors_data = $errors['last_name'];
                }
                if (!empty($errors['customer_mobile'])) {
                    $errors_data = $errors['customer_mobile'];
                }
                if (!empty($errors['customer_email'])) {
                    $errors_data = $errors['customer_email'];
                }
                if (!empty($errors['password'])) {
                    $errors_data = $errors['password'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $data = array(
                    'customer_id'   => generator(15),
                    'customer_code' => $this->number_generator(),
                    'first_name'    => filter_input_data($this->input->post('first_name', TRUE)),
                    'last_name'     => filter_input_data($this->input->post('last_name', TRUE)),
                    'customer_name' => filter_input_data($this->input->post('first_name', TRUE)) . ' ' . filter_input_data($this->input->post('last_name', TRUE)),
                    'customer_mobile' => filter_input_data($this->input->post('customer_mobile', TRUE)),
                    'customer_email' => filter_input_data($this->input->post('customer_email', TRUE)),
                    'status'         => 1,
                );
                $res = $this->customer_api_model->customer_signup($data);
                if (!empty($res)) {
                    //saving api history in database
                    api_response_history();
                    $response = $data;
                    //success message by checking apiToken
                    JSONSuccessOutput($response, check_api_token());
                } else {
                    JSONErrorOutput(display('phone_no_or_email_already_exists'));
                }
            }
        // }
    }
    /*
	|-------------------------------
	|	customer login
	|	GET method 
	|	route = api/customer_api/customer_login
	|-------------------------------
	*/
    public function customer_login()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('email', 'login email', 'trim|valid_email|required|xss_clean');
            $this->form_validation->set_rules('password', 'login password', 'trim|required|xss_clean');

            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['email'])) {
                    $errors_data = $errors['email'];
                }
                if (!empty($errors['password'])) {
                    $errors_data = $errors['password'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $error    = '';
                $email    = filter_input_data($this->input->post('email', TRUE));
                $password = filter_input_data($this->input->post('password', TRUE));

                if ($email == '' || $password == '' || $this->check_valid_user($email, $password) === FALSE) {
                    JSONErrorOutput(display('wrong_username_or_password'));
                }

                if ($error != '') {
                    JSONErrorOutput($error);
                } else {
                    $customer_info_query =  $this->db->select('*')
                        ->from('customer_login')
                        ->where('email', $email)
                        ->where('email !=', null)
                        ->get();
                    $response =  $customer_info_query->row();
                    JSONSuccessOutput($response->customer_id, check_api_token());
                    //limarket / company name
                    // $com_info = $this->customer_api_model->retrieve_company_editdata();
                    // if (!empty($com_info)) {
                    //     //saving api history in database
                    //     api_response_history();
                    //     $response = display('welcome_to') . " " . $com_info[0]['company_name'];
                    //     JSONSuccessOutput($response, check_api_token());
                    // }
                }
            }
        }
    }
    /*
	|---------------------------------------------------
	|	Password Reset for customer
	|	get method 
	|	route = api/customer_api/password_reset
	|---------------------------------------------------
	*/
    public function password_reset()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('email', 'user email', 'valid_email|trim|required|xss_clean');

            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['email'])) {
                    $errors_data = $errors['email'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $email = filter_input_data($this->input->post('email', TRUE));

                if ($email) {
                    //check if email is in the database
                    if ($this->customer_api_model->customer_email_exists($email)) {
                        //if return true
                        //$temp_pass is the varible to be sent to the user's email
                        $temp_pass = md5(uniqid());

                        //send email with #temp_pass as a link
                        $setting_detail  = $this->customer_api_model->retrieve_email_editdata(); //result_array()
                        $company_info    = $this->customer_api_model->company_list(); //result_array()
                        $template_details = $this->customer_api_model->retrieve_template('15'); //row()

                        $config = array(
                            'protocol'      => $setting_detail[0]['protocol'],
                            'smtp_host'     => $setting_detail[0]['smtp_host'],
                            'smtp_port'     => $setting_detail[0]['smtp_port'],
                            'smtp_user'     => $setting_detail[0]['sender_email'],
                            'smtp_pass'     => $setting_detail[0]['password'],
                            'mailtype'      => $setting_detail[0]['mailtype'],
                            'charset'       => 'utf-8'
                        );
                        $this->email->initialize($config);
                        $this->email->set_mailtype($setting_detail[0]['mailtype']);
                        $this->email->set_newline("\r\n");

                        //Email content
                        $message  = !empty($template_details->message) ? $template_details->message : null;
                        $message .= "<p><a href='" . base_url() . "recover_password/reset_password/token/$temp_pass'> " . display('click_here') . " </a>" . display('if_you_reset_password_if_not_then_ignore') . "</p>";

                        $this->email->to($email);
                        $this->email->from($setting_detail[0]['sender_email'], $company_info[0]['company_name']);
                        $this->email->subject(!empty($template_details->subject) ? $template_details->subject : null);
                        $this->email->message($message);

                        if ($this->email->send()) {
                            if ($this->customer_api_model->temp_reset_password($temp_pass, $email)) {
                                //if return TRUE
                                //saving api history in database
                                api_response_history();
                                $response = display('varifaction_mail_was_sent_please_check_your_email');
                                JSONSuccessOutput($response, check_api_token());
                            }
                        } else {
                            $data['response_status'] = 0;
                            $data['message'] = display('email_was_not_sent_please_contact_administrator');
                            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            exit;
                        }
                    } else {
                        $data['response_status'] = 0;
                        $data['message'] = display('your_email_was_not_found');
                        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        exit;
                    }
                } else {
                    JSONErrorOutput();
                }
            }
        }
    }
    /*
	|---------------------------------------------------
	|	Retrieve profile data for customer
	|	post method 
	|	route = api/customer_api/profile_data
	|---------------------------------------------------
	*/
    public function profile_data()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('customer_id', 'id', 'trim|required|xss_clean');

            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['customer_id'])) {
                    $errors_data = $errors['customer_id'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
                $this->db->select('*');
                $this->db->from('customer_information');
                $this->db->where('customer_id', $customer_id);
                $query = $this->db->get();
                $data   =  $query->result();
                if (!empty($data)) {
                    //saving api history in database
                    api_response_history();
                    $response = $data;
                    JSONSuccessOutput($response, check_api_token());
                } else {
                    JSONErrorOutput(display('not_found'));
                }
            }
        }
    }
    /*
	|---------------------------------------------------
	|	update_profile for customer
	|	post method 
	|	route = api/customer_api/update_profile
	|---------------------------------------------------
	*/
    public function update_profile()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean');
            $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean');
            $this->form_validation->set_rules('customer_email', 'Email', 'trim|required|max_length[100]|valid_email|xss_clean');
            $this->form_validation->set_rules('customer_mobile', 'Mobile', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
            $this->form_validation->set_rules('customer_short_address', 'short address', 'trim|xss_clean');
            $this->form_validation->set_rules('customer_address_1', 'first address', 'trim|required|xss_clean');
            $this->form_validation->set_rules('customer_address_2', 'second address', 'trim|xss_clean');
            $this->form_validation->set_rules('city', 'city', 'trim|required|xss_clean');
            $this->form_validation->set_rules('state', 'state', 'trim|required|xss_clean');
            $this->form_validation->set_rules('country', 'country', 'trim|required|xss_clean');
            $this->form_validation->set_rules('zip', 'zip', 'trim|required|xss_clean');
            $this->form_validation->set_rules('company', 'company', 'trim|required|xss_clean');
            $this->form_validation->set_rules('old_image', 'old_image', 'trim|xss_clean');
            $this->form_validation->set_rules('customer_id', 'customer id', 'trim|required|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['first_name'])) {
                    $errors_data = $errors['first_name'];
                }
                if (!empty($errors['last_name'])) {
                    $errors_data = $errors['last_name'];
                }
                if (!empty($errors['customer_email'])) {
                    $errors_data = $errors['customer_email'];
                }
                if (!empty($errors['customer_mobile'])) {
                    $errors_data = $errors['customer_mobile'];
                }
                if (!empty($errors['customer_short_address'])) {
                    $errors_data = $errors['customer_short_address'];
                }
                if (!empty($errors['customer_address_1'])) {
                    $errors_data = $errors['customer_address_1'];
                }
                if (!empty($errors['customer_address_2'])) {
                    $errors_data = $errors['customer_address_2'];
                }
                if (!empty($errors['city'])) {
                    $errors_data = $errors['city'];
                }
                if (!empty($errors['state'])) {
                    $errors_data = $errors['state'];
                }
                if (!empty($errors['country'])) {
                    $errors_data = $errors['country'];
                }
                if (!empty($errors['zip'])) {
                    $errors_data = $errors['zip'];
                }
                if (!empty($errors['company'])) {
                    $errors_data = $errors['company'];
                }
                if (!empty($errors['customer_id'])) {
                    $errors_data = $errors['customer_id'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $this->load->library('upload');
                if (($_FILES['image']['name'])) {
                    $files = $_FILES;
                    $config = array();
                    $config['upload_path'] = 'assets/dist/img/profile_picture/';
                    $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|GIF|JPG|PNG';
                    $config['max_size']      = '1024';
                    $config['max_width']     = '*';
                    $config['max_height']    = '*';
                    $config['overwrite']     = FALSE;
                    $config['encrypt_name']  = true;

                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('image')) {
                        $errors = $this->upload->display_errors();
                        JSONErrorOutput($errors);
                    } else {
                        $view = $this->upload->data();
                        $image = base_url($config['upload_path'] . $view['file_name']);
                    }
                }

                $old_image = filter_input_data($this->input->post('old_image', TRUE));
                $customer_id = filter_input_data($this->input->post('customer_id', TRUE));

                $data = array(
                    'first_name'                => filter_input_data($this->input->post('first_name', TRUE)),
                    'last_name'                 => filter_input_data($this->input->post('last_name', TRUE)),
                    'customer_name'             => filter_input_data($this->input->post('first_name', TRUE)) . ' ' . filter_input_data($this->input->post('last_name', TRUE)),
                    'customer_email'            => filter_input_data($this->input->post('customer_email', TRUE)),
                    'customer_mobile'           => filter_input_data($this->input->post('customer_mobile', TRUE)),
                    'customer_address_1'        => filter_input_data($this->input->post('customer_address_1', TRUE)),
                    'customer_address_2'        => filter_input_data($this->input->post('customer_address_2', TRUE)),
                    'city'                      => filter_input_data($this->input->post('city', TRUE)),
                    'state'                     => filter_input_data($this->input->post('state', TRUE)),
                    'country'                   => filter_input_data($this->input->post('country', TRUE)),
                    'customer_short_address'    => filter_input_data($this->input->post('country', TRUE)) . ', ' . filter_input_data($this->input->post('state', TRUE)) . ', ' . filter_input_data($this->input->post('city', TRUE)) . ', ' . filter_input_data($this->input->post('customer_address_1', TRUE)),
                    'zip'                       => filter_input_data($this->input->post('zip', TRUE)),
                    'company'                   => filter_input_data($this->input->post('company', TRUE)),
                    'image'                     => (!empty($image) ? $image : $old_image),
                );

                // $this->db->where('customer_id', $customer_id);
                // $res = $this->db->update('customer_information', $data);
                $exist_user = $this->db->select('*')
                    ->from('customer_login')
                    ->where('customer_id', $customer_id)
                    ->get()
                    ->num_rows();
                //echo $this->db->last_query();exit;
                //var_dump($exist_user);exit;

                if ($exist_user > 0) {

                    $customer_login = array(
                        'email'      => $data['customer_email'],
                        'phone'      => $data['customer_mobile'],
                    );
                    $this->db->where('customer_id', $customer_id);
                    $this->db->update('customer_information', $data);
                    $this->db->where('customer_id', $customer_id);
                    $this->db->update('customer_login', $customer_login);
                    //echo $this->db->last_query();exit;
                    //saving api history in database
                    api_response_history();
                    $response = display('successfully_updated');
                    //success message by checking apiToken
                    JSONSuccessOutput($response, check_api_token());
                } else {
                    JSONErrorOutput('Customer not found! ');
                }
            }
        }
    }
    /*
	|---------------------------------------------------
	|	Change Password for customer
	|	post method 
	|	route = api/customer_api/change_password
	|---------------------------------------------------
	*/
    public function change_password()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('customer_id', 'customer_id', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|max_length[100]|valid_email|xss_clean');
            $this->form_validation->set_rules('old_password', 'Previous Password', 'trim|required|min_length[6]|max_length[32]|xss_clean');
            $this->form_validation->set_rules('password', 'New Password', 'trim|required|min_length[6]|max_length[32]|xss_clean');
            $this->form_validation->set_rules('repassword', 'Confirm Password', 'trim|required|min_length[6]|max_length[32]|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['customer_id'])) {
                    $errors_data = $errors['customer_id'];
                }
                if (!empty($errors['email'])) {
                    $errors_data = $errors['email'];
                }
                if (!empty($errors['old_password'])) {
                    $errors_data = $errors['old_password'];
                }
                if (!empty($errors['password'])) {
                    $errors_data = $errors['password'];
                }
                if (!empty($errors['repassword'])) {
                    $errors_data = $errors['repassword'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $error = '';
                $email = filter_input_data($this->input->post('email', TRUE));
                $old_password = filter_input_data($this->input->post('old_password', TRUE));
                $new_password = filter_input_data($this->input->post('password', TRUE));
                $repassword = filter_input_data($this->input->post('repassword', TRUE));

                $edit_data = $this->customer_api_model->profile_edit_data();
                $old_email = $edit_data->customer_email;

                if ($email == '' || $old_password == '' || $new_password == '') {
                    $error = display('blank_field_does_not_accept');
                } else if ($email != $old_email) {
                    $error = display('you_put_wrong_email_address');
                } else if (strlen($new_password) < 6) {
                    $error = display('new_password_at_least_six_character');
                } else if ($new_password != $repassword) {
                    $error = display('password_and_repassword_does_not_match');
                } else if ($this->customer_api_model->change_password($email, $old_password, $new_password) === FALSE) {
                    $error = display('you_are_not_authorised_person');
                }

                if ($error != '') {
                    JSONErrorOutput($error);
                } else {
                    //saving api history in database
                    api_response_history();
                    $response = display('successfully_changed_password');
                    JSONSuccessOutput($response, check_api_token());
                }
            }
        }
    }
    /*
	|---------------------------------------------------
	|	Retrieve order list
	|	post method 
	|	route = api/customer_api/order_list
	|---------------------------------------------------
	*/
    public function order_list()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('customer_id', 'customer id', 'trim|required|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|xss_clean');
            $this->form_validation->set_rules('order_no', 'Order Number', 'trim|xss_clean');
            $this->form_validation->set_rules('date', 'Order Date', 'trim|xss_clean');
            $this->form_validation->set_rules('order_status', 'Order Status', 'trim|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['customer_id'])) {
                    $errors_data = $errors['customer_id'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                if (!empty($errors['order_no'])) {
                    $errors_data = $errors['order_no'];
                }
                if (!empty($errors['date'])) {
                    $errors_data = $errors['date'];
                }
                if (!empty($errors['order_status'])) {
                    $errors_data = $errors['order_status'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                $order_no = filter_input_data($this->input->post('order_no', TRUE));
                $date = filter_input_data($this->input->post('date', TRUE)); //2021-02-28
                $order_status = filter_input_data($this->input->post('order_status', TRUE));

                $orders_list = $this->customer_api_model->order_list($per_page, $page, $order_no, $date, $order_status); //result_array()

                if ($orders_list != 0) {
                    $orders_list_data = $orders_list;
                } else {
                    $orders_list_data = array();
                }
                if (!empty($orders_list)) {

                    $i = 0;
                    foreach ($orders_list as $k => $v) {
                        $i++;
                        $orders_list[$k]['sl'] = $i;
                    }
                }

                $currency_details = $this->customer_api_model->retrieve_currency_info();
                $data = array(
                    'title'    => display('manage_order'),
                    'orders_list' => $orders_list_data,
                    //'orders_list' => $orders_list,
                    'currency' => $currency_details[0]['currency_icon'],
                    'position' => $currency_details[0]['currency_position'],
                );
                if (!empty($data)) {
                    //saving api history in database
                    api_response_history();
                    $response = $data;
                    //success message by checking apiToken
                    JSONSuccessOutput($response, check_api_token());
                } else {
                    JSONErrorOutput();
                }
            }
        }
    }
    /*
	|---------------------------------------------------
	|	Retrieve Country List
	|	post method 
	|	route = api/customer_api/country_list
	|---------------------------------------------------
	*/
    public function country_list()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('id', 'country id', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('sortname', 'country sortname', 'trim|xss_clean');
            $this->form_validation->set_rules('name', 'country name', 'trim|xss_clean');
            $this->form_validation->set_rules('phonecode', 'country phonecode', 'trim|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['id'])) {
                    $errors_data = $errors['id'];
                }
                if (!empty($errors['sortname'])) {
                    $errors_data = $errors['sortname'];
                }
                if (!empty($errors['name'])) {
                    $errors_data = $errors['name'];
                }
                if (!empty($errors['phonecode'])) {
                    $errors_data = $errors['phonecode'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $country_id = filter_input_data($this->input->post('id', TRUE));
                $country_sortname = filter_input_data($this->input->post('sortname', TRUE));
                $country_name = filter_input_data($this->input->post('name', TRUE));
                $country_phonecode = filter_input_data($this->input->post('phonecode', TRUE));
                //
                $this->db->select('*');
                $this->db->from('countries');
                if (!empty($country_id)) {
                    $this->db->where('id', $country_id);
                }
                if (!empty($country_sortname)) {
                    $this->db->where('sortname', $country_sortname);
                }
                if (!empty($country_name)) {
                    $this->db->where('name', $country_name);
                }
                if (!empty($country_phonecode)) {
                    $this->db->where('phonecode', $country_phonecode);
                }
                $query = $this->db->get();
                $data   =  $query->result();
                if (!empty($data)) {
                    //saving api history in database
                    api_response_history();
                    $response = $data;
                    JSONSuccessOutput($response, check_api_token());
                } else {
                    JSONErrorOutput(display('not_found'));
                }
            }
        }
    }



    //class end
}
