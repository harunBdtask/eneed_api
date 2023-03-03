<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class React_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
    }
    public function index()
    {
        return FALSE;
    }
    
    /*
	|-------------------------------
	|	campaign_banar Load Here
	|	route = api/react_api/campaign_banar
	|-------------------------------
	*/
    public function campaign_banar()
    {
        $this->db->where('on_promotion', 1);
        $this->db->where('promo_date >', date('Y-m-d'));
        $result_array = $this->db->get('product_category')->row();
        echo json_encode($result_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /*
	|-------------------------------
	|	campaign_category Load Here
	|	route = api/react_api/campaign_category
	|-------------------------------
	*/
    public function campaign_category()
    {
        $this->db->where('on_promotion', 1);
        $this->db->where('promo_date >', date('Y-m-d'));
        $result_array = $this->db->get('product_category')->result_array();
        echo json_encode($result_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /*
	|-------------------------------
	|	campaign_products Load Here
	|	route = api/react_api/campaign_products
	|-------------------------------
	*/
    public function campaign_products()
    {
        $this->db->select('a.*,c.*');
        $this->db->from('product_information a');
        // $this->db->join('product_category b', 'a.category_id  = b.category_id', 'left');
        $this->db->join('product_title c', 'a.product_id = c.product_id', 'left');
        $this->db->group_by('a.product_id');
        
        $result_array = $this->db->get()->result_array();
        echo json_encode($result_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /*
	|-------------------------------
	|	promo_cat_list Load Here
	|	route = api/limarket_api/promo_cat_list
	|-------------------------------
	*/
    public function promo_cat_list()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|numeric|is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                //load from model file
                $promo_cat_list = $this->limarket_api_model->promo_cat_list($per_page, $page);
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'promo_cat_list' => $promo_cat_list,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	promotion_product Load Here
	|	route = api/limarket_api/promotion_product
	|-------------------------------
	*/
    public function promotion_product()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('category_id', 'Category ID', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|numeric|is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['category_id'])) {
                    $errors_data = $errors['category_id'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                $category_id = filter_input_data($this->input->post('category_id', TRUE));
                //load from model file
                $promotion_product = $this->limarket_api_model->promotion_product($per_page, $page, $category_id);
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'promotion_product' => $promotion_product,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	product_list
	|	route = api/limarket_api/product_list
	|-------------------------------
	*/
    public function product_list()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'required|trim|numeric|is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('price_start', 'Product Price Starting Amount', 'trim|numeric|is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('price_end', 'Product Price End Amount', 'trim|numeric|is_natural_no_zero|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                if (!empty($errors['price_start'])) {
                    $errors_data = $errors['price_start'];
                }
                if (!empty($errors['price_end'])) {
                    $errors_data = $errors['price_end'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                $user_lang = filter_input_data($this->input->post('language', TRUE));
                $product_id = filter_input_data($this->input->post('product_id', TRUE));
                $seller_id = filter_input_data($this->input->post('seller_id', TRUE));
                $cat_id = filter_input_data($this->input->post('category_id', TRUE));
                $all_cat_id = (explode("--",$cat_id));
                $price_start = filter_input_data($this->input->post('price_start', TRUE));
                $price_end = filter_input_data($this->input->post('price_end', TRUE));
                $best_sale = filter_input_data($this->input->post('best_sale', TRUE));
                $product_status = filter_input_data($this->input->post('product_status', TRUE));
                //load from model file
                //$user_lang = $this->session->userdata('language');
            if (empty($user_lang)) {
                $lang_id = 'english';
            }else{
                $lang_id = $user_lang;
            }
            
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            
            $this->db->select('a.*,
			a.status as product_status,
			b.category_name,
			c.*,
			d.brand_name,
			e.business_name,e.seller_guarantee,e.first_name,e.last_name,e.seller_store_name,f.description,pct.trans_category_name,t.meta_title meta_title_trans,t.meta_keyword meta_keyword_trans,t.meta_description meta_description_trans');
            $this->db->from('product_information a');
            $this->db->join('product_information_translation t', "a.product_id = t.product_id AND t.lang='$lang_id'", 'left');
            $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
            $this->db->join('product_title c', 'a.product_id = c.product_id', 'left');
            $this->db->join('brand d', 'a.brand_id = d.brand_id', 'left');
            $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
            $this->db->join('product_description f', "a.product_id = f.product_id AND f.lang_id='$lang_id' AND f.description_type=1", 'left');
            $this->db->join('product_category_translation pct', "pct.category_id = b.category_id AND pct.lang='$lang_id'", 'left');

            $this->db->where('a.status',2);
            $this->db->where($where);

            if ($price_start && $price_end) {
                $this->db->where('a.price >=', $price_start);
            	$this->db->where('a.price <=', $price_end);
            }

            if ($product_id) {
            	$this->db->where('a.product_id', $product_id);
            }

            if ($seller_id) {
            	$this->db->where('a.seller_id', $seller_id);
            }

            if ($cat_id) {
            	$this->db->where_in('a.category_id', $all_cat_id);
            }

            if ($best_sale != '' && $best_sale != null) {
                $this->db->where('a.best_sale', $best_sale);
            }

            // $this->db->where('c.lang_id',$lang_id);
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
            $response_data = $query->result_array();
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'product_list' => $response_data,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	product_img_list Load Here
	|	route = api/limarket_api/product_img_list
	|-------------------------------
	*/
    public function product_img_list()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|is_natural_no_zero|numeric|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['product_id'])) {
                    $errors_data = $errors['product_id'];
                }
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                $status = filter_input_data($this->input->post('status', TRUE));
                $product_id = filter_input_data($this->input->post('product_id', TRUE));
                //load from model file
                $this->db->select('*');
                $this->db->from('product_image');
                $this->db->where('product_id', $product_id);
                $this->db->group_by('image_name');
                $query = $this->db->get();
                $product_img_list   =  $query->result();
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'product_img_list' => $product_img_list,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	product_review_list Load Here
	|	route = api/limarket_api/product_review_list
	|-------------------------------
	*/
    public function product_review_list()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|is_natural_no_zero|numeric|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['product_id'])) {
                    $errors_data = $errors['product_id'];
                }
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                $status = filter_input_data($this->input->post('status', TRUE));
                $product_id = filter_input_data($this->input->post('product_id', TRUE));
                //load from model file
                $this->db->select('product_review.*, IF(customer_information.customer_name is null, "", customer_information.customer_name) as customer_name');
                $this->db->from('product_review');
                $this->db->join('customer_information', 'product_review.reviewer_id = customer_information.customer_id', 'left');
                $this->db->where('product_review.status', 1);
                $this->db->where('product_review.product_id', $product_id);
                //$this->db->group_by('image_name');
                $query = $this->db->get();
                //echo $this->db->last_query();exit;
                $product_review_list   =  $query->result();
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'product_review_list' => $product_review_list,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }


    /*
	|-------------------------------
	|	product_review_count Load Here
	|	route = api/limarket_api/product_review_count
	|-------------------------------
	*/
    public function product_review_count()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['product_id'])) {
                    $errors_data = $errors['product_id'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $product_id = filter_input_data($this->input->post('product_id', TRUE));
                //load from model file
                $this->db->select('count(product_review_id) as total_review, round(AVG(rate), 2) AS product_rating, SUM(IF(rate=5, 1, 0)) AS five_star, SUM(IF(rate=4, 1, 0)) AS four_star, SUM(IF(rate=3, 1, 0)) AS three_star, SUM(IF(rate=2, 1, 0)) AS two_star, SUM(IF(rate=1, 1, 0)) AS one_star');
                $this->db->from('product_review');
                $this->db->where('status', 1);
                $this->db->where('product_id', $product_id);
                //$this->db->group_by('image_name');
                $query = $this->db->get();
                $result_array   =  $query->row_array();
                //var_dump($result_array);exit;

                $product_review_count =  array_map("null_check", $result_array);

                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'product_review_count' => $product_review_count,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	product_review_submit Load Here
	|	route = api/limarket_api/product_review_submit
	|-------------------------------
	*/
    public function product_review_submit()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('rate', 'Product Rating', 'trim|required|is_natural_no_zero|numeric|xss_clean');
            $this->form_validation->set_rules('title', 'Product Rating Title', 'trim|required|xss_clean');
            $this->form_validation->set_rules('comments', 'Product Rating Comments', 'trim|required|xss_clean');
            $this->form_validation->set_rules('customer_id', 'Customer ID', 'trim|required|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['product_id'])) {
                    $errors_data = $errors['product_id'];
                }
                if (!empty($errors['rate'])) {
                    $errors_data = $errors['rate'];
                }
                if (!empty($errors['title'])) {
                    $errors_data = $errors['title'];
                }
                if (!empty($errors['comments'])) {
                    $errors_data = $errors['comments'];
                }
                if (!empty($errors['customer_id'])) {
                    $errors_data = $errors['customer_id'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $product_id = filter_input_data($this->input->post('product_id', TRUE));
                $rate = filter_input_data($this->input->post('rate', TRUE));
                $title = filter_input_data($this->input->post('title', TRUE));
                $comments = filter_input_data($this->input->post('comments', TRUE));
                $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
                //varify customer
                $this->db->select('*');
                $this->db->from('customer_information');
                $this->db->where('customer_id', $customer_id);
                $query = $this->db->get();
                //echo $query->num_rows();exit;
                if ($query->num_rows() == 0) {
                    JSONErrorOutput('Customer ID Not Found!');
                } else {
                    // 20-05-2021 harun check purchases start
                    $result_purchased = $this->db->select('*')
                    ->from('seller_order')
                    ->where('customer_id', $customer_id)
                    ->where('product_id', $product_id)
                    ->get()
                    ->num_rows();

                    if ($result_purchased == 0) {
                        JSONErrorOutput(display('not_purchased'));
                    }
                    // 20-05-2021 harun check purchases end
                    //varify review
                    $this->db->select('*');
                    $this->db->from('product_review');
                    $this->db->where('product_id', $product_id);
                    $this->db->where('reviewer_id', $customer_id);
                    $review_query = $this->db->get();
                    //echo $review_query->num_rows();exit;
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
                            JSONSuccessOutput($response, check_api_token());
                        } else {
                            JSONErrorOutput(display('ooops_something_went_wrong'));
                        }
                    } else {
                        JSONErrorOutput('This Customer has previous review of this product');
                    }
                }
                //response data list
                // $data = array(
                //     'base_url' => base_url(),
                //     'product_review_submit' => $response,
                // );
                // echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                // exit;

            }
        }
    }
    /*
	|-------------------------------
	|	product_description Load Here
	|	route = api/limarket_api/product_description
	|-------------------------------
	*/
    public function product_description()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('description_type', 'Product Description Type', 'trim|is_natural_no_zero|numeric|xss_clean');
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|is_natural_no_zero|numeric|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['product_id'])) {
                    $errors_data = $errors['product_id'];
                }
                if (!empty($errors['description_type'])) {
                    $errors_data = $errors['description_type'];
                }
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                $status = filter_input_data($this->input->post('status', TRUE));
                $product_id = filter_input_data($this->input->post('product_id', TRUE));
                $language = filter_input_data($this->input->post('language', TRUE));
                $description_type = filter_input_data($this->input->post('description_type', TRUE));
                //load from model file
                $this->db->select('*');
                $this->db->from('product_description');
                $this->db->where('product_id', $product_id);
                if (!empty($language)) {
                    $this->db->where('lang_id', $language);
                }
                if (!empty($description_type)) {
                    $this->db->where('description_type', $description_type);
                }
                $query = $this->db->get();
                $product_img_list   =  $query->result();
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'product_description' => $product_img_list,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	best_merchant_product_list Load Here
	|	route = api/limarket_api/best_merchant_product_list
	|-------------------------------
	*/
    public function best_merchant_product_list()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'required|trim|numeric|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                //load from model file
                $best_merchant_product = $this->limarket_api_model->best_merchant_product($per_page, $page);
                $best_merchant_product_list =  $this->limarket_api_model->get_seller_product_list($per_page, $page, array_column($best_merchant_product, 'seller_id'));
                //$best_merchant_product_all_list =  $this->limarket_api_model->get_seller_product_list($per_page, $page);

                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'best_merchant_product_list' => $best_merchant_product_list,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	best_merchant_info Load Here
	|	route = api/limarket_api/best_merchant_info
	|-------------------------------
	*/
    public function best_merchant_info()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'required|trim|numeric|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                //load from model file
                $best_merchant_product = $this->limarket_api_model->best_merchant_product($per_page, $page);
                $get_seller_info = $this->limarket_api_model->get_seller_info($per_page, $page, array_column($best_merchant_product, 'seller_id'));
                //$best_merchant_product_list =  $this->limarket_api_model->get_seller_product_list($per_page, $page, array_column($best_merchant_product, 'seller_id'));
                //$best_merchant_product_all_list =  $this->limarket_api_model->get_seller_product_list($per_page, $page);

                //response data list
                $data = array(
                    'base_url' => base_url(),
                    //'best_merchant_product' => $best_merchant_product,
                    'best_merchant_info' => $get_seller_info,
                    //'best_merchant_product_list' => $best_merchant_product_list,
                    //'best_merchant_product_all_list' => $best_merchant_product_all_list,
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	active_block_list Load Here
	|	route = api/limarket_api/active_block_list
	|-------------------------------
	*/
    public function active_block_list()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                //load from model file

                $block_list = $this->limarket_api_model->active_block_list();
                $block_ids = array_column($block_list, 'block_id');
                $category_ids = array_column($block_list, 'block_cat_id');
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    //
                    'block_ids' => $block_ids,
                    'category_ids' => $category_ids,
                    'total_block' => count($block_ids)
                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	brand_list Load Here
	|	route = api/limarket_api/brand_list
	|-------------------------------
	*/
    public function brand_list()
    {
        //api key checking
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('language', 'System Language', 'trim|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|numeric|is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                //$errors = validation_errors();
                $errors = $this->form_validation->error_array();
                if (!empty($errors['language'])) {
                    $errors_data = $errors['language'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            } else {
                $per_page = filter_input_data($this->input->post('per_page', TRUE));
                $page = filter_input_data($this->input->post('page', TRUE));
                //load from model file
                $brand_list = $this->limarket_api_model->brand_list($per_page, $page);
                //response data list
                $data = array(
                    'base_url' => base_url(),
                    'brand_list' => $brand_list,

                );
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
    }
    /*
	|-------------------------------
	|	category_product Load Here
	|	route = api/limarket_api/category_product
	|-------------------------------
	*/
    //Category product no problem
    public function category_product()
    {
        $this->form_validation->set_rules('cat_id', 'Category ID', 'trim|required|xss_clean');
        //
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['cat_id'])) {
                $errors_data = $errors['cat_id'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $cat_id = filter_input_data($this->input->post('cat_id', TRUE));
            $price_range = filter_input_data($this->input->post('price_range', TRUE));
            $size = filter_input_data($this->input->post('size', TRUE));
            $brand = filter_input_data($this->input->post('brand', TRUE));
            $sort = filter_input_data($this->input->post('sort', TRUE));
            $rate = filter_input_data($this->input->post('rate', TRUE));
            $seller_score = filter_input_data($this->input->post('seller_score', TRUE));

            $max_value = 0;
            $min_value = 0;
            //
            $per_page = filter_input_data($this->input->post('per_page', TRUE));
            $page = filter_input_data($this->input->post('page', TRUE));
            //
            $category_product = $this->limarket_api_model->category_product($cat_id, $price_range, $size, $brand, $sort, $rate, $seller_score);
            $top_category_list = $this->limarket_api_model->top_category_list($per_page, $page);
            $total_cat_pro = $this->limarket_api_model->select_total_sub_cat_pro($cat_id);
            $category = $this->limarket_api_model->select_single_category($cat_id);
            $categoryList = $this->limarket_api_model->parent_category_list($per_page, $page);
            $pro_category_list = $this->limarket_api_model->category_list($per_page, $page);
            $best_sales = $this->limarket_api_model->best_sales($per_page, $page);
            $footer_block = $this->limarket_api_model->footer_block($per_page, $page);
            $block_list = $this->limarket_api_model->block_list();
            $currency_details = $this->limarket_api_model->retrieve_currency_info();
            $soft_settings = $this->limarket_api_model->retrieve_soft_setting_editdata();
            $web_settings = $this->limarket_api_model->retrieve_setting_editdata();
            $languages = $this->limarket_api_model->languages();
            $currency_info = $this->limarket_api_model->currency_info();
            $selected_currency_info = $this->limarket_api_model->selected_currency_info();
            $selected_default_currency_info = $this->limarket_api_model->selected_default_currency_info();
            $company_info = $this->limarket_api_model->company_list();
            $variant_list = $this->limarket_api_model->variant_list();
            $all_ads = $this->limarket_api_model->select_page_ads('category');
            $brand_list = $this->limarket_api_model->brand_list($per_page, $page);
            $max_value = $this->limarket_api_model->select_max_value_of_cat_pro($cat_id, 1);
            $min_value = $this->limarket_api_model->select_max_value_of_cat_pro($cat_id, 0);
            //Max value and min value
            if ($max_value == $min_value) {
                $min_value = 0;
            }
            //Price range
            $from_price = 0;
            $to_price     = 0;
            if (!(empty($price_range))) {
                $ex = explode("-", $price_range);
                $from_price = $ex[0];
                $to_price   = $ex[1];
            }

            $data = array(
                'title' => display('category_wise_product'),
                'category_product' => $category_product,
                'top_category_list' => $top_category_list,
                'category_id' => $cat_id,
                'category_name' => $category[0]['category_name'],
                'trans_category_name' => $category[0]['trans_category_name'],
                'on_promotion' => $category[0]['on_promotion'],
                'promo_date' => $category[0]['promo_date'],
                'promo_color' => $category[0]['promo_color'],
                'promo_text_color' => $category[0]['promo_text_color'],
                'pro_category_list' => $pro_category_list,
                'category_list' => $categoryList,
                'block_list' => $block_list,
                'best_sales' => $best_sales,
                'footer_block' => $footer_block,
                'languages' => $languages,
                'currency_info' => $currency_info,
                'selected_cur_id' => (($selected_currency_info->currency_id) ? $selected_currency_info->currency_id : ""),
                'selected_currency_icon' => $selected_currency_info->currency_icon,
                'selected_currency_name' => $selected_currency_info->currency_name,
                'default_currency_icon'  => $selected_default_currency_info->currency_icon,
                'web_settings'  => $web_settings,
                'soft_settings' => $soft_settings,
                'logo' => $web_settings[0]['logo'],
                'favicon' => $web_settings[0]['favicon'],
                'footer_text' => $web_settings[0]['footer_text'],
                'company_name' => $company_info[0]['company_name'],
                'email' => $company_info[0]['email'],
                'address' => $company_info[0]['address'],
                'mobile' => $company_info[0]['mobile'],
                'website' => $company_info[0]['website'],
                'currency' => $currency_details[0]['currency_icon'],
                'position' => $currency_details[0]['currency_position'],
                'max_value' => (!empty($max_value) ? $max_value : 0),
                'min_value' => (!empty($min_value) ? $min_value : 0),
                'from_price' => $from_price,
                'to_price' => $to_price,
                'variant_list' => $variant_list,
                'all_ads' => $all_ads,
                'brand_list' => $brand_list,
                //'meta_title'     => ($lang_id == 'english') ? ((empty($category[0]['meta_title'])) ? $category[0]['category_name'] : $category[0]['meta_title']) : ((empty($category[0]['meta_title_trans'])) ? $category[0]['category_name'] : $category[0]['meta_title_trans']),
                //'meta_keyword'     => ($lang_id == 'english') ? ((empty($category[0]['meta_keyword'])) ? $category[0]['category_name'] : $category[0]['meta_keyword']) : ((empty($category[0]['meta_keyword_trans'])) ? $category[0]['category_name'] : $category[0]['meta_keyword_trans']),
                //'meta_description'     => ($lang_id == 'english') ? ((empty($category[0]['meta_description'])) ? $category[0]['category_name'] : $category[0]['meta_description']) : ((empty($category[0]['meta_description_trans'])) ? $category[0]['category_name'] : $category[0]['meta_description_trans'])
            );
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
    /*
	|-------------------------------
	|	product_details Load Here
	|	route = api/limarket_api/product_details
	|-------------------------------
	*/
    //Product Details Page Load Here no problem
    public function product_details()
    {
        $this->form_validation->set_rules('p_id', 'Product ID', 'trim|required|xss_clean');
        //
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['p_id'])) {
                $errors_data = array(
                    'Product ID' => $errors['p_id'],
                );
            }
            JSONErrorOutput($errors_data);
        } else {
            $p_id = filter_input_data($this->input->post('p_id', TRUE));
            $per_page = filter_input_data($this->input->post('per_page', TRUE));
            $page = filter_input_data($this->input->post('page', TRUE));
            //
            $product_info = $this->limarket_api_model->product_info($per_page, $page, $p_id);
            //var_dump($product_info[0]);exit;
            //
            $data = array(
                'title' => display('product_details'),
                //'id' => $product_info[0]['product_id'],
                'product_link' => base_url('product_details/').$product_info[0]['product_id'],
                'product_info' => $product_info,
            );
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
    //Product Details Page Load Here no problem
    public function product_details_backup()
    {
        $this->form_validation->set_rules('p_id', 'Product ID', 'trim|required|xss_clean');
        //
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['p_id'])) {
                $errors_data = array(
                    'Product ID' => $errors['p_id'],
                );
            }
            JSONErrorOutput($errors_data);
        } else {
            $p_id = filter_input_data($this->input->post('p_id', TRUE));
            $per_page = filter_input_data($this->input->post('per_page', TRUE));
            $page = filter_input_data($this->input->post('page', TRUE));
            //
            $pro_category_list = $this->limarket_api_model->category_list($per_page, $page);
            $top_category_list = $this->limarket_api_model->top_category_list($per_page, $page);
            $parent_category_list = $this->limarket_api_model->parent_category_list($per_page, $page);
            $best_sales = $this->limarket_api_model->best_sales($per_page, $page);
            $footer_block = $this->limarket_api_model->footer_block($per_page, $page);
            $slider_list = $this->limarket_api_model->slider_list();
            $block_list = $this->limarket_api_model->block_list();
            $product_info = $this->limarket_api_model->product_info($p_id);
            //
            $category_id = $product_info->category_id;
            $product_id = $product_info->product_id;
            $best_sales_category = $this->limarket_api_model->best_sales_category($product_id);
            $get_thumb_image = $this->limarket_api_model->get_thumb_image($product_id);
            $related_product = $this->limarket_api_model->related_product($category_id, $product_id);
            $review_list = $this->limarket_api_model->review_list($product_id);
            $refund_policy = $this->limarket_api_model->retrieve_refund_policy();
            $product_stock = $this->limarket_api_model->stock_report_single_item($product_id);
            $select_single_category = $this->limarket_api_model->select_single_category($category_id);
            $currency_details = $this->limarket_api_model->retrieve_currency_info();
            $soft_settings = $this->limarket_api_model->retrieve_soft_setting_editdata();
            $web_settings = $this->limarket_api_model->retrieve_setting_editdata();
            $languages = $this->limarket_api_model->languages();
            $currency_info = $this->limarket_api_model->currency_info();
            $company_info = $this->limarket_api_model->company_list();
            $selected_currency_info = $this->limarket_api_model->selected_currency_info();
            $all_ads = $this->limarket_api_model->select_page_ads('details');
            $brand_list = $this->limarket_api_model->brand_list($per_page, $page);
            //
            $data = array(
                'title' => display('product_details'),
                'category_list' => $parent_category_list,
                'top_category_list' => $top_category_list,
                'slider_list' => $slider_list,
                'block_list' => $block_list,
                'best_sales' => $best_sales,
                'footer_block' => $footer_block,
                'product_name' => $product_info->title,
                'product_meta_keyword' => $product_info->product_meta_keyword,
                'product_meta_description' => $product_info->product_meta_description,
                'product_image' => $product_info->thumb_image_url,
                'product_description' => $product_info->description,
                'product_id' => $product_info->product_id,
                'seller_id' => $product_info->seller_id,
                'brand_id' => $product_info->brand_id,
                'brand_name' => $product_info->brand_name,
                'product_model' => $product_info->product_model,
                'business_name' => $product_info->business_name,
                'price' => $product_info->price,
                'on_sale' => $product_info->on_sale,
                'on_promotion' => $product_info->on_promotion,
                'promo_date' => $product_info->promo_date,
                'promo_color' => $product_info->promo_color,
                'promo_text_color' => $product_info->promo_text_color,
                'promo_details' => $product_info->details,
                'offer_price' => $product_info->offer_price,
                'first_name' => $product_info->first_name,
                'last_name' => $product_info->last_name,
                'seller_store_name' => $product_info->seller_store_name,
                'get_thumb_image' => $get_thumb_image,
                'variant' => $product_info->variant_id,
                'category_name' => $product_info->category_name,
                'trans_category_name' => $product_info->trans_category_name,
                'category_id' => $category_id,
                'best_sales_category' => $best_sales_category,
                'related_product' => $related_product,
                'tag' => $product_info->tag,
                'seller_guarantee' => $product_info->seller_guarantee,
                'quantity' => $product_info->quantity,
                'pre_order' => $product_info->pre_order,
                'pre_order_quantity' => (!empty($product_info->pre_order_quantity) ? $product_info->pre_order_quantity : 0),
                //'meta_title'=> ($lang_id=='english')?((empty($product_info->meta_title))?$product_info->title:$product_info->meta_title):((empty($product_info->meta_title_trans))?$product_info->title:$product_info->meta_title_trans),
                //'meta_keyword'=> ($lang_id=='english')?((empty($product_info->product_meta_keyword))?$product_info->title:$product_info->product_meta_keyword):((empty($product_info->meta_keyword_trans))?$product_info->title:$product_info->meta_keyword_trans),
                //'meta_description'=> ($lang_id=='english')?((empty($product_info->product_meta_description))?$product_info->title:$product_info->product_meta_description):((empty($product_info->meta_description_trans))?$product_info->title:$product_info->meta_description_trans),
                'video' => $product_info->video,
                'warranty' => $product_info->warranty,
                'refund_policy' => $refund_policy->details,
                'pro_category_list' => $pro_category_list,
                'soft_settings' => $soft_settings,
                'languages' => $languages,
                'currency_info' => $currency_info,
                'review_list' => $review_list,
                'product_stock' => $product_stock,
                'select_single_category' => $select_single_category,
                //'selected_cur_id'=> (($selected_currency_info->currency_id)?$selected_currency_info->currency_id:""),
                'selected_currency_icon' => $selected_currency_info->currency_icon,
                'selected_currency_name' => $selected_currency_info->currency_name,
                'web_settings' => $web_settings,
                'logo' => $web_settings[0]['logo'],
                'favicon' => $web_settings[0]['favicon'],
                'footer_text' => $web_settings[0]['footer_text'],
                'company_name' => $company_info[0]['company_name'],
                'email' => $company_info[0]['email'],
                'address' => $company_info[0]['address'],
                'mobile' => $company_info[0]['mobile'],
                'website' => $company_info[0]['website'],
                'currency' => $currency_details[0]['currency_icon'],
                'position' => $currency_details[0]['currency_position'],
                'all_ads' => $all_ads,
                'brand_list' => $brand_list
            );
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
    //testing complete no problem
    public function testing_category_product()
    {
        $this->form_validation->set_rules('cat_id', 'Category ID', 'trim|required|xss_clean');
        //
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            if (!empty($errors['cat_id'])) {
                $errors_data = $errors['cat_id'];
            }
            JSONErrorOutput($errors_data);
        } else {
            $cat_id = filter_input_data($this->input->post('cat_id', TRUE));
            $all_cat_id = (explode("--",$cat_id));
            $per_page = filter_input_data($this->input->post('per_page', TRUE));
            $page = filter_input_data($this->input->post('page', TRUE));
            $user_lang = filter_input_data($this->input->post('language', TRUE));
            $lang_id = 0;
            //$user_lang = $this->session->userdata('language');
            if (empty($user_lang)) {
                $lang_id = 'english';
            }else{
                $lang_id = $user_lang;
            }
            //
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            
            $this->db->select('a.*,b.*,c.*,d.first_name,d.last_name,e.brand_name,pi.image_name');
            $this->db->from('product_information a');
            $this->db->join('product_category b','a.category_id = b.category_id','left');
            $this->db->join('product_title c','a.product_id = c.product_id','left');
            $this->db->join('seller_information d','a.seller_id = d.seller_id','left');
            $this->db->join('brand e','a.brand_id = e.brand_id','left');
            $this->db->join('product_image pi','pi.product_id = a.product_id','left');

            $this->db->where('a.status',2);
            $this->db->where($where);

            if ($cat_id) {
            	$this->db->where_in('a.category_id', $all_cat_id);
            }

            $this->db->where('c.lang_id',$lang_id);
            $this->db->group_by('a.product_id');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
            $category_product = $query->result_array();
            
            $data = array(
                'category_product' => $category_product,
            );
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function submit_checkout_validation()
    {
        $this->form_validation->set_rules('first_name', display('first_name'), 'trim|required|max_length[50]|regex_match[/^([0-9\p{L}\.\-]|\s)+$/u]|xss_clean', array('required' => display('first_name_is_required')));
        $this->form_validation->set_rules('last_name', display('last_name'), 'trim|required|max_length[50]|regex_match[/^([0-9\p{L}\.\-]|\s)+$/u]|xss_clean', array('required' => display('last_name_is_required')));
        $this->form_validation->set_rules('customer_email', display('email'), 'trim|max_length[100]|valid_email|xss_clean');
        $this->form_validation->set_rules('customer_mobile', display('mobile'), 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean', array('required' => display('mobile_is_required')));
        $this->form_validation->set_rules('customer_address_1', display('address'), 'trim|required|max_length[250]|regex_match[/^([0-9\p{L}\:\.\+\-\,\#]|\s)+$/u]|xss_clean', array('required' => display('address_is_required')));
        $this->form_validation->set_rules('customer_address_2', display('address'), 'trim|max_length[250]|regex_match[/^([0-9\p{L}\:\.\+\-\,\#]|\s)*$/u]|xss_clean');
        $this->form_validation->set_rules('zip', display('zip'), 'trim|max_length[20]|regex_match[/^([0-9\p{L}\-]|\s)*$/u]|xss_clean');
        $this->form_validation->set_rules('company', display('company'), 'trim|max_length[100]|regex_match[/^([0-9\p{L}\.\-]|\s)*$/u]|xss_clean');

        if ($this->form_validation->run() == FALSE) {
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

    //Submit checkout
    public function submit_checkout()
    {
        $get_order_data = file_get_contents('php://input');
        $order_info = json_decode($get_order_data);
        //var_dump($order_info);exit;
        //print_r($order_info);exit;
        //JSONErrorOutput($order_info);

        //$order_info->data validation
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
        // if (empty($order_info->customer_mobile)) {
        //     JSONErrorOutput('Customer mobile is required!');
        // }
        if (empty($order_info->country_id)) {
            JSONErrorOutput('Country ID is required!');
        }
        if (empty($order_info->state_id)) {
            JSONErrorOutput('State ID is required!');
        }
        if (empty($order_info->city_id)) {
            JSONErrorOutput('City ID is required!');
        }
        // if (!is_numeric($order_info->ship_cost)) {
        //     JSONErrorOutput('Invalid shipping cost');
        // }
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
            //var_dump($data);exit;
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
        //$_POST['customer_address_1'] ='gyg';
        //$customer_address_1 = filter_input_data($this->input->post('customer_address_1', TRUE));
        //$this->submit_checkout_validation();
        //exit;
        //check product status
        $is_exist = 'yes';
        if (!empty($order_info->cart_details)) {
            foreach ($order_info->cart_details as $items) {
                if (!empty($items->product_id)) {
                    $this->db->where('product_id', $items->product_id);
                    $query  = $this->db->get('product_information');
                    $result = $query->result_array();
                    //var_dump($result);exit;
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
        //$this->customer_shipping_order_entry($order_id, $customer_id, $diff_ship_adrs);
        //here load customer_shipping_order_entry()
        if ($diff_ship_adrs == 1) {

            //$customer_code = $this->customer_number_generator();
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
            //if (!$this->user_auth->is_logged()) {
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
            //echo $return_order_id; exit;
            //gererating order pdf
            $this->order_html_data($return_order_id);
            // $this->cart->destroy();
            // //$array_items = array('customer_name', 'first_name', 'last_name', 'customer_short_address', 'customer_address_1', 'customer_address_2', 'city', 'state', 'country', 'zip', 'company', 'customer_mobile', 'customer_email','vat_amount','ship_cost','cart_total_amount');
            // //
            // $array_items = array('city', 'state', 'country', 'zip', 'company', 'vat_amount', 'ship_cost', 'cart_total_amount');
            // $this->session->unset_userdata($array_items);
            // redirect(base_url());
        }
        //liyeplimal payment
        $confirm = 'yes';
        if ($payment_method == 'limo') {
            //echo 'limo1';
            //$email               = filter_input_post('customer_email');
            //$customer_mobile     = filter_input_post('customer_mobile');
            //$cart_total_amount   = filter_input_post('cart_total_amount');
            //$product_name        = filter_input_post('product_name');
            //$product_quantity    = filter_input_post('product_quantity');
            //$product_model       = filter_input_post('product_model');
            //$ship_cost           = filter_input_post('ship_cost');
            $coupon_amnt           = 0;
            //$user_id            = $this->session->userdata('user_id');
            //
            $cart_details_array = (array)$order_info->cart_details;
            //
            $payment_history = array(
                'payment_method' => $payment_method,
                'request' => 'send',
                'token' => '',
                'total_price' => $order_info->totalAmount,
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'request_time' => date('Y-m-d H:i:s'),
                //'user_id' => $user_id,
                'customer_email' => $order_info->customer_email,
                'customer_mobile' => $order_info->customer_mobile,
                'vat_amount' => 0,
                //'vat_amount' => (is_array($vat_amount)) ? implode(",", $vat_amount) : $vat_amount,
                'coupon_amnt' => (is_array($coupon_amnt)) ? implode(",", $coupon_amnt) : $coupon_amnt,
                'ship_cost' => $ship_cost,
                'product_name' => 'api_test_product',
                'product_quantity' => 1,
                'product_model' => 'M01',
                //'product_name' => (is_array($product_name)) ? implode(",", $product_name) : $product_name,
                //'product_quantity' => (is_array($product_quantity)) ? implode(",", $product_quantity) : $product_quantity,
                //'product_model' => (is_array($product_model)) ? implode(",", $product_model) : $product_model
            );
            //print_r($payment_history);exit;
            $this->db->insert('payment_history', $payment_history);
            $payment_history_id = $this->db->insert_id();

            $this->limarket_api_model->payment_by_liplimal($confirm, $payment_history['customer_email'], $order_info->cart_details, $ship_cost, $payment_history['total_price'], $coupon_amnt, $payment_history_id);
        }
    }
    //end submit_checkout api
    public function limoney_confirm()
    {
        //05-04-2021 Harun
        echo '<pre>';print_r($_GET);exit;
        //echo '<pre>';print_r($_SESSION);exit;
        $success_return = filter_input_get('vhut');
        $amount = filter_input_get('amount');
        $success = filter_input_get('success');
        $status = filter_input_get('status');
        //var_dump($success);exit();
        $payment_history = array(
            'payment_method' => 'limo',
            'request' => 'recieve',
            'token' => $success_return,
            'amount' => $amount,
            'order_id' => $this->session->userdata('order_id'),
            'customer_id' => $this->session->userdata('customer_id'),
            'request_time' => date('Y-m-d H:i:s')
        );
        $this->db->insert('payment_history', $payment_history);
        $payment_history_id = $this->db->insert_id();

        // if ($status == '200' && $success == 'true' && $success_return == $this->session->userdata('token') && !is_null($amount) && $this->session->userdata('order_id') != '' && $this->session->userdata('customer_id') != '') {
        //condition change for apk response
        if ($status == '200' && $success == 'true' && !is_null($amount)) {
            echo "true";exit();
        } else {
            //echo "false";exit();
            $this->session->unset_userdata('token');
            if($status != '200'){
                $message = "API Error: " . $status . " Service Not Available";
                $this->session->set_userdata('error_message', $message);
            }else{
                $this->session->set_userdata('error_message', 'Invalid token or session timout!');
            }
            // $this->cart->destroy();
            redirect(base_url('checkout'));
        }
    }
    //Order html Data
    public function order_html_data($order_id)
    {
        // $return_data = $this->limarket_api_model->retrieve_order_html_data($order_id);
        // var_dump($return_data);exit;
        $CI = &get_instance();

        $CI->load->model('website/Homes');
        $CI->load->model('Soft_settings');
        $CI->load->model('Orders');
        $CI->load->library('occational');
        $CI->load->library('Pdfgenerator');

        $order_detail         = $CI->Homes->retrieve_order_html_data($order_id);
        if (!$order_detail) {
            $order_detail     = $CI->Homes->retrieve_pre_order_html_data($order_id);
        }

        //Payment Method
        $paymethod = $CI->Homes->get_payment_method($order_id);

        $subTotal_quantity     = 0;
        $subTotal_cartoon     = 0;
        $subTotal_discount     = 0;

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
        $order_email = $CI->parser->parse('order/order_email', $data, true);

        require_once("./vendor/dompdf/dompdf/dompdf_config.inc.php");
        //PDF Generator
        $dompdf = new DOMPDF();
        $dompdf->load_html($chapterList);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents('my-assets/pdf/' . $order_id . '.pdf', $output);
        $file_path = 'my-assets/pdf/' . $order_id . '.pdf';

        //File path save to database
        $CI->db->set('file_path', base_url($file_path));
        $CI->db->where('order_id', $order_id);
        $CI->db->update('order');

        $send_email = '';
        if (!empty($data['customer_email'])) {
            $send_email = $this->setmail($data['customer_email'], $file_path, $order_email);
        }

        if ($send_email != null) {
            return true;
        } else {
            JSONSuccessOutput(display('product_successfully_order'), check_api_token());
            // $CI->session->set_userdata(array('message' => display('product_successfully_order')));
            // return true;
        }
    }

    //Send Customer Email with invoice
    public function setmail($email, $file_path, $order_email)
    {
        $CI = &get_instance();
        $CI->load->model('Soft_settings');
        $CI->load->model('Companies');
        $CI->load->model('Email_templates');

        if ($email) {

            //send email with as a link
            $setting_detail = $CI->Soft_settings->retrieve_email_editdata();
            $company_info   = $CI->Companies->company_list();
            $template         = $CI->Email_templates->retrieve_template('8');

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
            //if($email == 'raihan.exe@gmail.com'){
            $CI->email->message($order_email);
            //} else {
            //    $CI->email->message($template->message);
            //}
            $CI->email->attach($file_path);

            $email = $this->test_input($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($CI->email->send()) {
                    JSONSuccessOutput(display('product_successfully_order') . '(' . $email . ')', check_api_token());
                    // $CI->session->set_userdata(array('message' => display('product_successfully_order') . '(' . $email . ')'));
                    // return true;
                } else {
                    JSONErrorOutput(display('email_not_send'));
                    // $CI->session->set_userdata(array('error_message' => display('email_not_send')));
                    // return false;
                }
            } else {
                JSONSuccessOutput(display('please_enter_valid_email'), check_api_token());
                // $CI->session->set_userdata(array('message' => display('please_enter_valid_email')));
                // return true;
            }
        } else {
            JSONErrorOutput(display('your_email_was_not_found'));
            // $CI->session->set_userdata(array('error_message' => display('your_email_was_not_found')));
            // return false;
        }
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
	|	Retrieve State List
	|	post method 
	|	route = api/limarket_api/state_list
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
                $country_id = filter_input_data($this->input->post('country_id', TRUE));
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
                    //api_response_history();
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
	|	Retrieve Cities List
	|	post method 
	|	route = api/limarket_api/cities_list
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
                $state_id = filter_input_data($this->input->post('state_id', TRUE));
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
                    //api_response_history();
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
	|	Retrieve Cities List
	|	post method 
	|	route = api/limarket_api/shipping_charge
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
                $city_id = filter_input_data($this->input->post('city_id', TRUE));
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
                    //api_response_history();
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
	|	Searching product
	|	post method 
	|	route = api/limarket_api/retrieve_category_product
	|---------------------------------------------------
	*/
	public function retrieve_category_product()
	{
        if (checkAuth(check_api_key())) {
            $this->form_validation->set_rules('product_name', 'Product Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('per_page', 'Data Limit Per Page', 'trim|numeric|is_natural_no_zero|xss_clean');
            $this->form_validation->set_rules('page', 'Page Number', 'trim|is_natural_no_zero|numeric|xss_clean');
            if ($this->form_validation->run() == FALSE) {
                $errors = $this->form_validation->error_array();
                if (!empty($errors['product_name'])) {
                    $errors_data = $errors['product_name'];
                }
                if (!empty($errors['per_page'])) {
                    $errors_data = $errors['per_page'];
                }
                if (!empty($errors['page'])) {
                    $errors_data = $errors['page'];
                }
                JSONErrorOutput($errors_data);
            }
            
            $per_page = filter_input_data($this->input->post('per_page', TRUE));
            $page = filter_input_data($this->input->post('page', TRUE));
            $user_lang = filter_input_data($this->input->post('language', TRUE));
            $product_name = filter_input_data($this->input->post('product_name', TRUE));
            $price_sorting = filter_input_data($this->input->post('price_sorting', TRUE));
            $price_range = filter_input_data($this->input->post('price_range', TRUE));
            $brand = filter_input_data($this->input->post('brand', TRUE));
            $all_brand = (explode("--",$brand));
            $cat_id = filter_input_data($this->input->post('cat_id', TRUE));
            $all_cat_id = (explode("--",$cat_id));
            $lang_id = 0;
            //$user_lang = $this->session->userdata('language');
            if (empty($user_lang)) {
                $lang_id = 'english';
            }else{
                $lang_id = $user_lang;
            }
            
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            
            $this->db->select('a.*,b.*,c.*,d.first_name,d.last_name,e.brand_name,pi.image_name');
            $this->db->from('product_information a');
            $this->db->join('product_category b','a.category_id = b.category_id','left');
            $this->db->join('product_title c','a.product_id = c.product_id','left');
            $this->db->join('seller_information d','a.seller_id = d.seller_id','left');
            $this->db->join('brand e','a.brand_id = e.brand_id','left');
            $this->db->join('product_image pi','pi.product_id = a.product_id','left');

            $this->db->where('a.status',2);
            $this->db->where($where);

            if ($price_range) {
            	$ex = explode("-", $price_range);
                $from = $ex[0];
                $to = $ex[1];
                $this->db->where('price >=', $from);
            	$this->db->where('price <=', $to);
            }
        
            // if ($size) {
            // 	$this->db->where('a.variant_id', $size);
            // }

            if ($product_name) {
                $like_where = "(`c`.`title` LIKE '%".$product_name."%' ESCAPE '!' OR  `a`.`product_model` LIKE '%".$product_name."%' ESCAPE '!' OR  `b`.`category_name` LIKE '%".$product_name."%' ESCAPE '!')";
                $this->db->where($like_where);
            }

            if ($brand) {
            	$this->db->where_in('a.brand_id', $all_brand);
            }

            if ($cat_id) {
            	$this->db->where_in('a.category_id', $all_cat_id);
            }

            $this->db->where('c.lang_id',$lang_id);
            $this->db->group_by('a.product_id');
            if ($price_sorting) {
                $this->db->order_by('a.price', $price_sorting);
            }else{
                $this->db->order_by('a.product_info_id','desc');
            }
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
            $w_cat_pro = $query->result_array();
            // category_list
            $this->db->select('a.category_id, b.category_name');
            $this->db->from('product_information a');
            $this->db->join('product_category b','a.category_id = b.category_id','left');
            $this->db->join('product_title c','a.product_id = c.product_id','left');
            $this->db->join('seller_information d','a.seller_id = d.seller_id','left');
            $this->db->join('brand e','a.brand_id = e.brand_id','left');
            $this->db->join('product_image pi','pi.product_id = a.product_id','left');

            $this->db->where('a.status',2);
            $this->db->where($where);

            if ($price_range) {
            	$ex = explode("-", $price_range);
                $from = $ex[0];
                $to = $ex[1];
                $this->db->where('price >=', $from);
            	$this->db->where('price <=', $to);
            }
    
            if ($product_name) {
                $like_where = "(`c`.`title` LIKE '%".$product_name."%' ESCAPE '!' OR  `a`.`product_model` LIKE '%".$product_name."%' ESCAPE '!' OR  `b`.`category_name` LIKE '%".$product_name."%' ESCAPE '!')";
                $this->db->where($like_where);
            }

            if ($brand) {
            	$this->db->where_in('a.brand_id', $all_brand);
            }

            if ($cat_id) {
            	$this->db->where_in('a.category_id', $all_cat_id);
            }

            $this->db->where('c.lang_id',$lang_id);
            $this->db->group_by('a.category_id');
            if ($price_sorting) {
                $this->db->order_by('a.price', $price_sorting);
            }else{
                $this->db->order_by('a.product_info_id','desc');
            }
            //$this->db->limit($per_page, $page);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
            $w_category_list = $query->result_array();

            // $product_count = count($w_cat_pro);
            // for ($i=0; $i < $product_count; $i++) { 
            //     $category_list = $w_cat_pro[$i]['category_id'];
            // }

            // if ($rate) {
            // 	$w_cat_pro = $this->get_rating_product($w_cat_pro,$rate);
            // }

            // if ($seller_score) {
            // 	$w_cat_pro = $this->get_product_by_seller_rate($w_cat_pro,$seller_score);
            // }

            $data = array(
                'base_url' => base_url(),
                'product_list' => $w_cat_pro,
                'category_list' => $w_category_list,
            );
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
}
