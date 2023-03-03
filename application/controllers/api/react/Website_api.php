<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

class Website_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
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
    }
    public function index()
    {
        return FALSE;
    }
    
    /*
    |-------------------------------
    |   seller_signup Load Here
    |   route = api/react/website_api/seller_signup
    |-------------------------------
    */
    public function seller_signup()
	{
        $this->form_validation->set_rules('first_name', display('first_name'), 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean', array( 'required' => display('first_name_is_required')) );
		$this->form_validation->set_rules('last_name', display('last_name'), 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean', array( 'required' => display('last_name_is_required')) );
        $this->form_validation->set_rules('business_name', display('business_name'), 'required|trim|max_length[50]|alpha_numeric_spaces|xss_clean', array( 'required' => display('business_name').' '.display('required') ) );
        $this->form_validation->set_rules('store_name', 'Store Name', 'required|trim|max_length[50]|alpha_numeric_spaces|xss_clean');
        $this->form_validation->set_rules('phone', display('mobile'), 'required|trim|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean', array( 'required' => display('mobile').' '.display('required') ));
        $this->form_validation->set_rules('email', display('email'), 'required|trim|max_length[100]|valid_email|xss_clean', array( 'required' => display('email').' '.display('required') ) );
        $this->form_validation->set_rules('password', display('password'), 'trim|required|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})/]|min_length[6]|max_length[32]|xss_clean', array( 'required' => display('password').' '.display('required'), 'regex_match' => display('strong_password_combination_msg') ) );
        
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['first_name'])) {
                $errors_data = $errors['first_name'];
            }
            if (!empty($errors['last_name'])) {
                $errors_data = $errors['last_name'];
            }
            if (!empty($errors['business_name'])) {
                $errors_data = $errors['business_name'];
            }
            if (!empty($errors['store_name'])) {
                $errors_data = $errors['store_name'];
            }
            if (!empty($errors['phone'])) {
                $errors_data = $errors['phone'];
            }
            if (!empty($errors['email'])) {
                $errors_data = $errors['email'];
            }
            if (!empty($errors['password'])) {
                $errors_data = $errors['password'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $first_name = filter_input_data($this->input->post('first_name', TRUE));
            $last_name = filter_input_data($this->input->post('last_name', TRUE));
            $business_name = filter_input_data($this->input->post('business_name', TRUE));
            $store_name = filter_input_data($this->input->post('store_name', TRUE));
            $phone = filter_input_data($this->input->post('phone', TRUE));
            $email = filter_input_data($this->input->post('email', TRUE));
            $password = filter_input_data($this->input->post('password', TRUE));
            $seller_id 	= generator(10);

            $data=array(
				'seller_id' 	=> $seller_id,
				'first_name' 	=> $first_name,
				'last_name' 	=> $last_name,
				'business_name' => $business_name,
				'seller_store_name' => $store_name,
				'mobile' 		=> $phone,
				'email'			=> $email,
				'password' 		=> md5("gef".$password),
				'status' 		=> 2,
			);
            $ex_sel = $this->db->select('*')
				->from('seller_information')
				->where('email',$data['email'])
				->get()
				->num_rows();
            if ($ex_sel > 0) {
                JSONErrorOutput('Email Already Exits!');
            }else{
                $result = $this->db->insert('seller_information',$data);
                if ($result) {
                    JSONSuccessOutput(null, display('you_have_successfully_signup_wait_for_admin_approval'));
                }
            }
        }
	}
    
    /*
    |-------------------------------
    |   seller_login Load Here
    |   route = api/react/website_api/seller_login
    |-------------------------------
    */
    public function seller_login()
	{
		$error 		= '';
        $this->form_validation->set_rules('request_url', 'URL', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|max_length[100]|valid_email|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[3]|max_length[32]|xss_clean');
        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors_data = 'Invalid Request!';
            if (!empty($errors['request_url'])) {
                $errors_data = $errors['request_url'];
            }
            if (!empty($errors['email'])) {
                $errors_data = $errors['email'];
            }
            if (!empty($errors['password'])) {
                $errors_data = $errors['password'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $email = filter_input_data($this->input->post('email', TRUE));
            $password = filter_input_data($this->input->post('password', TRUE));
            $request_url = filter_input_data($this->input->post('request_url', TRUE));
            
            if ( $email == '' || $password == '' || $this->seller_auth($email, $password) === FALSE ){
                $error = display('wrong_username_or_password');
            }
            ////harun 11-05-2021 start
            $password_md5	= md5("gef".$password);
            $this->db->select('status');
            $this->db->where(array('email'=>$email,'password'=>$password_md5));
            $query 		= $this->db->get('seller_information');
            $result 	= $query->result_array();
            if (count($result) == 1){
                $seller_status = $query->row_array();
                if ((int)$seller_status['status'] != 1) {
                    $error = 'Your account is not active! Wait for admin approval';
                }
            }else{
                $error = 'Invalid credentials!';
            }
            ////end
            if ( $error != '' ){
                JSONErrorOutput($error);
            }else{
                $this->db->select('seller_domain');
                $this->db->from('soft_setting');
                $row_array = $this->db->get()->row_array();
                $redirect_url = array(
                    "url" => $row_array['seller_domain']."seller/login/remote_data?email=".$email."&password=".$password."&request_url=".$request_url
                );
                JSONSuccessOutput($redirect_url, 'Loging');
            }
        }
	}
    public function seller_auth($email,$password)
	{
        $CI 		=& get_instance();
		$password 	= md5("gef".$password);

        $CI->db->where(array('email'=>$email,'password'=>$password,'status' => '1'));
		$query 		= $CI->db->get('seller_information');
		$result 	= $query->result_array();
		
		if (count($result) == 1){
            return TRUE;
		}else {
            return FALSE;
        }
	}
    /*
    |-------------------------------
    |   company_information Load Here
    |   route = api/react/website_api/company_information
    |-------------------------------
    */
    public function company_information()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        //query
        $this->db->select('*');
        $this->db->from('company_information');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->row();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   web_setting Load Here
    |   route = api/react/website_api/web_setting
    |-------------------------------
    */
    public function web_setting()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        //query
        $this->db->select('*');
        $this->db->from('web_setting');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->row();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   link_page Load Here
    |   route = api/react/website_api/link_page
    |-------------------------------
    */
    public function link_page()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        $page_id = filter_input_data(@$info->page_id);
        //query
        $this->db->select('*');
        $this->db->from('link_page');
        $this->db->where('status', "1");
        if (!empty($page_id)) {
            $this->db->where('page_id', $page_id);
        }
        $this->db->where('language_id', "english");
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   social_medias Load Here
    |   route = api/react/website_api/social_medias
    |-------------------------------
    */
    public function social_medias()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        //query
        $this->db->select('*');
        $this->db->from('social_medias');
        $this->db->where('status', "1");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   header_top Load Here
    |   route = api/react/website_api/header_top
    |-------------------------------
    */
    public function header_top()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        //query
        $this->db->select('*');
        $this->db->from('company_information');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->row();
            JSONSuccessOutput($data);
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
    public function slider_list()
    {
        //api key checking
        // if (checkAuth(check_api_key())) {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);
        //query
        $this->db->select('slider_id,slider_link,slider_image');
        $this->db->from('slider');
        $this->db->where('status', 1);
        $this->db->order_by('slider_position');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }

        // }
    }
    /*
    |-------------------------------
    |   campaign_slider_list Load Here
    |   route = api/react/website_api/campaign_slider_list
    |-------------------------------
    */
    public function campaign_slider_list()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);
        //query
        $this->db->select('campaign_id, campaign_name, campaign_bannar');
        $this->db->from('campaign_info');
        $this->db->where('status', 1);
        $this->db->where('start_datetime <=',date('Y-m-d H:i:s'));
        $this->db->where('end_datetime >=',date('Y-m-d H:i:s'));
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
    |   coupon_list Load Here
    |   route = api/react/website_api/coupon_list
    |-------------------------------
    */
    public function coupon_list()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        //query
        $this->db->select('*');
        $this->db->from('coupon');
        $this->db->where('status', 1);
        $this->db->where('start_date <=',date('Y-m-d'));
        $this->db->where('end_date >=',date('Y-m-d'));
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
    |   category_list Load Here
    |   route = api/react/website_api/category_list
    |-------------------------------
    */
    public function category_list()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);
        $cat_id = filter_input_data(@$info->category_id);
        if ($cat_id) {
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

        $this->db->select('a.category_id, a.category_name, cat_image AS image, status');
        $this->db->from('product_category a');
        $this->db->where('a.status', 1);
        if (!empty($cat_id)) {
            $this->db->where_in('a.category_id', $all_cat_id);
        }
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
    |   top_menu Load Here
    |   route = api/react/website_api/top_menu
    |-------------------------------
    */
    public function top_menu()
    {

        $this->db->select('a.category_id, a.category_name, a.cat_image');
        $this->db->from('product_category a');
        $this->db->where('a.status', 1);
        $this->db->where('a.top_menu', 1);
        $this->db->where('a.cat_type', 1);
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
    |   mega_menu Load Here
    |   route = api/react/website_api/mega_menu
    |-------------------------------
    */
    public function mega_menu()
    {
        $this->db->select('a.category_id, a.category_name, a.cat_image');
        $this->db->from('product_category a');
        $this->db->where('a.status', 1);
        $this->db->where('a.featured', 1);
        $this->db->where('a.cat_type', 1);
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
    |   top_categories_of_the_month Load Here
    |   route = api/react/website_api/top_categories_of_the_month
    |-------------------------------
    */
    public function top_categories_of_the_month()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);
        $this->db->select('a.category_id, a.category_name, cat_image AS image, status');
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
    |   products
    |   GET
    |   route = api/react/website_api/products
    |-------------------------------
    */
    public function products()
    {
        $id_in = filter_input_data($this->input->get('id_in', TRUE));
        if (empty($id_in)) {
            JSONErrorOutput("Invalid Request!");
        }
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
                // $condition_campaign_campaign[] = '';
            } else {
                // $condition_campaign_normal[] = '';
                $condition_campaign_campaign[] = ("(cpi.product_id = '" . $p_array[$i]['p'] . "' AND cpi.campaign_id = '" . $p_array[$i]['c'] . "')");
            }
            $i++;
        }
        $condition_campaign = implode(" OR ", $condition_campaign_campaign);
        // d($condition_campaign);
        $condition_normal = implode(" OR ", $condition_campaign_normal);
        // dd($condition_normal);
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
                    $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : THUMB_CDN_DIR . $value['image_name'];
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
            // $this->db->where($where);
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
                    $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : THUMB_CDN_DIR . $value['image_name'];
                }
                $array2 = ($data);
            } 
        } 
        $result = array_merge($array1, $array2);
        JSONSuccessOutput($result);
        // print_r($result);
        // print_r($res);

    }
    
    /*
    |-------------------------------
    |   all_products
    |   POST
    |   route = api/react/website_api/all_products
    |-------------------------------
    */
    public function all_products()
    {

        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);

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
        // $this->db->where($where);

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
                $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : THUMB_CDN_DIR . $value['image_name'];
            }
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   block_products
    |   POST
    |   route = api/react/website_api/block_products
    |-------------------------------
    */
    public function block_products()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);
        $this->db->select('block.*,product_category.category_name AS block_name');
        $this->db->from('block');
        $this->db->join('product_category', 'block.block_cat_id = product_category.category_id');
        $this->db->where('block.status', 1);
        $this->db->order_by('block_position', 'asc');
        $query_block = $this->db->get();
        if ($query_block->num_rows() > 0) {
            $block_list = $query_block->result_array();
            foreach ($block_list as $key_off => $block) {
                $response = $this->category_wise_product_list($per_page, $page, $block['block_cat_id']);
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
    |   category_wise_product
    |   POST
    |   route = api/react/website_api/category_wise_product
    |-------------------------------
    */
    public function category_wise_product()
    {

        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        if (empty($info->category_id)) {
            JSONErrorOutput("Category is required!");
        } else {
            $cat_id = filter_input_data($info->category_id);
        }
        $page = filter_input_data(@$info->page_offset);
        $latest = filter_input_data(@$info->latest);
        $price = filter_input_data(@$info->price);

        $res = $this->category_wise_product_list($per_page, $page, $cat_id, $latest, $price);
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
    |   category_wise_product
    |   POST
    |   route = api/react/website_api/category_wise_product
    |-------------------------------
    */
    public function category_wise_product_list($per_page=null, $page=null, $cat_id=null, $latest=null, $price=null)
    {
        if ($cat_id) {
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
        // $this->db->where($where);
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

            $this->db->select('category_id, parent_category_id, cat_image AS image, category_name, status');
            $this->db->from('product_category');
            $this->db->where('category_id', $cat_id);
            $info = $this->db->get()->row();
          
            $data = $query->result_array();
            foreach ($data as $key => $value) {
                if ($value['on_sale'] == "1") {
                    $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                    $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                } else {
                    $data[$key]['discount_amount'] = null;
                    $data[$key]['discount_percent'] = null;
                }
                $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name'];
            }
            $response['data'] = $data;
            $response['info'] = $info;
            return $response;
        } else {
            return false;
        }
    }
    /*
    |-------------------------------
    |   brand_wise_products
    |   POST
    |   route = api/react/website_api/brand_wise_products
    |-------------------------------
    */
    public function brand_wise_products()
    {

        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        if (empty($info->brand_id)) {
            JSONErrorOutput("Brand is required!");
        } else {
            $brand_id = filter_input_data($info->brand_id);
        }
        $page = filter_input_data(@$info->page_offset);

        $where = "(a.quantity > 0 OR a.pre_order = 1)";
        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
        pi.image_path,
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
        $this->db->where('a.brand_id', $brand_id);
        // $this->db->where($where);

        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {

            $info = $this->db->where('brand_id', $brand_id)->get('brand')->row();

            $data = $query->result_array();
            foreach ($data as $key => $value) {
                if ($value['on_sale'] == "1") {
                    $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                    $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                } else {
                    $data[$key]['discount_amount'] = null;
                    $data[$key]['discount_percent'] = null;
                }
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
    |   store_wise_products
    |   POST
    |   route = api/react/website_api/store_wise_products
    |-------------------------------
    */
    public function store_wise_products()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        if (empty($info->seller_id)) {
            JSONErrorOutput("Store is required!");
        } else {
            $seller_id = filter_input_data($info->seller_id);
        }
        $page = filter_input_data(@$info->page_offset);
        $latest = filter_input_data(@$info->latest);
        $price = filter_input_data(@$info->price);

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
        $this->db->where('a.seller_id', $seller_id);
        // $this->db->where($where);
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

            $info = $this->db->select('seller_id,seller_store_name,address,image,
                    (SELECT AVG(pr.rate) FROM product_review pr WHERE pr.seller_id = si.seller_id) AS store_rating,
                    verfication_status')
                ->where('seller_id', $seller_id)
                ->get('seller_information si')->row();

            $data = $query->result_array();
            foreach ($data as $key => $value) {
                if ($value['on_sale'] == "1") {
                    $data[$key]['discount_amount'] = $value['price'] - $value['offer_price'];
                    $data[$key]['discount_percent'] = get_percent($value['price'], $value['offer_price']);
                } else {
                    $data[$key]['discount_amount'] = null;
                    $data[$key]['discount_percent'] = null;
                }
                $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name'];
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
    |   campaign_products
    |   POST
    |   route = api/react/website_api/campaign_products
    |-------------------------------
    */
    public function campaign_products()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        if (empty($info->campaign_id)) {
            JSONErrorOutput("Campaign is required!");
        } else {
            $campaign_id = filter_input_data($info->campaign_id);
            //query
            $this->db->select('campaign_id, campaign_name, campaign_bannar');
            $this->db->from('campaign_info');
            $this->db->where('status', 1);
            $this->db->where('campaign_id', $campaign_id);
            $this->db->where('start_datetime <=',date('Y-m-d H:i:s'));
            $this->db->where('end_datetime >=',date('Y-m-d H:i:s'));
            $q = $this->db->get();

            if ($q->num_rows() > 0) {
                $q->result();
            } else {
                JSONNoOutput("No Data Found!");
            }
        }
        $page = filter_input_data(@$info->page_offset);
        $latest = filter_input_data(@$info->latest);
        $price = filter_input_data(@$info->price);

        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,cpi.campaign_id,a.price,IF(a.on_sale = 0, "1", a.on_sale) as on_sale,cpi.product_campaign_price as offer_price,cpi.product_quantity as quantity,
        pi.image_path,
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
    |   featured_products
    |   POST
    |   route = api/react/website_api/featured_products
    |-------------------------------
    */
    public function featured_products()
    {

        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);

        $where = "(a.quantity > 0 OR a.pre_order = 1)";
        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
        pi.image_path,
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
        // $this->db->where($where);

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
            }
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
    public function best_selling()
    {

        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);

        $where = "(a.quantity > 0 OR a.pre_order = 1)";
        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
        pi.image_path,
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
        $this->db->where('a.best_sale', 1);
        // $this->db->where($where);

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
            }
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |-------------------------------
    |   new_arrival
    |   POST
    |   route = api/react/website_api/new_arrival
    |-------------------------------
    */
    public function new_arrival()
    {

        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);

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
        // $this->db->where($where);
        $this->db->order_by('a.product_info_id', 'desc');

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
                $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name'];
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
    public function recommended_products()
    {

        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);

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
        // $this->db->where($where);
        $this->db->order_by('a.product_info_id', 'desc');

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
                $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name'];
            }
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No Data Found!");
        }
    }
    /*
    |---------------------------------------------------
    |   Searching product
    |   post method 
    |   route = api/react/website_api/retrieve_category_product
    |---------------------------------------------------
    */
    public function retrieve_category_product()
    {
        $get_order_data = file_get_contents('php://input');
        $info = json_decode($get_order_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        $per_page = filter_input_data($info->per_page);
        $page = filter_input_data(@$info->page_offset);
        $category_id = filter_input_data(@$info->category_id);
        $product_name = filter_input_data($info->product_name);
        if (empty($per_page)) {
            JSONErrorOutput("Page Limit is required!");
        }
        if (empty($product_name)) {
            JSONErrorOutput("Product Keyword is required!");
        }
        $where = "(a.quantity > 0 OR a.pre_order = 1)";

        $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,IF(a.category_id is not null, 1, 1) as campaign_id,a.price,a.on_sale,a.offer_price,a.quantity,
        pi.image_path,
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
        // $this->db->where($where);
        if ($product_name) {
            $like_where = "(`c`.`title` LIKE '%" . $product_name . "%' ESCAPE '!' OR  `a`.`product_model` LIKE '%" . $product_name . "%' ESCAPE '!' OR  `b`.`category_name` LIKE '%" . $product_name . "%' ESCAPE '!')";
            $this->db->where($like_where);
        }
        if (!empty($category_id)) {
            $this->db->where('a.category_id', $category_id);
        }
        $this->db->order_by('a.product_info_id', 'desc');
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
    |   product_review_list Load Here
    |   route = api/react/website_api/product_review_list
    |-------------------------------
    */
    public function product_review_list()
    {
        $per_page = filter_input_data($this->input->get('per_page', TRUE));
        $page = filter_input_data($this->input->get('page', TRUE));
        $status = filter_input_data($this->input->get('status', TRUE));
        $product_id = filter_input_data($this->input->get('product_id', TRUE));
        //load from model file
        $this->db->select('product_review.*, IF(customer_information.customer_name is null, "", customer_information.customer_name) as customer_name');
        $this->db->from('product_review');
        $this->db->join('customer_information', 'product_review.reviewer_id = customer_information.customer_id', 'left');
        $this->db->where('product_review.status', 1);
        $this->db->where('product_review.product_id', $product_id);
        $query = $this->db->get();
        $product_review_list   =  $query->result();
        //response data list
        $data = array(
            'base_url' => base_url(),
            'product_review_list' => $product_review_list,
        );
        JSONSuccessOutput($data);
    }
    /*
    |-------------------------------
    |   product_review_count Load Here
    |   route = api/react/website_api/product_review_count
    |-------------------------------
    */
    public function product_review_count()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->product_id)) {
            JSONErrorOutput("Product is required!");
        } else {
            $product_id = filter_input_data($info->product_id);
        }
        // $product_id = filter_input_data($this->input->get('product_id', TRUE));
        //load from model file
        $this->db->select('count(product_review_id) as total_review, round(AVG(rate), 2) AS avg_rating, SUM(IF(rate=5, 1, 0)) AS five_star, SUM(IF(rate=4, 1, 0)) AS four_star, SUM(IF(rate=3, 1, 0)) AS three_star, SUM(IF(rate=2, 1, 0)) AS two_star, SUM(IF(rate=1, 1, 0)) AS one_star');
        $this->db->from('product_review');
        $this->db->where('status', 1);
        $this->db->where('product_id', $product_id);
        $query = $this->db->get();
        $result_array   =  $query->row_array();
        $data =  array_map("null_check", $result_array);
        JSONSuccessOutput($data);
    }
    /*
    |-------------------------------
    |   product_review_submit Load Here
    |   route = api/react/website_api/product_review_submit
    |-------------------------------
    */
    public function product_review_submit()
    {
        $product_id = filter_input_data($this->input->get('product_id', TRUE));
        $rate = filter_input_data($this->input->get('rate', TRUE));
        $title = filter_input_data($this->input->get('title', TRUE));
        $comments = filter_input_data($this->input->get('comments', TRUE));
        $customer_id = filter_input_data($this->input->get('customer_id', TRUE));
        //varify customer
        $this->db->select('*');
        $this->db->from('customer_information');
        $this->db->where('customer_id', $customer_id);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            JSONErrorOutput('Customer ID Not Found!');
        } else {
            $result_purchased = $this->db->select('*')
                ->from('seller_order')
                ->where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->get()
                ->num_rows();

            if ($result_purchased == 0) {
                JSONErrorOutput(display('not_purchased'));
            }
            $this->db->select('*');
            $this->db->from('product_review');
            $this->db->where('product_id', $product_id);
            $this->db->where('reviewer_id', $customer_id);
            $review_query = $this->db->get();
            if ($review_query->num_rows() == 0) {
                //load from model file
                $field_data = array(
                    'product_id'  => $product_id,
                    'rate'          => $rate,
                    'reviewer_id' => $customer_id,
                    'title'       => $title,
                    'comments'       => $comments,
                    'date_time'   => date("Y-m-d h:i:s"),
                    'status'      => 1,
                );
                $result = $this->db->insert('product_review', $field_data);
                if (!empty($result)) {
                    $response = display('your_review_added');
                    JSONSuccessOutput($response);
                } else {
                    JSONErrorOutput(display('ooops_something_went_wrong'));
                }
            } else {
                JSONErrorOutput('This Customer has previous review of this product');
            }
        }
    }
    /*
    |-------------------------------
    |   product_description Load Here
    |   route = api/react/website_api/product_description
    |-------------------------------
    */
    public function product_description()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->product_id)) {
            JSONErrorOutput("Product ID is required!");
        } else {
            $product_id = $info->product_id;
        }
        //query
        $this->db->select('description');
        $this->db->from('product_description');
        $this->db->where('product_id', $product_id);
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
    |   brand_list Load Here
    |   route = api/react/website_api/brand_list
    |-------------------------------
    */
    public function brand_list()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);
        //query
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
    |   shop_by_store
    |   route = api/react/website_api/shop_by_store
    |-------------------------------
    */
    public function shop_by_store()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->per_page)) {
            JSONErrorOutput("Page Limit is required!");
        } else {
            $per_page = filter_input_data($info->per_page);
        }
        $page = filter_input_data(@$info->page_offset);
        //query
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
    |   product_details Load Here
    |   route = api/react/website_api/product_details
    |-------------------------------
    */
    public function product_details()
    {
        $get_data = file_get_contents('php://input');
        $info = json_decode($get_data);
        if (empty($info)) {
            JSONErrorOutput("Invalid Request!");
        }
        if (empty($info->product_id)) {
            JSONErrorOutput("Product ID is required!");
        } else {
            $product_id = $info->product_id;
        }
        if (empty($info->campaign_id)) {
            JSONErrorOutput("Campaign is required!");
        } else {
            $campaign_id = $info->campaign_id;
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
                        $data[$key]['image_path'] = (!empty($value['img_url'])) ? $value['img_url'] : IMAGE_CDN_DIR . $value['image_name'];
                    }
                    JSONSuccessOutput($data);
                } else {
                    JSONNoOutput("No Data Found!");
                }
            } else {
                $this->db->select('a.product_id,a.seller_id,a.brand_id,a.category_id,cpi.campaign_id,a.price,IF(a.on_sale = 0, "1", a.on_sale) as on_sale,cpi.product_campaign_price as offer_price,cpi.product_quantity as quantity,
                pi.image_path,
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




    //Submit checkout
    public function submit_checkout()
    {
        $get_order_data = file_get_contents('php://input');
        $order_info = json_decode($get_order_data);

        if (empty($order_info->first_name)) {
            JSONErrorOutput('Customer first name is required!');
        }
        if (empty($order_info->last_name)) {
            JSONErrorOutput('Customer last name is required!');
        }
        if (empty($order_info->customer_email)) {
            JSONErrorOutput('Customer email is required!');
        }
        if (!filter_var($order_info->customer_email, FILTER_VALIDATE_EMAIL)) {
            JSONErrorOutput('Customer email is not valid!');
        }
        if (empty($order_info->country_id)) {
            JSONErrorOutput('Country ID is required!');
        }
        if (empty($order_info->state_id)) {
            JSONErrorOutput('State ID is required!');
        }
        if (empty($order_info->city_id)) {
            JSONErrorOutput('City ID is required!');
        }
        if (!is_numeric($order_info->vat_amount)) {
            JSONErrorOutput('Invalid vat amount');
        }
        //customer_id & customer_code set
        if (!empty($order_info->customer_id)) {
            //customer_information by customer_id
            $this->db->select('*');
            $this->db->from('customer_information');
            $this->db->where('customer_id', $order_info->customer_id);
            $query = $this->db->get();
            $data   =  $query->row();
            if (!empty($data)) {
                $customer_id = $order_info->customer_id;
            } else {
                JSONErrorOutput('Invalid Customer ID');
            }
            //data values
            $customer_code = $data->customer_code;
        } else {
            $customer_id = generator(15);
            $customer_code = $this->customer_number_generator();
        }
        //select country name by country id
        if (!empty($order_info->country_id)) {
            $this->db->select('*');
            $this->db->from('countries');
            $this->db->where('id', $order_info->country_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $country_name = $result->name;
            } else {
                JSONErrorOutput('Invalid country id!');
            }
        }
        //select state name by id id
        if (!empty($order_info->state_id)) {
            $this->db->select('*');
            $this->db->from('states');
            $this->db->where('id', $order_info->state_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $state_name = $result->name;
            } else {
                JSONErrorOutput('Invalid state id!');
            }
        }
        //select city name by city id
        if (!empty($order_info->city_id)) {
            $this->db->select('*');
            $this->db->from('cities');
            $this->db->where('id', $order_info->city_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $city_name = $result->name;
            } else {
                JSONErrorOutput('Invalid city id!');
            }
        }
        //shipping cost by city
        if (!empty($order_info->city_id)) {
            $this->db->select('*');
            $this->db->from('shipping_method');
            $this->db->where('city', $order_info->city_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $ship_cost = $result->charge_amount;
            } else {
                $ship_cost = 0;
            }
        }
        //set $order_info->data 
        $order_id               = generator(15);
        $payment_method         = $order_info->payment_method;
        $diff_ship_adrs         = 0; //if checked then value = 1
        //set amount parameter in costing_info array
        $costing_info = array(
            'cart_total_amount' => $order_info->cart_total_amount,
            'vat_amount' => $order_info->vat_amount,
            'ship_cost' => $ship_cost,
            'discount' => $order_info->discount,
            'totalAmount' => $order_info->totalAmount,
        );
        $order_details_info = array(
            'customer_id' => $customer_id,
            'order_id' => $order_id,
            'order_details' => $order_info->order_details,
            'payment_method' => $payment_method,
        );
        // Check product status before order
        // //submit_checkout_validation
        //check product status
        $is_exist = 'yes';
        if (!empty($order_info->cart_details)) {
            foreach ($order_info->cart_details as $items) {
                if (!empty($items->product_id)) {
                    $this->db->where('product_id', $items->product_id);
                    $query  = $this->db->get('product_information');
                    $result = $query->result_array();
                    if (count($result) == 1) {
                        $pinfo = $this->db->select('status')
                            ->from('product_information')
                            ->where('product_id', $items->product_id)
                            ->get()->row();
                        if ($pinfo->status != '2') {
                            $is_exist = 'no';
                        }
                    } else {
                        JSONErrorOutput('Product ID not found!');
                    }
                }
            }
        } else {
            JSONErrorOutput('No product is added in cart!');
        }
        // If all ordered products not approved
        if ($is_exist == 'no') {
            JSONErrorOutput('Failed! Products not exist!');
        }


        //new customer new shipping address and order entry
        //here load customer_shipping_order_entry()
        if ($diff_ship_adrs == 1) {
            //For creating customer short address
            if (!$this->user_auth->is_logged()) {
                $country_id = $this->session->userdata('country');
                $country = $this->Homes->get_country($country_id);
                $short_address = "";
                if ($this->session->userdata('city')) {
                    $short_address .= $this->session->userdata('city_name') . ',';
                }
                if ($this->session->userdata('state')) {
                    $short_address .= $this->session->userdata('state_name') . ',';
                }
                if ($country->name) {
                    $short_address .= $country->name . ',';
                }
                if ($this->session->userdata('zip')) {
                    $short_address .= $this->session->userdata('zip') . ',';
                }
                if ($this->session->userdata('customer_address_1')) {
                    $short_address .= $this->session->userdata('customer_address_1');
                }

                $billing = array(
                    'customer_id'               => $customer_id,
                    'customer_code'             => $customer_code,
                    'customer_name'             => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'),
                    'first_name'                => $this->session->userdata('first_name'),
                    'last_name'                 => $this->session->userdata('last_name'),
                    'customer_short_address'    => $short_address,
                    'customer_address_1'        => $this->session->userdata('customer_address_1'),
                    'customer_address_2'        => $this->session->userdata('customer_address_2'),
                    'city'                      => $this->session->userdata('city_name'),
                    'state'                     => $this->session->userdata('state_name'),
                    'country'                   => $this->session->userdata('country'),
                    'zip'                       => $this->session->userdata('zip'),
                    'company'                   => $this->session->userdata('company'),
                    'customer_mobile'           => $this->session->userdata('customer_mobile'),
                    'customer_email'            => $this->session->userdata('customer_email'),
                    'image'                     => 'my-assets/image/avatar.png',
                );

                //Billing information insert
                $this->db->insert('customer_information', $billing);
            }
            //Shipping data entry
            $ship_country_id = $this->session->userdata('ship_country');
            $ship_country = $this->Homes->get_country($ship_country_id);
            $ship_short_address = "";
            if ($this->session->userdata('ship_city')) {
                $ship_short_address .= $this->session->userdata('ship_city') . ',';
            }
            if ($this->session->userdata('ship_state')) {
                $ship_short_address .= $this->session->userdata('ship_state') . ',';
            }
            if ($country->name) {
                $ship_short_address .= $ship_country->name . ',';
            }
            if ($this->session->userdata('ship_zip')) {
                $ship_short_address .= $this->session->userdata('ship_zip') . ',';
            }
            if ($this->session->userdata('ship_address_1')) {
                $ship_short_address .= $this->session->userdata('ship_address_1');
            }

            //New customer shipping entry
            $shipping = array(
                'customer_id'           => $customer_id,
                'customer_code'         => $customer_code,
                'order_id'              => $order_id,
                'customer_name'         => $this->session->userdata('ship_first_name') . ' ' . $this->session->userdata('ship_last_name'),
                'first_name'            => $this->session->userdata('ship_first_name'),
                'last_name'             => $this->session->userdata('ship_last_name'),
                'customer_short_address' => $ship_short_address,
                'customer_address_1'    => $this->session->userdata('ship_address_1'),
                'customer_address_2'    => $this->session->userdata('ship_address_2'),
                'city'                  => $this->session->userdata('ship_city'),
                'state'                 => $this->session->userdata('ship_state'),
                'country'               => $this->session->userdata('ship_country'),
                'zip'                   => $this->session->userdata('ship_zip'),
                'company'               => $this->session->userdata('ship_company'),
                'customer_mobile'       => $this->session->userdata('ship_mobile'),
                'customer_email'        => $this->session->userdata('ship_email'),
            );
            $this->limarket_api_model->shipping_entry($shipping);
        } else {
            //For creating customer short address
            $short_address = "";
            if (!empty($city_name)) {
                $short_address .= $city_name . ',';
            }
            if (!empty($state_name)) {
                $short_address .= $state_name . ',';
            }
            if (!empty($country_name)) {
                $short_address .= $country_name . ',';
            }
            if (!empty($order_info->zip)) {
                $short_address .= $order_info->zip . ',';
            }
            if (!empty($order_info->customer_address_1)) {
                $short_address .= $order_info->customer_address_1;
            }

            $billing = array(
                'customer_id'           => $customer_id,
                'customer_code'         => $customer_code,
                'customer_name'         => $order_info->first_name . ' ' . $order_info->last_name,
                'first_name'            => $order_info->first_name,
                'last_name'             => $order_info->last_name,
                'customer_short_address' => $short_address,
                'customer_address_1'    => $order_info->customer_address_1,
                'customer_address_2'    => $order_info->customer_address_2,
                'city'                  => $city_name,
                'state'                 => $state_name,
                'country'               => $country_name,
                'zip'                   => $order_info->zip,
                'company'               => $order_info->company,
                'customer_mobile'       => $order_info->customer_mobile,
                'customer_email'        => $order_info->customer_email,
                'image'                 => 'my-assets/image/avatar.png',
            );
            //customer_information insert
            if (empty($order_info->customer_id)) {
                $this->limarket_api_model->customer_information_insert($billing);
            }
            //Shipping info entry
            $this->limarket_api_model->shipping_entry($billing, $order_id);
        }

        if (!($payment_method == 'cash' || $payment_method == 'limo')) {
            JSONErrorOutput('Invalid Payment Method!');
        }
        //Cash on delivery
        if ($payment_method == 'cash') {
            //Order entry
            $return_order_id = $this->limarket_api_model->order_entry($order_info->cart_details, $order_details_info, $costing_info);
            //gererating order pdf
            $this->order_html_data($return_order_id);
        }
        //liyeplimal payment
        $confirm = 'yes';
        if ($payment_method == 'limo') {
            $coupon_amnt           = 0;
            $liyeplimal_data = array(
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'cart_total_amount' => $order_info->totalAmount,
            );
            //
            $payment_history = array(
                'payment_method' => $payment_method,
                'request' => 'send',
                'token' => '',
                'total_price' => $order_info->totalAmount,
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'request_time' => date('Y-m-d H:i:s'),
                'customer_email' => $order_info->customer_email,
                'customer_mobile' => $order_info->customer_mobile,
                'vat_amount' => 0,
                'coupon_amnt' => (is_array($coupon_amnt)) ? implode(",", $coupon_amnt) : $coupon_amnt,
                'ship_cost' => $ship_cost,
                'product_name' => 'api_test_product',
                'product_quantity' => 1,
                'product_model' => 'M01',
            );
            $this->db->insert('payment_history', $payment_history);
            $payment_history_id = $this->db->insert_id();

            $this->limarket_api_model->payment_by_liplimal($confirm, $payment_history['customer_email'], $order_info->cart_details, $ship_cost, $payment_history['total_price'], $coupon_amnt, $payment_history_id, $liyeplimal_data, $order_info->cart_details, $order_details_info, $costing_info);
        }
    }


    //end submit_checkout api
    public function limoney_confirm()
    {
        $apk = filter_input_get('apk');
        $amount = filter_input_get('amount');
        $success = filter_input_get('success');
        $status = filter_input_get('status');
        if ($apk == 'yes') {
            $success_return = filter_input_get('/?vhut');
            $order_id       = filter_input_get('order_id');
            $customer_id    = filter_input_get('customer_id');
            $paid_amount    = filter_input_get('cart_total_amount');
            $old_token      = filter_input_get('old_token');
        } else {
            $success_return = filter_input_get('vhut');
            $order_id       = $this->session->userdata('order_id');
            $customer_id    = $this->session->userdata('customer_id');
            $paid_amount    = $this->session->userdata('cart_total_amount');
            $old_token      = $this->session->userdata('token');
        }
        $payment_history = array(
            'payment_method' => 'limo',
            'request' => 'recieve',
            'token' => $success_return,
            'amount' => $amount,
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'request_time' => date('Y-m-d H:i:s')
        );
        $this->db->insert('payment_history', $payment_history);
        $payment_history_id = $this->db->insert_id();

        if ($status == '200' && $success == 'true' && $success_return == $old_token && !is_null($amount) && $order_id != '' && $customer_id != '') {
            $this->db->update('order', array('paid_amount' => $paid_amount), array('order_id' => $order_id));
            //gererating order pdf
            $this->order_html_data($order_id);
        } else {
            $this->session->unset_userdata('token');
            if ($status != '200') {
                $message = "API Error: " . $status . " Service Not Available";
                JSONErrorOutput($message);
            } else {
                $this->session->set_userdata('error_message', 'Invalid token or session timout!');
                JSONErrorOutput('Invalid token or session timout!');
            }
        }
    }



    //Send Customer Email with invoice
    public function setmail($email, $file_path, $order_email)
    {
        $CI = &get_instance();

        if ($email) {

            //send email with as a link
            $setting_detail = $CI->Soft_settings->retrieve_email_editdata();
            $company_info   = $CI->Companies->company_list();
            $template       = $CI->Email_templates->retrieve_template('8');

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


    //Email testing for email
    public function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    /*
    |---------------------------------------------------
    |   Retrieve State List
    |   post method 
    |   route = api/react/website_api/state_list
    |---------------------------------------------------
    */
    public function state_list()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('country_id', 'country id', 'trim|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['country_id'])) {
                    $errors_data = $errors['country_id'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $country_id = filter_input_data($this->input->get('country_id', TRUE));
                //
                $this->db->select('*');
                $this->db->from('states');
                if (!empty($country_id)) {
                    $this->db->where('country_id', $country_id);
                }
                $query = $this->db->get();
                $data   =  $query->result();
                if (!empty($data)) {
                    //saving api history in database
                    $response = $data;
                    JSONSuccessOutput($response);
                } else {
                    JSONErrorOutput(display('not_found'));
                }
            }
        }
    }
    /*
    |---------------------------------------------------
    |   Retrieve Cities List
    |   post method 
    |   route = api/react/website_api/cities_list
    |---------------------------------------------------
    */
    public function cities_list()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('state_id', 'State id', 'trim|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['state_id'])) {
                    $errors_data = $errors['state_id'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $state_id = filter_input_data($this->input->get('state_id', TRUE));
                //
                $this->db->select('*');
                $this->db->from('cities');
                if (!empty($state_id)) {
                    $this->db->where('state_id', $state_id);
                }
                $query = $this->db->get();
                $data   =  $query->result();
                if (!empty($data)) {
                    //saving api history in database
                    $response = $data;
                    JSONSuccessOutput($response);
                } else {
                    JSONErrorOutput(display('not_found'));
                }
            }
        }
    }
    /*
    |---------------------------------------------------
    |   Retrieve Cities List
    |   post method 
    |   route = api/react/website_api/shipping_charge
    |---------------------------------------------------
    */
    public function shipping_charge()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('city_id', 'City id', 'trim|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['city_id'])) {
                    $errors_data = $errors['city_id'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $city_id = filter_input_data($this->input->get('city_id', TRUE));
                //
                $this->db->select('*');
                $this->db->from('shipping_method');
                if (!empty($city_id)) {
                    $this->db->where('city', $city_id);
                }
                $query = $this->db->get();
                $data   =  $query->result();
                if (!empty($data)) {
                    //saving api history in database
                    $response = $data;
                    JSONSuccessOutput($response);
                } else {
                    JSONErrorOutput(display('not_found'));
                }
            }
        }
    }
    /*
    |---------------------------------------------------
    |   API = category list via brand id 
    |   SQL = SELECT product_category.category_name FROM `product_information` JOIN product_category ON product_category.category_id = product_information.category_id WHERE `brand_id` LIKE 'BY7KJUUG6TS1GNO' GROUP BY product_category.category_id
    |   Result = category_name
    |---------------------------------------------------
    */
    public function category_name_via_brand()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('brand_id', 'Brand ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|numeric|is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['brand_id'])) {
                    $errors_data = $errors['brand_id'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            }

            $per_page = filter_input_data($this->input->get('per_page', TRUE));
            $page = filter_input_data($this->input->get('page', TRUE));
            $user_lang = filter_input_data($this->input->get('language', TRUE));
            $brand = filter_input_data($this->input->get('brand_id', TRUE));
            $all_brand = (explode("--", $brand));
            $lang_id = 0;
            if (empty($user_lang)) {
                $lang_id = 'english';
            } else {
                $lang_id = $user_lang;
            }

            $where = "(a.quantity > 0 OR a.pre_order = 1)";

            $this->db->select('b.category_id, b.category_name, b.cat_image');
            $this->db->from('product_information a');
            $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');

            $this->db->where('a.status', 2);
            // $this->db->where($where);

            if ($brand) {
                $this->db->where_in('a.brand_id', $all_brand);
            }

            $this->db->group_by('b.category_id');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            $category_name = $query->result_array();

            $data = array(
                'base_url' => base_url(),
                'category_name' => $category_name,
            );
            JSONSuccessOutput($data);
        }
    }

    /*
    |---------------------------------------------------
    |   API =api/react/website_api/order_details
    |   SQL = SELECT a.date, a.order_id, a.order_no, a.order_status, a.file_path, a.total_amount, a.paid_amount, (a.total_amount - a.paid_amount) AS due_amount, IF(a.total_amount = a.paid_amount, "Paid", "Unpaid") AS payment_status, c.agent AS payment_method FROM `order` a LEFT JOIN order_payment b ON a.order_id = b.order_id LEFT JOIN payment_gateway c ON b.payment_id = c.id WHERE a.order_id = 'CZC8W2216WJYOJQ'
    |   Result = order_details
    |---------------------------------------------------
    */
    public function order_details()
    {
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('order_id', 'Order ID', 'trim|required|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['order_id'])) {
                    $errors_data = $errors['order_id'];
                }
                JSONErrorOutput($errors_data);
            }

            $order_id = filter_input_data($this->input->get('order_id', TRUE));

            $this->db->select('
                    a.date, a.order_id, a.order_no, a.order_status, a.file_path, a.total_amount, a.paid_amount, 
                    (a.total_amount - a.paid_amount) AS due_amount, IF(a.total_amount = a.paid_amount, "Paid", "Unpaid") AS payment_status, 
                    c.agent AS payment_method
            ');
            $this->db->from('order a');
            $this->db->join('order_payment b', 'a.order_id = b.order_id', 'left');
            $this->db->join('payment_gateway c', 'b.payment_id = c.code', 'left');

            $this->db->where('a.order_id', $order_id);

            $query = $this->db->get();
            $order_details = $query->result_array();

            $data = array(
                'order_details' => $order_details,
            );
            JSONSuccessOutput($data);
        }
    }

    public function customer_signup()
    {
        // if (checkAuth(check_api_key())) {

        $this->form_validation->set_rules('customer_name', 'Name', 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean');
        $this->form_validation->set_rules('customer_address', 'Address', 'trim|xss_clean');
        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('customer_email', 'Email', 'trim|max_length[100]|valid_email|xss_clean');
        $this->form_validation->set_rules('password', display('password'), 'trim|required|min_length[6]|max_length[32]|xss_clean');
        // $this->form_validation->set_rules('password', display('password'), 'trim|required|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})/]|min_length[6]|max_length[32]|xss_clean', array( 'required' => display('password').' '.display('required'), 'regex_match' => display('strong_password_combination_msg') ) );
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
	|	customer_login
	|	POST method 
	|	route = api/react/website_api/customer_login
	|-------------------------------
	*/
    public function customer_login()
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
                JSONErrorOutput('This Mobile No. has no User, Please Try with Another No. ');
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
                JSONSuccessOutput($response, "Login Successfull");
            }
        }
        // }

    }

    /*
	|-------------------------------
	|	password_reset
	|	POST method 
	|	route = api/react/website_api/password_reset
	|-------------------------------
	*/
    public function password_reset()
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
                            JSONSuccessOutput(display('varifaction_mail_was_sent_please_check_your_email'));
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

    public function new_mobile_otp($mobile, $customer_id)
    {
        if (empty($mobile)) {
            JSONErrorOutput('Invalid Info');
        } else {
            // $this->db->where('phone', $mobile);
            // $this->db->where('status', '0');
            // $this->db->group_by('phone');
            // $query = $this->db->get('customer_login');
            // if ($query->num_rows() > 0) {
            //     $result = $query->row();
                
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
                    // JSONSuccessOutput(null, 'OTP Sent to Your Mobile Number');
                } else {
                    JSONErrorOutput('Service Unavailable, Please Try Later');
                }
            // } else {
            //     JSONErrorOutput('This Mobile No. has no User, Please Try with Another');
            // }
        }
    }

    /*
	|---------------------------------------------------
	|	customer_otp_validation_mobile
	|	POST method 
	|	route = api/react/website_api/customer_otp_validation_mobile
	|---------------------------------------------------
	*/
    public function customer_otp_validation_mobile()
    {
        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['customer_mobile'])) {
                $errors_data = $errors['customer_mobile'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
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
                $this->db->insert('reset_password_otp', $wdata);
                $msg = "Your Validation OTP: " . $wdata['otp'];
                $response = send_sms($wdata['mobile'], $msg);
                if (strlen($response) != 4) {
                    JSONSuccessOutput(null, 'OTP Sent to Your Mobile Number');
                } else {
                    JSONErrorOutput('Service Unavailable, Please Try Later');
                }
            } else {
                JSONErrorOutput('This Mobile No. has no User, Please Try with Another');
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
    public function submit_otp_validation()
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

    /*
	|---------------------------------------------------
	|	confirm_otp_password
	|	POST method 
	|	route = api/react/website_api/confirm_otp_password
	|---------------------------------------------------
	*/
    public function confirm_otp_password()
    {
        $this->form_validation->set_rules('customer_mobile', 'Mobile No.', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean');
        $this->form_validation->set_rules('password', display('password'), 'trim|required|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})/]|min_length[6]|max_length[32]|xss_clean', array( 'required' => display('password').' '.display('required'), 'regex_match' => display('strong_password_combination_msg') ) );
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
            $password = md5( "gef".filter_input_data($this->input->post('password', TRUE)) );
            
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

    public function order_dompdf()
    {
        
        if (!empty($_POST['order_id'])) {
            $this->order_html_data($_POST['order_id']);
        }else{
            JSONErrorOutput('Not Found');
        }


        // if (!empty($_FILES['payment_slip']['name'])) {
        //     d($_FILES['payment_slip']);
        //     $file_array = array(
        //         'name' => 'EZ214932955835.pdf',
        //         'type' => 'application/pdf',
        //         'tmp_name' => 'D:\xampp_7.4\tmp\php2FC9.tmp',
        //         'error' => 0,
        //         'size' => 3450
        //     );
        //     dd($file_array);
        //     $sizes = array(1300 => 1300, 235 => 235);
        //     $file_location = $this->do_upload_file($_FILES['payment_slip'], $sizes);
        //     $image_name = explode('/', $file_location[0]);
        //     $image_name = end($image_name);
        //     $base_path = SPACE_URL;
        //     $payment_slip = $base_path . '/' . 'bankPayslip/' . $image_name;
        // }else{
        //     JSONErrorOutput('Not Found');
        // }
    }

    //Order html Data
    public function order_html_data($order_id)
    {
        // $return_data = $this->limarket_api_model->retrieve_order_html_data($order_id);
        // var_dump($order_id);exit;
        $CI = &get_instance();

        $CI->load->model('website/Homes');
        $CI->load->model('Soft_settings');
        $CI->load->model('Orders');
        $CI->load->library('occational');
        $CI->load->library('Pdfgenerator');

        $order_detail         = $CI->Homes->retrieve_order_html_data($order_id);
        
        if (empty($order_detail)) {
            JSONErrorOutput('Invalid Info');
        }

        //Payment Method
        $paymethod = $CI->Homes->get_payment_method($order_id);

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

        $currency_details = $CI->Soft_settings->retrieve_currency_info();
        $company_info       = $CI->Orders->retrieve_company();
        $data = array(
            'title'                =>    display('order_details'),
            'order_id'            =>    $order_detail[0]['order_id'],
            'order_no'            =>    $order_detail[0]['order_no'],
            //'invoice_no'          =>  $order_detail[0]['invoice_no'],
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
        );

        $chapterList = $CI->parser->parse('order/order_pdf', $data, true);
        //PDF Generator
        $dompdf = new DOMPDF();
        $dompdf->loadHtml($chapterList);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents('my-assets/pdf/' . $order_detail[0]['order_no'] . '.pdf', $output);
        $file_path = 'my-assets/pdf/' . $order_detail[0]['order_no'] . '.pdf';
        dd(base_url($file_path));
        $sizes = array(1300 => 1300, 235 => 235);
        $file_location = $this->do_upload_file($_FILES['payment_slip'], $sizes);
        $image_name = explode('/', $file_location[0]);
        $image_name = end($image_name);
        $base_path = SPACE_URL;
        $payment_slip = $base_path . '/' . 'bankPayslip/' . $image_name;
        //File path save to database
        $CI->db->set('file_path', base_url($file_path));
        $CI->db->where('order_id', $order_id);
        $CI->db->update('order');

        $send_email = '';
        if (!empty($data['customer_email'])) {
            $send_email = $this->setmail($data['customer_email'], $file_path, null);
        }

        if ($send_email != null) {
            return true;
        } else {
            JSONSuccessOutput(null, display('product_successfully_order'));
        }
    }

    function do_upload_file($FILES, $sizes, $type = null)
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

                    $files[] = $this->resize_file($w, $h, $FILES, $filetype[$k], $filename, $type);
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

    function resize_file($width, $height, $FILES, $filetype, $filename, $type = null)
    {
        // $this->do_resize($width, $height, $FILES);
        if ($filetype == 'main' & $type == null) {
            $save_as = 'bankPayslip/' . $filename;
            $uploads = $this->spaceobj->upload_to_space($FILES['tmp_name'], $save_as);
        } else {
            // $this->do_resize($width, $height, $FILES);
            $save_as = 'issueAttachment/' . $filename;
            $uploads = $this->spaceobj->upload_to_space($FILES['tmp_name'], $save_as);
        }
        return $filename;
    }


    public function submit_checkout_validation()
    {

        // print_r($_GET);
        $this->form_validation->set_rules('first_name', display('first_name'), 'trim|required|max_length[50]|regex_match[/^([0-9\p{L}\.\-]|\s)+$/u]|xss_clean', array('required' => display('first_name_is_required')));
        $this->form_validation->set_rules('last_name', display('last_name'), 'trim|required|max_length[50]|regex_match[/^([0-9\p{L}\.\-]|\s)+$/u]|xss_clean', array('required' => display('last_name_is_required')));
        $this->form_validation->set_rules('customer_email', display('email'), 'trim|max_length[100]|valid_email|xss_clean');
        $this->form_validation->set_rules('customer_mobile', display('mobile'), 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean', array('required' => display('mobile_is_required')));
        $this->form_validation->set_rules('customer_address_1', display('address'), 'trim|required|max_length[250]|regex_match[/^([0-9\p{L}\:\.\+\-\,\#]|\s)+$/u]|xss_clean', array('required' => display('address_is_required')));
        $this->form_validation->set_rules('customer_address_2', display('address'), 'trim|max_length[250]|regex_match[/^([0-9\p{L}\:\.\+\-\,\#]|\s)*$/u]|xss_clean');
        $this->form_validation->set_rules('zip', display('zip'), 'trim|max_length[20]|regex_match[/^([0-9\p{L}\-]|\s)*$/u]|xss_clean');
        $this->form_validation->set_rules('company', display('company'), 'trim|max_length[100]|regex_match[/^([0-9\p{L}\.\-]|\s)*$/u]|xss_clean');

        if ($this->form_validation->run() === FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['first_name'])) {
                $errors_data = array(
                    display('first_name') => $errors['first_name'],
                );
            }
            if (!empty($errors['last_name'])) {
                $errors_data = array(
                    display('last_name') => $errors['last_name'],
                );
            }
            if (!empty($errors['customer_email'])) {
                $errors_data = array(
                    display('email') => $errors['customer_email'],
                );
            }
            if (!empty($errors['customer_mobile'])) {
                $errors_data = array(
                    display('mobile') => $errors['customer_mobile'],
                );
            }
            if (!empty($errors['customer_address_1'])) {
                $errors_data = array(
                    display('address') => $errors['customer_address_1'],
                );
            }
            if (!empty($errors['customer_address_2'])) {
                $errors_data = array(
                    display('address') => $errors['customer_address_2'],
                );
            }
            if (!empty($errors['zip'])) {
                $errors_data = array(
                    display('zip') => $errors['zip'],
                );
            }
            if (!empty($errors['company'])) {
                $errors_data = array(
                    display('company') => $errors['company'],
                );
            }
            JSONErrorOutput($errors_data);
        }
    }
}
