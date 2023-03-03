<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

require(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Mobile_customer_api extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Authorization_Token');
        //load part
        $this->load->model('website/Homes');
        $this->load->model('Orders');
        $this->load->library('occational');
        $this->load->library('Pdfgenerator');
        $this->load->model('Soft_settings');
        $this->load->model('Companies');
        $this->load->model('Email_templates');
        //header
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        Header('Access-Control-Allow-Headers: *');
        Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    }

    

    public function register_post()
    {
        $token_data['user_id'] = 121;
        $token_data['fullname'] = 'code';
        $token_data['email'] = 'code@gmail.com';

        $tokenData = $this->authorization_token->generateToken($token_data);

        $final = array();
        $final['token'] = $tokenData;
        $final['status'] = 'ok';

        $this->response($final);
    }
    public function verify_get()
    {
        $headers = $this->input->request_headers();
        $decodedToken = $this->authorization_token->validateToken($headers['authorization']);

        $this->response($decodedToken);
    }

    public function customer_info_post()
    {
        $this->form_validation->set_rules('customer_id', 'Customer ID', 'trim|required|xss_clean');
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['customer_id'])) {
                $errors_data = $errors['customer_id'];
            }
            JSONErrorOutput($errors_data);
        }

        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }

        $this->db->select('co.customer_name,co.customer_email,co.customer_mobile,co.image');
        $this->db->from('customer_information co');
        $this->db->where('co.customer_id', $customer_id);
        $query = $this->db->get();
        $result = $query->row();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            JSONErrorOutput("Invalid customer id");
        }
    }

    public function tokenVerify($customer_id)
    {
        $headers = $this->input->request_headers();
        if (array_key_exists('authorization', $headers) == false) {
            JSONErrorOutput("Invalid Authorization Request!");
        }
        if (empty($headers['authorization'])) {
            JSONErrorOutput("Empty Authorization Request!");
        }

        $decodedToken = $this->authorization_token->validateToken($headers['authorization']);

        if ($customer_id == $decodedToken['data']->customer_id) {
            return 'ok';
        }else{
            header('Content-Type: application/json');
            $response['response_status'] = 401;
            $response['message'] = 'Invalid Token';
            $response['status'] = 'failed';
            $response['data'] = null;
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
    public function refresh_token_post()
    {
        $this->form_validation->set_rules('old_token', 'old token', 'trim|required|xss_clean');
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['old_token'])) {
                $errors_data = $errors['old_token'];
            }
            JSONErrorOutput($errors_data);
        }

        $old_token = filter_input_data($this->input->post('old_token', TRUE));
        $this->activeToken($old_token);

    }

    public function activeToken($old_token)
    {
        $decodedToken = $this->authorization_token->activeToken($old_token);

        if (!empty($decodedToken->customer_id)) {
            $res = $this->customer_info_exists($decodedToken->customer_id, $decodedToken->customer_mobile);
            if ($res == 0) {
                JSONErrorOutput('Invalid Token');
            }else {
                JSONSuccessOutput($res, "Active Token");
            }
        }else {
            JSONErrorOutput('Invalid Token');
        }
    }

    public function customer_info_exists($customer_id, $customer_mobile)
    {
        $customer_exists = $this->db->select('*')
            ->from('customer_login')
            ->where('customer_id', $customer_id)
            ->where('phone', $customer_mobile)
            ->get()
            ->num_rows();
        if ($customer_exists > 0) {
            $token_data['customer_id'] = $customer_id;
            $token_data['customer_mobile'] = $customer_mobile;

            $tokenData = $this->authorization_token->generateToken($token_data);

            $final = array();
            $final['customer_id'] = $customer_id;
            $final['token'] = $tokenData;

            return $final;
        } else {
            return 0;
        }
    }

    /*
	|-------------------------------
	|	customer_login
	|	POST method 
	|	route = api/react/mobile_customer_api/customer_login
	|-------------------------------
	*/
    public function login_post()
    {
        // if (checkAuth(check_api_key())) {

        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[32]|xss_clean');
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['customer_mobile'])) {
                $errors_data = $errors['customer_mobile'];
            }
            if (!empty($errors['password'])) {
                $errors_data = $errors['password'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
            if ($this->customer_mobile_exists($mobile) == false) {
                JSONErrorOutput('This Mobile No. has no User, Please Try with Valid No. ');
            }

            $password = filter_input_data($this->input->post('password', TRUE));

            if ($mobile == '' || $password == '' || $this->check_valid_user($mobile, $password) === FALSE) {
                JSONErrorOutput(display('wrong_username_or_password'));
            } else if ($this->check_otp_validation($mobile, $password) === FALSE) {
                JSONErrorOutput('You have Not Verified Your Account, Please Verify Your Account');
            } else {
                $customer_info_query =  $this->db->select('a.customer_id, b.customer_name, b.customer_email, b.customer_mobile, b.image')
                    ->from('customer_login a')
                    ->join('customer_information b', 'a.customer_id = b.customer_id', 'left')
                    ->where('a.phone', $mobile)
                    ->where('a.phone !=', null)
                    ->get();
                $response =  $customer_info_query->row();

                $token_data['customer_id'] = $response->customer_id;
                $token_data['customer_mobile'] = $response->customer_mobile;

                $tokenData = $this->authorization_token->generateToken($token_data);

                $final = array();
                $final['customer_id'] = $response->customer_id;
                $final['token'] = $tokenData;

                JSONSuccessOutput($final, "Login Successfull");
            }
        }
        // }

    }

    public function otp_request_post()
    {

        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['customer_mobile'])) {
                $errors_data = $errors['customer_mobile'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
            if ($this->customer_mobile_exists($mobile) == false) {
                JSONErrorOutput('This Mobile No. has no User, Please Try with Valid No. ');
            }

            if ($mobile == '' || $this->check_verify_user($mobile) === FALSE) {
                JSONErrorOutput('Already Verified');
            } else {
                $customer_info_query =  $this->db->select('a.customer_id, b.customer_name, b.customer_email, b.customer_mobile, b.image')
                    ->from('customer_login a')
                    ->join('customer_information b', 'a.customer_id = b.customer_id', 'left')
                    ->where('a.phone', $mobile)
                    ->where('a.phone !=', null)
                    ->get();
                $response =  $customer_info_query->row();

                $response = $this->new_mobile_otp($mobile, $response->customer_id);
                if ($response == 1) {
                    JSONSuccessOutput(null, 'OTP Sent to Your Mobile Number');
                } else {
                    JSONErrorOutput('Service Unavailable');
                }
            }
        }
    }

    //Customer email existing check
    public function customer_mobile_exists($mobile = null)
    {
        $customer_exists = $this->db->select('*')
            ->from('customer_login')
            ->where('phone', $mobile)
            ->where('phone !=', null)
            ->get()
            ->num_rows();
        if ($customer_exists > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
	|-------------------------------
	|	Check valid user
	|-------------------------------
	*/
    public function check_valid_user($phone, $password)
    {
        $password     = md5("gef" . $password);
        $this->db->where('phone', $phone);
        $this->db->where('password', $password);
        $query  = $this->db->get('customer_login');
        $result = $query->result_array();
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
    public function check_verify_user($phone)
    {
        $this->db->where('phone', $phone);
        $this->db->where('status !=', 1);
        $query  = $this->db->get('customer_login');
        $result = $query->result_array();
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
	|-------------------------------
	|	Check valid user
	|-------------------------------
	*/
    public function check_otp_validation($phone, $password)
    {
        $password     = md5("gef" . $password);
        $this->db->where('phone', $phone);
        $this->db->where('password', $password);
        $this->db->where('status', 1);
        $query  = $this->db->get('customer_login');
        $result = $query->result_array();
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
	|-------------------------------
	|	password_reset
	|	POST method 
	|	route = api/react/mobile_customer_api/password_reset
	|-------------------------------
	*/
    public function password_reset_post()
    {
        $this->form_validation->set_rules('reset_option', 'Reset Option', 'trim|required|xss_clean');
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['reset_option'])) {
                $errors_data = $errors['reset_option'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $reset_option  = filter_input_data($this->input->post('reset_option', TRUE));
            $email  = filter_input_data($this->input->post('customer_email', TRUE));
            $mobile  = filter_input_data($this->input->post('customer_mobile', TRUE));
            if ($reset_option == 'email') {
                if (!empty($email) && $this->customer_email_exists($email)) {
                    //$temp_pass is the varible to be sent to the user's email
                    $temp_pass = md5(uniqid());
                    //send email with #temp_pass as a link
                    $setting_detail  = $this->Soft_settings->retrieve_email_editdata();
                    $company_info    = $this->Companies->company_list();
                    $template_details = $this->Email_templates->retrieve_template('15');

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
                        if ($this->Homes->temp_reset_password($temp_pass, $email)) {
                            JSONSuccessOutput(null, display('varifaction_mail_was_sent_please_check_your_email'));
                        }
                    } else {
                        JSONErrorOutput(display('email_was_not_sent_please_contact_administrator'));
                    }
                } else {
                    JSONErrorOutput(display('your_email_was_not_found'));
                }
            } elseif ($reset_option == 'mobile') {
                $this->db->where('phone', $mobile);
                $this->db->group_by('phone');
                $query = $this->db->get('customer_login');
                if ($query->num_rows() > 0) {
                    $result = $query->row();
                    $otp = mt_rand(100000, 999999);

                    $wdata = array(
                        'customer_id' => $result->customer_id,
                        'mobile' => $mobile,
                        'otp' => $otp,
                        'created_at' => date("Y-m-d H:i:s")
                    );
                    // insert otp_data in reset_password_otp table
                    $this->db->insert('reset_password_otp', $wdata);
                    //sms_template
                    $otp_sms_template = sms_template('forgot_password_otp');
                    if (count($otp_sms_template) == 1) {
                        $msg = str_replace("{otp}", $wdata['otp'], $otp_sms_template[0]['message']);
                    } else {
                        JSONErrorOutput('Please Specify SMS Template');
                    }
                    $response = send_sms($wdata['mobile'], $msg);
                    if (strlen($response) != 4) {
                        JSONSuccessOutput(null, 'OTP Sent to Your Mobile Number');
                    } else {
                        JSONErrorOutput('Service Unavailable');
                    }
                } else {
                    JSONErrorOutput('Invalid Mobile Number');
                }
            } else {
                JSONErrorOutput('Invalid Request!');
            }
        }
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


    /*
	|---------------------------------------------------
	|	confirm_otp_password
	|	POST method 
	|	route = api/react/website_api/confirm_otp_password
	|---------------------------------------------------
	*/
    public function confirm_otp_password_post()
    {
        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('password', display('password'), 'trim|required|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})/]|min_length[6]|max_length[32]|xss_clean', array('required' => display('password') . ' ' . display('required'), 'regex_match' => display('strong_password_combination_msg')));
        $this->form_validation->set_rules('cpassword', display('confirm_password'), 'required|trim|matches[password]');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_mobile'])) {
                $errors_data = $errors['customer_mobile'];
            }
            if (!empty($errors['otp'])) {
                $errors_data = $errors['otp'];
            }
            if (!empty($errors['password'])) {
                $errors_data = $errors['password'];
            }
            if (!empty($errors['cpassword'])) {
                $errors_data = $errors['cpassword'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
            if ($this->customer_mobile_exists($mobile) == false) {
                JSONErrorOutput('This Mobile No. has no User, Please Try with Another No. ');
            }
            $input_otp = filter_input_data($this->input->post('otp', TRUE));
            $password = md5("gef" . filter_input_data($this->input->post('password', TRUE)));

            $this->db->where('mobile', $mobile);
            $this->db->where('otp', $input_otp);
            $query = $this->db->get('reset_password_otp');
            if ($query->num_rows() > 0) {
                $result = $query->row();
                if ($result->otp == $input_otp) {
                    //customer_login table status set
                    $this->db->set('password', $password)->where('phone', $mobile)->update('customer_login');
                    //reset_password_otp table delete exist otp_data
                    $this->db->where('mobile', $mobile);
                    $this->db->delete('reset_password_otp');
                    JSONSuccessOutput(null, 'Password Updated');
                } else {
                    JSONErrorOutput('Invalid OTP');
                }
            } else {
                JSONErrorOutput('Wrong OTP, Try Again for OTP');
            }
        }
    }

    public function customer_signup_post()
    {
        // if (checkAuth(check_api_key())) {

        $this->form_validation->set_rules('customer_name', 'Name', 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean');
        $this->form_validation->set_rules('customer_address', 'Address', 'trim|xss_clean');
        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('customer_email', 'Email', 'trim|required|max_length[100]|valid_email|xss_clean');
        // $this->form_validation->set_rules('password', display('password'), 'trim|required|min_length[6]|max_length[32]|xss_clean');
        $this->form_validation->set_rules('password', display('password'), 'trim|required|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})/]|min_length[6]|max_length[32]|xss_clean', array('required' => display('password') . ' ' . display('required'), 'regex_match' => display('strong_password_combination_msg')));
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['customer_name'])) {
                $errors_data = $errors['customer_name'];
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
            if (!empty($errors['customer_address'])) {
                $errors_data = $errors['customer_address'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
            if ($this->customer_mobile_exists($mobile) == true) {
                JSONErrorOutput('This Mobile No. has a User, Please Try with Another No. ');
            }
            $data = array(
                'customer_id'   => generator(15),
                'customer_code' => $this->customer_number_generator(),
                'first_name'    => '',
                'last_name'     => '',
                'image'     => 'https://sgp1.digitaloceanspaces.com/eneedz/customer_img/default.jpg',
                'customer_name' => filter_input_data($this->input->post('customer_name', TRUE)),
                'customer_mobile' => $mobile,
                'customer_email' => filter_input_data($this->input->post('customer_email', TRUE)),
                'customer_short_address' => filter_input_data($this->input->post('customer_address', TRUE)),
                'customer_address_1' => filter_input_data($this->input->post('customer_address', TRUE)),
                'status'         => 1,
            );
            $customer_login = array(
                'status'        => 0,
                'customer_id'   => $data['customer_id'],
                'email'         => $data['customer_email'],
                'phone'         => $mobile,
                'password'      => md5("gef" . filter_input_data($this->input->post('password', TRUE))),
            );
            $response = $this->new_mobile_otp($mobile, $data['customer_id']);
            if ($response == 1) {
                $this->db->insert('customer_information', $data);
                $this->db->insert('customer_login', $customer_login);
                JSONSuccessOutput(null, 'OTP Sent to Your Mobile Number');
            }
            // JSONSuccessOutput(null, 'Signup Successfull');
        }
        // }
    }

    //NUMBER GENERATOR
    public function customer_number_generator()
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

    public function new_mobile_otp($mobile, $customer_id)
    {
        if (empty($mobile)) {
            JSONErrorOutput('Invalid Info');
        } else {
            $this->db->select('id');
            $this->db->from('reset_password_otp');
            $this->db->where('customer_id', $customer_id);
            $query = $this->db->get();
            if ($query->num_rows() > 5) {
                JSONErrorOutput('Your OTP Request Limit Exceeded');
            }

            $otp = mt_rand(100000, 999999);
            $wdata = array(
                'customer_id' => $customer_id,
                'mobile' => $mobile,
                'otp' => $otp,
                'created_at' => date("Y-m-d H:i:s")
            );
            $this->db->insert('reset_password_otp', $wdata);
            $msg = "Your Validation OTP: " . $wdata['otp'];
            $response = send_sms($wdata['mobile'], $msg);
            if (strlen($response) != 4) {
                return 1;
            } else {
                JSONErrorOutput('Service Unavailable, Please Try Later');
            }
        }
    }

    /*
	|---------------------------------------------------
	|	submit_otp_validation
	|	POST method 
	|	route = api/react/website_api/submit_otp_validation
	|---------------------------------------------------
	*/
    public function submit_otp_validation_post()
    {
        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_mobile'])) {
                $errors_data = $errors['customer_mobile'];
            }
            if (!empty($errors['otp'])) {
                $errors_data = $errors['otp'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
            $input_otp = filter_input_data($this->input->post('otp', TRUE));
            $this->db->where('mobile', $mobile);
            $this->db->where('otp', $input_otp);
            $query = $this->db->get('reset_password_otp');

            if ($query->num_rows() > 0) {
                $result = $query->row();
                if ($result->otp == $input_otp) {
                    //customer_login table status set
                    $this->db->set('status', 1)->where('phone', $mobile)->update('customer_login');
                    //reset_password_otp table delete exist otp_data
                    $this->db->where('mobile', $mobile);
                    $this->db->delete('reset_password_otp');
                    JSONSuccessOutput(null, 'OTP Validation Successful');
                } else {
                    JSONErrorOutput('Invalid OTP');
                }
            } else {
                JSONErrorOutput('Wrong OTP, Try Again for OTP');
            }
        }
    }

    public function profile_update_post()
    {
        $this->form_validation->set_rules('customer_id', 'Customer ID', 'trim|required|xss_clean');
        $this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('customer_email', 'Customer Mail', 'trim|required|xss_clean');
        $this->form_validation->set_rules('customer_mobile', 'Customer Mobile', 'trim|required|xss_clean');
        $this->form_validation->set_rules('old_image', 'Customer Old Image', 'trim|required|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_mobile'])) {
                $errors_data = $errors['customer_mobile'];
            }
            if (!empty($errors['customer_email'])) {
                $errors_data = $errors['customer_email'];
            }
            if (!empty($errors['customer_id'])) {
                $errors_data = $errors['customer_id'];
            }
            if (!empty($errors['customer_name'])) {
                $errors_data = $errors['customer_name'];
            }
            if (!empty($errors['old_image'])) {
                $errors_data = $errors['old_image'];
            }
            JSONErrorOutput($errors_data);
        }
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $customer_name = filter_input_data($this->input->post('customer_name', TRUE));
        $customer_email = filter_input_data($this->input->post('customer_email', TRUE));
        $customer_mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
        $old_image = filter_input_data($this->input->post('old_image', TRUE));
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        if ($_FILES['image']['name']) {
            $sizes = array(1300 => 1300, 235 => 235);
            $file_location = $this->do_upload_file($_FILES['image'], $sizes, 'customer_img');
            $image_name = explode('/', $file_location[0]);
            $image_name = end($image_name);
            $base_path = SPACE_URL;
            $customer_img = $base_path . '/' . 'customer_img/' . $image_name;
        }

        $check = $this->customer_check($customer_id);
        if (!$check) {
            JSONErrorOutput("Customer not found!");
        }

        $data = array(
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            // 'customer_mobile' => $customer_mobile,
            'image' => !empty($customer_img) ? $customer_img : $old_image,
        );
        $update = $this->db->where("customer_id", $customer_id)->update("customer_information", $data);
        if ($update) {
            JSONSuccessOutput(null, "Successfully Updated.");
        } else {
            JSONErrorOutput("Please try again");
        }
    }

    //Check customer
    public function customer_check($customer_id)
    {
        $cust_check = $this->db->select("COUNT(customer_id) as customer")->from("customer_information")->where("customer_id", $customer_id)->get()->row();
        if (!empty($cust_check->customer)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function do_upload_file($FILES, $sizes, $folder)
    {
        // Load Space Library
        $this->load->library('Space');
        $this->spaceobj = new Space();

        // settings
        $max_file_size = 1500 * 1500; // 1MB
        $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

        $filetype = array('main', 'thumb');
        if ($FILES['size'] < $max_file_size) {
            // get file extension
            $ext = strtolower(pathinfo($FILES['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $valid_exts)) {
                $ext = explode(".", $FILES['name']);
                $filename = time() . '.' . end($ext);

                /* resize image */
                $k = 0;
                foreach ($sizes as $w => $h) {

                    $files[] = $this->resize_file($w, $h, $FILES, $filetype[$k], $filename, $folder);
                    $k++;
                }
            } else {
                $files['msg'] = $msg = 'Unsupported file';
            }
        } else {
            $files['msg'] = $msg = 'Please upload image smaller than 200KB';
        }
        sleep(1);
        return $files;
    }
    function resize_file($width, $height, $FILES, $filetype, $filename, $folder)
    {
        // $this->do_resize($width, $height, $FILES);
        if ($filetype == 'main') {
            $save_as = $folder . '/' . $filename;
            $this->spaceobj->upload_to_space($FILES['tmp_name'], $save_as);
        }
        return $filename;
    }

    public function change_password_post()
    {
        $this->form_validation->set_rules('customer_id', 'Customer ID', 'trim|required|xss_clean');
        $this->form_validation->set_rules('phone', 'Customer Phone', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Old Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('newpassword', 'New Password', 'trim|required|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})/]|min_length[6]|max_length[32]|xss_clean', array('required' => display('password') . ' ' . display('required'), 'regex_match' => 'New ' . display('strong_password_combination_msg')));
        $this->form_validation->set_rules('retypepassword', display('confirm_password'), 'required|trim|matches[newpassword]');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_id'])) {
                $errors_data = $errors['customer_id'];
            }
            if (!empty($errors['phone'])) {
                $errors_data = $errors['phone'];
            }
            if (!empty($errors['password'])) {
                $errors_data = $errors['password'];
            }
            if (!empty($errors['newpassword'])) {
                $errors_data = $errors['newpassword'];
            }
            if (!empty($errors['retypepassword'])) {
                $errors_data = $errors['retypepassword'];
            }
            JSONErrorOutput($errors_data);
        }
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $phone = filter_input_data($this->input->post('phone', TRUE));
        $password = filter_input_data($this->input->post('password', TRUE));
        $newpassword = filter_input_data($this->input->post('newpassword', TRUE));
        $retypepassword = filter_input_data($this->input->post('retypepassword', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $check = $this->customerlogin_check($customer_id);
        if (!$check) {
            JSONErrorOutput("Customer not found!");
        }
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        $this->db->select('cl.phone,cl.password');
        $this->db->from('customer_login cl');
        $this->db->where('cl.customer_id', $customer_id);
        $query = $this->db->get();
        $result = $query->row();
        if (!empty($result)) {
            $oldphone = $result->phone;
            $oldpassword = $result->password;
            if ($phone != $oldphone) {
                JSONErrorOutput("Wrong phone number!");
            }
            if (md5("gef" . $password) != trim($oldpassword)) {
                JSONErrorOutput("Wrong Old Password !");
            }
        }

        $data = array(
            'password' => md5("gef" . $newpassword),
        );
        $update = $this->db->where("customer_id", $customer_id)->update("customer_login", $data);
        if ($update) {
            JSONSuccessOutput(null, "Successfully Updated.");
        } else {
            JSONErrorOutput("Please try again");
        }
    }

    public function customerlogin_check($customer_id)
    {
        $cust_check = $this->db->select("COUNT(customer_id) as customer")->from("customer_login")->where("customer_id", $customer_id)->get()->row();
        if (!empty($cust_check->customer)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function statelist_get()
    {

        $statelist = $this->db->select("id,name")->from("states")->where("country_id", COUNTRY_ID)->get()->result();
        if ($statelist) {
            JSONSuccessOutput($statelist);
        } else {
            JSONNoOutput("No data found");
        }
    }
    public function citylist_post()
    {
        $this->form_validation->set_rules('state_id', 'State ID', 'required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['state_id'])) {
                $errors_data = $errors['state_id'];
            }
            JSONErrorOutput($errors_data);
        }
        $state_id = filter_input_data($this->input->post('state_id', TRUE));
        $citylist = $this->db->select("id,name")->from("cities")->where("state_id", $state_id)->get()->result();
        if ($citylist) {
            JSONSuccessOutput($citylist);
        } else {
            JSONNoOutput("No data found");
        }
    }

    //Customer multiple address
    public function customer_address_post()
    {
        $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_id'])) {
                $errors_data = $errors['customer_id'];
            }
            JSONErrorOutput($errors_data);
        }
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $address = $this->db->select("customer_id")->from("customer_address")->where("customer_id", $customer_id)->group_by("customer_id")->get()->row();
        if (!empty($address)) {
            $address->address_list = $this->db->select("cd.address_id,cd.customer_name,cd.customer_phone,cd.division,st.name as dname,cd.city,ct.name as tname,cd.area,cd.address,cd.is_primary")
                ->from("customer_address cd")
                ->join('states st', 'cd.division = st.id', 'left')
                ->join('cities ct', 'cd.city = ct.id', 'left')
                ->where("cd.customer_id", $customer_id)
                ->get()->result();
            JSONSuccessOutput($address->address_list);
        } else {
            JSONNoOutput("No address found");
        }
    }

    public function create_address_post()
    {
        $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim|xss_clean');
        $this->form_validation->set_rules('customer_name', 'Name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('customer_phone', 'Phone No.', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('division', 'division', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('city', 'city', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('area', 'area', 'required|trim|xss_clean');
        $this->form_validation->set_rules('address', 'address', 'required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_id'])) {
                $errors_data = $errors['customer_id'];
            }
            if (!empty($errors['customer_name'])) {
                $errors_data = $errors['customer_name'];
            }
            if (!empty($errors['customer_phone'])) {
                $errors_data = $errors['customer_phone'];
            }
            if (!empty($errors['division'])) {
                $errors_data = $errors['division'];
            }
            if (!empty($errors['city'])) {
                $errors_data = $errors['city'];
            }
            if (!empty($errors['area'])) {
                $errors_data = $errors['area'];
            }
            if (!empty($errors['address'])) {
                $errors_data = $errors['address'];
            }
            JSONErrorOutput($errors_data);
        }
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $customer_name = filter_input_data($this->input->post('customer_name', TRUE));
        $customer_phone = filter_input_data($this->input->post('customer_phone', TRUE));
        $division = filter_input_data($this->input->post('division', TRUE));
        $city = filter_input_data($this->input->post('city', TRUE));
        $area = filter_input_data($this->input->post('area', TRUE));
        $address = filter_input_data($this->input->post('address', TRUE));
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }

        $address_check = $this->db->select("COUNT(address_id) as address")->from("customer_address")->where("customer_id", $customer_id)->get()->row();
        if (!empty($address_check->address)) {
            $is_primary = 0;
        } else {
            $is_primary = 1;
        }
        $data = array(
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'division' => $division,
            'city' => $city,
            'area' => $area,
            'address' => $address,
            'is_primary' => $is_primary,
        );
        $insert = $this->db->insert("customer_address", $data);
        if ($insert) {
            JSONSuccessOutput(NULL, "Save Successfully");
        } else {
            JSONErrorOutput("Please try again");
        }
    }

    public function update_address_post()
    {
        $this->form_validation->set_rules('address_id', 'Address ID', 'required|trim|xss_clean');
        $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim|xss_clean');
        $this->form_validation->set_rules('customer_name', 'Name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('customer_phone', 'Phone No.', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('division', 'division', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('city', 'city', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('area', 'area', 'required|trim|xss_clean');
        $this->form_validation->set_rules('address', 'address', 'required|trim|xss_clean');
        $this->form_validation->set_rules('is_primary', 'is_primary', 'in_list[0,1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['address_id'])) {
                $errors_data = $errors['address_id'];
            }
            if (!empty($errors['customer_id'])) {
                $errors_data = $errors['customer_id'];
            }
            if (!empty($errors['customer_name'])) {
                $errors_data = $errors['customer_name'];
            }
            if (!empty($errors['customer_phone'])) {
                $errors_data = $errors['customer_phone'];
            }
            if (!empty($errors['division'])) {
                $errors_data = $errors['division'];
            }
            if (!empty($errors['city'])) {
                $errors_data = $errors['city'];
            }
            if (!empty($errors['area'])) {
                $errors_data = $errors['area'];
            }
            if (!empty($errors['address'])) {
                $errors_data = $errors['address'];
            }
            if (!empty($errors['is_primary'])) {
                $errors_data = $errors['is_primary'];
            }
            JSONErrorOutput($errors_data);
        }
        $address_id = filter_input_data($this->input->post('address_id', TRUE));
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $customer_name = filter_input_data($this->input->post('customer_name', TRUE));
        $customer_phone = filter_input_data($this->input->post('customer_phone', TRUE));
        $division = filter_input_data($this->input->post('division', TRUE));
        $city = filter_input_data($this->input->post('city', TRUE));
        $area = filter_input_data($this->input->post('area', TRUE));
        $address = filter_input_data($this->input->post('address', TRUE));
        $is_primary = filter_input_data($this->input->post('is_primary', TRUE));
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $address_check = $this->db->select("COUNT(address_id) as address")->from("customer_address")->where("address_id", $address_id)->where("customer_id", $customer_id)->get()->row();
        if (empty($address_check->address)) {
            JSONErrorOutput("Invalid customer address!");
        }

        $old_address = $this->db->select("is_primary")->from("customer_address")->where("address_id", $address_id)->get()->row();
        $data = array(
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'division' => $division,
            'city' => $city,
            'area' => $area,
            'address' => $address,
            'is_primary' => !empty($is_primary) ? $is_primary : $old_address->is_primary,
        );
        $update = $this->db->where("address_id", $address_id)->update("customer_address", $data);
        if ($update) {
            if ($old_address->is_primary == 0 & $data['is_primary'] == 1) {
                $address_total = $this->db->select("address_id")->from("customer_address")->where("customer_id", $customer_id)->get()->result();
                for ($i = 0; $i < count($address_total); $i++) {
                    if ($address_id != $address_total[$i]->address_id) {
                        $this->db->where("address_id", $address_total[$i]->address_id)->update("customer_address", array("is_primary" => 0));
                    }
                }
            }
            JSONSuccessOutput(NULL, "Updated Successfully");
        } else {
            JSONErrorOutput("Please try again");
        }
    }

    /*
    |-------------------------------
    |   top_menu Load Here
    |   route = api/react/website_api/menu_list
    |-------------------------------
    */
    public function menu_list_get()
    {
        $this->db->select('a.category_id, a.category_name, a.cat_image');
        $this->db->from('product_category a');
        $this->db->where('a.status', 1);
        $this->db->where('a.cat_type', 1);
        // $this->db->where('a.top_menu', 1);
        $query = $this->db->get();
        $parent_menu = $query->result_array();

        if (!empty($parent_menu)) {
            foreach ($parent_menu as $key => $value) {

                $this->db->select('a.category_id, a.category_name, a.cat_image, a.parent_category_id');
                $this->db->from('product_category a');
                $this->db->where('a.status', 1);
                $this->db->where('a.cat_type', 2);
                $this->db->where('a.parent_category_id', $value['category_id']);
                $subitems = $this->db->get()->result_array();
                $parent_menu[$key]['sub_items'] = (!empty($subitems) ? $subitems : []);
            }
        }
        JSONSuccessOutput($parent_menu);
    }

    /*
    |-------------------------------
    |   top_categories
    |   route = api/react/website_api/top_categories
    |-------------------------------
    */
    public function top_categories_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $per_page = 10;
        $page = $per_page * $page;
        $this->db->select('a.category_id, a.category_name, IF(cat_image is null, "", cat_image) as cat_image');
        $this->db->from('product_category a');
        $this->db->where('a.status', 1);
        $this->db->order_by('a.menu_pos', 'asc');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }

    /*
    |-------------------------------
    |   store_list
    |   route = api/react/website_api/store_list
    |-------------------------------
    */
    public function store_list_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $per_page = 10;
        $page = $per_page * $page;
        $this->db->select('seller_id, first_name, last_name, email, mobile, image, seller_store_name, business_name, address, status');
        $this->db->from('seller_information');
        $this->db->where('status', 1);
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   brand_list
    |   route = api/react/website_api/brand_list
    |-------------------------------
    */
    public function brand_list_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $per_page = 10;
        $page = $per_page * $page;
        $this->db->select('brand_id, brand_name, brand_image AS image, website, status');
        $this->db->from('brand');
        $this->db->where('status', 1);
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }

    /*
    |-------------------------------
    |   best_selling
    |   POST
    |   route = api/react/website_api/best_selling
    |-------------------------------
    */
    public function best_selling_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;

        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, null, null, null, 1);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }

    }
    /*
    |-------------------------------
    |   featured_products
    |   POST
    |   route = api/react/website_api/featured_products
    |-------------------------------
    */
    public function featured_products_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;

        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, null, null, null);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }

    }
    /*
    |-------------------------------
    |   new_products
    |   POST
    |   route = api/react/website_api/new_products
    |-------------------------------
    */
    public function new_products_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;

        $where = "(a.quantity > 0 OR a.pre_order = 1)";
        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
        pi.image_path as img_url,pi.image_name,
        (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.product_id = a.product_id) AS ratings,
        b.category_name,
        c.title, 
        e.seller_store_name,
        br.brand_name
        ');
        $this->db->from('product_information a');
        $this->db->join('product_image pi', "a.product_id = pi.product_id AND pi.image_type = 1", 'left');
        $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
        $this->db->join('product_title c', "a.product_id = c.product_id AND c.lang_id='english' ", 'left');
        $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
        $this->db->join('brand br', 'a.brand_id = br.brand_id', 'left');
        $this->db->where('a.status', 2);
        $this->db->where($where);
        if (!empty($latest)) {
            $this->db->order_by('a.product_info_id', 'desc');
        }
        if (!empty($price)) {
            if ($price == 'low_to_high') {
                $this->db->order_by('a.price', 'ASC');
            }
            if ($price == 'high_to_low') {
                $this->db->order_by('a.price', 'desc');
            }
        }else{
            $this->db->order_by('a.product_info_id', 'desc');
        }

        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
            foreach ($data as $key => $value) {
                if ($value['on_sale'] == "1") {
                    $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                    $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                } else {
                    $data[$key]['discount_amount'] = null;
                    $data[$key]['discount_percent'] = null;
                }
                $data[$key]['image_path'] = trim((!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name']);
            }
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   recommended_products
    |   POST
    |   route = api/react/website_api/recommended_products
    |-------------------------------
    */
    public function recommended_products_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;

        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, null, null, null);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }

    /*
    |-------------------------------
    |   slider_list Load Here
    |   route = api/react/website_api/slider_list
    |-------------------------------
    */
    public function slider_list_get()
    {
        //query
        $this->db->select('slider_id,slider_link,slider_image');
        $this->db->from('slider');
        $this->db->where('status', 1);
        $this->db->order_by('slider_position');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }

    /*
    |-------------------------------
    |   campaign_slider_list Load Here
    |   route = api/react/website_api/campaign_slider_list
    |-------------------------------
    */
    public function campaign_slider_list_get()
    {
        //query
        $this->db->select('campaign_id, campaign_name, campaign_bannar');
        $this->db->from('campaign_info');
        $this->db->where('status', 1);
        $this->db->where('start_datetime <=', date('Y-m-d H:i:s'));
        $this->db->where('end_datetime >=', date('Y-m-d H:i:s'));
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $data = $query->result();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   campaign_wise_product
    |   POST
    |   route = api/react/website_api/campaign_wise_product
    |-------------------------------
    */
    public function campaign_wise_product_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('campaign_id', 'campaign', 'required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['campaign_id'])) {
                $errors_data = $errors['campaign_id'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $campaign_id = filter_input_data($this->input->post('campaign_id', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;


        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,cpi.campaign_id,a.price,IF(a.on_sale = 0, "1", a.on_sale) as on_sale,cpi.product_campaign_price as offer_price,cpi.product_quantity as quantity,
        
        pi.image_path as img_url,pi.image_name,
        (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.product_id = a.product_id) AS ratings,
        b.category_name,
        c.title, 
        e.seller_store_name,
        br.brand_name
        ');
        $this->db->from('campaign_product_info cpi');
        $this->db->join('product_information a', "cpi.product_id = a.product_id", 'left');
        $this->db->join('product_image pi', "cpi.product_id = pi.product_id AND pi.image_type = 1", 'left');
        $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
        $this->db->join('product_title c', "cpi.product_id = c.product_id AND c.lang_id='english' ", 'left');
        $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
        $this->db->join('brand br', 'a.brand_id = br.brand_id', 'left');
        $this->db->where('a.status', 2);
        $this->db->where('cpi.campaign_id', $campaign_id);
        if (!empty($latest)) {
            $this->db->order_by('a.product_info_id', 'desc');
        }
        if (!empty($price)) {
            if ($price == 'low_to_high') {
                $this->db->order_by('a.price', 'ASC');
            }
            if ($price == 'high_to_low') {
                $this->db->order_by('a.price', 'desc');
            }
        }
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $info = $this->db->where('campaign_id', $campaign_id)->get('campaign_info')->row();
            $data = $query->result_array();
            foreach ($data as $key => $value) {
                if ($value['on_sale'] == "1") {
                    $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                    $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                } else {
                    $data[$key]['discount_amount'] = null;
                    $data[$key]['discount_percent'] = null;
                }
                $data[$key]['image_path'] = trim((!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name']);
            }
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $data;
            $response['info'] = $info;
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   category_wise_product
    |   POST
    |   route = api/react/website_api/category_wise_product
    |-------------------------------
    */
    public function category_wise_product_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('category_id', 'category', 'required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['category_id'])) {
                $errors_data = $errors['category_id'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $cat_id = filter_input_data($this->input->post('category_id', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;


        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, $cat_id, null);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   related_product
    |   POST
    |   route = api/react/website_api/related_product
    |-------------------------------
    */
    public function related_product_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('category_id', 'category', 'required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['category_id'])) {
                $errors_data = $errors['category_id'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $cat_id = filter_input_data($this->input->post('category_id', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;


        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, $cat_id, null);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   brand_wise_product
    |   POST
    |   route = api/react/website_api/brand_wise_product
    |-------------------------------
    */
    public function brand_wise_product_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('brand_id', 'brand', 'required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['brand_id'])) {
                $errors_data = $errors['brand_id'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $brand_id = filter_input_data($this->input->post('brand_id', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;


        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, null, $brand_id);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    
    /*
    |-------------------------------
    |   search_products
    |   POST
    |-------------------------------
    */
    public function search_products_post()
    {
        $this->form_validation->set_rules('product_name', 'product name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['product_name'])) {
                $errors_data = $errors['product_name'];
            }
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $product_name = filter_input_data($this->input->post('product_name', TRUE));
        $page = filter_input_data($this->input->post('page', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;

        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, null, null, null, null, $product_name);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   block_products
    |   POST
    |-------------------------------
    */
    public function block_products_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;


        $this->db->select('block.*,product_category.category_name AS block_name');
        $this->db->from('block');
        $this->db->join('product_category', 'block.block_cat_id = product_category.category_id');
        $this->db->where('block.status', 1);
        $this->db->order_by('block_position', 'asc');
        $query_block = $this->db->get();
        if ($query_block->num_rows() > 0) {
            $block_list = $query_block->result_array();
            foreach ($block_list as $key_off => $block) {
                $response = $this->category_wise_product_list($per_page, $page, $latest, $price, $block['block_cat_id']);
                if (!empty($response)) {
                    $block_list[$key_off]['product_list'] = (!empty($response['data']) ? $response['data'] : []);
                }
            }
            JSONSuccessOutput($block_list);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   store_wise_product
    |   POST
    |   route = api/react/website_api/store_wise_product
    |-------------------------------
    */
    public function store_wise_product_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('store_id', 'store', 'required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['store_id'])) {
                $errors_data = $errors['store_id'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $seller_id = filter_input_data($this->input->post('store_id', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;


        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, null, null, $seller_id);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    
    public function category_wise_product_list($per_page = null, $page = null, $latest = null, $price = null, $cat_id = null, $brand_id = null, $seller_id = null, $best_sale = null, $product_name = null)
    {
        if (!empty($cat_id)) {
            $cat_id_list = $this->db->query("
                SELECT c1.category_id as c1cat, c2.category_id as c2cat, c3.category_id as c3cat, c4.category_id as c4cat
                    FROM product_category c1 
                    LEFT outer join ( SELECT `parent_category_id`,category_id FROM `product_category` ) c2 on (c2.`parent_category_id`=c1.`category_id`) 
                    LEFT outer join ( SELECT `parent_category_id`,category_id FROM `product_category` ) c3 on (c3.`parent_category_id`=c2.`category_id`)
                    LEFT outer join ( SELECT `parent_category_id`,category_id FROM `product_category` ) c4 on (c3.`parent_category_id`=c3.`category_id`) 
                    WHERE c1.`parent_category_id`= '" . $cat_id . "'
            ");
            $cat_id_array = ($cat_id_list->result_array());
            if (!empty($cat_id_array)) {
                foreach ($cat_id_array as $row_data) {
                    $c1cat[] = $row_data['c1cat'];
                    $c1cat[] = $row_data['c2cat'];
                    $c1cat[] = $row_data['c3cat'];
                    $c1cat[] = $row_data['c4cat'];
                }

                $c1cat_uniq = array_unique($c1cat);
                $all_cat_id = $c1cat_uniq;
            } else {
                $all_cat_id = $cat_id;
            }
        }

        $where = "(a.quantity > 0 OR a.pre_order = 1)";
        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
        pi.image_path as img_url,pi.image_name,
        (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.product_id = a.product_id) AS ratings,
        b.category_name,
        c.title, 
        e.seller_store_name,
        br.brand_name
        ');
        $this->db->from('product_information a');
        $this->db->join('product_image pi', "a.product_id = pi.product_id AND pi.image_type = 1 AND pi.status = 1", 'left');
        $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
        $this->db->join('product_title c', "a.product_id = c.product_id AND c.lang_id='english' ", 'left');
        $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
        $this->db->join('brand br', 'a.brand_id = br.brand_id', 'left');
        $this->db->where('a.status', 2);
        $this->db->where($where);
        if (!empty($best_sale)) {
            $this->db->where('a.best_sale', 1);
        }
        if (!empty($product_name)) {
            $like_where = "(`c`.`title` LIKE '%" . $product_name . "%' ESCAPE '!' OR  `a`.`product_model` LIKE '%" . $product_name . "%' ESCAPE '!' OR  `b`.`category_name` LIKE '%" . $product_name . "%' ESCAPE '!')";
            $this->db->where($like_where);
        }
        if (!empty($brand_id)) {
            $this->db->where('a.brand_id', $brand_id);
        }
        if (!empty($seller_id)) {
            $this->db->where('a.seller_id', $seller_id);
        }
        if (!empty($cat_id)) {
            $this->db->where_in('a.category_id', $all_cat_id);
        }
        if (!empty($latest)) {
            $this->db->order_by('a.product_info_id', 'desc');
        }
        if (!empty($price)) {
            if ($price == 'low_to_high') {
                $this->db->order_by('a.price', 'ASC');
            }
            if ($price == 'high_to_low') {
                $this->db->order_by('a.price', 'desc');
            }
        }
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {

            if (!empty($cat_id)) {
                $this->db->select('category_id as id, cat_image AS image, category_name as name, status');
                $this->db->from('product_category');
                $this->db->where('category_id', $cat_id);
                $info = $this->db->get()->row();
            }

            if (!empty($brand_id)) {
                $this->db->select('brand_id as id, brand_image as image, brand_name as name, status');
                $this->db->from('brand');
                $this->db->where('brand_id', $brand_id);
                $info = $this->db->get()->row();
            }

            if (!empty($seller_id)) {
                $this->db->select('seller_id as id, image, business_name as name, status');
                $this->db->from('seller_information');
                $this->db->where('seller_id', $seller_id);
                $info = $this->db->get()->row();
            }

            $data = $query->result_array();
            foreach ($data as $key => $value) {
                if ($value['on_sale'] == "1") {
                    $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                    $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                } else {
                    $data[$key]['discount_amount'] = null;
                    $data[$key]['discount_percent'] = null;
                }
                $data[$key]['image_path'] = trim((!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name']);
            }
            $response['data'] = $data;
            $response['info'] = !empty($info)?$info:[];
            return $response;
        } else {
            return false;
        }
    }
    /*
    |-------------------------------
    |   product_details Load Here
    |   route = api/react/website_api/product_details
    |-------------------------------
    */
    public function product_details_post()
    {
        $this->form_validation->set_rules('product_id', 'product', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('campaign_id', 'campaign', 'is_natural|required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['product_id'])) {
                $errors_data = $errors['product_id'];
            }
            if (!empty($errors['campaign_id'])) {
                $errors_data = $errors['campaign_id'];
            }
            JSONErrorOutput($errors_data);
        }
        $product_id = filter_input_data($this->input->post('product_id', TRUE));
        $campaign_id = filter_input_data($this->input->post('campaign_id', TRUE));

        if (empty($campaign_id)) {
            JSONErrorOutput("Campaign is required!");
        } else {
            if ($campaign_id == "1") {
                $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
                pi.image_path as img_url,pi.image_name,
                (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.product_id = a.product_id) AS ratings,
                (SELECT count(pr.product_review_id) FROM product_review pr WHERE pr.product_id = a.product_id) AS total_review,
                b.category_name,
                c.title, 
                e.seller_store_name,
                br.brand_name
                ');
                $this->db->from('product_information a');
                $this->db->join('product_image pi', "a.product_id = pi.product_id AND pi.image_type = 1 AND pi.status = 1", 'left');
                $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
                $this->db->join('product_title c', "a.product_id = c.product_id AND c.lang_id='english' ", 'left');
                $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
                $this->db->join('brand br', 'a.brand_id = br.brand_id', 'left');
                $this->db->where('a.status', 2);
                $this->db->where('a.product_id', $product_id);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $data = $query->result_array();
                    foreach ($data as $key => $value) {
                        if ($value['on_sale'] == "1") {
                            $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                            $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                        } else {
                            $data[$key]['discount_amount'] = null;
                            $data[$key]['discount_percent'] = null;
                        }
                        $data[$key]['image_path'] = trim((!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name']);
                    }
                    JSONSuccessOutput($data);
                } else {
                    JSONNoOutput("No Data Found!");
                }
            } else {
                $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,cpi.campaign_id,a.price,IF(a.on_sale = 0, "1", a.on_sale) as on_sale,cpi.product_campaign_price as offer_price,cpi.product_quantity as quantity,
                pi.image_path as img_url,pi.image_name,
                (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.product_id = a.product_id) AS ratings,
                b.category_name,
                c.title, 
                e.seller_store_name,
                br.brand_name
                ');
                $this->db->from('campaign_product_info cpi');
                $this->db->join('product_information a', "cpi.product_id = a.product_id", 'left');
                $this->db->join('product_image pi', "cpi.product_id = pi.product_id AND pi.image_type = 1", 'left');
                $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
                $this->db->join('product_title c', "cpi.product_id = c.product_id AND c.lang_id='english' ", 'left');
                $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
                $this->db->join('brand br', 'a.brand_id = br.brand_id', 'left');
                $this->db->where('a.status', 2);
                $this->db->where('cpi.product_id', $product_id);
                $this->db->where('cpi.campaign_id', $campaign_id);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $info = $this->db->where('campaign_id', $campaign_id)->get('campaign_info')->row();
                    $data = $query->result_array();
                    foreach ($data as $key => $value) {
                        if ($value['on_sale'] == "1") {
                            $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                            $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                        } else {
                            $data[$key]['discount_amount'] = null;
                            $data[$key]['discount_percent'] = null;
                        }
                        $data[$key]['image_path'] = trim((!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name']);
                    }
                    header('Content-Type: application/json');
                    $response['response_status'] = 200;
                    $response['message'] = '';
                    $response['status'] = 'success';
                    $response['data'] = $data;
                    $response['info'] = $info;
                    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    exit;
                } else {
                    JSONNoOutput("No Data Found!");
                }
            }
        }
    }
    /*
    |-------------------------------
    |   product_description
    |   post
    |-------------------------------
    */
    public function product_description_post()
    {
        $this->form_validation->set_rules('product_id', 'product', 'is_natural|required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['product_id'])) {
                $errors_data = $errors['product_id'];
            }
            JSONErrorOutput($errors_data);
        }
        $product_id = filter_input_data($this->input->post('product_id', TRUE));
        $result = [];
        //description
        $this->db->select('description');
        $this->db->from('product_description');
        $this->db->where('product_id', $product_id);
        $query1 = $this->db->get();
        if ($query1->num_rows() > 0) {
            $result['description'] = $query1->row();
        }else{
            $result['description'] = '';
        }
        //product_review
        $this->db->select('count(product_review_id) as total_review, round(AVG(rate), 2) AS avg_rating, SUM(IF(rate=5, 1, 0)) AS five_star, SUM(IF(rate=4, 1, 0)) AS four_star, SUM(IF(rate=3, 1, 0)) AS three_star, SUM(IF(rate=2, 1, 0)) AS two_star, SUM(IF(rate=1, 1, 0)) AS one_star');
        $this->db->from('product_review');
        $this->db->where('status', 1);
        $this->db->where('product_id', $product_id);
        $query2 = $this->db->get();
        if ($query2->num_rows() > 0) {
            $result_array   =  $query2->row_array();
            $data =  array_map("null_check", $result_array);
            $result['product_review'] = $data;
        }else{
            $result['product_review'] = '';
        }
        //refund_policy
        $this->db->select('headlines, details, image');
        $this->db->from('link_page');
        $this->db->where('status', "1");
        $this->db->where('page_id', 7);
        $this->db->where('language_id', "english");
        $query3 = $this->db->get();
        if ($query3->num_rows() > 0) {
            $result['refund_policy'] = $query3->row();
        }else{
            $result['refund_policy'] = '';
        }
        
        JSONSuccessOutput($result);
    }
    /*
    |-------------------------------
    |   product_list
    |   post
    |-------------------------------
    */
    public function product_list_post()
    {
        $this->form_validation->set_rules('page', 'page', 'is_natural|required|trim|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'in_list[low_to_high,high_to_low]|trim|xss_clean');
        $this->form_validation->set_rules('latest', 'latest', 'in_list[1]|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            if (!empty($errors['price'])) {
                $errors_data = $errors['price'];
            }
            if (!empty($errors['latest'])) {
                $errors_data = $errors['latest'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $price = filter_input_data($this->input->post('price', TRUE));
        $latest = filter_input_data($this->input->post('latest', TRUE));
        $per_page = 10;
        $page = $per_page * $page;


        $res = $this->category_wise_product_list($per_page, $page, $latest, $price, null, null);
        if (!empty($res)) {
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['data'] = $res['data'];
            $response['info'] = $res['info'];
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No Data Found!");
        }
    }

    /*
    |-------------------------------
    |   brand_list Load Here
    |   route = api/react/website_api/brand_list
    |-------------------------------
    */
    public function brand_list_get()
    {
        //query
        $this->db->select('brand_id, brand_name, brand_image AS image, website, status');
        $this->db->from('brand');
        $this->db->where('status', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   cart_products
    |   post
    |-------------------------------
    */
    public function cart_products_post()
    {
        $this->form_validation->set_rules('id_in', 'id_in', 'required|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['id_in'])) {
                $errors_data = $errors['id_in'];
            }
            JSONErrorOutput($errors_data);
        }
        $id_in = filter_input_data($this->input->post('id_in', TRUE));
        $all_id = (explode("--", $id_in));
        
        $i = 0;
        $condition_campaign_normal = [];
        $condition_campaign_campaign = [];
        $array1 = [];
        $array2 = [];
        foreach ($all_id as $key => $value) {
            $data_array = (explode("@@", $value));
            $p_array[$i]['p'] = !empty($data_array[0]) ? $data_array[0] : '';
            $p_array[$i]['c'] = !empty($data_array[1]) ? $data_array[1] : '';
            if ($p_array[$i]['c'] == "1") {
                $condition_campaign_normal[] = ("a.product_id = " . $p_array[$i]['p']);
            } else {
                $condition_campaign_campaign[] = ("(cpi.product_id = '" . $p_array[$i]['p'] . "' AND cpi.campaign_id = '" . $p_array[$i]['c'] . "')");
            }
            $i++;
        }
        $condition_campaign = implode(" OR ", $condition_campaign_campaign);
        $condition_normal = implode(" OR ", $condition_campaign_normal);
        if (!empty($condition_campaign)) {
            $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,cpi.campaign_id,a.price,IF(a.on_sale = 0, "1", a.on_sale) as on_sale,cpi.product_campaign_price as offer_price,cpi.product_quantity as quantity,
            pi.image_path as img_url,pi.image_name,
            (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.product_id = a.product_id) AS ratings,
            b.category_name,
            c.title, 
            e.seller_store_name,
            br.brand_name
            ');
            $this->db->from('campaign_product_info cpi');
            $this->db->join('product_information a', "cpi.product_id = a.product_id", 'left');
            $this->db->join('product_image pi', "cpi.product_id = pi.product_id AND pi.image_type = 1 AND pi.status = 1", 'left');
            $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
            $this->db->join('product_title c', "cpi.product_id = c.product_id AND c.lang_id='english' ", 'left');
            $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
            $this->db->join('brand br', 'a.brand_id = br.brand_id', 'left');
            $this->db->where('a.status', 2);
            $this->db->where($condition_campaign);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $data = $query->result_array();
                foreach ($data as $key => $value) {
                    if ($value['on_sale'] == "1") {
                        $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                        $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                    } else {
                        $data[$key]['discount_amount'] = null;
                        $data[$key]['discount_percent'] = null;
                    }
                    $data[$key]['image_path'] = trim((!empty($value['img_url'])) ? $value['img_url'] : THUMB_CDN_DIR . $value['image_name']);
                }
                $array1 = ($data);
            }
        } 
        if (!empty($condition_normal)) {
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
            pi.image_path as img_url,pi.image_name,
            (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.product_id = a.product_id) AS ratings,
            b.category_name,
            c.title, 
            e.seller_store_name,
            br.brand_name
            ');
            $this->db->from('product_information a');
            $this->db->join('product_image pi', "a.product_id = pi.product_id AND pi.image_type = 1 AND pi.status = 1", 'left');
            $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
            $this->db->join('product_title c', "a.product_id = c.product_id AND c.lang_id='english' ", 'left');
            $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
            $this->db->join('brand br', 'a.brand_id = br.brand_id', 'left');
            $this->db->where('a.status', 2);
            $this->db->where($where);
            $this->db->where($condition_normal);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $data = $query->result_array();
                foreach ($data as $key => $value) {
                    if ($value['on_sale'] == "1") {
                        $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                        $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                    } else {
                        $data[$key]['discount_amount'] = null;
                        $data[$key]['discount_percent'] = null;
                    }
                    $data[$key]['image_path'] = trim((!empty($value['img_url'])) ? $value['img_url'] : THUMB_CDN_DIR . $value['image_name']);
                }
                $array2 = ($data);
            } 
        } 
        $result = array_merge($array1, $array2);
        JSONSuccessOutput($result);
    }


    //Submit checkout
    public function submit_checkout_post()
    {
        $get_order_data = file_get_contents('php://input');
        $order_info = json_decode($get_order_data);
        if (empty($order_info->address_id)) {
            JSONErrorOutput('Address is required!');
        }
        if (empty($order_info->customer_id)) {
            JSONErrorOutput('Customer ID is required!');
        }
        if (empty($order_info->cart_details)) {
            JSONErrorOutput('Cart Details is required!');
        }

        $customer_id = filter_input_data($order_info->customer_id);
        $cart_details = $order_info->cart_details;
        $address_id = filter_input_data($order_info->address_id);
        $coupon_code = filter_input_data(@$order_info->coupon_code);
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
       
        $address = $this->db->select("COUNT(address_id) as num")->from("customer_address")->where("address_id", $address_id)->where("customer_id", $customer_id)->get()->row();
        if (empty($address->num)) {
            JSONErrorOutput('Invalid customer address');
        }
        $coupon_amnt = 0;
        if (!empty($coupon_code)) {
            $cart_details = ($cart_details);
            $coupon_res = $this->apply_coupon($customer_id, $coupon_code, $cart_details);
            $coupon_amnt = $coupon_res['coupon_amnt'];
        }
        //customer_id & customer_code set
        if (!empty($customer_id)) {
            //customer_information by customer_id
            $this->db->select('customer_code,customer_email,company');
            $this->db->from('customer_information');
            $this->db->where('customer_id', $customer_id);
            $query = $this->db->get();
            $data   =  $query->row();
            if (!empty($data)) {
                $customer_code = $data->customer_code;
                $customer_email = $data->customer_email;
                $company = $data->company;
            } else {
                JSONErrorOutput('Invalid customer ID');
            }
        }
        //select address details
        $this->db->select('*');
        $this->db->from('customer_address');
        $this->db->where('address_id', $address_id);
        $query = $this->db->get();
        $result = $query->row();

        $customer_name = $result->customer_name;
        $customer_phone = $result->customer_phone;
        $division_id = $result->division;
        $city_id = $result->city;
        $area_name = $result->area;
        $address_name = $result->address;

        if (!empty(COUNTRY_ID)) {
            $this->db->select('*');
            $this->db->from('countries');
            $this->db->where('id', COUNTRY_ID);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $country_name = $result->name;
            } else {
                JSONErrorOutput('Invalid country id!');
            }
        }
        //select state name by id id
        if (!empty($division_id)) {
            $this->db->select('*');
            $this->db->from('states');
            $this->db->where('id', $division_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $state_name = $result->name;
            } else {
                JSONErrorOutput('Invalid state id!');
            }
        }
        //select city name by city id
        if (!empty($city_id)) {
            $this->db->select('*');
            $this->db->from('cities');
            $this->db->where('id', $city_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $city_name = $result->name;
            } else {
                JSONErrorOutput('Invalid city id!');
            }
        }
        //shipping cost by city
        if (!empty($city_id)) {
            $this->db->select('*');
            $this->db->from('shipping_method');
            $this->db->where('city', $city_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $ship_cost = $result->charge_amount;
            } else {
                $ship_cost = 0;
            }
        }

        //set $order_info->data 
        $order_id   = generator(15);

        // Check product status before order
        // check product status
        $is_exist = 'yes';
        $cart_total_amount = 0;
        $vat_amount = 0;
        $discount = 0;
        $order_details = "";
        $temp = null;
        if (!empty($cart_details)) {
            $cart_detail = ($cart_details);
            $dateTime = date("Y-m-d H:i:s");
            if (count($cart_detail) > 1 ) {
                $f=0;
            }else {
                $f=1;
            }
            foreach ($cart_detail as $items) {
                $checkcategory = $this->db->select('pi.category_id, pi.seller_id')->from('product_information pi')->where('product_id', $items->product_id)->get()->row();
                if (empty($checkcategory)) {
                    JSONErrorOutput("Invalid productid: $items->product_id!");
                }
                if (empty($items->campaign_id)) {
                    JSONErrorOutput("Campaign id required!");
                }
                $campaign_info = $this->db->select("*")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->get()->row();
                if (empty($campaign_info) & $items->campaign_id != 1) {
                    JSONErrorOutput("Invalid campaign_id: $items->campaign_id!");
                }
                if ($items->qty <= 0) {
                    JSONErrorOutput("Invalid Quantity: $items->qty!");
                }
                if ($temp == null) {
                    $temp = $checkcategory->seller_id;
                } else if ($temp == $checkcategory->seller_id) {
                    $f=1;
                }
                $multiInvCheck = $this->db->select("COUNT(category_id) as num")->from("product_category")->where("category_id", $checkcategory->category_id)->where("multi_inv", 1)->get()->row();
                // if ($multiInvCheck->num > 0 && $items->qty > 1) {
                //     JSONErrorOutput('Please don\'t order more than 1 item and 1 quantity, We may cancel your Order if you order more than one in one invoice');
                // }
                if ($items->campaign_id != 1 ) {
                    $this->db->where('campaign_id', $items->campaign_id);
                    $this->db->where('start_datetime<=', $dateTime);
                    $this->db->where('end_datetime>=', $dateTime);
                    $query  = $this->db->get('campaign_info');
                    $result = $query->result_array();
                    if (count($result) == 1) {
                        $order_info = $this->db->select("product_campaign_price as price")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->where("product_id", $items->product_id)->get()->row();
                        if (empty($order_info)) {
                            JSONErrorOutput('Campaign product not found!');
                        }
                    } else {
                        JSONErrorOutput('Campaign id not found!');
                    }
                    $cart_total_amount += $order_info->price*$items->qty;
                } else if (!empty($items->product_id) & $items->campaign_id == 1) {
                    $this->db->where('product_id', $items->product_id);
                    $query  = $this->db->get('product_information');
                    $result = $query->result_array();
                    if (count($result) == 1) {
                        $pinfo = $this->db->select('status,on_sale')
                            ->from('product_information')
                            ->where('product_id', $items->product_id)
                            ->get()->row();
                        if ($pinfo->status != '2') {
                            $is_exist = 'no';
                        } else {
                            if ($pinfo->on_sale == 0) {
                                $order_info = $this->db->select('pi.seller_id,pi.category_id,pi.price,pi.quantity,pi.vat')
                                    ->from('product_information pi')
                                    ->where('product_id', $items->product_id)
                                    ->get()->row();
                                $cart_total_amount += $order_info->price*$items->qty;
                            } else {
                                $order_info = $this->db->select('pi.seller_id,pi.category_id,pi.offer_price,pi.price,pi.quantity,pi.vat')
                                    ->from('product_information pi')
                                    ->where('product_id', $items->product_id)
                                    ->get()->row();
                                $cart_total_amount += $order_info->offer_price*$items->qty;
                                $discount += ($order_info->price - $order_info->offer_price)*$items->qty;
                            }
                            $vat_amount += $order_info->vat*$items->qty;
                        }
                    } else {
                        JSONErrorOutput('Product ID not found!');
                    }
                } else {
                    JSONErrorOutput("Campaign Products and General Products can not be in same Cart!, Please Order Differently");
                }
            }
            //end foreach
            // if ($f == 0) {
            //     JSONErrorOutput('Please don\'t order different seller product in same cart, We may cancel your Order if you order more than one in one invoice');
            
            // }
            if ($cart_total_amount < 500) {
                JSONErrorOutput('You have to orderd 500tk minimum to procced checkout');
            }
        } else {
            JSONErrorOutput('No product is added in cart!');
        }
        // If all ordered products not approved
        if ($is_exist == 'no') {
            JSONErrorOutput('Failed! Products not exist!');
        }
        $totalAmount = $cart_total_amount + $vat_amount + $ship_cost;
        $paid_amount = null;
        //set amount parameter in costing_info array
        $costing_info = array(
            'cart_total_amount' => $cart_total_amount,
            'vat_amount' => $vat_amount,
            'ship_cost' => $ship_cost,
            'coupon_amnt' => $coupon_amnt,
            'discount' => $discount,
            'totalAmount' => $totalAmount,
            'paid_amount' => $paid_amount,
        );
        $order_details_info = array(
            'customer_id' => $customer_id,
            'order_id' => $order_id,
            'order_details' => $order_details,
            'city_id' => $city_id,
        );

        //new customer new shipping address and order entry
        $country_name = "";
        $billing_info = array(
            'customer_id'           => $customer_id,
            'customer_code'         => $customer_code,
            'customer_name'         => $customer_name,
            'first_name'            => $customer_name,
            'last_name'             => "",
            'customer_short_address' => $city_name . "," . $state_name . "," . $area_name,
            'customer_address_1'    => $city_name . "," . $state_name . "," . $area_name,
            'customer_address_2'    => "",
            'city'                  => $city_name,
            'state'                 => $state_name,
            'country'               => $country_name,
            'zip'                   => $area_name,
            'company'               => $company ? $company : "None",
            'customer_mobile'       => $customer_phone,
            'customer_email'        => $customer_email,
            'image'                 => 'my-assets/image/avatar.png',
        );
        $return_order_id = $this->order_entry($cart_detail, $order_details_info, $costing_info, $billing_info, $f);
        if (!empty($return_order_id)) {
            JSONSuccessOutput(Null, 'Product Successfully Ordered');
        }else {
            JSONErrorOutput("Order Failed!");
        }
        exit;
    }

    
    public function shipping_entry($data, $order_id = NULL)
    {
        if ($order_id != NULL) {
            $data['order_id'] = $order_id;
        }
        $result = $this->db->insert('shipping_info', $data);
        if ($result) {
            return true;
        }
        return false;
    }
    public function order_entry($cart_details = null, $order_details_info = null, $costing_info = null, $billing_info = null, $diff_seller = null)
    {
        //costing_info
        $vat            = (!empty($costing_info['vat_amount']) ? $costing_info['vat_amount'] : 0);
        $cart_ship_cost = (!empty($costing_info['ship_cost']) ? $costing_info['ship_cost'] : 0);
        $discount       = (!empty($costing_info['discount']) ? $costing_info['discount'] : 0);
        $coupon_amnt    = (!empty($costing_info['coupon_amnt']) ? $costing_info['coupon_amnt'] : 0);
        $totalAmount    = (!empty($costing_info['totalAmount']) ? $costing_info['totalAmount'] : 0);
        $paid_amount    = (!empty($costing_info['paid_amount']) ? $costing_info['paid_amount'] : 0);
        //order_details_info
        $customer_id = $order_details_info['customer_id'];
        $order_details = $order_details_info['order_details'];
        $payment_method = 'cash';
        // $payment_method = $order_details_info['payment_method'];
        $city_id = $order_details_info['city_id'];

        if ($diff_seller == 0) {
            if ($cart_details) {
                $quantity = 0;
                foreach ($cart_details as $items) {
                    $order_id = generator(15);
                    if ($items->campaign_id != 1) {
                        $stock = $this->db->select('product_quantity as quantity')
                            ->from('campaign_product_info')
                            ->where('product_id', $items->product_id)
                            ->where('campaign_id', $items->campaign_id)
                            ->get()
                            ->row();
                        if (!empty($stock)) {
                            if ($stock->quantity < $items->qty) {
                                JSONErrorOutput("You can not order more than stock");
                            }
                        }
                    } else {
                        $stock = $this->db->select('*')
                            ->from('product_information')
                            ->where('product_id', $items->product_id)
                            //->where('pre_order',1)
                            ->get()
                            ->row();
                        if (!empty($stock)) {
                            if ($stock->quantity < $items->qty) {
                                JSONErrorOutput("You can not order more than stock");
                            }
                        }
                    }
                    if (!empty($items)) {
                        //Seller percentage
                        $comission_rate = $this->comission_info($items->product_id);
                        $category_id   = $this->category_id($items->product_id);
                        $sinfo = $this->product_infos($items->product_id);
                        $seller_id = $sinfo->seller_id;
                        $rate = $sinfo->price;
    
                        //seller_order_data
                        if ($items->campaign_id != 1) {
                            $order_info = $this->db->select("product_campaign_price as price")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->where("product_id", $items->product_id)->get()->row();
                            $total_price = $order_info->price;
                            $discount_per_product = $rate - $order_info->price;
    
                        } else {
                            if ($sinfo->on_sale == 0) {
                                $total_price = $rate;
                                $discount_per_product = 0;
                            } else {
                                $total_price = $sinfo->offer_price;
                                $discount_per_product = $rate - $sinfo->offer_price;
                            }
                        }
                        $seller_order_data = array(
                            'order_id'                =>    $order_id,
                            'seller_id'                =>    $seller_id,
                            'seller_percentage'     =>  $comission_rate,
                            'customer_id'            =>    $customer_id,
                            'campaign_id'            =>    $items->campaign_id,
                            'category_id'            =>    $category_id,
                            'product_id'            =>    $items->product_id,
                            'variant_id'            =>    '',
                            'quantity'                =>    $items->qty,
                            'rate'                    =>    $rate,
                            'total_price'           => ($total_price * $items->qty),
                            'discount_per_product'    =>    $discount_per_product,
                            'product_vat'            =>    '',
                        );
                        //Total quantity count
                        $quantity += $items->qty;
                        $this->db->insert('seller_order', $seller_order_data);
                        if ($items->campaign_id != 1) {
                            //Product stock update
                            $this->db->set('product_quantity', 'product_quantity-' . $items->qty, FALSE);
                            $this->db->where('product_id', $items->product_id);
                            $this->db->update('campaign_product_info');
                        } else {
                            //Product stock update
                            $this->db->set('quantity', 'quantity-' . $items->qty, FALSE);
                            $this->db->where('product_id', $items->product_id);
                            $this->db->update('product_information');
                        }
                    }

                    //insert shipping info
                    $this->shipping_entry($billing_info, $order_id);
                    //order_payment entry start
                    $order_payment_data = array(
                        'order_payment_id' => generator(15), //api_helper.php
                        'payment_id'        => $payment_method,
                        'order_id'            => $order_id,
                        'details'           => $order_details,
                    );
                    $this->db->insert('order_payment', $order_payment_data);
                    ////////
                    $order_no = "EZ" . mt_rand(100000000000, 999999999999);
                    $this->db->select('order_no');
                    $this->db->where('order_no', $order_no);
                    $query = $this->db->get('order');
                    $result = $query->num_rows();
                    if ($result > 0) {
                        $order_no = "EZ" . mt_rand(100000000000, 999999999999);
                    }
                    //Data insert into order table
                    $n_order = array(
                        'order_id'           => $order_id,
                        'order_no'           => $order_no,
                        'customer_id'     => $customer_id,
                        'shipping_id'     => $city_id,
                        'date'               => date("Y-m-d"),
                        'time'               => date("h:i a"),
                        // 'details'          => $order_details,
                        'total_amount'    => $seller_order_data['total_price'],
                        'paid_amount'     => 0,
                        'total_discount'  => 0,
                        // 'coupon_discount'   => $coupon_amnt,
                        // 'service_charge' => $cart_ship_cost,
                        // 'vat'             => $vat,
                        'order_status'     => 1,
                        'pending'        => date("Y-m-d")
                    );
                    $this->db->insert('order', $n_order);
            
                    //Order intsert in order_tracking table
                    $order_tracking = array(
                        'order_id'      => $order_id,
                        'customer_id'   => $customer_id,
                        'date'          => date("Y-m-d h:i a"),
                        'message'       => 'Order Placed',
                        'order_status'  => 1
            
                    );
                    $this->db->insert('order_tracking', $order_tracking);
            
                    //delete coupon_log
                    $this->db->delete('coupon_logs', array('customer_id' => $customer_id));
                    
                    // return $order_id;
                }
                //end foreach
                JSONSuccessOutput(Null, 'Product Successfully Ordered');
            }
            
        }else {
            $order_id = $order_details_info['order_id'];
            //insert shipping info
            $this->shipping_entry($billing_info, $order_id);
            //order_payment entry start
            $order_payment_data = array(
                'order_payment_id' => generator(15), //api_helper.php
                'payment_id'        => $payment_method,
                'order_id'            => $order_id,
                'details'           => $order_details,
            );
            $this->db->insert('order_payment', $order_payment_data);
            //order_payment entry end
            //Insert order to seller_order table and update quantity in product_information start
            $seller_order = $this->seller_order($order_id, $customer_id, $cart_details);
            if ($seller_order == 0) {
                JSONErrorOutput('Order did not Placed');
            }
            ////////
            $order_no = "EZ" . mt_rand(100000000000, 999999999999);
            $this->db->select('order_no');
            $this->db->where('order_no', $order_no);
            $query = $this->db->get('order');
            $result = $query->num_rows();
            if ($result > 0) {
                $order_no = "EZ" . mt_rand(100000000000, 999999999999);
            }
            //Data insert into order table
            $n_order = array(
                'order_id'           => $order_id,
                'order_no'           => $order_no,
                'customer_id'       => $customer_id,
                'shipping_id'       => $city_id,
                'date'               => date("Y-m-d"),
                'time'               => date("h:i a"),
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paid_amount,
                'total_discount'  => ($discount + $coupon_amnt),
                'order_status'     => 1,
                'pending'        => date("Y-m-d")
            );
            $this->db->insert('order', $n_order);
    
            //Order intsert in order_tracking table
            $order_tracking = array(
                'order_id'      => $order_id,
                'customer_id'   => $customer_id,
                'date'          => date("Y-m-d h:i a"),
                'message'       => 'Order Placed',
                'order_status'  => 1
    
            );
            $this->db->insert('order_tracking', $order_tracking);
    
            //delete coupon_log
            $this->db->delete('coupon_logs', array('customer_id' => $customer_id));
            
            return $order_id;
        }
    }



    //Comission info by product id from model website\Homes.php
    public function comission_info($product_id)
    {
        $comission = $this->db->select('*')
            ->from('product_information')
            ->where('product_id', $product_id)
            ->get()
            ->row();

        if ($comission) {
            return $comission->comission;
        } else {
            return 0;
        }
    }
    //Category id by product id from model website\Homes.php
    public function category_id($product_id)
    {
        $category = $this->db->select('*')
            ->from('product_information')
            ->where('product_id', $product_id)
            ->get()
            ->row();

        if ($category) {
            return $category->category_id;
        } else {
            return null;
        }
    }
    public function product_infos($product_id)
    {
        return $this->db->select('*')
            ->from('product_information')
            ->where('product_id', $product_id)
            ->get()
            ->row();
    }
    //Order html Data
    public function order_html_data($order_id)
    {
        $CI = &get_instance();
        $CI->load->library('occational');
        $CI->load->library('Pdfgenerator');

        $order_detail         = $this->retrieve_order_html_data($order_id);
        if (empty($order_detail)) {
            JSONErrorOutput('Invalid Info');
        }

        //Payment Method
        $paymethod = $this->get_payment_method($order_id);

        $subTotal_quantity     = 0;

        if (!empty($order_detail)) {
            $i = 1;
            foreach ($order_detail as $k => $v) {
                $order_detail[$k]['final_date'] = $CI->occational->dateConvert($order_detail[$k]['date']);
                $subTotal_quantity = $subTotal_quantity + $order_detail[$k]['quantity'];

                $order_detail[$k]['sl'] = $i;
                $i++;
            }
        }

        $currency_details = $this->retrieve_currency_info();
        $company_info       = $this->retrieve_company();
        $agent = $this->db->select("agent")->from("payment_gateway")->where("code", $paymethod['payment_id'])->get()->row();
        $data = array(
            'title'                =>    display('order_details'),
            'order_id'            =>    $order_detail[0]['order_id'],
            'order_no'            =>    $order_detail[0]['order_no'],
            'customer_address'    =>    $order_detail[0]['ship_address'],
            'customer_city'     =>  $order_detail[0]['ship_city'],
            'customer_state'     =>  $order_detail[0]['ship_state'],
            'seller_name'     =>  $order_detail[0]['seller_name'],
            'customer_name'        =>    $order_detail[0]['customer_name'],
            'customer_mobile'    =>    $order_detail[0]['customer_mobile'],
            'customer_email'    =>    $order_detail[0]['customer_email'],
            'final_date'        =>    $order_detail[0]['final_date'],
            'total_amount'        =>    $order_detail[0]['total_amount'],
            'order_discount'     =>    $order_detail[0]['order_discount'],
            'total_discount'     =>    $order_detail[0]['total_discount'] + $order_detail[0]['order_discount'],
            'paid_amount'        =>    $order_detail[0]['paid_amount'],
            'due_amount'        =>    $order_detail[0]['total_amount'] - $order_detail[0]['paid_amount'],
            'details'            =>    $order_detail[0]['details'],
            'service_charge'    =>    $order_detail[0]['service_charge'],
            'subTotal_quantity'    =>    $subTotal_quantity,
            'order_all_data'     =>    $order_detail,
            'company_info'        =>    $company_info,
            'currency'             =>     $currency_details[0]['currency_icon'],
            'position'             =>     $currency_details[0]['currency_position'],
            'paymethod'         =>     $paymethod,
            'paymethod_name'         =>  $agent->agent,
            'vats'         =>  $order_detail[0]['vat'],
        );


        $send_email = '';
        if (ENVIRONMENT == "production") {
            $chapterList = $CI->parser->parse('order/order_pdf', $data, true);
            $dompdf = new DOMPDF();
            $dompdf->loadHtml($chapterList);
            $dompdf->render();
            $output = $dompdf->output();
            file_put_contents('my-assets/pdf/' . $order_detail[0]['order_no'] . '.pdf', $output);
            $file_path = 'my-assets/pdf/' . $order_detail[0]['order_no'] . '.pdf';
            //File path save to database
            $CI->db->set('file_path', base_url($file_path));
            $CI->db->where('order_id', $order_id);
            $CI->db->update('order');

            if (!empty($data['customer_email'])) {
                $send_email = $this->setmail($data['customer_email'], $file_path, null);
            }
        }
        if ($send_email != null) {
            return true;
        } else {
            JSONSuccessOutput(null, 'Product successfully ordered');
        }
    }

    //Retrieve order_html_data
    public function retrieve_order_html_data($order_id)
    {
        $lang_id   = 0;
        $user_lang = $this->session->userdata('language');
        if (empty($user_lang)) {
            $lang_id = 'english';
        } else {
            $lang_id = $user_lang;
        }

        $this->db->select('
			a.*,
			b.*,
			c.*,
			d.product_id,d.thumb_image_url,
			d.product_model,d.unit,
			e.unit_short_name,
			f.variant_name,
			g.title as product_name,
			a.details, 
			CONCAT(s.first_name," ", s.last_name) as seller_name,
			p.customer_address_1 as ship_address, p.city as ship_city, p.state as ship_state
			');
        $this->db->from('order a');
        $this->db->join('customer_information b', 'b.customer_id = a.customer_id');
        $this->db->join('seller_order c', 'c.order_id = a.order_id');
        $this->db->join('seller_information s', 'c.seller_id = s.seller_id and s.status=1', 'left');
        $this->db->join('shipping_info p', 'a.customer_id = p.customer_id and a.order_id = p.order_id', 'left');
        $this->db->join('product_information d', 'd.product_id = c.product_id');
        $this->db->join('unit e', 'e.unit_id = d.unit', 'left');
        $this->db->join('variant f', 'f.variant_id = c.variant_id', 'left');
        $this->db->join('product_title g', 'g.product_id = d.product_id', 'left');
        $this->db->where('a.order_id', $order_id);
        $this->db->where('g.lang_id', $lang_id);
        $query = $this->db->get();
        //echo $this->db->last_query();

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

     // Get payment method name
    public function get_payment_method($order_id)
    {
        $this->db->where('order_id', $order_id);
        $result = $this->db->get('order_payment')->row_array();
        return $result;
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

    //Send Customer Email with invoice
    public function setmail($email, $file_path, $order_email)
    {
        $CI = &get_instance();

        if ($email) {

            //send email with as a link
            $setting_detail = $this->retrieve_email_editdata();
            $company_info   = $this->company_list();
            $template         = $this->retrieve_template('8');

            $config = array(
                'protocol'      => $setting_detail[0]['protocol'],
                'smtp_host'     => $setting_detail[0]['smtp_host'],
                'smtp_port'     => $setting_detail[0]['smtp_port'],
                'smtp_user'     => $setting_detail[0]['sender_email'],
                'smtp_pass'     => $setting_detail[0]['password'],
                'mailtype'      => $setting_detail[0]['mailtype'],
                'charset'       => 'utf-8'
            );
            $CI->email->initialize($config);
            $CI->email->set_mailtype($setting_detail[0]['mailtype']);
            $CI->email->set_newline("\r\n");

            //Email content
            $CI->email->to($email);
            $CI->email->from($setting_detail[0]['sender_email'], $company_info[0]['company_name']);
            $CI->email->subject($template->subject);
            $CI->email->message($order_email);
            $CI->email->attach($file_path);

            $email = $this->test_input($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($CI->email->send()) {
                    JSONSuccessOutput(display('product_successfully_order') . '(' . $email . ')');
                } else {
                    JSONErrorOutput(display('email_not_send'));
                }
            } else {
                JSONSuccessOutput(display('please_enter_valid_email'));
            }
        } else {
            JSONErrorOutput(display('your_email_was_not_found'));
        }
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

    public function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public function apply_coupon($customer_id, $coupon_code, $cartitems)
    {
        if (empty($customer_id) || empty($coupon_code) || empty($cartitems)) {
            JSONErrorOutput('Invalid Info');
        } else {
            $result = $this->db->select('*')
                ->from('coupon')
                ->where('coupon_discount_code', $coupon_code)
                ->where('status', 1)
                ->get()
                ->row();
            if ($result) {
                $products = array_column($cartitems, 'product_id');
                $quantity = array_column($cartitems, 'qty');
                // Check Coupon product is exist in cart or not
                $productpos = array_search($result->product_id, $products);
                // var_dump($products);exit;
                if (in_array($result->product_id, $products)) {
                    $start_date = strtotime($result->start_date);
                    $end_date = strtotime($result->end_date);
                    $today_date = time();
                    if (($today_date >= $start_date) && ($today_date <= $end_date)) {
                        $total_dis = 0;
                        if ($result->discount_type == 1) {
                            $total_dis = $result->discount_amount * $quantity[$productpos];
                        } elseif ($result->discount_type == 2) {
                            $dis = ($this->cart->total() * $result->discount_percentage) / 100;
                            $total_dis = $dis * $quantity[$productpos];
                        }

                        $coupondata = array(
                            'coupon_id' =>  uniqid(),
                            'coupon_code' => $coupon_code,
                            'customer_id' => $customer_id,
                            'product_id' => $result->product_id,
                            'quantity' => $quantity[$productpos],
                            'discount_amt' => $total_dis
                        );
                        $return_response = $this->check_coupon_logs($coupondata);
                        if (!empty($return_response)) {
                            return $return_response;
                        }
                    } else {
                        JSONErrorOutput(display('coupon_is_expired'));
                    }
                } else {
                    JSONErrorOutput(display('this_coupon_is_not_applicable'));
                }
            } else {
                JSONErrorOutput(display('invalid_coupon'));
            }
        }
    }

    // Coupon Udate
    public function check_coupon_logs($cdata)
    {
        $this->db->where('customer_id', $cdata['customer_id']);
        $this->db->where('product_id', $cdata['product_id']);
        $cinfo = $this->db->get('coupon_logs')->row();
        if ($cinfo) {
            $this->db->update('coupon_logs', $cdata, array('customer_id' => $cdata['customer_id'], 'product_id' => $cdata['product_id']));
        } else {
            $this->db->insert('coupon_logs', $cdata);
        }
        // Get Total Suam data
        $total_amt = $this->db->select('IFNULL(SUM(discount_amt), 0) as discount_amt')
            ->from('coupon_logs')
            ->where('coupon_id', $cdata['coupon_id'])
            ->where('customer_id', $cdata['customer_id'])
            ->get()->row();
        $return_array = array(
            'coupon_id' => $cdata['coupon_id'],
            'coupon_amnt' => $total_amt->discount_amt
        );
        return $return_array;
    }

    public function seller_order($order_id, $customer_id, $cart_details)
    {
        if ($cart_details) {
            $quantity = 0;
            foreach ($cart_details as $items) {
                if ($items->campaign_id != 1) {
                    $stock = $this->db->select('product_quantity as quantity')
                        ->from('campaign_product_info')
                        ->where('product_id', $items->product_id)
                        ->where('campaign_id', $items->campaign_id)
                        ->get()
                        ->row();
                    if (!empty($stock)) {
                        if ($stock->quantity < $items->qty) {
                            JSONErrorOutput("You can not order more than stock");
                        }
                    }
                } else {
                    $stock = $this->db->select('*')
                        ->from('product_information')
                        ->where('product_id', $items->product_id)
                        //->where('pre_order',1)
                        ->get()
                        ->row();
                    if (!empty($stock)) {
                        if ($stock->quantity < $items->qty) {
                            JSONErrorOutput("You can not order more than stock");
                        }
                    }
                }
                if (!empty($items)) {
                    //Seller percentage
                    $comission_rate = $this->comission_info($items->product_id);
                    $category_id   = $this->category_id($items->product_id);
                    $sinfo = $this->product_infos($items->product_id);
                    $seller_id = $sinfo->seller_id;
                    $rate = $sinfo->price;

                    //seller_order_data
                    if ($items->campaign_id != 1) {
                        $order_info = $this->db->select("product_campaign_price as price")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->where("product_id", $items->product_id)->get()->row();
                        $total_price = $order_info->price;
                        $discount_per_product = $rate - $order_info->price;

                    } else {
                        if ($sinfo->on_sale == 0) {
                            $total_price = $rate;
                            $discount_per_product = 0;
                        } else {
                            $total_price = $sinfo->offer_price;
                            $discount_per_product = $rate - $sinfo->offer_price;
                        }
                    }
                    $seller_order_data = array(
                        'order_id'                =>    $order_id,
                        'seller_id'                =>    $seller_id,
                        'seller_percentage'     =>  $comission_rate,
                        'customer_id'            =>    $customer_id,
                        'campaign_id'            =>    $items->campaign_id,
                        'category_id'            =>    $category_id,
                        'product_id'            =>    $items->product_id,
                        'variant_id'            =>    '',
                        'quantity'                =>    $items->qty,
                        'rate'                    =>    $rate,
                        'total_price'           => ($total_price * $items->qty),
                        'discount_per_product'    =>    $discount_per_product,
                        'product_vat'            =>    '',
                    );
                    //Total quantity count
                    $quantity += $items->qty;
                    $this->db->insert('seller_order', $seller_order_data);
                    if ($items->campaign_id != 1) {
                        //Product stock update
                        $this->db->set('product_quantity', 'product_quantity-' . $items->qty, FALSE);
                        $this->db->where('product_id', $items->product_id);
                        $this->db->update('campaign_product_info');
                    } else {
                        //Product stock update
                        $this->db->set('quantity', 'quantity-' . $items->qty, FALSE);
                        $this->db->where('product_id', $items->product_id);
                        $this->db->update('product_information');
                    }
                }
            }
            //end foreach
            return 1;
        }else{
            return 0;
        }
    }

    public function confirm_order($order_id, $customer_id, $cart_contents, $payment_method, $total_amount, $paid_amount, $costing_info)
    {
        //Delivery order payment entry
        $data = array(
            'order_payment_id'  => generator(15),
            'payment_id'        => $payment_method,
            'order_id'          => $order_id,
        );
        $this->db->insert('order_payment', $data);
        ////////
        $seller_order = $this->seller_order($order_id, $customer_id, $cart_contents);
        if ($seller_order == 0) {
            JSONErrorOutput('Order did not Placed');
        }
        ////////
        $order_no = "EZ" . mt_rand(100000000000, 999999999999);
        $this->db->select('order_no');
        $this->db->where('order_no', $order_no);
        $query = $this->db->get('order');
        $result = $query->num_rows();
        if ($result > 0) {
            $order_no = "EZ" . mt_rand(100000000000, 999999999999);
        }
        ///////////////////
        if ($total_amount == $paid_amount) {
            $n_order = array(
                'order_id'        => $order_id,
                'order_no'        => $order_no,
                'customer_id'     => $customer_id,
                'shipping_id'     => 0,
                'date'            => date("Y-m-d"),
                'time'            => date("h:i a"),
                'total_amount'    => $total_amount,
                'paid_amount'     => $paid_amount,
                'order_status'    => 2,
                'payment_date'    => date("Y-m-d h:i:sa"),
                'pending'         => date("Y-m-d")
            );
            $this->db->insert('order', $n_order);
            //Order intsert info order tracking
            $order_tracking_pending = array(
                'order_id'           => $order_id,
                'customer_id'     => $customer_id,
                'date'          => date("Y-m-d h:i:sa"),
                'message'       => 'Order Placed',
                'order_status'  => 1

            );
            $this->db->insert('order_tracking', $order_tracking_pending);
            $order_tracking = array(
                'order_id'           => $order_id,
                'customer_id'     => $customer_id,
                'date'          => date("Y-m-d h:i:sa"),
                'message'       => 'Order Processing',
                'order_status'  => 2

            );
            $this->db->insert('order_tracking', $order_tracking);
        } else {
            $n_order = array(
                'order_id'           => $order_id,
                'order_no'           => $order_no,
                'customer_id'     => $customer_id,
                'shipping_id'     => 0,
                'date'               => date("Y-m-d"),
                'time'               => date("h:i a"),
                'total_amount'    => $total_amount,
                'paid_amount'     => $paid_amount,
                'order_status'     => 1,
                'pending'        => date("Y-m-d")
            );
            $this->db->insert('order', $n_order);
            //Order intsert info order tracking
            $order_tracking_pending = array(
                'order_id'      => $order_id,
                'customer_id'   => $customer_id,
                'date'          => date("Y-m-d h:i:sa"),
                'message'       => 'Order Placed',
                'order_status'  => 1

            );
            $this->db->insert('order_tracking', $order_tracking_pending);
        }

        return $order_id;
    }

    public function order_inserted_data($order_id)
    {
        if (ENVIRONMENT == 'production') {
            JSONSuccessOutput(Null, 'Product Successfully Ordered');
            // $content = $this->order_html_data($order_id);
            // return $content;
        } else {
            JSONSuccessOutput(Null, 'Product Successfully Ordered');
        }
    }

    //manage_order
    public  function manage_order_post()
    {
        $this->form_validation->set_rules('customer_id', 'customer_id', 'required|trim|xss_clean');
        $this->form_validation->set_rules('order_no', 'order no', 'trim|xss_clean');
        $this->form_validation->set_rules('order_status', 'order status', 'trim|xss_clean');
        $this->form_validation->set_rules('page', 'page', 'is_natural|trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_id'])) {
                $errors_data = $errors['customer_id'];
            }
            if (!empty($errors['order_no'])) {
                $errors_data = $errors['order_no'];
            }
            if (!empty($errors['order_status'])) {
                $errors_data = $errors['order_status'];
            }
            if (!empty($errors['page'])) {
                $errors_data = $errors['page'];
            }
            JSONErrorOutput($errors_data);
        }
        $page = filter_input_data($this->input->post('page', TRUE));
        $per_page = 10;
        $order_no = filter_input_data($this->input->post('order_no', TRUE));
        $order_status = filter_input_data($this->input->post('order_status', TRUE));
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        $this->db->select('o.date,o.order_id,o.order_no,o.total_amount,o.paid_amount,o.order_status');
        $this->db->from('order o');
        $this->db->where('o.customer_id', $customer_id);
        if (!empty($order_no)) {
            $this->db->where('o.order_no', $order_no);
        }
        if (!empty($order_status)) {
            $this->db->where('o.order_status', $order_status);
        }
        $this->db->order_by('id', 'DESC');
        if ($page >= 0 ) {
            $page = $per_page * $page;
            $this->db->limit($per_page, $page);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $result[$key]['due_amount'] = ($value['total_amount'] - $value['paid_amount']);
                $result[$key]['order_status_title'] = $this->order_status_title($value['order_status']);
            }
            $data = $this->remaining_time($result);
            JSONSuccessOutput($data);
        } else {
            $cust_check = $this->customer_check($customer_id);
            if ($cust_check) {
                JSONNoOutput("No order Found");
            } else {
                JSONNoOutput("No customer found");
            }
        }
    }

    //Remaining time
    public function remaining_time($result)
    {
        $orderPolicy = $this->order_policy();
        foreach ($result as $key => $res) {
            if ($res["order_status"] == 1) {
                $date = $res["date"];
                $maxdate = date("Y-m-d", strtotime($date . "+$orderPolicy->payment_duration days"));
                $remaining = strtotime($maxdate) - time();
                $convtime = strtotime($maxdate) * 1000;
                $sec = ($remaining % 60);
                $remtime = "";
                if ($sec) $remtime = $convtime;
                $status = TRUE;
                if ($remtime == "" | $remaining <= 0) {
                    $remtime = "Payment Time is Over, Please Contact with e-needz";
                    $status = FALSE;
                }
                $result[$key]["remainingTime"] = $remtime;
                $result[$key]["remainingStatus"] = $status;
            }
            if ($res["order_status"] == 2) {
                $date = $res["date"];
                $maxdate = date("Y-m-d", strtotime($date . "+$orderPolicy->delivery_duration days"));
                $remaining = strtotime($maxdate) - time();
                $convtime = strtotime($maxdate) * 1000;
                $sec = ($remaining % 60);
                $remtime = "";
                if ($sec) $remtime = $convtime;
                if ($remtime == "" | $remaining <= 0) {
                    $remtime = "Delivery Time is Over, Please Contact with e-needz";
                }
                $result[$key]["remainingTime"] = $remtime;
                $result[$key]["remainingStatus"] = FALSE;
            }
        }
        return $result;
    }
    //Order check
    public function order_policy()
    {
        $this->db->select("payment_duration,delivery_duration");
        $this->db->from("order_policy");
        $this->db->where("id", 1);
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }

    public function cancel_order_post()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $check = $this->customerlogin_check($customer_id);
        if (!$check) {
            JSONErrorOutput("Customer not found!");
        }
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        if (empty($order_id)) {
            JSONErrorOutput("Order Id is required!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id");
        } else {
            $status_check = $this->db->select("date,order_status")->from("order")->where("order_id", $order_id)->get()->row();
            $orderPolicy = $this->order_policy();
            $date = $status_check->date;
            $durationDate = date("Y-m-d", strtotime($date . "+$orderPolicy->payment_duration days"));
            $currentDate = date("Y-m-d");
            if ($status_check->order_status == 1 && $durationDate < $currentDate) {
                $result = $this->db->where("order_id", $order_id)->update("order", array("order_status" => 6));
                if ($result) {
                    JSONSuccessOutput(NULL, 'Order cancelled successfully');
                } else {
                    JSONErrorOutput('Please try again');
                }
            } else {
                JSONErrorOutput('Order cancelation does not possible at this time');
            }
        }
    }

     //Order details
    public function details_order_post()
    {
         $order_id = filter_input_data($this->input->post('order_id', TRUE));
         if (empty($order_id)) {
             JSONErrorOutput("Order Id is required!");
         }
         $this->db->select("o.order_id,o.order_no,o.date,sh.customer_id,sh.customer_name,sh.customer_short_address,sh.customer_mobile,sh.customer_email,pg.agent pmethod,o.total_discount,o.total_amount,o.paid_amount,o.order_status,o.file_path");
         $this->db->from("order o");
         $this->db->join("shipping_info sh", "sh.order_id = o.order_id", "left");
         $this->db->join("order_payment op", "op.order_id = o.order_id", "left");
         $this->db->join("payment_gateway pg", "pg.code = op.payment_id", "left");
         $this->db->where("o.order_id", $order_id);
         $query = $this->db->get();
         $result = $query->row();
         if (!empty($result)) {
             $result->{"order_status_title"} = $this->order_status_title($result->order_status);
             $result->{"due_amount"} = $result->total_amount - $result->paid_amount;
             
             $result->{"payment_status"} = $this->payment_status($result->order_no);
             $result->{"company_details"} = $this->company_details();
             $result->{"product_information"} = $this->product_info($order_id);
             $result->{"order_timeline"} = $this->order_timeline($order_id);
             JSONSuccessOutput($result);
         } else {
             JSONErrorOutput("Invalid order id");
         }
    }

    public function order_timeline($order_id)
    {
        $array1 = [];
        $data = $this->db->select("order_id, customer_id, order_status")->from("order")->where("order_id", $order_id)->get()->row_array();
        $data['order_status_title'] = $this->order_status_title($data['order_status']);
        if (!empty($data)) {
            $result = $this->db->select("ot.date,ot.order_status,et.subject as message")
            ->from("order o")
            ->join('order_tracking ot', 'ot.order_id = o.order_id', 'left')
            ->join('email_template et', 'et.status = ot.order_status', 'left')
            ->where("ot.order_id", $data['order_id'])
            ->order_by("ot.id", "DESC")
            ->get()->result_array();

            $this->db->select('a.updated_date as date, o.order_status, CONCAT("TK ",a.payment_amount, " Paid ", "by ", a.payment_method) AS  message');
            $this->db->from('customer_make_payment_list a');
            $this->db->join('order o', 'o.order_id = a.order_id', 'left');
            $this->db->where('a.status', 2);
            $this->db->where('a.order_id', $data['order_id']);
            $this->db->where('a.customer_id', $data['customer_id']);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $result_array = $query->result_array();
                $array1 = ($result_array);
            }
            $result = array_merge($array1, $result);
            $price = array();
            foreach ($result as $key => $row)
            {
                $price[$key] = $row['date'];
                $result[$key]['order_status_title'] = $this->order_status_title($row['order_status']);
            }
            array_multisort($price, SORT_DESC, $result);
            $data['track_details'] = $result;
            return $data;
        } else {
            $data = [];
            return $data;
        }
    }

    public function payment_status($oNo)
    {
        $this->db->select("paid_amount,order_status");
        $this->db->from("order");
        $this->db->where("order_no", $oNo);
        $query = $this->db->get();
        $row = $query->row();
        if ($row->order_status != 1 && $row->order_status != 6) {
            return "Paid";
        } else {
            if ($row->order_status == 1) {
                if ($row->paid_amount > 0) {
                    return "Partial Paid";
                } else {
                    return "Unpaid";
                }
            } else {
                return "Unpaid";
            }
        }
    }

    public function company_details()
    {
        $this->db->select("c.company_name,c.email,c.address,c.mobile,c.website");
        $this->db->from("company_information c");
        $this->db->where("c.company_id", "4JE5HGQDS3GZW2V");
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }

    public function product_info($oNo)
    {
        $this->db->select("pi.product_id,pi.seller_id,pt.title,pi.unit,v.variant_name,so.campaign_id,so.quantity,so.rate,so.total_price,
            so.discount_per_product,pimg.image_path as img_url,pimg.image_name");
        $this->db->from("seller_order so");
        $this->db->join("product_title pt", "pt.product_id=so.product_id AND pt.lang_id='english' ", "left");
        $this->db->join('product_image pimg', "pimg.product_id = so.product_id AND pimg.image_type = 1 AND pimg.status = 1", 'left');
        $this->db->join("product_information pi", "pi.product_id=so.product_id", "left");
        $this->db->join("variant v", "v.variant_id=so.variant_id", "left");
        $this->db->where("so.order_id", $oNo);
        $query = $this->db->get();
        $result = $query->result();
        foreach ($result as $key => $res) {
            if ($res->quantity == 0) {
                $result[$key]->{"sell_price"} = 0;
            }else {
                $result[$key]->{"sell_price"} = ($res->total_price / $res->quantity);
            }
            $result[$key]->{"amount"} = $res->total_price ;
            $result[$key]->{"image_path"} = trim((!empty($res->img_url)) ? $res->img_url : THUMB_CDN_DIR . $res->image_name);
        }
        return $result;
    }

    public function issuetype_list_post()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $this->db->select("it.issueType_id,it.type_name");
        $this->db->from("issue_type it");
        $this->db->where("status", 1);
        $this->db->order_by("issueType_id", "DESC");
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            JSONNoOutput("No issue list found");
        }
    }

    public function issue_create_post()
    {
        $status = filter_input_data($this->input->post('status', TRUE));
        if ($status == 1) {
            $issue_id = filter_input_data($this->input->post('issue_id', TRUE));
            if (empty($issue_id)) {
                JSONErrorOutput("Issue id required!");
            }
            $issue = $this->db->select("status")->from("customer_issue")->where("issue_id", $issue_id)->where("action", 1)->get()->row();
            if ($issue->status == 0) {
                JSONErrorOutput("Your issue marked as invalid");
            }
            if ($issue->status == 1) {
                JSONErrorOutput("Your issue already resolved");
            }
            $data = array(
                'status' => $status,
            );
            $update = $this->db->where("issue_id", $issue_id)->update("customer_issue", $data);
            if (!empty($update)) {
                JSONSuccessOutput(NULL, "Save successfully");
            } else {
                JSONErrorOutput("Please try again");
            }
        } else {
            $order_id = filter_input_data($this->input->post('order_id', TRUE));
            $details = filter_input_data($this->input->post('details', TRUE));
            $submited_by = filter_input_data($this->input->post('submited_by', TRUE));
            $action = filter_input_data($this->input->post('action', TRUE));
            $submited_type = "customer";
            $date_time = date("Y-m-d H:i:sa");
            if (empty($order_id)) {
                JSONErrorOutput("Order id is required!");
            }
            $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
            if (empty($order_check->orderid)) {
                JSONErrorOutput("Invalid order id");
            }
            if (empty($details)) {
                JSONErrorOutput("Details is required!");
            }
            if (empty($order_id)) {
                JSONErrorOutput("Order id is required!");
            }
            $customer_order = $this->is_order($order_id, $submited_by);
            if (!$customer_order) {
                JSONErrorOutput("Invalid customer order!");
            }
            $cust_check = $this->customer_check($submited_by);
            if (!$cust_check) {
                JSONErrorOutput("Invalid customer id!");
            }
            if (empty($action)) {
                JSONErrorOutput("Action is required!");
            }
            if ($action == 2) {
                $issue_id = filter_input_data($this->input->post('issue_id', TRUE));
                $status = null;
                $parent_id = $issue_id;
                if (empty($issue_id)) {
                    JSONErrorOutput("Issue id required!");
                }
                $issue = $this->db->select("COUNT(issue_id) as issue")->from("customer_issue")->where("issue_id", $issue_id)->where("status!=", 0)->where("action", 1)->get()->row();
                if (empty($issue->issue)) {
                    JSONErrorOutput("You do not have any valid issue");
                }
            }
            if ($action == 1) {
                $issueType_id = filter_input_data($this->input->post('issueType_id', TRUE));
                if (empty($issueType_id)) {
                    JSONErrorOutput("Issue type is required!");
                }
                $issue_check = $this->db->select("COUNT(issueType_id) as issue")->from("issue_type")->where("issueType_id", $issueType_id)->get()->row();
                if (empty($issue_check->issue)) {
                    JSONErrorOutput("Invalid issue type");
                }
                $status = 2;
                $parent_id = 0;
                $issuecheck = $this->db->select("COUNT(issue_id) as issue")->from("customer_issue")->where("order_id", $order_id)->where("status!=", 1)->where("action", 1)->get()->row();
                if (!empty($issuecheck->issue)) {
                    JSONErrorOutput("You already submitted an issue");
                }
            }
            if (!empty($_FILES['attachment']['name'])) {
                $sizes = array(1300 => 1300, 235 => 235);
                
                $file_location = $this->do_upload_file($_FILES['attachment'], $sizes, 'issueAttachment');
                $image_name = explode('/', $file_location[0]);
                $image_name = end($image_name);
                $base_path = SPACE_URL;
                $attachment = $base_path . '/' . 'issueAttachment/' . $image_name;
            }
            $data = array(
                'issueType_id' => $issueType_id,
                'parent_id' => $parent_id,
                'order_id' => $order_id,
                'details' => $details,
                'submited_by' => $submited_by,
                'action' => $action,
                'submited_type' => $submited_type,
                'date_time' => $date_time,
                'status' => $status,
                'attachment' => !empty($attachment) ? $attachment : null,
            );
            $insert = $this->db->insert("customer_issue", $data);
            if (!empty($insert)) {
                JSONSuccessOutput(NULL, "Save successfully");
            } else {
                JSONErrorOutput("Please try again");
            }
        }
    }

    public function issue_list_post()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id");
        }
        $customer_order = $this->is_order($order_id, $customer_id);
        if (!$customer_order) {
            JSONErrorOutput("Invalid customer order!");
        }
        $issue = $this->db->select("ci.issue_id,ci.issueType_id,it.type_name,ci.date_time,ci.order_id,ci.details,ci.attachment,ci.status")
        ->from("customer_issue ci")
        ->join("issue_type it", "it.issueType_id=ci.issueType_id", "left")
        ->where("order_id", $order_id)
        ->where("submited_by", $customer_id)
        ->where("action", 1)
        ->order_by('ci.issue_id', 'desc')
        ->get()->result_array();
        if (!empty($issue)) {
            foreach ($issue as $key => $val) {
                if ($val['status'] == 1) {
                    $status_title = 'Resolved';
                }elseif ($val['status'] == 2) {
                    $status_title = 'New';
                }elseif ($val['status'] == 3) {
                    $status_title = 'Checked';
                }elseif ($val['status'] == 0) {
                    $status_title = 'Inactive';
                }

                $issue[$key]['status_title'] = $status_title;
            }
            JSONSuccessOutput($issue);
        } else {
            JSONNoOutput("No data dound");
        }
    }

    public function issue_details_post()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        $issue_id = filter_input_data($this->input->post('issue_id', TRUE));
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id");
        }
        $customer_order = $this->is_order($order_id, $customer_id);
        if (!$customer_order) {
            JSONErrorOutput("Invalid customer order!");
        }
        if (empty($issue_id)) {
            JSONErrorOutput("Issue id required!");
        }
        $issue = $this->db->select("ci.issue_id,ci.issueType_id,it.type_name,ci.date_time,ci.order_id,ci.details,ci.attachment,ci.status")->from("customer_issue ci")->join("issue_type it", "it.issueType_id=ci.issueType_id", "left")->where("issue_id", $issue_id)->where("order_id", $order_id)->where("submited_by", $customer_id)->where("action", 1)->get()->row();
        if (!empty($issue)) {
            $comment = $this->db->select("ci.issue_id as cmnt_id,ci.order_id,ci.date_time,ci.details as msg,ci.submited_type,ci.attachment")->from("customer_issue ci")->where("parent_id", $issue_id)->where("order_id", $order_id)->where("action", 2)->get()->result();
            // foreach($comment as $k =>$val){
            //     $comment[$k]->date_time = strtotime($val->date_time) * 1000;
            // }
            $name = $this->db->select("customer_name")->from("customer_information")->where("customer_id", $customer_id)->get()->row();
            $issue->customer_name = $name->customer_name;
            if ($issue->status == 1) {
                $status_title = 'Resolved';
            }elseif ($issue->status == 2) {
                $status_title = 'New';
            }elseif ($issue->status == 3) {
                $status_title = 'Checked';
            }elseif ($issue->status == 0) {
                $status_title = 'Inactive';
            }
            $issue->status_title = $status_title;
            $issue->comment = !empty($comment) ? $comment : [];
            JSONSuccessOutput($issue);
        } else {
            JSONErrorOutput("Invalid issue id!");
        }
    }
    public function is_order($order_id, $customer_id)
    {
        $this->db->select("so.order_id");
        $this->db->from("seller_order so");
        $this->db->where("so.order_id", $order_id);
        $this->db->where("so.customer_id", $customer_id);
        $this->db->order_by("so.seller_order_id", "DESC");
        $query = $this->db->get();
        $row = $query->row();
        if (!empty($row)) {
            $this->db->select("COUNT(o.order_status) as num");
            $this->db->from("order o");
            $this->db->where("o.order_id", $row->order_id);
            $subQuery = $this->db->get();
            $status = $subQuery->row();
            if (!empty($status)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function order_status_title($order_status)
    {
        if ($order_status == 1) {
            $order_status_title = 'Pending';
        }elseif ($order_status == 2) {
            $order_status_title = 'Processing';
        }elseif ($order_status == 19) {
            $order_status_title = 'Picked';
        }elseif ($order_status == 20) {
            $order_status_title = 'Confirmed';
        }elseif ($order_status == 3) {
            $order_status_title = 'Shipping';
        }elseif ($order_status == 4) {
            $order_status_title = 'Delivered';
        }elseif ($order_status == 5) {
            $order_status_title = 'Returned';
        }elseif ($order_status == 18) {
            $order_status_title = 'Refunded';
        }elseif ($order_status == 6) {
            $order_status_title = 'Cancelled';
        }elseif ($order_status == 7) {
            $order_status_title = 'Partial Delivery';
        }else{
            $order_status_title = 'Unknown';
        }
        return $order_status_title;
    }

    public function order_tracking_post()
    {
        $array1 = [];
        $order_no = filter_input_data($this->input->post('order_no', TRUE));
        if (empty($order_no)) {
            JSONErrorOutput("Order no. is required!");
        }
        $data = $this->db->select("order_id, customer_id, order_status")->from("order")->where("order_no", $order_no)->get()->row_array();
        $data['order_status_title'] = $this->order_status_title($data['order_status']);
        if (!empty($data)) {
            $result = $this->db->select("ot.date,ot.order_status,et.subject as message")
            ->from("order o")
            ->join('order_tracking ot', 'ot.order_id = o.order_id', 'left')
            ->join('email_template et', 'et.status = ot.order_status', 'left')
            ->where("ot.order_id", $data['order_id'])
            ->order_by("ot.id", "DESC")
            ->get()->result_array();

            $this->db->select('a.updated_date as date, o.order_status, CONCAT("TK ",a.payment_amount, " Paid ", "by ", a.payment_method) AS  message');
            $this->db->from('customer_make_payment_list a');
            $this->db->join('order o', 'o.order_id = a.order_id', 'left');
            $this->db->where('a.status', 2);
            $this->db->where('a.order_id', $data['order_id']);
            $this->db->where('a.customer_id', $data['customer_id']);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $result_array = $query->result_array();
                $array1 = ($result_array);
            }
            $result = array_merge($array1, $result);
            $price = array();
            foreach ($result as $key => $row)
            {
                $price[$key] = $row['date'];
                $result[$key]['order_status_title'] = $this->order_status_title($row['order_status']);
            }
            array_multisort($price, SORT_DESC, $result);
            $data['track_details'] = $result;
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No order Found");
        }
    }

    public function customer_dashboard_payment_gateway_post()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $this->db->select("pg.id,pg.code,pg.agent");
        $this->db->from("payment_gateway pg");
        $this->db->where("customer_dashboard_status", "1");
        $this->db->order_by("agent", "ASC");
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            JSONNoOutput("No payment gateway list found");
        }
    }

    public function make_payment_submit_post()
    {
        $payment_amount = filter_input_data($this->input->post('payment_amount', TRUE));
        $payment_method = filter_input_data($this->input->post('payment_method', TRUE));
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        $tokenVerify = $this->tokenVerify($customer_id);
        if ($tokenVerify != 'ok') {
            JSONErrorOutput("Invalid Token!");
        }
        if (empty($payment_amount)) {
            JSONErrorOutput("Payment amount is required!");
        }
        if (empty($payment_method)) {
            JSONErrorOutput("Payment method is required!");
        }
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        if (empty($order_id)) {
            JSONErrorOutput("Order id is required!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id!");
        }
        $this->db->select('o.date,o.order_status');
        $this->db->from('order o');
        $this->db->where('o.customer_id', $customer_id);
        $this->db->where('o.order_id', $order_id);
        $this->db->where('o.order_status', 1);
        $query = $this->db->get();
        $result = $query->result_array();
        if (!empty($result)) {
            $data = $this->remaining_time($result);
            if ($data[0]["remainingTime"] == "Payment Time is Over, Please Contact with e-needz") {
                JSONErrorOutput("Payment Time is Over, Please Contact with e-needz");
            }
        } else {
            JSONErrorOutput("Please Contact with e-needz");
        }
        ///////////////////////

        $payment_entry = array(
            'payment_id_no' => generator(15),
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'payment_method' => $payment_method,
            'payment_amount' => $payment_amount,
            'payment_date' => date('Y-m-d'),
        );

        $order_info = $this->db->where('order_id', $order_id)->get('order')->row_array();
        if (empty($order_info)) {
            JSONErrorOutput("Invalid Info");
        }

        $order_no = $order_info['order_no'];

        if ($payment_method == 'sslcommerz') {
            $response_url = filter_input_data($this->input->post('response_url', TRUE));
            if (empty($response_url)) {
                JSONErrorOutput("Response URL is required!");
            }
            $trans_id = "eneedz" . uniqid();
            $data_sslcommerz = array(
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'payment_method' => $payment_method,
                'payment_amount' => $payment_amount,
                'payment_date' => date("Y-m-d"),
                'payment_from' => "customer_dashboard",
                'trans_id' => $trans_id,
                'response_url' => $response_url
            );
            $sslcommerz_order_info = array(
                'trans_id' => $trans_id,
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'payment_amount' => $payment_amount,
                'payment_from' => "customer_dashboard",
                'response_url' => $response_url,
            );
            $this->db->insert('sslcommerz_order_info', $sslcommerz_order_info);
            $this->payment_by_sslcommerz($data_sslcommerz);
        }

        if ($payment_method == 'nagad') {
            $response_url = filter_input_data($this->input->post('response_url', TRUE));
            if (empty($response_url)) {
                JSONErrorOutput("Response URL is required!");
            }
            $nagad_inv = $order_no . generator(6);
            $this->db->select('nagad_inv');
            $this->db->where('nagad_inv', $nagad_inv);
            $query = $this->db->get('nagad_order_info');
            $result = $query->num_rows();
            if ($result > 0) {
                $nagad_inv = $order_no . generator(6);
            }
            //nagad_order_info Entry
            $nagad_order_info = array(
                'nagad_inv'     => $nagad_inv,
                'order_id'      => $order_id,
                'customer_id'   => $customer_id,
                'cart_total'    => $payment_amount,
                'date'          => date("Y-m-d h:i a"),
                'response_url'    => $response_url,
            );
            $this->db->insert('nagad_order_info', $nagad_order_info);
            $this->nagad_payment($nagad_inv, $payment_amount, "customer_dashboard");
        }

        if ($payment_method == 'bank') {
            $bank_name = filter_input_data($this->input->post('bank_name', TRUE));
            $bank_ac_no = filter_input_data($this->input->post('bank_ac_no', TRUE));
            if (empty($bank_name)) {
                JSONErrorOutput("Bank name is required!");
            }
            if (empty($bank_ac_no)) {
                JSONErrorOutput("Bank account no is required!");
            }
            if (!empty($_FILES['payment_slip']['name'])) {
                $sizes = array(1300 => 1300, 235 => 235);
                $file_location = $this->do_upload_file($_FILES['payment_slip'], $sizes, 'bankPayslip');
                $image_name = explode('/', $file_location[0]);
                $image_name = end($image_name);
                $base_path = SPACE_URL;
                $payment_slip = $base_path . '/' . 'bankPayslip/' . $image_name;
            }
            ///////////////
            $data_bank =
                array(
                    'payment_id_no' => $payment_entry['payment_id_no'],
                    'order_id'      => $order_id,
                    'customer_id'   => $customer_id,
                    'bank_name'     => filter_input_post('bank_name', true),
                    'bank_ac_no'     => filter_input_post('bank_ac_no', true),
                    'payment_amount' => $payment_amount,
                    'payment_slip'     => (!empty($payment_slip) ? $payment_slip : null),
                );
            $this->db->insert('customer_make_payment_list', $payment_entry);
            $result = $this->db->insert('bank_statement_list', $data_bank);
            ////////////////////
            if ($result) {
                JSONSuccessOutput(NULL, "Save Successfully");
            } else {
                JSONErrorOutput("Please try again");
            }
        }
    }

    public function payment_by_sslcommerz($data)
    {
        $CI = &get_instance();
        $gateway = $this->db->select('*')->from('payment_gateway')->where('code', 'sslcommerz')->get()->row();
        $total_amount = number_format($data['payment_amount'], 2, '.', '');
        if ($total_amount >= 500000) {
            JSONErrorOutput("SSLCOMMERZ Amount Limitation");
        }
        $trans_id = $data['trans_id'];
        $post_data = array();
        $post_data['store_id'] = $gateway->public_key;
        $post_data['store_passwd'] = $gateway->private_key;
        $post_data['total_amount'] = $total_amount;
        $post_data['currency'] = $gateway->currency;
        $post_data['tran_id'] = $trans_id;
        if ($data['payment_from'] == "web") {
            $post_data['success_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_success_web');
        }else {
            $post_data['success_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_success');
        }
        $post_data['fail_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_failed');
        $post_data['cancel_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_cancel');
        # EMI INFO
        $post_data['emi_option'] = "0";
        # OPTIONAL PARAMETERS
        $post_data['value_a'] = $data['order_id'];
        $post_data['value_b'] = $data['payment_method'];
        $post_data['value_c'] = $data['customer_id'];
        $post_data['value_d'] = $data['response_url'];
        $product_amount = '';
        $post_data['product_amount'] = '';
        // check is live pay or sandbox
        if (!empty($gateway->is_live)) {
            $direct_api_url = "https://securepay.sslcommerz.com/gwprocess/v3/api.php";
        } else {
            $direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";
        }

        # REQUEST SEND TO SSLCOMMERZ
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC

        $content = curl_exec($handle);

        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($code == 200 && !(curl_errno($handle))) {
            curl_close($handle);
            $sslcommerzResponse = $content;
        } else {
            curl_close($handle);

            JSONErrorOutput("FAILED TO CONNECT WITH SSLCOMMERZ API");
        }

        # PARSE THE JSON RESPONSE
        $sslcz = json_decode($sslcommerzResponse, true);

        if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
            $url = substr($sslcz['GatewayPageURL'], 8, strlen($sslcz['GatewayPageURL']));
            $redirect_url = array(
                "url" => $url
            );
            JSONSuccessOutput($redirect_url);
        } else {
            if (!empty($sslcz) && !empty($sslcz['failedreason'])) {
                $err_msg = $sslcz['failedreason'];
            } else {
                $err_msg = 'Payment Configuration error!';
            }
            JSONErrorOutput($err_msg);
        }
    }

    /*
    |-------------------------------
    |   Nagad Start
    |-------------------------------
    */
    public function nagad_gateway_setting()
    {
        $gateway = $this->db->select('*')->from('payment_gateway')->where('code', 'nagad')->get()->row();
        return $gateway;
    }

    public function getPgPublicKey()
    {
        return $this->nagad_gateway_setting()->public_key;
    }

    public function getMerchantPrivateKey()
    {
        return $this->nagad_gateway_setting()->private_key;
    }

    public function getBaseUrl()
    {
        return $this->nagad_gateway_setting()->shop_id;
    }

    public function getMerchantID()
    {
        return $this->nagad_gateway_setting()->secret_key;
    }

    public function apiUrl()
    {
        $apiUrl = 'api/dfs/check-out/initialize/';
        return $apiUrl;
    }

    public function statusCheckAPI()
    {
        $url = 'api/dfs/verify/payment/';
        return $url;
    }

    public function nagad_payment($order_id, $amount, $payment_from)
    {
        $postUrl = $this->getBaseUrl() . $this->apiUrl()
            . $this->getMerchantID() .
            "/" . $order_id;
        $sensitiveData = array(
            'merchantId' => $this->getMerchantID(),
            'datetime' => Date('YmdHis'),
            'orderId' => $order_id,
            'challenge' => $this->generateRandomString(40, 'you', 'me')
        );

        $postData = array(
            'dateTime' => Date('YmdHis'),
            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($sensitiveData)),
            'signature' => $this->SignatureGenerate(json_encode($sensitiveData))
        );

        $resultData = $this->HttpPostMethod($postUrl, $postData);
        $this->initUrl = $postUrl;

        if (is_array($resultData) && array_key_exists('reason', $resultData)) {
            $this->showResponse($resultData, $sensitiveData, $postData);
            return $this->response;
        } else if (is_array($resultData) && array_key_exists('error', $resultData)) {
            $this->showResponse($resultData, $sensitiveData, $postData);
            return $this->response;
        }

        if (array_key_exists('sensitiveData', $resultData) && array_key_exists('signature', $resultData)) {
            if (!empty($resultData['sensitiveData']) && !empty($resultData['signature'])) {
                $PlainResponse = json_decode($this->DecryptDataWithPrivateKey($resultData['sensitiveData']), true);
                if (isset($PlainResponse['paymentReferenceId']) && isset($PlainResponse['challenge'])) {
                    $paymentReferenceId = $PlainResponse['paymentReferenceId'];
                    $challenge = $PlainResponse['challenge'];

                    $SensitiveDataOrder = array(
                        'merchantId' => $this->getMerchantID(),
                        'orderId' => $order_id,
                        'currencyCode' => "050",
                        'amount' => (int)$amount,
                        'challenge' => $challenge
                    );
                    if ($payment_from != 'web') {
                        $PostDataOrder = array(
                            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                            'signature' => $this->SignatureGenerate(json_encode($SensitiveDataOrder)),
                            'merchantCallbackURL' => base_url('api/react/customer_dashboard/nagad_customer_dashboard')
                        );
                        $OrderSubmitUrl = $this->getBaseUrl() . "api/dfs/check-out/complete/" . $paymentReferenceId;
                        $Result_Data_Order = $this->HttpPostMethod($OrderSubmitUrl, $PostDataOrder);
                        if (array_key_exists('status', $Result_Data_Order)) {
                            if ($Result_Data_Order['status'] == "Success") {
                                $data = array(
                                    "url" => $Result_Data_Order['callBackUrl']
                                );
                                JSONSuccessOutput($data);
                            }
                        }
                    } else {
                        $PostDataOrder = array(
                            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                            'signature' => $this->SignatureGenerate(json_encode($SensitiveDataOrder)),
                            'merchantCallbackURL' => base_url('api/react/customer_dashboard/nagad_api_response_web')
                        );
                        $OrderSubmitUrl = $this->getBaseUrl() . "api/dfs/check-out/complete/" . $paymentReferenceId;
                        $Result_Data_Order = $this->HttpPostMethod($OrderSubmitUrl, $PostDataOrder);
                        if (array_key_exists('status', $Result_Data_Order)) {
                            if ($Result_Data_Order['status'] == "Success") {
                                $data = array(
                                    "url" => $Result_Data_Order['callBackUrl']
                                );
                                JSONSuccessOutput($data);
                            }
                        }
                    }
                }
            }
        }
    }

    private function showResponse($resultData, $sensitiveData, $postData)
    {
        $this->response = [
            'status' => 'error',
            'response' => $resultData,
            'request' => [
                'environment' => 'development',
                'time' => [
                    'request time' => date('Y-m-d H:i:s'),
                    'timezone' => 'Asia/Dhaka'
                ],
                'url' => [
                    'base_url' => $this->getBaseUrl(),
                    'api_url' => $this->apiUrl(),
                    'request_url' => $this->getBaseUrl() . $this->apiUrl()
                ],
                'data' => [
                    'sensitiveData' => $sensitiveData,
                    'postData' => $postData
                ],

            ],
            'server' => $this->serverDetails()
        ];
    }

    public function generateRandomString($length = 40, $prefix = '', $suffix = '')
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        if (!empty($prefix)) {
            $randomString = $prefix . $randomString;
        }
        if (!empty($suffix)) {
            $randomString .= $suffix;
        }
        return $randomString;
    }

    public function EncryptDataWithPublicKey($data)
    {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . $this->getPgPublicKey() . "\n-----END PUBLIC KEY-----";
        $keyResource = openssl_get_publickey($publicKey);
        openssl_public_encrypt($data, $cryptoText, $keyResource);
        return base64_encode($cryptoText);
    }

    public function SignatureGenerate($data)
    {
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . $this->getMerchantPrivateKey() . "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }


    public function HttpPostMethod($PostURL, $PostData)
    {
        $url = curl_init($PostURL);
        $postToken = json_encode($PostData);
        $header = array(
            'Content-Type:application/json',
            'X-KM-Api-Version:v-0.2.0',
            'X-KM-IP-V4:' . $this->getClientIP(),
            'X-KM-Client-Type:PC_WEB'
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $postToken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, 0);
        $resultData = curl_exec($url);
        $curl_error = curl_error($url);

        if (!empty($curl_error)) {
            return [
                'error' => $curl_error
            ];
        } else {
            $response = json_decode($resultData, true, 512);
            curl_close($url);
            return $response;
        }
    }

    public function getClientIP()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN IP';
        }
        return $ipaddress;
    }

    public function DecryptDataWithPrivateKey($cryptoText)
    {
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $this->getMerchantPrivateKey() . "\n-----END RSA PRIVATE KEY-----";
        openssl_private_decrypt(base64_decode($cryptoText), $plain_text, $private_key);
        return $plain_text;
    }

    public function generateFakeInvoice($length = 20, $capitalize = false, $prefix = '', $suffix = '')
    {
        $invoice = $prefix . $this->generateRandomString($length) . $suffix;
        if ($capitalize === true) {
            $invoice = strtoupper($invoice);
        }
        return $invoice;
    }

    public static function errorLog($data)
    {
        if (!file_exists('logs/nagadApi') && !mkdir('logs', 0775) && !is_dir('logs')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', 'logs'));
        }

        if (!file_exists('logs/nagadApi/error.log')) {

            $logFile = "logs/error.log";
            $fh = fopen($logFile, 'w+') or die("can't open file");
            fclose($fh);
            chmod($logFile, 0755);
        }
        $date = '=====================' . date('Y-m-d H:i:s') . '=============================================\n';
        file_put_contents('logs/nagadApi/error.log', print_r($date, true), FILE_APPEND);
        file_put_contents('logs/nagadApi/error.log', PHP_EOL . print_r($data, true), FILE_APPEND);
        $string = '=====================' . date('Y-m-d H:i:s') . '=============================================' . PHP_EOL;
        file_put_contents('logs/nagadApi/error.log', print_r($string, true), FILE_APPEND);
    }

    public static function serverDetails()
    {
        return [
            'base' => $_SERVER['SERVER_ADDR'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'port' => $_SERVER['REMOTE_PORT'],
            'request_url' => $_SERVER['REQUEST_URI'],
            'user agent' => $_SERVER['HTTP_USER_AGENT'],
        ];
    }

    public function successResponse($response)
    {
        $parts = parse_url($response);
        parse_str($parts['query'], $query);
        return $query;
    }
    /*
    |-------------------------------
    |   Nagad End
    |-------------------------------
    */

    public function payment_history_post()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $this->db->select('cmpl.payment_id,o.order_no,cmpl.order_id,ci.customer_name,cmpl.payment_amount');
        $this->db->from('customer_make_payment_list cmpl');
        $this->db->join('customer_information ci', 'ci.customer_id=cmpl.customer_id', 'left');
        $this->db->join('order o', 'o.order_id=cmpl.order_id', 'left');
        $this->db->where('cmpl.customer_id', $customer_id);
        $this->db->order_by('cmpl.payment_id', 'DESC');
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            $cust_check = $this->customer_check($customer_id);
            if ($cust_check) {
                JSONNoOutput("No order Found");
            } else {
                JSONNoOutput("No customer found");
            }
        }
    }

    public function payment_details_post()
    {
        $order_no = filter_input_data($this->input->post('order_no', TRUE));
        if (empty($order_no)) {
            JSONErrorOutput("Order no is required!");
        }
        $this->db->select('cmpl.payment_id_no,cmpl.order_id,ci.customer_name,cmpl.payment_method,o.date,cmpl.status as payment_status,cmpl.payment_amount');
        $this->db->from('customer_make_payment_list cmpl');
        $this->db->join('customer_information ci', 'ci.customer_id=cmpl.customer_id', 'left');
        $this->db->join('order o', 'o.order_id=cmpl.order_id', 'left');
        $this->db->where('o.order_no', $order_no);
        $this->db->order_by('cmpl.payment_id', 'ASC');
        $query = $this->db->get();
        $result = $query->result_array();
        if (!empty($result)) {
            foreach ($result as $key => $res) {
                if ($res['payment_method'] == 'bank') {
                    $result[$key]['bank_details'] = $this->bankPayment_details($res['payment_id_no']);
                }
                if ($res['payment_status'] == '1') {
                    $result[$key]['payment_status_title'] = 'Unchecked';
                }
                if ($res['payment_status'] == '2') {
                    $result[$key]['payment_status_title'] = 'Checked';
                }
            }
            JSONSuccessOutput($result);
        } else {
            JSONErrorOutput("Invalid order no");
        }
    }
    public function bankPayment_details($id)
    {
        $this->db->select("bsl.bank_name,bsl.bank_ac_no,bsl.payment_slip,bsl.status as payment_status");
        $this->db->from("bank_statement_list bsl");
        $this->db->where("bsl.payment_id_no", $id);
        $this->db->where("bsl.status", 2);
        $query = $this->db->get();
        $row = $query->row();
        if (!empty($row)) {
            return $row;
        }
    }

}
