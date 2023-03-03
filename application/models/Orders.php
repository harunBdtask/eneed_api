<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Orders extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Customers');
	}

	//Count order
	public function count_order()
	{
		return $this->db->count_all("order");
	}
	//Order List
	public function order_list($per_page=null,$page=null,$order_no=null,$category_id=null,$pre_order_no=null,$customer=null,$date=null,$order_status=null,$shipping=null,$product_title=null)
	{
		$this->db->select('
			a.order_id,a.order_no,a.shipping_id,a.customer_id,a.date,a.total_amount,a.total_discount,a.paid_amount,a.payment_date,a.service_charge,a.order_status,a.status,
			b.customer_name,
			b.customer_email,
			b.customer_mobile,
			b.customer_short_address,
			d.invoice,d.invoice_id,
			g.agent AS payment_method,
			si.customer_address_1 AS ship_address, 
			si.customer_name AS ship_customer_name, 
			SUM(so.quantity) AS sell_quantity,
			GROUP_CONCAT(DISTINCT pt.title) AS product_title, 
			GROUP_CONCAT(DISTINCT pc.category_name) AS product_category
			');
		$this->db->from('order a');
		$this->db->join('seller_order so','so.order_id = a.order_id','left');
		$this->db->join('product_title pt','pt.product_id = so.product_id AND pt.lang_id = \'english\' ','left');
		$this->db->join('product_category pc','pc.category_id = so.category_id','left');
		$this->db->join('shipping_info si','si.order_id = a.order_id','left');
		$this->db->join('customer_information b','b.customer_id = a.customer_id','left');
		$this->db->join('invoice d','a.order_no = d.order_no','left');
		$this->db->join('order_payment f', 'f.order_id = a.order_id', 'left');
        $this->db->join('payment_gateway g', 'g.code = f.payment_id', 'left');
		if ($order_no) {
			$this->db->where('a.order_no',$order_no);
		}

		if ($category_id) {
			$this->db->where('so.category_id',$category_id);
		}

		if ($pre_order_no) {
			$this->db->where('a.pre_order_id',$pre_order_no);
		}

		if ($customer) {
			$this->db->like('b.customer_name',$customer,'both');
			$this->db->or_where('b.customer_mobile',$customer);
			$this->db->or_where('b.customer_code',$customer);
		}

		if ($order_status) {
			$this->db->where('a.order_status',$order_status);
		}

		if ($shipping) {
			$this->db->where('a.shipping_id',$shipping);
		}

		if ($product_title) {
            $this->db->like('pt.title', $product_title, 'both');
        }

		if ($date) {
			$a = explode("---", $date);
			if (count($a) == 1) {
				$from_date =  $a[0];
				$this->db->where('a.date', $from_date);
			}else{
				$from_date = $a[0];
				$this->db->where('a.date >=', $from_date);
				$to_date   = $a[1];
				$this->db->where('a.date <=', $to_date);
			}
			
		}
		$this->db->limit($per_page,$page);
		$this->db->group_by('a.order_id');
		$this->db->order_by('a.id','desc');
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Order List
	public function order_list_all($order_no=null,$category_id=null,$pre_order_no=null,$customer=null,$date=null,$order_status=null,$shipping=null,$product_title=null)
	{
		$this->db->select('
			a.order_id,a.order_no,a.shipping_id,a.customer_id,a.date,a.total_amount,a.total_discount,a.paid_amount,a.payment_date,a.service_charge,a.order_status,a.status,
			b.customer_name,
			b.customer_email,
			b.customer_mobile,
			b.customer_short_address,
			d.invoice,d.invoice_id,
			g.agent AS payment_method,
			si.customer_address_1 AS ship_address, 
			si.customer_name AS ship_customer_name, 
			SUM(so.quantity) AS sell_quantity,
			GROUP_CONCAT(DISTINCT pt.title) AS product_title, 
			GROUP_CONCAT(DISTINCT pc.category_name) AS product_category
			');
		$this->db->from('order a');
		$this->db->join('seller_order so','so.order_id = a.order_id','left');
		$this->db->join('product_title pt','pt.product_id = so.product_id AND pt.lang_id = \'english\' ','left');
		$this->db->join('product_category pc','pc.category_id = so.category_id','left');
		$this->db->join('shipping_info si','si.order_id = a.order_id','left');
		$this->db->join('customer_information b','b.customer_id = a.customer_id','left');
		$this->db->join('invoice d','a.order_no = d.order_no','left');
		$this->db->join('order_payment f', 'f.order_id = a.order_id', 'left');
        $this->db->join('payment_gateway g', 'g.code = f.payment_id', 'left');
		if ($order_no) {
			$this->db->where('a.order_no',$order_no);
		}

		if ($category_id) {
			$this->db->where('so.category_id',$category_id);
		}

		if ($pre_order_no) {
			$this->db->where('a.pre_order_id',$pre_order_no);
		}

		if ($customer) {
			$this->db->like('b.customer_name',$customer,'both');
			$this->db->or_where('b.customer_mobile',$customer);
			$this->db->or_where('b.customer_code',$customer);
		}

		if ($order_status) {
			$this->db->where('a.order_status',$order_status);
		}

		if ($shipping) {
			$this->db->where('a.shipping_id',$shipping);
		}

		if ($product_title) {
            $this->db->like('pt.title', $product_title, 'both');
        }

		if ($date) {
			$a = explode("---", $date);
			if (count($a) == 1) {
				$from_date =  $a[0];
				$this->db->where('a.date', $from_date);
			}else{
				$from_date = $a[0];
				$this->db->where('a.date >=', $from_date);
				$to_date   = $a[1];
				$this->db->where('a.date <=', $to_date);
			}
			
		}
		$this->db->group_by('a.order_id');
		$this->db->order_by('a.id','desc');
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Order List Count
	public function order_count($order_no=null,$category_id=null,$pre_order_no=null,$customer=null,$date=null,$order_status=null,$shipping=null,$product_title=null)
	{
		$this->db->select('a.*,b.customer_name,b.customer_email');
		$this->db->from('order a');
		$this->db->join('seller_order so','so.order_id = a.order_id','left');
		$this->db->join('customer_information b','b.customer_id = a.customer_id','left');
		$this->db->join('shipping_method c','c.city = a.shipping_id','left');
		$this->db->join('cities s','s.id = c.city','left');
		$this->db->join('invoice d','a.order_no = d.order_no','left');

		if ($order_no) {
			$this->db->where('a.order_no',$order_no);
		}

		if ($category_id) {
			$this->db->where('so.category_id',$category_id);
		}

		if ($pre_order_no) {
			$this->db->where('a.pre_order_id',$pre_order_no);
		}

		if ($customer) {
			$this->db->like('b.customer_name',$customer,'both');
			$this->db->or_where('b.customer_mobile',$customer);
			$this->db->or_where('b.customer_code',$customer);
		}

		if ($order_status) {
			$this->db->where('a.order_status',$order_status);
		}
		if ($shipping) {
			$this->db->where('a.shipping_id',$shipping);
		}

		if ($date) {
			$a = explode("---", $date);
			if (count($a) == 1) {
				$from_date =  $a[0];
				$this->db->where('a.date', $from_date);
			}else{
				$from_date = $a[0];
				$this->db->where('a.date >=', $from_date);
				$to_date   = $a[1];
				$this->db->where('a.date <=', $to_date);
			}
			
		}
		$this->db->group_by('a.id');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();	
		}
		return false;
	}	
	//Pre order list
	public function pre_order_list($per_page=null,$page=null,$pre_order_no=null,$customer=null,$date=null)
	{
		$this->db->select('a.*,b.customer_name');
		$this->db->from('pre_order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');

		if ($pre_order_no) {
			$this->db->where('a.id',$pre_order_no);
		}

		if ($customer) {
			$this->db->where('a.customer_id',$customer);
		}

		if ($date) {
			$a = explode("---", $date);
			if (count($a) == 1) {
				$from_date =  $a[0];
				$this->db->where('a.date', $from_date);
			}else{
				$from_date = $a[0];
				$this->db->where('a.date >=', $from_date);
				$to_date   = $a[1];
				$this->db->where('a.date <=', $to_date);
			}
			
		}
		$this->db->group_by('a.id');
		$this->db->limit($per_page,$page);
		$this->db->order_by('a.id','desc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Pre order count list
	public function pre_order_count($pre_order_no=null,$customer=null,$date=null)
	{
		$this->db->select('a.*,b.customer_name');
		$this->db->from('pre_order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');

		if ($pre_order_no) {
			$this->db->where('a.id',$pre_order_no);
		}

		if ($customer) {
			$this->db->where('a.customer_id',$customer);
		}

		if ($date) {
			$a = explode("---", $date);
			if (count($a) == 1) {
				$from_date =  $a[0];
				$this->db->where('a.date', $from_date);
			}else{
				$from_date = $a[0];
				$this->db->where('a.date >=', $from_date);
				$to_date   = $a[1];
				$this->db->where('a.date <=', $to_date);
			}
			
		}
		$this->db->group_by('a.id');
		$this->db->order_by('a.id','desc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();	
		}
		return false;
	}
	//New order count
	public function new_order_count()
	{
		$this->db->select('a.*,b.customer_name');
		$this->db->from('order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->order_by('a.id','desc');
		$this->db->where('a.order_status','1');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();	
		}
		return false;
	}

	public function payment_incomplete($per_page,$page,$email,$mobile)
	{
		$this->db->select('a.*,DATE_FORMAT(a.request_time, "%d-%b-%Y %r") as request_datetime,b.id as bid,b.token as btoken,c.token as ctoken');
		$this->db->from('payment_history a');
		$this->db->join('payment_history b','b.customer_id = a.customer_id AND b.order_id = a.order_id AND b.request = "recieve"','left');
		$this->db->join('payment_history c','c.token = a.token AND c.amount = a.amount AND c.request = "recieve"','left');
		$this->db->order_by('a.id','desc');
		$this->db->where('a.request','send');
		if($email!=''){
			$this->db->where('a.customer_email',$email);
		}
		if($mobile!=''){
			$this->db->where('a.customer_mobile',$mobile);
		}
		$this->db->limit($per_page,$page);
		$query = $this->db->get();
		
		return $query->result();
	}

	public function payment_count($email,$mobile)
	{
		$this->db->select('a.*,DATE_FORMAT(a.request_time, "%d-%b-%Y %r") as request_datetime,b.id as bid,b.token as btoken');
		$this->db->from('payment_history a');
		$this->db->join('payment_history b','b.customer_id = a.customer_id AND b.order_id = a.order_id AND b.request = "recieve"','left');
		$this->db->order_by('a.id','desc');
		$this->db->where('a.request','send');
		if($email!=''){
			$this->db->where('a.customer_email',$email);
		}
		if($mobile!=''){
			$this->db->where('a.customer_mobile',$mobile);
		}
		$query = $this->db->get();
		
		return $query->num_rows();
	}

	//Updated Invoice count
	public function updated_inv_count()
	{
		$this->db->from('invoice');
		$this->db->where('inv_update', '1');
		$result = $this->db->count_all_results();
		return $result;
	}

	//New order tracking count
	public function new_order_tracking_count()
	{
		$this->db->select('a.*');
		$this->db->from('order_tracking a');
		$this->db->where('a.customer_id !=',null);
		$this->db->where('a.status','0');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();	
		}
		return false;
	}
	//Pending order list
	public function pending_order_list()
	{
		$this->db->select('a.*,b.*');
		$this->db->from('order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->where('a.status','1');
		$this->db->order_by('a.id','desc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//New pre order count
	public function new_pre_order_count()
	{
		$this->db->select('a.*,b.customer_name');
		$this->db->from('pre_order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->order_by('a.id','desc');
		$this->db->where('a.status','1');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();	
		}
		return false;
	}
	//POS customer list
	public function customer_list(){
		$query= $this->db->select('*')
		->from('customer_information')
		->where('customer_name !=','Walking Customer')
		->order_by('customer_name','asc')
		->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}else{
			return false;
		}
	}
	//Order entry
	public function order_entry()
	{
		//Order information
		$order_id 			= $this->auth->generator(15);

		$custom_order_no = $this->input->post('custom_order_id', TRUE);
		if(!empty($custom_order_no)){

			$is_exist = $this->db->select("order_id")
				->from('order')
				->where('order_no', $custom_order_no)
				->get()->num_rows();
			if($is_exist > 0){
				$this->session->set_userdata(array('error_message'=>display('order_no_exist')));
				redirect('new_order');
			}else{
				$order_no = $custom_order_no;
			}
		}else{
			$order_no = "EZ".mt_rand(100000000000,999999999999);
			// $order_no = "EZ".strtotime("now").mt_rand(10,99);
			$this->db->select('order_no');
			$this->db->where('order_no', $order_no);
			$query = $this->db->get('order');	
			$result = $query->num_rows();
			if ($result > 0) {
				$order_no = "EZ".mt_rand(100000000000,999999999999);	
			}
		}

		$quantity 			= filter_input_post('product_quantity');
		$available_quantity = filter_input_post('available_quantity');
		$product_id 		= filter_input_post('product_id');

		
		//Product existing check
		if ($product_id == null) {
			$this->session->set_userdata(array('error_message'=>display('please_select_product')));
			redirect('corder');
		}

		//Customer existing check
		if ((filter_input_post('customer_name_others') == null) && (filter_input_post('customer_id') == null )) {
			$this->session->set_userdata(array('error_message'=>display('please_select_customer')));
			redirect(base_url().'corder');
		}
		
		//Customer data Existence Check.
		if(filter_input_post('customer_id') == "" ){
			$this->form_validation->set_rules('customer_name_others', display('customer_name'), 'trim|required|max_length[50]|alpha_numeric_spaces|xss_clean', array( 'required' => display('customer_name').' '.display('required'))  );
            $this->form_validation->set_rules('customer_mobile', display('mobile'), 'trim|required|max_length[20]|regex_match[/^([0-9\+\-]|\s)+$/i]|xss_clean', array( 'required' => display('mobile').' '.display('required') ) );
            $this->form_validation->set_rules('customer_email', display('email'), 'trim|max_length[100]|valid_email|xss_clean');
            $this->form_validation->set_rules('customer_name_others_address', display('address'), 'trim|max_length[20]|regex_match[/^([0-9\p{L}\:\.\+\-\,\#]|\s)*$/u]|xss_clean' );

            if ($this->form_validation->run() == FALSE)
            {
                $this->session->set_userdata('error_message', validation_errors());
                redirect('new_order');
            }
			$customer_id=$this->auth->generator(15);
		  	//Customer  basic information adding.
			$data=array(
				'customer_id' 				=> $customer_id,
				'customer_code' 			=> $this->customer_number_generator(),
				'customer_name' 			=> filter_input_post('customer_name_others'),
				'customer_short_address'	=>filter_input_post('customer_name_others_address'),
				'customer_mobile' 			=> filter_input_post('customer_mobile'),
				'customer_email' 			=> filter_input_post('customer_email'),
				'status' 					=> 1
			);

			$result = $this->Customers->customer_entry($data);
			if ($result == false) {
				$this->session->set_userdata(array('error_message'=>display('already_exists')));
				redirect('corder/manage_order');
			}
		  	//Previous balance adding -> Sending to customer model to adjust the data.
			//$this->Customers->previous_balance_add(0,$customer_id);
		}
		else{
			$customer_id=filter_input_post('customer_id');
		}

		$pending_date = filter_input_post('invoice_date');

		//Data inserting into order table
		$data=array(
			'order_id'			=>	$order_id,
			'customer_id'		=>	$customer_id,
			'order_no'			=>	$order_no,
			'shipping_id'		=>	filter_input_post('city'),
			'date'				=>	filter_input_post('invoice_date'),
			'total_amount'		=>	(filter_input_post('grand_total_price'))? $this->input->post
			('grand_total_price') : $this->session->userdata('total_amount') ,
			'details'			=>	filter_input_post('details'),
			'total_discount' 	=> 	floatval(filter_input_post('product_discount')) + floatval(filter_input_post('invoice_discount')),
			'order_discount' 	=> 	filter_input_post('invoice_discount'),
			'vat'				=>  filter_input_post('unittotalvat'),
			'payable_amount'		=>	filter_input_post('payable_amount'),
			'paid_amount'		=>	filter_input_post('paid_amount'),
			// 'due_amount'		=>	filter_input_post('due_amount'),
			'affiliate_id'		=>	null,
			'number_product'	=>	null,
			'service_charge'	=>	filter_input_post('service_charge'),
			'pending'			=>	$pending_date,
		);
		
		$this->db->insert('order',$data);

		//Data insert info order tracking table 
		$order_tracking=array(
			'order_id'	=>	$order_id,
			'user_id'	=>	$this->session->userdata('user_id'),
			'date'		=>	date("Y-m-d h:i a"),
		);
		$this->db->insert('order_tracking',$order_tracking);

		//Seller order info
		$rate 		= filter_input_post('product_rate');
		$p_id 		= filter_input_post('product_id');
		$total_amount = filter_input_post('total_price');
		$discount 	= filter_input_post('discount');
		$variants 	= filter_input_post('variant_id');
		$seller_ids = filter_input_post('seller_id');

		//Seller order entry
		for ($i=0, $n=count($p_id); $i < $n; $i++) {
			$product_quantity = $quantity[$i];
			$product_rate 	  = $rate[$i];
			$product_id 	  = $p_id[$i];
			$discount_rate    = $discount[$i];
			$total_price      = $total_amount[$i];
			$variant_id       = $variants[$i];
			$seller_id        = $seller_ids[$i];

			//Seller percentage
			$comission_rate= $this->comission_info($product_id);
			$category_id   = $this->category_id($product_id);
			
			$seller_order = array(
				'order_id'			=>	$order_id,
				'seller_id'			=>	$seller_id,
				'seller_percentage'	=>	$comission_rate,
				'category_id'		=>	$category_id,
				'customer_id'		=>	$customer_id,
				'product_id'		=>	$product_id,
				'variant_id'		=>	$variant_id,
				'quantity'			=>	$product_quantity,
				'rate'				=>	$product_rate,
				'total_price'       =>	$total_price,
				'discount_per_product' =>	$discount_rate,
			);

			if(!empty($product_id))
			{

				//Stock update in product information
				$this->db->set('quantity','quantity-'.$product_quantity,FALSE)
				->where('product_id',$product_id)
				->update('product_information');

				$result = $this->db->select('*')
				->from('seller_order')
				->where('order_id',$order_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('quantity', 'quantity+'.$product_quantity, FALSE);
					$this->db->set('total_price', 'total_price+'.$total_price, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('product_id', $product_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('seller_order');
				}else{
					$this->db->insert('seller_order',$seller_order);
				}
			}
		}

		//Tax info
		$cgst = filter_input_post('cgst');if(empty($cgst)){ $cgst = array(); }
		$sgst = filter_input_post('sgst');if(empty($sgst)){ $sgst = array(); }
		$igst = filter_input_post('igst');if(empty($igst)){ $igst = array(); }
		$cgst_id = filter_input_post('cgst_id');
		$sgst_id = filter_input_post('sgst_id');
		$igst_id = filter_input_post('igst_id');

		//Tax collection summary for three
		//CGST tax info
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$cgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'tax_amount' 		=> 	$cgst_tax, 
				'tax_id' 			=> 	$cgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$cgst_summary);
				}
			}
		}

		//SGST tax info
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			
			$sgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'tax_amount' 		=> 	$sgst_tax, 
				'tax_id' 			=> 	$sgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$sgst_summary);
				}
			}
		}

		//IGST tax info
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			
			$igst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'tax_amount' 		=> 	$igst_tax, 
				'tax_id' 			=> 	$igst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->get()
				->num_rows();

				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$igst_summary);
				}
			}
		}
		//Tax collection summary for three

		//Tax collection details for three
		//CGST tax info
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax 	 = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$cgst_details = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'amount' 			=> 	$cgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$cgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){

				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$cgst_details);
				}
			}
		}

		//SGST tax info
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax 	 = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$sgst_summary = array(
				'order_tax_col_de_id'	=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'amount' 			=> 	$sgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$sgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$sgst_summary);
				}
			}
		}

		//IGST tax info
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax 	 = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$igst_summary = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'amount' 			=> 	$igst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$igst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$igst_summary);
				}
			}
		}
		//Tax collection details for three
		return $order_id;
	}
	//Update order
	public function update_order()
	{
		//Order information
		$order_id  	 = filter_input_post('order_id');
		$customer_id = filter_input_post('customer_id');
		$order_no 	 = filter_input_post('order_no');

		if($order_id!='')
		{
			if((int)filter_input_post('grand_total_price')==(int)filter_input_post('paid_amount')){
				$payment_date = date("Y-m-d h:i:sa");
			}else{
				$payment_date = '';
			}
			$pending_date = filter_input_post('invoice_date');

			//Data update into order table
			$data=array(
				'order_id'			=>	$order_id,
				'order_no'			=>	$order_no,
				'customer_id'		=>	$customer_id,
				'shipping_id'		=>	filter_input_post('city'),
				'date'				=>	filter_input_post('invoice_date'),
				'total_amount'		=>	filter_input_post('grand_total_price'),
				'total_discount' 	=> 	filter_input_post('product_discount') + filter_input_post('invoice_discount'),
				'order_discount' 	=> 	filter_input_post('invoice_discount'),
				'service_charge' 	=> 	filter_input_post('service_charge'),
				'pending' 			=> 	$pending_date,
				'vat'				=>	filter_input_post('total_vat'),				
				'paid_amount'		=>	filter_input_post('paid_amount'),
				'payment_date'		=>	$payment_date,
				'details'			=>	filter_input_post('details'),
				'status'			=>	filter_input_post('status'),
			);

			$res = $this->db->update('order',$data, array('order_no' => $order_no));

			// Order Tracking Data insert
			if($res) {
				//Insert data into order tracking table
				$order_tracking = array(
					'order_id'	=>	$order_id,
					'user_id'	=>	$this->session->userdata('user_id'),
					'order_status'	=>	9,
					'date'		=>	date("Y-m-d h:i a"),
				);
				$this->db->insert('order_tracking',$order_tracking);
			}


			// $this->db->where('order_id',$order_id);
			// $result = $this->db->delete('order');

		}


		//Seller order info
		$rate 				= filter_input_post('product_rate');
		$p_id 				= filter_input_post('product_id');
		$total_amount 		= filter_input_post('total_price');
		$discount 			= filter_input_post('discount');
		$variants 			= filter_input_post('variant_id');
		$seller_ids 		= filter_input_post('seller_id');
		$quantity 			= filter_input_post('product_quantity');
		$return_quantity 	= filter_input_post('return_quantity');
		$plus_qnty  		= filter_input_post('plus_quantity');
		$minus_qnty 		= filter_input_post('minus_quantity');
		$product_vats		= filter_input_post('product_vat');

		$this->db->trans_start();
		
		// Update Invoice Inofo
		$fdata=array(
				'total_amount'		=>	filter_input_post('grand_total_price'),
				'total_discount' 	=> 	filter_input_post('product_discount') + filter_input_post('invoice_discount'),
				'invoice_discount' 	=> 	filter_input_post('invoice_discount'),
				'service_charge' 	=> 	filter_input_post('service_charge'),
				'paid_amount'		=>	filter_input_post('paid_amount'),
				'due_amount'		=>	filter_input_post('grand_total_price') - filter_input_post('paid_amount'),
				'inv_update' => '1'
			);
		$this->db->update('invoice', $fdata, array('order_no' => $order_no));

		//Delete old invoice info
		if (!empty($order_id)) {
			$this->db->where('order_id',$order_id);
			$this->db->delete('seller_order'); 
		}

		//Delete old invoice info
		$invoice_id  = $this->get_invoice_id_by_order_id($order_no);
		if (!empty($order_no)) {
			$this->db->where('order_no',$order_no);
			$this->db->delete('invoice_details'); 
		}

		//Seller order for entry
		for ($i=0, $n=count($p_id); $i < $n; $i++) {
			$product_quantity = $quantity[$i];
			$return_qty  	  = $return_quantity[$i];
			$product_rate 	  = $rate[$i];
			$product_id 	  = $p_id[$i];
			$discount_rate    = $discount[$i];
			$total_price      = $total_amount[$i];
			$variant_id       = $variants[$i];
			$seller_id        = $seller_ids[$i];
			$plus_quantity    = $plus_qnty[$i];
			$minus_quantity   = $minus_qnty[$i];
			$product_vat	  = $product_vats[$i];
			//Seller percentage
			$comission_rate= $this->comission_info($product_id);
			$category_id   = $this->category_id($product_id);
			
			$seller_order = array(
				'order_id'				=>	$order_id,
				'seller_id'				=>	$seller_id,
				'seller_percentage' 	=>	$comission_rate,
				'category_id'			=>	$category_id,
				'customer_id'			=>	$customer_id,
				'product_id'			=>	$product_id,
				'variant_id'			=>	$variant_id,
				'quantity'				=>	$product_quantity,
				'rate'					=>	$product_rate,
				'total_price'       	=>	$total_price,
				'discount_per_product' 	=>	$discount_rate,
				'return_quantity'		=>	$return_qty,
				'product_vat'			=>	$product_vat,
			);

			if(!empty($product_id))
			{
				$result = $this->db->select('*')
				->from('seller_order')
				->where('order_id',$order_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {

					//Stock update in product information
					$this->db->set('quantity','quantity-'.$product_quantity,FALSE)
					->where('product_id',$product_id)
					->update('product_information');

					$this->db->set('quantity', 'quantity+'.$product_quantity, FALSE);
					$this->db->set('total_price', 'total_price+'.$total_price, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('product_id', $product_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('seller_order');
				}else{
					$this->db->insert('seller_order',$seller_order);
				}

				//Insert updated Invoice details data
				$inv_details = array(
					'invoice_details_id' => $this->auth->generator(15), 
					'invoice_id' 		 => $invoice_id, 
					'order_no' 		 	 => $order_no, 
					'seller_id'		 	 =>	$seller_id,
					'category_id'		 =>	$category_id,
					'product_id' 		 => $product_id, 
					'variant_id'		 => $variant_id, 
					'quantity'			 => $product_quantity, 
					'return_quantity'	=>	$return_qty,
					'rate'				 => $product_rate, 
					'total_price'		 => $total_price, 
					'discount'			 => $discount_rate
				);
				$this->db->insert('invoice_details',$inv_details);

			// End of invoice Update

				if ($plus_quantity > 0) {
					//Stock update in product information
					$this->db->set('quantity','quantity-'.$plus_quantity,FALSE)
					->where('product_id',$product_id)
					->update('product_information');
				}

				if ($minus_quantity > 0) {
					//Stock update in product information
					$this->db->set('quantity','quantity+'.$minus_quantity,FALSE)
					->where('product_id',$product_id)
					->update('product_information');
				}
			}
		}

		$this->db->trans_complete();

		//Tax info
		$cgst = filter_input_post('cgst');
		$sgst = filter_input_post('sgst');
		$igst = filter_input_post('igst');
		$cgst_id = filter_input_post('cgst_id');
		$sgst_id = filter_input_post('sgst_id');
		$igst_id = filter_input_post('igst_id');

		//Tax collection summary for three

		//Delete all tax  from summary
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_summary');

		//CGST Tax Summary
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$cgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'tax_amount' 		=> 	$cgst_tax, 
				'tax_id' 			=> 	$cgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$cgst_summary);
				}
			}
		}

		//SGST Tax Summary
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			
			$sgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'tax_amount' 		=> 	$sgst_tax, 
				'tax_id' 			=> 	$sgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$sgst_summary);
				}
			}
		}

		//IGST Tax Summary
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			
			$igst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'tax_amount' 		=> 	$igst_tax, 
				'tax_id' 			=> 	$igst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->get()
				->num_rows();

				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$igst_summary);
				}
			}
		}
		//Tax collection summary for three

		//Tax collection details for three
		//Delete all tax  from summary
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_details');

		//CGST Tax Details
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax 	 = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$cgst_details = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'amount' 			=> 	$cgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$cgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$cgst_details);
				}
			}
		}

		//SGST Tax Details
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax 	 = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$sgst_summary = array(
				'order_tax_col_de_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'amount' 			=> 	$sgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$sgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$sgst_summary);
				}
			}
		}

		//IGST Tax Details
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax 	 = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$igst_summary = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'amount' 			=> 	$igst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$igst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$igst_summary);
				}
			}
		}
		//End tax details
		return $order_id;
	}
	//Pre order update
	public function pre_order_update()
	{
		//Order information
		$order_id  	 = filter_input_post('order_id');
		$customer_id = filter_input_post('customer_id');

		if($order_id!='')
		{
			//Data update into order table
			$data=array(
				'order_id'			=>	$order_id,
				'customer_id'		=>	$customer_id,
				'date'				=>	filter_input_post('invoice_date'),
				'total_amount'		=>	filter_input_post('grand_total_price'),
				'total_discount' 	=> 	filter_input_post('product_discount') + filter_input_post('invoice_discount'),
				'order_discount' 	=> 	filter_input_post('invoice_discount'),
				'service_charge' 	=> 	filter_input_post('service_charge'),
				'paid_amount'		=>	filter_input_post('paid_amount'),
				'details'			=>	filter_input_post('details'),
				'status'			=>	filter_input_post('status'),
			);

			$this->db->where('order_id',$order_id);
			$result = $this->db->delete('pre_order');

			if ($result) {
				$this->db->insert('pre_order',$data);
			}
		}

		//Seller order info
		$rate 		= filter_input_post('product_rate');
		$p_id 		= filter_input_post('product_id');
		$total_amount = filter_input_post('total_price');
		$discount 	= filter_input_post('discount');
		$variants 	= filter_input_post('variant_id');
		$seller_ids = filter_input_post('seller_id');
		$quantity 	= filter_input_post('product_quantity');

		//Delete old invoice info
		if (!empty($order_id)) {
			$this->db->where('order_id',$order_id);
			$this->db->delete('seller_pre_order'); 
		}

		//Seller order for entry
		for ($i=0, $n=count($p_id); $i < $n; $i++) {
			$product_quantity = $quantity[$i];
			$product_rate 	  = $rate[$i];
			$product_id 	  = $p_id[$i];
			$discount_rate    = $discount[$i];
			$total_price      = $total_amount[$i];
			$variant_id       = $variants[$i];
			$seller_id        = $seller_ids[$i];
			
			$seller_pre_order = array(
				'order_id'			=>	$order_id,
				'seller_id'			=>	$seller_id,
				'customer_id'		=>	$customer_id,
				'product_id'		=>	$product_id,
				'variant_id'		=>	$variant_id,
				'quantity'			=>	$product_quantity,
				'rate'				=>	$product_rate,
				'total_price'       =>	$total_price,
				'discount_per_product' =>	$discount_rate,
			);

			if(!empty($product_id))
			{
				$result = $this->db->select('*')
				->from('seller_pre_order')
				->where('order_id',$order_id)
				->where('product_id',$product_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('quantity', 'quantity+'.$product_quantity, FALSE);
					$this->db->set('total_price', 'total_price+'.$total_price, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('product_id', $product_id);
					$this->db->update('seller_pre_order');
				}else{
					$this->db->insert('seller_pre_order',$seller_pre_order);
				}
			}
		}

		//Tax info
		$cgst = filter_input_post('cgst');
		$sgst = filter_input_post('sgst');
		$igst = filter_input_post('igst');
		$cgst_id = filter_input_post('cgst_id');
		$sgst_id = filter_input_post('sgst_id');
		$igst_id = filter_input_post('igst_id');

		//Tax collection summary for three

		//Delete all tax  from summary
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_summary');

		//CGST Tax Summary
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$cgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'tax_amount' 		=> 	$cgst_tax, 
				'tax_id' 			=> 	$cgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$cgst_summary);
				}
			}
		}

		//SGST Tax Summary
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			
			$sgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'tax_amount' 		=> 	$sgst_tax, 
				'tax_id' 			=> 	$sgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$sgst_summary);
				}
			}
		}

		//IGST Tax Summary
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			
			$igst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'tax_amount' 		=> 	$igst_tax, 
				'tax_id' 			=> 	$igst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->get()
				->num_rows();

				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$igst_summary);
				}
			}
		}
		//Tax collection summary for three

		//Tax collection details for three
		//Delete all tax  from summary
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_details');

		//CGST Tax Details
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax 	 = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$cgst_details = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'amount' 			=> 	$cgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$cgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$cgst_details);
				}
			}
		}

		//SGST Tax Details
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax 	 = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$sgst_summary = array(
				'order_tax_col_de_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'amount' 			=> 	$sgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$sgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$sgst_summary);
				}
			}
		}

		//IGST Tax Details
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax 	 = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$igst_summary = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'amount' 			=> 	$igst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$igst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$igst_summary);
				}
			}
		}
		//End tax details
		return $order_id;
	}
	//Return order
	public function return_order()
	{
		//Order information
		$order_id  	 = filter_input_post('order_id');
		$customer_id = filter_input_post('customer_id');

		$order 		 = $this->get_order_no($order_id);

		$invoice_no  = $this->get_invoice_no_by_order_id($order->order_no);

		if($order_id!='')
		{
			$pending_date = filter_input_post('invoice_date');

			//Data update into order table
			$data=array(
				'order_id'			=>	$order_id,
				'customer_id'		=>	$customer_id,
				'order_no'			=>	filter_input_post('order_no'),
				'shipping_id'		=>	filter_input_post('shipping_id'),
				'date'				=>	filter_input_post('invoice_date'),
				'total_amount'		=>	filter_input_post('grand_total_price'),
				'total_discount' 	=> 	filter_input_post('product_discount') + filter_input_post('order_discount'),
				'order_discount' 	=> 	filter_input_post('order_discount'),
				'service_charge' 	=> 	filter_input_post('service_charge'),
				'pending' 			=> 	$pending_date,
				'paid_amount'		=>	filter_input_post('paid_amount'),
				'details'			=>	filter_input_post('details'),
				'status'			=>	filter_input_post('status'),
				'order_status'		=>	filter_input_post('order_status'),
			);

			$this->db->where('order_id',$order_id);
			$result = $this->db->delete('order');

			if ($result) {
				//Data insert into order table
				$this->db->insert('order',$data);

				//order update
				$this->db->set('status','2');
				$this->db->where('order_id',$order_id);
				$this->db->update('order');

				if ($invoice_no) {
					$this->db->where('invoice',$invoice_no);
					$result = $this->db->delete('invoice');
				}else{
					$invoice_no = $this->number_generator();
				}

				$invoice_id = $this->auth->generator(15);

				$invoice_data = array(
					'invoice_id' 	=> $invoice_id,
					'order_no' 		=> $order->order_no,
					'customer_id' 	=> $customer_id,
					'shipping_id' 	=> filter_input_post('shipping_id'),
					'invoice' 		=> $invoice_no,
					'date' 			=> date('Y-m-d'),
					'total_amount' 	=> filter_input_post('grand_total_price'),
					'total_discount'=> filter_input_post('total_discount') + filter_input_post('invoice_discount'),
					'invoice_discount' => filter_input_post('invoice_discount'),
					'service_charge' => filter_input_post('service_charge'),
					'paid_amount' 	=> filter_input_post('paid_amount'),
					'due_amount' 	=> filter_input_post('grand_total_price') - filter_input_post('paid_amount'),
					'status' 		=> filter_input_post('status'),
					'invoice_status'=> filter_input_post('order_status'),
				);
				//Data insert into invoice table
				$this->db->insert('invoice',$invoice_data);

				//Update to customer ledger Table 
				$data2 = array(
					'transaction_id'	=>	$this->auth->generator(15),
					'customer_id'		=>	$customer_id,
					'invoice_no'		=>	$invoice_id,
					'order_no' 			=>  $order_id, 
					'date'				=>	date('Y-m-d'),
					'amount'			=>	filter_input_post('grand_total_price'),
					'status'			=>	1
				);
				$ledger = $this->db->insert('customer_ledger',$data2);
			}
		}

		//Seller order info
		$rate 		= filter_input_post('product_rate');
		$p_id 		= filter_input_post('product_id');
		$total_amount = filter_input_post('total_price');
		$discount 	= filter_input_post('discount');
		$variants 	= filter_input_post('variant_id');
		$seller_ids = filter_input_post('seller_id');
		$quantity 	= filter_input_post('product_quantity');
		$return_quantity 	= filter_input_post('return_quantity');

		//Delete old order info
		if (!empty($order_id)) {
			$this->db->where('order_id',$order_id);
			$this->db->delete('seller_order'); 
		}

		//Delete old invoice info
		$invoice_id  = $this->get_invoice_id_by_order_id($order->order_no);
		if (!empty($invoice_id)) {
			$this->db->where('invoice_id',$invoice_id);
			$this->db->delete('invoice_details'); 
		}

		//Seller order for entry
		for ($i=0, $n=count($p_id); $i < $n; $i++) {
			$product_quantity = $quantity[$i];
			$return_qty  	  = $return_quantity[$i];
			$product_rate 	  = $rate[$i];
			$product_id 	  = $p_id[$i];
			$discount_rate    = $discount[$i];
			$total_price      = $total_amount[$i];
			$variant_id       = $variants[$i];
			$seller_id        = $seller_ids[$i];

			//Seller percentage
			$comission_rate   = $this->comission_info($product_id);
			$category_id   	  = $this->category_id($product_id);
			
			$seller_order = array(
				'order_id'			=>	$order_id,
				'seller_id'			=>	$seller_id,
				'seller_percentage' =>	$comission_rate,
				'category_id'		=>	$category_id,
				'customer_id'		=>	$customer_id,
				'product_id'		=>	$product_id,
				'variant_id'		=>	$variant_id,
				'quantity'			=>	$product_quantity,
				'return_quantity'	=>	$return_qty,
				'rate'				=>	$product_rate,
				'total_price'       =>	$total_price,
				'discount_per_product' =>	$discount_rate,
			);

			if(!empty($product_id))
			{
				$result = $this->db->select('*')
				->from('seller_order')
				->where('order_id',$order_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('quantity', 'quantity+'.$product_quantity, FALSE);
					$this->db->set('total_price', 'total_price+'.$total_price, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('product_id', $product_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('seller_order');
				}else{
					$this->db->insert('seller_order',$seller_order);
				}
			}

			$order_no  = $this->get_order_no_by_order_id($order_id);

			//Invoice details data
			$invoice_details = array(
				'invoice_details_id' => $this->auth->generator(15), 
				'invoice_id' 		 => $invoice_id, 
				'order_no' 		 	 => $order_no, 
				'seller_id'		 	 =>	$seller_id,
				'category_id'		 =>	$category_id,
				'product_id' 		 => $product_id, 
				'variant_id'		 => $variant_id, 
				'quantity'			 => $product_quantity, 
				'return_quantity'	=>	$return_qty,
				'rate'				 => $product_rate, 
				'total_price'		 => $total_price, 
				'discount'			 => $discount_rate,
				'seller_percentage'  =>	$comission_rate,
			);
			$this->db->insert('invoice_details',$invoice_details);

			if ($return_qty) {
				//Product restock in product table
				$this->db->set('quantity','quantity+'.$return_qty,FALSE)
				->where('product_id',$product_id)
				->update('product_information');
			}
		}

		//Tax info
		$cgst = filter_input_post('cgst');
		$sgst = filter_input_post('sgst');
		$igst = filter_input_post('igst');
		$cgst_id = filter_input_post('cgst_id');
		$sgst_id = filter_input_post('sgst_id');
		$igst_id = filter_input_post('igst_id');

		//Tax collection summary for three

		//Delete all tax  from summary
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_summary');

		//CGST Tax Summary
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$cgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'tax_amount' 		=> 	$cgst_tax, 
				'tax_id' 			=> 	$cgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$cgst_summary);
				}
			}
		}

		//SGST Tax Summary
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			
			$sgst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'tax_amount' 		=> 	$sgst_tax, 
				'tax_id' 			=> 	$sgst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$sgst_summary);
				}
			}
		}

		//IGST Tax Summary
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			
			$igst_summary = array(
				'order_tax_col_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'tax_amount' 		=> 	$igst_tax, 
				'tax_id' 			=> 	$igst_tax_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_summary')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->get()
				->num_rows();

				if ($result > 0) {
					$this->db->set('tax_amount', 'tax_amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->update('order_tax_col_summary');
				}else{
					$this->db->insert('order_tax_col_summary',$igst_summary);
				}
			}
		}
		//Tax collection summary for three

		//Tax collection details for three
		//Delete all tax  from summary
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_details');

		//CGST Tax Details
		for ($i=0, $n=count($cgst); $i < $n; $i++) {
			$cgst_tax 	 = $cgst[$i];
			$cgst_tax_id = $cgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$cgst_details = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'			=>	$order_id,
				'amount' 			=> 	$cgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$cgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($cgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$cgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$cgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $cgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$cgst_details);
				}
			}
		}

		//SGST Tax Details
		for ($i=0, $n=count($sgst); $i < $n; $i++) {
			$sgst_tax 	 = $sgst[$i];
			$sgst_tax_id = $sgst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$sgst_summary = array(
				'order_tax_col_de_id'	=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'amount' 			=> 	$sgst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$sgst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($sgst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$sgst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$sgst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $sgst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$sgst_summary);
				}
			}
		}

		//IGST Tax Details
		for ($i=0, $n=count($igst); $i < $n; $i++) {
			$igst_tax 	 = $igst[$i];
			$igst_tax_id = $igst_id[$i];
			$product_id  = $p_id[$i];
			$variant_id  = $variants[$i];
			$igst_summary = array(
				'order_tax_col_de_id'=>	$this->auth->generator(15),
				'order_id'		=>	$order_id,
				'amount' 			=> 	$igst_tax, 
				'product_id' 		=> 	$product_id, 
				'tax_id' 			=> 	$igst_tax_id,
				'variant_id' 		=> 	$variant_id,
				'date'				=>	filter_input_post('invoice_date'),
			);
			if(!empty($igst[$i])){
				$result= $this->db->select('*')
				->from('order_tax_col_details')
				->where('order_id',$order_id)
				->where('tax_id',$igst_tax_id)
				->where('product_id',$product_id)
				->where('variant_id',$variant_id)
				->get()
				->num_rows();
				if ($result > 0) {
					$this->db->set('amount', 'amount+'.$igst_tax, FALSE);
					$this->db->where('order_id', $order_id);
					$this->db->where('tax_id', $igst_tax_id);
					$this->db->where('variant_id', $variant_id);
					$this->db->update('order_tax_col_details');
				}else{
					$this->db->insert('order_tax_col_details',$igst_summary);
				}
			}
		}
		//End tax details
		return $order_id;
	}
	//Get invoice id by order id
	public function get_invoice_no_by_order_id($order_id=null){
		$result = $this->db->select('invoice')
		->from('invoice')
		->where('order_no',$order_id)
		->get()
		->row();
		if ($result) {
			return $result->invoice;
		}else{
			return false;
		}
	}		
	//Get order no by order id
	public function get_order_no_by_order_id($order_id=null){
		$result = $this->db->select('order_no')
		->from('order')
		->where('order_id',$order_id)
		->get()
		->row();
		if ($result) {
			return $result->order_no;
		}else{
			return false;
		}
	}	
	//Get invoice no by order id
	public function get_invoice_id_by_order_id($order_id=null){
		$result = $this->db->select('invoice_id')
		->from('invoice')
		->where('order_no',$order_id)
		->get()
		->row();
		if ($result) {
			return $result->invoice_id;
		}else{
			return false;
		}
	}

	//Order paid to invoice
	public function order_paid_data($order_id=null){

		$invoice_id = $this->auth->generator(15);

		$result = $this->db->select('*')
		->from('order')
		->where('order_id',$order_id)
		->where('status',1)
		->get()
		->row();

		if ($result) {

			$order_no = $result->order_no;
			if($this->session->userdata('payment_method') == 1 || ($this->session->userdata('payment_method'))== ""){
				$due_amount= $result->total_amount - $result->paid_amount;
			}else{
				$due_amount= 0;
			}
			$data = array(
				'invoice_id' 		=> $invoice_id,
				'order_no' 			=> $order_no,
				'customer_id' 		=> $result->customer_id,
				'shipping_id' 		=> $result->shipping_id,
				'invoice' 			=> $this->number_generator(),
				'date' 				=> date('Y-m-d'),
				'total_amount' 		=> $result->total_amount,
				'vat' 				=> $result->vat,
				'total_discount'	=> $result->total_discount,
				'invoice_discount'	=> $result->order_discount,
				'service_charge' 	=> $result->service_charge,
				'paid_amount' 		=> $result->paid_amount,
				'due_amount' 		=> $due_amount,
				'status' 			=> $result->status,
				'invoice_status'	=> $result->order_status,
			);
			$this->db->insert('invoice',$data);

			//Update to customer ledger Table 
			$data2 = array(
				'transaction_id'	=>	$this->auth->generator(15),
				'customer_id'		=>	$result->customer_id,
				'invoice_no'		=>	$invoice_id,
				'order_no' 			=>  $order_no, 
				'date'				=>	date('Y-m-d'),
				'amount'			=>	$result->total_amount,
				'payment_type'		=>	($this->session->userdata('payment_method'))? $this->session->userdata('payment_method'): 1,
				'status'			=>	1
			);
			$ledger = $this->db->insert('customer_ledger',$data2);

		}else{
			return true;
		}

		if ($ledger) {

			//order update
			$this->db->set('status','2');
			$this->db->where('order_id',$order_id);
			$order = $this->db->update('order');

			$order_details=$this->db->select('*')
			->from('seller_order')
			->where('order_id',$order_id)
			->get()
			->result();

			if ($order_details) {
				foreach ($order_details as $details) {


					$invoice_details = array(
						'invoice_details_id' => $this->auth->generator(15), 
						'invoice_id' 		 => $invoice_id, 
						'order_no' 		 	 => $order_no, 
						'seller_id'		 	 =>	$details->seller_id,
						'category_id'		 =>	$details->category_id,
						'product_id' 		 => $details->product_id, 
						'variant_id'		 => $details->variant_id, 
						'quantity'			 => $details->quantity, 
						'rate'				 => $details->rate, 
						'total_price'		 => $details->total_price, 
						'discount'			 => $details->discount_per_product,
						'seller_percentage'  =>	$details->seller_percentage,
					);

					$order_details = $this->db->insert('invoice_details',$invoice_details);
				}
			}
		}

		//Tax summary entry start
		$this->db->select('*');
		$this->db->from('order_tax_col_summary');
		$this->db->where('order_id',$order_id);
		$query = $this->db->get();
		$tax_summary = $query->result();

		if ($tax_summary) {
			foreach ($tax_summary as $summary) {
				$tax_col_summary = array(
					'tax_collection_id' => $summary->order_tax_col_id,
					'invoice_id' 		=> $invoice_id,
					'tax_id' 			=> $summary->tax_id,
					'tax_amount' 		=> $summary->tax_amount,
					'date' 				=> $summary->date,
				);
				$this->db->insert('tax_collection_summary',$tax_col_summary);
			}
		}
		//Tax summary entry end

		//Tax details entry start
		$this->db->select('*');
		$this->db->from('order_tax_col_details');
		$this->db->where('order_id',$order_id);
		$query = $this->db->get();
		$tax_details = $query->result();

		if ($tax_details) {
			foreach ($tax_details as $details) {
				$tax_col_details = array(
					'tax_col_de_id' 	=> $details->order_tax_col_de_id,
					'invoice_id' 		=> $invoice_id,
					'product_id' 		=> $details->product_id,
					'variant_id' 		=> $details->variant_id,
					'tax_id' 			=> $details->tax_id,
					'amount' 			=> $details->amount,
					'date' 				=> $details->date,
				);
				$this->db->insert('tax_collection_details',$tax_col_details);
			}
		}
		//Tax details entry end
		return true;
	}
	//Comission info by product id
	public function comission_info($product_id){
		$comission = $this->db->select('*')
		->from('product_information')
		->where('product_id',$product_id)
		->get()
		->row();

		if ($comission) {
			return $comission->comission;
		}else{
			return 0;
		}
	}
	//Category id by product id
	public function category_id($product_id){
		$category = $this->db->select('*')
		->from('product_information')
		->where('product_id',$product_id)
		->get()
		->row();

		if ($category) {
			return $category->category_id;
		}else{
			return null;
		}
	}
	//Pre order paid to invoice
	public function pre_order_paid_data($order_id=null){

		$pre_order_details=$this->db->select('*')
		->from('seller_pre_order')
		->where('order_id',$order_id)
		->get()
		->result();
		
		//Stock check for order to pre-order
		if ($pre_order_details) {
			foreach ($pre_order_details as $order_details) {
				$product_id = $order_details->product_id;
				$quantity   = $order_details->quantity;

				$stock = $this->db->select('quantity')
				->from('product_information')
				->where('product_id',$product_id)
				->get()
				->row();
				if ($stock->quantity < $quantity) {
					$this->session->set_userdata(array('error_message'=>display('product_is_not_available_in_stock')));
					redirect('corder/manage_pre_order');
				}
			}
		}

		$new_order_id = $this->auth->generator(15);
		//Shipping address 
		$shipping_info = $this->db->select('*')
		->from('shipping_info')
		->where('order_id',$order_id)
		->get()
		->row();

		if ($shipping_info) {
			$data = array(
				'customer_id' 		=> $shipping_info->customer_id,
				'order_id' 	  		=> $new_order_id,
				'customer_name' 	=> $shipping_info->customer_name,
				'first_name' 		=> $shipping_info->first_name,
				'last_name' 		=> $shipping_info->last_name,
				'customer_short_address' => $shipping_info->customer_short_address,
				'customer_address_1'=> $shipping_info->customer_address_1,
				'customer_address_2'=> $shipping_info->customer_address_2,
				'customer_mobile' 	=> $shipping_info->customer_mobile,
				'customer_email' 	=> $shipping_info->customer_email,
				'city' 				=> $shipping_info->city,
				'state' 			=> $shipping_info->state,
				'country' 			=> $shipping_info->country,
				'zip' 				=> $shipping_info->zip,
				'company' 			=> $shipping_info->company,
			);
			//New shipping info
			$this->db->insert('shipping_info',$data);
		}
		$result = $this->db->select('*')
		->from('pre_order')
		->where('order_id',$order_id)
		->get()
		->row();
		if ($result) {

			$order_no = "EZ".mt_rand(100000000000,999999999999);
			// $order_no = "EZ".strtotime("now").mt_rand(10,99);
			$this->db->select('order_no');
			$this->db->where('order_no', $order_no);
			$query = $this->db->get('order');	
			$result = $query->num_rows();
			if ($result > 0) {
				$order_no = "EZ".mt_rand(100000000000,999999999999);	
			}

			$data = array(
				'order_id' 		=> $new_order_id,
				'order_no' 		=> $order_no,
				'pre_order_id' 	=> $result->id,
				'customer_id' 	=> $result->customer_id,
				'shipping_id' 	=> $result->shipping_id,
				'date' 			=> date('Y-m-d'),
				'total_amount' 	=> $result->total_amount,
				'details' 		=> $result->details,
				'total_discount'=> $result->total_discount,
				'order_discount'=> $result->order_discount,
				'paid_amount' 	=> $result->paid_amount,
				'affiliate_id' 	=> $result->affiliate_id,
				'number_product'=> $result->number_product,
				'service_charge'=> $result->service_charge,
				'file_path'		=> $result->file_path,
				'status' 		=> $result->status,
				'pending' 		=> date('Y-m-d'),
			);
			$order = $this->db->insert('order',$data);

			//Insert data into order tracking table
			$order_tracking=array(
				'order_id'		=>	$new_order_id,
				'user_id'		=>	$this->session->userdata('user_id'),
				'order_status'	=>	$result->status,
				'date'			=>	date("Y-m-d h:i a"),
			);
			$this->db->insert('order_tracking',$order_tracking);
		}

		if ($order) {

			//Update order info
			$this->db->set('status','2');
			$this->db->where('order_id',$order_id);
			$order = $this->db->update('pre_order');

			//Pre order details
			$pre_order_details=$this->db->select('*')
			->from('seller_pre_order')
			->where('order_id',$order_id)
			->get()
			->result();

			if ($pre_order_details) {
				foreach ($pre_order_details as $details) {

					//Product stock update
					$this->db->set('quantity', 'quantity-'.$details->quantity, FALSE);
					$this->db->where('product_id',$details->product_id);
					$this->db->update('product_information');

					$order_details = array(
						'order_id' 		=> $new_order_id,
						'seller_id' 	=> $details->seller_id, 
						'customer_id' 	=> $details->customer_id, 
						'product_id'	=> $details->product_id, 
						'variant_id'	=> $details->variant_id, 
						'quantity'		=> $details->quantity, 
						'rate'			=> $details->rate, 
						'total_price'	=> $details->total_price,
						'discount_per_product'	=> $details->discount_per_product,
					);
					$this->db->insert('seller_order',$order_details);
				}
			}
		}
		return true;
	}
	//Retrieve order Edit Data
	public function retrieve_order_editdata($order_id)
	{
		$lang = $this->db->select('language')
		->from('soft_setting')
		->where('setting_id',1)
		->get()
		->row();

		if ($lang) {
			$language = $lang->language;
		}else{
			$language = 'english';
		}

		$this->db->select('
			a.*,
			b.customer_name,
			c.*,
			c.product_id,
			d.product_model,d.vat as vat_rate,
			a.status,
			e.title as product_name,
			f.name
			');

		$this->db->from('order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id', 'left');
		$this->db->join('seller_order c','c.order_id = a.order_id', 'left');
		$this->db->join('product_information d','d.product_id = c.product_id', 'left');
		$this->db->join('product_title e','e.product_id = c.product_id', 'left');
		$this->db->join('cities f','a.shipping_id = f.id', 'left');
		$this->db->where('a.order_id',$order_id);
		$this->db->where('e.lang_id',$language);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Retrieve order Edit Data
	public function retrieve_pre_order_editdata($order_id)
	{
		$lang = $this->db->select('language')
		->from('soft_setting')
		->where('setting_id',1)
		->get()
		->row();

		if ($lang) {
			$language = $lang->language;
		}else{
			$language = 'english';
		}

		$this->db->select('
			a.*,
			b.customer_name,
			c.*,
			c.product_id,
			d.product_model,
			a.status,
			e.title as product_name
			');

		$this->db->from('pre_order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->join('seller_pre_order c','c.order_id = a.order_id');
		$this->db->join('product_information d','d.product_id = c.product_id');
		$this->db->join('product_title e','e.product_id = c.product_id');
		$this->db->where('a.order_id',$order_id);
		$this->db->where('e.lang_id',$language);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Retrieve order_html_data
	public function retrieve_order_html_data($order_id)
	{
		$lang = $this->db->select('language')
		->from('soft_setting')
		->where('setting_id',1)
		->get()
		->row();

		if ($lang) {
			$language = $lang->language;
		}else{
			$language = 'english';
		}


		$this->db->select('
			a.*,
			b.customer_short_address,
			b.customer_name,
			b.customer_mobile,
			b.customer_email,
			c.*,
			d.product_id,
			d.product_model,d.unit,
			e.unit_short_name,
			f.variant_name,
			g.title as product_name
			');
		$this->db->from('order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->join('seller_order c','c.order_id = a.order_id');
		$this->db->join('product_information d','d.product_id = c.product_id');
		$this->db->join('unit e','e.unit_id = d.unit','left');
		$this->db->join('variant f','f.variant_id = c.variant_id','left');
		$this->db->join('product_title g','g.product_id = c.product_id','left');
		$this->db->where('a.order_id',$order_id);
		$this->db->where('g.lang_id',$language);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Retrieve order html data
	public function retrieve_pre_order_html_data($order_id)
	{
		$lang = $this->db->select('language')
		->from('soft_setting')
		->where('setting_id',1)
		->get()
		->row();

		if ($lang) {
			$language = $lang->language;
		}else{
			$language = 'english';
		}

		$this->db->select('
			a.*,
			b.customer_short_address,
			b.customer_name,
			b.customer_mobile,
			b.customer_email,
			c.*,
			d.product_id,
			d.product_model,d.unit,
			e.unit_short_name,
			f.variant_name,
			g.title as product_name
			');
		$this->db->from('pre_order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->join('seller_pre_order c','c.order_id = a.order_id');
		$this->db->join('product_information d','d.product_id = c.product_id');
		$this->db->join('unit e','e.unit_id = d.unit','left');
		$this->db->join('variant f','f.variant_id = c.variant_id','left');
		$this->db->join('product_title g','g.product_id = c.product_id','left');
		$this->db->where('a.order_id',$order_id);
		$this->db->where('g.lang_id',$language);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
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
	// Delete order Item
	public function delete_order($order_id=null,$order_no=null)
	{
		$result = $this->db->select('*')
		->from('seller_order')
		->where('order_id',$order_id)
		->get()
		->result();

		if ($result) {
			foreach ($result as $seller_order) {
				$this->db->set('quantity','quantity+'.$seller_order->quantity,FALSE)
				->where('product_id',$seller_order->product_id)
				->update('product_information');
			}
		}

		//Delete order table
		$this->db->where('order_id',$order_id);
		$this->db->delete('order'); 
		//Delete seller_order table
		$this->db->where('order_id',$order_id);
		$this->db->delete('seller_order');
		//Delete order table
		$this->db->where('order_no',$order_no);
		$this->db->delete('invoice'); 
		//Delete seller_order table
		$this->db->where('order_no',$order_no);
		$this->db->delete('invoice_details'); 


		//Order tax summary delete
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_summary'); 
		//Order tax details delete
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_details'); 
		return true;
	}

	//Delete order Item
	public function delete_pre_order($order_id)
	{
		$result = $this->db->select('*')
		->from('seller_pre_order')
		->where('order_id',$order_id)
		->get()
		->result();

		if ($result) {
			foreach ($result as $seller_order) {
				$this->db->set('pre_order_quantity','pre_order_quantity+'.$seller_order->quantity,FALSE)
				->where('product_id',$seller_order->product_id)
				->update('product_information');
			}
		}

		//Delete order table
		$this->db->where('order_id',$order_id);
		$this->db->delete('pre_order'); 
		//Delete seller_order table
		$this->db->where('order_id',$order_id);
		$this->db->delete('seller_pre_order'); 
		//Order tax summary delete
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_summary'); 
		//Order tax details delete
		$this->db->where('order_id',$order_id);
		$this->db->delete('order_tax_col_details'); 
		return true;
	}
	public function order_search_list($cat_id,$company_id)
	{
		$this->db->select('a.*,b.sub_category_name,c.category_name');
		$this->db->from('orders a');
		$this->db->join('order_sub_category b','b.sub_category_id = a.sub_category_id');
		$this->db->join('order_category c','c.category_id = b.category_id');
		$this->db->where('a.sister_company_id',$company_id);
		$this->db->where('c.category_id',$cat_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	//Get total product
	public function get_total_product($product_id){
		$this->db->select('
			product_name,
			product_id,
			supplier_price,
			price,
			supplier_id,
			unit,
			variants,
			product_model,
			unit.unit_short_name
			');
		$this->db->from('product_information');
		$this->db->join('unit','unit.unit_id = product_information.unit','left');
		$this->db->where(array('product_id' => $product_id,'status' => 1)); 
		$product_information = $this->db->get()->row();

		$html = "";
		if (!empty($product_information->variants)) {
			$exploded = explode(',',$product_information->variants);
			$html .="<option>".display('select_variant')."</option>";
			foreach ($exploded as $elem) {
				$this->db->select('*');
				$this->db->from('variant');
				$this->db->where('variant_id',$elem);
				$this->db->order_by('variant_name','asc');
				$result = $this->db->get()->row();

				$html .="<option value=".$result->variant_id.">".$result->variant_name."</option>";
			}
		}

		$this->db->select('tax.*,tax_product_service.product_id,tax_percentage');
		$this->db->from('tax_product_service');
		$this->db->join('tax','tax_product_service.tax_id = tax.tax_id','left');
		$this->db->where('tax_product_service.product_id',$product_id);
		$tax_information = $this->db->get()->result();

		//New tax calculation for discount
		if(!empty($tax_information)){
			foreach($tax_information as $k=>$v){
				if ($v->tax_id == 'H5MQN4NXJBSDX4L') {
					$tax['cgst_tax'] 	= ($v->tax_percentage)/100;
					$tax['cgst_name']	= $v->tax_name; 
					$tax['cgst_id']	 	= $v->tax_id; 
				}elseif($v->tax_id == '52C2SKCKGQY6Q9J'){
					$tax['sgst_tax'] 	= ($v->tax_percentage)/100;
					$tax['sgst_name']	= $v->tax_name; 
					$tax['sgst_id']	 	= $v->tax_id; 
				}elseif($v->tax_id == '5SN9PRWPN131T4V'){
					$tax['igst_tax'] 	= ($v->tax_percentage)/100;
					$tax['igst_name']	= $v->tax_name; 
					$tax['igst_id']		= $v->tax_id; 
				}
			}
		}

		$purchase = $this->db->select("SUM(quantity) as totalPurchaseQnty")
		->from('product_purchase_details')
		->where('product_id',$product_id)
		->get()
		->row();

		$sales = $this->db->select("SUM(quantity) as totalSalesQnty")
		->from('invoice_details')
		->where('product_id',$product_id)
		->get()
		->row();

		$stock = $purchase->totalPurchaseQnty - $sales->totalSalesQnty;


		$data2 = array(
			'total_product'	=> $stock, 
			'supplier_price'=> $product_information->supplier_price, 
			'price' 		=> $product_information->price, 
			'variant_id' 	=> $product_information->variants, 
			'supplier_id' 	=> $product_information->supplier_id, 
			'product_name' 	=> $product_information->product_name, 
			'product_model' => $product_information->product_model, 
			'product_id' 	=> $product_information->product_id, 
			'variant' 		=> $html, 
			'sgst_tax' 		=> (!empty($tax['sgst_tax'])?$tax['sgst_tax']:null), 
			'cgst_tax' 		=> (!empty($tax['cgst_tax'])?$tax['cgst_tax']:null), 
			'igst_tax' 		=> (!empty($tax['igst_tax'])?$tax['igst_tax']:null), 
			'cgst_id' 		=> (!empty($tax['cgst_id'])?$tax['cgst_id']:null), 
			'sgst_id' 		=> (!empty($tax['sgst_id'])?$tax['sgst_id']:null), 
			'igst_id' 		=> (!empty($tax['igst_id'])?$tax['igst_id']:null), 
			'unit' 			=> $product_information->unit_short_name, 
		);

		return $data2;
	}
	//NUMBER GENERATOR
	public function number_generator()
	{
		$this->db->select_max('invoice');
		$query 		= $this->db->get('invoice');	
		$result 	= $query->result_array();	
		$invoice_no 	= $result[0]['invoice'];
		if (!empty($invoice_no)) {
			$invoice_no = $invoice_no + 1;	
		}else{
			$invoice_no = 1000;
		}
		return $invoice_no;		
	}
	//Product Search
	public function product_search($product_name,$category_id)
	{

		$this->db->select('*');
		$this->db->from('product_information');
		if (!empty($product_name)) {
			$this->db->like('product_name', $product_name, 'both');
		}
		
		if (!empty($category_id)) {
			$this->db->where('category_id',$category_id);
		}

		$this->db->where('status',1);
		$this->db->order_by('product_name','asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}else{
			return false;
		}
	}
	//Monthly order status
	public function monthly_order_deliver($day=null,$status=null){

		$result = $this->db->query("
			SELECT count(id) as total_order FROM `order`
			WHERE MONTH(date) = MONTH(CURRENT_TIMESTAMP) 
			AND YEAR(date) = YEAR(CURRENT_TIMESTAMP)
			AND DAY(date) = $day
			AND order_status = $status;
			");

		return $result->row();
	}

	//Yearly order status
	public function yearly_order_status($month=null,$status=null){

		$result = $this->db->query("
			SELECT count(id) as total_order FROM `order`
			WHERE MONTH(date)  = $month
			AND YEAR(date) = YEAR(CURRENT_TIMESTAMP)
			AND order_status = $status;
			");

		return $result->row();
	}	

	//Yearly orderded invoice
	public function yearly_order_invoice($month=null){

		$result = $this->db->query("
			SELECT count(id) as total_invoice FROM `invoice`
			WHERE MONTH(date)  = $month
			AND YEAR(date) = YEAR(CURRENT_TIMESTAMP);
			");

		return $result->row();
	}

	//Monthly total invoice
	public function monthly_total_invoice($day=null){

		$result = $this->db->query("
			SELECT count(id) as total_invoice FROM `invoice`
			WHERE MONTH(date) = MONTH(CURRENT_TIMESTAMP) 
			AND YEAR(date) = YEAR(CURRENT_TIMESTAMP)
			AND DAY(date) = $day
			");

		return $result->row();
	}

	//State list
	public function state_list(){
		$state = $this->db->select('*')
		->from('states')
		->where('country_id',37)
		->get()
		->result();
		return $state;
	}

	//Method list
	public function shipping_method(){
		$ship_method = $this->db->select('*')
		->from('shipping_method')
		->get()
		->result();
		return $ship_method;
	}

	//get total customer by state
	public function get_total_customer($state_name=null){
		return $this->db->select('*')
		->from('customer_information')
		->where('state',$state_name)
		->get()
		->num_rows();
	}
	//get total customer by state
	public function get_customer_prcentage($state_name=null){

		$total_customer = 0;
		$state_customer = 0;
		$percentage = 0;

		$total_customer = $this->db->select('*')
		->from('customer_information')
		->where('country',COUNTRY_ID)
		->get()
		->num_rows();

		$state_customer = $this->db->select('*')
		->from('customer_information')
		->where('state',$state_name)
		->get()
		->num_rows();

		return $percentage = floor(($state_customer/$total_customer)*100);
	}
	//Get total purcentage 
	public function get_percentage_of_shipping(){
		$result1 = $this->db->select('count(a.id) as total_order,shipping_id')
		->from('order a')
		->join('shipping_method b','a.shipping_id = b.method_id')
		->group_by('shipping_id')
		->get()
		->result();

		return $result1;
	}
	//Order traking
	public function order_traking($order_id=null){

		$this->db->set('status',1)
		->where('order_id',$order_id)
		->update('order_tracking');

		$order_traking = $this->db->select('*')
		->from('order_tracking')
		->where('order_id',$order_id)
		->order_by('id')
		->get()
		->result();
		return $order_traking;
	}

	//Order traking count
	public function order_traking_count($order_id=null){
		$order_traking = $this->db->select('*')
		->from('order_tracking')
		->where('order_id',$order_id)
		->order_by('id')
		->get()
		->num_rows();
		return $order_traking;
	}
	//Order tracking count for admin
	public function order_traking_count_admin($order_id='')
	{
		$order_traking = $this->db->select('*')
		->from('order_tracking')
		->where('order_id',$order_id)
		->where('customer_id !=',null)
		->where('status',0)
		->get()
		->num_rows();
		return $order_traking;
	}
	//Get order no
	public function get_order_no($order_id=null){
		$order_no = $this->db->select('*')
		->from('order a')
		->join('customer_information b','a.customer_id = b.customer_id')
		->where('a.order_id',$order_id)
		->get()
		->row();
		if ($order_no) {
			return $order_no;
		}else{
			return false;
		}
		
	}
	//Order message
	public function order_message($order_id=null){
		$order_message = $this->db->select('*')
		->from('order_tracking')
		->where('order_id',$order_id)
		->where('order_status',7)
		->order_by('id','desc')
		->limit(1)
		->get()
		->row();
		
		if ($order_message) {
			return $order_message->message;
		}else{
			return false;
		}
	}	
	//Order message
	public function customer_last_message($order_id=null,$customer_id=null){
		$order_message = $this->db->select('*')
		->from('order_tracking')
		->where('order_id',$order_id)
		->where('customer_id',$customer_id)
		->where('status',0)
		->order_by('id','desc')
		->limit(1)
		->get()
		->row();
		
		if ($order_message) {
			return $order_message->message;
		}else{
			return false;
		}
	}

	//NUMBER GENERATOR
	public function order_number_generator()
	{
		$this->db->select_max('order_no');
		$query = $this->db->get('order');	
		$result = $query->result_array();
		$order_no = $result[0]['order_no'];
		if ($order_no !='') {
			$order_no = $order_no + 1;	
		}else{
			$order_no = 100000;
		}
		return $order_no;		
	}
	//NUMBER GENERATOR
	public function customer_number_generator()
	{
		$this->db->select_max('customer_code');
		$query = $this->db->get('customer_information');	
		$result = $query->result_array();	
		$customer_code = $result[0]['customer_code'];
		if ($customer_code !='') {
			$customer_code = $customer_code + 1;	
		}else{
			$customer_code = 1000;
		}
		return $customer_code;		
	}
	// Get payment method name
	public function get_payment_method($order_id)
	{
		$this->db->where('order_id', $order_id);
		$result = $this->db->get('order_payment')->row_array();
		return $result;
	}
	public function get_payment_method_name($payment_id)
	{
		$this->db->where('id', $payment_id);
		$this->db->or_where('code', $payment_id);
		$result = $this->db->get('payment_gateway')->row_array();
		if(!empty($result)){
			return $result['agent'];
		} else return '';
	}
}