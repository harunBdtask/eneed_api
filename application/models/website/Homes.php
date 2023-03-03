<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Homes extends CI_Model {

	private $table  = "language";
	private $phrase = "phrase";
	public $lang_id;
	public $default_lang = 'english';

	public function __construct()
	{
		parent::__construct();
		$this->lang_id = $this->session->userdata('language');
		if(empty($this->lang_id)){
			$this->lang_id = $this->default_lang;
		}
	}
	//Parent Category List
	public function parent_category_list()
	{

		if($this->lang_id != $this->default_lang){
			$this->db->select("pc.*, (SELECT trans_category_name FROM product_category_translation WHERE lang='".$this->lang_id."' AND category_id = pc.category_id) as trans_category_name");
			$this->db->from('product_category pc');
			$this->db->where('pc.cat_type',1);
			$this->db->where('pc.status',1);
			$this->db->order_by('pc.menu_pos');
			//$this->db->limit('9');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result();	
			}
		}else{
			$this->db->select('*');
			$this->db->from('product_category');
			$this->db->where('cat_type',1);
			$this->db->where('status',1);
			$this->db->order_by('menu_pos');
			//$this->db->limit('9');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result();	
			}
		}
		return false;
	}
	//Top Category List
	public function top_category_list()
	{

		if($this->lang_id != $this->default_lang){
			$this->db->select("pc.*, (SELECT trans_category_name FROM product_category_translation WHERE lang='".$this->lang_id."' AND category_id = pc.category_id) as trans_category_name");
			$this->db->from('product_category pc');
			$this->db->where('pc.cat_type',1);
			$this->db->where('pc.status',1);
			$this->db->where('pc.top_menu',1);
			$this->db->order_by('pc.menu_pos');
			$this->db->limit('9');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result();	
			}
		}else{
			$this->db->select('*');
			$this->db->from('product_category');
			$this->db->where('cat_type',1);
			$this->db->where('status',1);
			$this->db->where('top_menu',1);
			$this->db->order_by('menu_pos');
			$this->db->limit('9');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result();	
			}
		}
		return false;
	}
	//Category list
	public function category_list()
	{

		if($this->lang_id != $this->default_lang){
			$this->db->select("pc.*, (SELECT trans_category_name FROM product_category_translation WHERE lang='".$this->lang_id."' AND category_id = pc.category_id) as trans_category_name");
			$this->db->from('product_category pc');
			$this->db->where('pc.status', 1);
			$this->db->order_by('pc.category_name','asc');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result_array();	
			}
		}else{
			$this->db->select('*');
			$this->db->from('product_category');
			$this->db->where('status', 1);
			$this->db->order_by('category_name','asc');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result_array();	
			}
		}
		
		return false;
	}	
	//Category list by id
	public function category_list_by_id($category_id=null)
	{
		if($this->lang_id != $this->default_lang){
			$this->db->select("a.*, b.trans_category_name");
			$this->db->from('product_category a');
			$this->db->join('product_category_translation b', 'a.category_id=b.category_id','left');
			$this->db->where('a.status', 1);
			$this->db->where('a.category_id',$category_id);
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->row();	
			}
		}else{

			$this->db->select('*');
			$this->db->from('product_category');
			$this->db->where('category_id',$category_id);
			$this->db->where('status', 1);
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->row();	
			}
		}
		return false;
	}
	//All sub category list
	public function get_sub_category($category_id){
		$main_cat = array();

			$sub_cate_gory = $this->db->select('a.category_id, a.category_name, b.trans_category_name')
			->from('product_category a')
			->join('product_category_translation b',"b.category_id=a.category_id AND b.lang='$this->lang_id'",'left')
			->where('a.parent_category_id',$category_id)
			->where('a.status', 1)
			->order_by('a.menu_pos')
			->where('a.cat_type',2)
			->get()
			->result();


		if ($sub_cate_gory) {
			foreach ($sub_cate_gory as $s_category) {
				$sub_cat = array();

				if ($s_category) {
					$parent_cat = array(
						'category_id'   => $s_category->category_id, 
						'category_name' => $s_category->category_name,
						'trans_category_name' => $s_category->trans_category_name
					);

					$l_category = $this->db->select('a.category_id, a.category_name, b.trans_category_name')
					->from('product_category a')
					->where('a.parent_category_id',$s_category->category_id)
					->join('product_category_translation b',"b.category_id=a.category_id AND b.lang='$this->lang_id'",'left')
					->order_by('a.menu_pos')
					->where('a.cat_type',2)
					->where('a.status', 1)
					->get()
					->result();
					
					if ($l_category) {
						foreach ($l_category as $category) {
							if ($category) {
								$data = array(
									'category_id'   => $category->category_id, 
									'category_name' => $category->category_name,
									'trans_category_name' => $category->trans_category_name
								);
								array_push($sub_cat, $data);
							}
						}
					}
				}

				$parent_cat['categorieslevelone'] = $sub_cat;
				array_push($main_cat, $parent_cat);
			}
		}
		return $main_cat;
	}
	//One level subcategory list
	public function get_one_level_sub_category($category_id){
		$main_cat = array();

		if($this->lang_id != $this->default_lang){

			$sub_cate_gory = $this->db->select('a.category_id, a.category_name,(SELECT trans_category_name FROM product_category_translation WHERE category_id=a.category_id AND lang = "'.$this->lang_id.'") AS trans_category_name')
			->from('product_category a')
			->where('a.parent_category_id',$category_id)
			->order_by('a.menu_pos')
			->where('a.cat_type',2)
			->where('a.status', 1)
			->get()
			->result();

		}else{

			$sub_cate_gory = $this->db->select('category_id, category_name')
			->from('product_category')
			->where('parent_category_id',$category_id)
			->where('status', 1)
			->order_by('menu_pos')
			->where('cat_type',2)
			->get()
			->result();
		}


		if ($sub_cate_gory) {
			foreach ($sub_cate_gory as $s_category) {
				if ($s_category) {
					$parent_cat = array(
						'category_id'   => $s_category->category_id, 
						'category_name' => $s_category->category_name,
						'trans_category_name' => @$s_category->trans_category_name
					);
				}
				array_push($main_cat, $parent_cat);
			}
		}
		return $main_cat;
	}
	//Featured category
	public function get_featured_category($category_id){
		$main_cat = array();

		if($this->lang_id != $this->default_lang){

			$sub_cate_gory = $this->db->select('a.category_id, a.category_name, a.menu_pos, a.details, a.cat_image,b.trans_category_name')
			->from('product_category a')
			->join('product_category_translation b',"a.category_id=b.category_id AND b.lang='$this->lang_id'", 'left')
			->where('a.parent_category_id',$category_id)
			->order_by('a.menu_pos')
			->where('a.cat_type',2)
			->where('a.featured',1)
			->where('a.status', 1)
			->limit(6)
			->get()
			->result();

		} else {

			$sub_cate_gory = $this->db->select('category_id, category_name, menu_pos, details, cat_image')
			->from('product_category')
			->where('parent_category_id',$category_id)
			->order_by('menu_pos')
			->where('cat_type',2)
			->where('featured',1)
			->where('status', 1)
			->limit(6)
			->get()
			->result();
		}

		if ($sub_cate_gory) {
			foreach ($sub_cate_gory as $s_category) {
				$parent_cat = array(
					'category_id'   => $s_category->category_id, 
					'category_name' => $s_category->category_name,
					'menu_pos' 		=> $s_category->menu_pos,
					'details' 		=> $s_category->details,
					'cat_image' 	=> $s_category->cat_image,
					'trans_cat_name' 	=> (isset($s_category->trans_category_name)?$s_category->trans_category_name:''),
				);

				if($this->lang_id != $this->default_lang){
					$l_category = $this->db->select('a.category_id, a.category_name, a.menu_pos, a.details, a.cat_image,b.trans_category_name')
					->from('product_category a')
					->join('product_category_translation b',"a.category_id=b.category_id AND b.lang='$this->lang_id'", 'left')
					->where('a.parent_category_id',$s_category->category_id)
					->order_by('a.menu_pos')
					->where('a.cat_type',2)
					->where('a.featured',1)
					->where('a.status', 1)
					->limit(5)
					->get()
					->result();
				} else {
					$l_category = $this->db->select('category_id, category_name, menu_pos, details, cat_image')
					->from('product_category')
					->where('parent_category_id',$s_category->category_id)
					->order_by('menu_pos')
					->where('cat_type',2)
					->where('featured',1)
					->where('status', 1)
					->limit(5)
					->get()
					->result();
				}

				if ($l_category) {
					foreach ($l_category as $category) {
						$data = array(
							'category_id'   => $category->category_id, 
							'category_name' => $category->category_name,
							'menu_pos' 		=> $category->menu_pos,
							'details' 		=> $category->details,
							'cat_image' 	=> $category->cat_image,
							'trans_cat_name' 	=> (isset($category->trans_category_name)?$category->trans_category_name:''),
						);
						array_push($main_cat, $data);
					}
					
				}
				array_push($main_cat, $parent_cat);
			}
		}
		return $main_cat;
	}
	//Get one level featured category
	public function get_one_level_featured_category($category_id){
		$main_cat = array();

		if($this->lang_id != $this->default_lang){
			$sub_cate_gory = $this->db->select("a.category_id, a.category_name, a.menu_pos, a.details, a.cat_image, (SELECT trans_category_name FROM product_category_translation WHERE lang='".$this->lang_id."' AND category_id = a.category_id) as trans_category_name")
			->from('product_category a')
			->where('a.parent_category_id',$category_id)
			->order_by('a.menu_pos')
			->where('a.cat_type',2)
			->where('a.featured',1)
			->where('a.status', 1)
			->limit(12)
			->get()
			->result();
		} else {
			$sub_cate_gory = $this->db->select('*')
			->from('product_category')
			->where('parent_category_id',$category_id)
			->order_by('menu_pos')
			->where('cat_type',2)
			->where('featured',1)
			->where('status', 1)
			->limit(12)
			->get()
			->result();
		}

		if ($sub_cate_gory) {
			foreach ($sub_cate_gory as $s_category) {
				$parent_cat = array(
					'category_id'   => $s_category->category_id, 
					'category_name' => $s_category->category_name,
					'menu_pos' 		=> $s_category->menu_pos,
					'details' 		=> $s_category->details,
					'cat_image' 	=> $s_category->cat_image,
					'trans_category_name' 	=> @$s_category->trans_category_name,
				);
				array_push($main_cat, $parent_cat);
			}
		}
		return $main_cat;
	}
	//Featured category for block 2
	public function get_featured_category2($category_id){
		$main_cat = array();
		if($this->lang_id != $this->default_lang){

			$sub_cate_gory = $this->db->select('a.category_id, a.category_name, a.menu_pos, a.details, a.cat_image,(SELECT b.trans_category_name FROM product_category_translation b WHERE b.category_id=a.category_id and b.lang="'.$this->lang_id.'") as trans_category_name')
			->from('product_category a')
			// ->join('product_category_translation b',"a.category_id=b.category_id AND b.lang='$this->lang_id'", 'left')
			->where('a.parent_category_id',$category_id)
			->order_by('a.menu_pos')
			->where('a.cat_type',2)
			->where('a.featured',1)
			->where('a.status', 1)
			->limit(6,2)
			->get()
			->result();
		} else {
			$sub_cate_gory = $this->db->select('category_id, category_name, menu_pos, details, cat_image')
			->from('product_category')
			->where('parent_category_id',$category_id)
			->order_by('menu_pos')
			->where('cat_type',2)
			->where('featured',1)
			->where('status', 1)
			->limit(6,2)
			->get()
			->result();
		}


		if ($sub_cate_gory) {
			foreach ($sub_cate_gory as $s_category) {
				$parent_cat = array(
					'category_id'   => $s_category->category_id, 
					'category_name' => $s_category->category_name,
					'menu_pos' 		=> $s_category->menu_pos,
					'details' 		=> $s_category->details,
					'cat_image' 	=> $s_category->cat_image,
					'trans_cat_name' 	=> (isset($s_category->trans_category_name)?$s_category->trans_category_name:'')
				);

		// if($this->lang_id != $this->default_lang){

			$l_category = $this->db->select('a.category_id, a.category_name, a.menu_pos, a.details, a.cat_image,b.trans_category_name')
				->from('product_category a')
				->join('product_category_translation b',"a.category_id=b.category_id AND b.lang='$this->lang_id'", 'left')
				->where('a.parent_category_id',$s_category->category_id)
				->order_by('a.menu_pos','asc')
				->where('a.cat_type',2)
				->where('a.featured',1)
				->where('a.status', 1)
				->limit(5)
				->get()
				->result();



				if ($l_category) {
					foreach ($l_category as $category) {
						$data = array(
							'category_id'   => $category->category_id, 
							'category_name' => $category->category_name,
							'menu_pos' 		=> $category->menu_pos,
							'details' 		=> $category->details,
							'cat_image' 	=> $category->cat_image,
							'trans_cat_name' 	=> (isset($category->trans_category_name)?$category->trans_category_name:'')
						);
						array_push($main_cat, $data);
					}
					
				}
				array_push($main_cat, $parent_cat);
			}
		}
		return $main_cat;
	}
	//Featured category single for block 2
	public function get_featured_category2_single($category_id){
		$main_cat = array();
		$sub_cate_gory = $this->db->select('a.*,b.trans_category_name')
		->from('product_category a')
		->join('product_category_translation b',"a.category_id=b.category_id AND b.lang='$this->lang_id'", 'left')
		->where('a.parent_category_id',$category_id)
		->order_by('a.menu_pos')
		->where('a.cat_type',2)
		->where('a.featured',1)
		->where('a.status', 1)
		->limit(1)
		->get()
		->row();

		return $sub_cate_gory;
	}
	//Get promotional category
	public function promo_cat_list(){
		$promo_cat_list = $this->db->select('*')
		->from('product_category')
		->order_by('promo_date','ASC')
		->where('on_promotion',1)
		->where('home_promo',1)
		->where('status', 1)
		->where('promo_date >',date('Y-m-d'))
		// ->limit(1)
		->get()
		->result_array();

		return $promo_cat_list;
	}	
	//Get featured category
	public function featured_cat_list(){
		$featured_cat = $this->db->select('*')
		->from('product_category')
		->order_by('menu_pos')
		->where('featured',1)
		->where('status', 1)
		->limit(3)
		->get()
		->result();

		return $featured_cat;
	}	
	//Get home category
	public function home_cat_list(){

		if($this->lang_id != $this->default_lang){
			$featured_cat =  $this->db->select("a.*, b.trans_category_name")
				->from('product_category a')
				->join('product_category_translation b', "b.category_id=a.category_id AND b.lang='$this->lang_id'", 'left')
				->order_by('a.menu_pos')
				->where('a.home_page',1)
				->where('a.status', 1)
				->limit(18)
				->get()
				->result();
		}else{
			$featured_cat = $this->db->select('*')
			->from('product_category')
			->order_by('menu_pos')
			->where('home_page',1)
			->where('status', 1)
			->limit(18)
			->get()
			->result();
		}

		return $featured_cat;
	}
	//Get all subcategory lsit
	public function get_sub_category_list($category_id){
		$main_cat = array();
		$sub_cate_gory = $this->db->select('*')
		->from('product_category')
		->where('parent_category_id',$category_id)
		->order_by('menu_pos')
		->where('cat_type',2)
		->where('status', 1)
		->limit(5)
		->get()
		->result();

		if ($sub_cate_gory) {
			foreach ($sub_cate_gory as $s_category) {
				$parent_cat = array(
					'category_id'   => $s_category->category_id, 
					'category_name' => $s_category->category_name,
					'menu_pos' 		=> $s_category->menu_pos,
					'details' 		=> $s_category->details,
					'cat_favicon' 	=> $s_category->cat_favicon,
					'cat_image' 	=> $s_category->cat_image,
				);

				$l_category = $this->db->select('*')
				->from('product_category')
				->where('parent_category_id',$s_category->category_id)
				->order_by('menu_pos')
				->where('cat_type',2)
				->where('status', 1)
				->limit(5)
				->get()
				->result();

				if ($l_category) {
					foreach ($l_category as $category) {
						$data = array(
							'category_id'   => $category->category_id, 
							'category_name' => $category->category_name,
							'menu_pos' 		=> $category->menu_pos,
							'details' 		=> $category->details,
							'cat_favicon' 	=> $category->cat_favicon,
							'cat_image' 	=> $category->cat_image,
						);
						array_push($main_cat, $data);
					}
					
				}
				array_push($main_cat, $parent_cat);
			}
		}
		return $main_cat;
	}
	//Best sales list
	public function best_sales()
	{
		if($this->lang_id != $this->default_lang) {

			$where = "(a.quantity > 0 OR a.pre_order = 1)";
			$this->db->select('a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,a.quantity,b.product_id,b.title,c.category_name,d.first_name,d.last_name,e.brand_name, pi.image_name,pct.trans_category_name');
			$this->db->from('product_information a');
			$this->db->join('product_title b','a.product_id = b.product_id','left');
			$this->db->join('product_category c','a.category_id = c.category_id','left');
			$this->db->join('seller_information d','a.seller_id = d.seller_id','left');
			$this->db->join('brand e','a.brand_id = e.brand_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->join('product_category_translation pct',"pct.category_id = c.category_id AND pct.lang='$this->lang_id'",'left');
			$this->db->where('a.status','2');
			$this->db->where('a.best_sale','1');
			$this->db->where('b.lang_id',$this->lang_id);
			$this->db->where($where);
			$this->db->group_by('a.product_id');
			$this->db->order_by('a.product_info_id','desc');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result();	
			}

		} else{

			$where = "(a.quantity > 0 OR a.pre_order = 1)";
			$this->db->select('a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,a.quantity,b.product_id,b.title,c.category_name,d.first_name,d.last_name,e.brand_name, pi.image_name');
			$this->db->from('product_information a');
			$this->db->join('product_title b','a.product_id = b.product_id','left');
			$this->db->join('product_category c','a.category_id = c.category_id','left');
			$this->db->join('seller_information d','a.seller_id = d.seller_id','left');
			$this->db->join('brand e','a.brand_id = e.brand_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->where('a.status','2');
			$this->db->where('a.best_sale','1');
			$this->db->where('b.lang_id',$this->lang_id);
			$this->db->where($where);
			$this->db->group_by('a.product_id');
			$this->db->order_by('a.product_info_id','desc');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result();	
			}
		}
		
		return false;
	}
	//Most popular product
	public function most_popular_product()
	{

		if($this->lang_id != $this->default_lang) {

			$where = "(b.quantity > 0 OR b.pre_order = 1)";
			$result = $this->db->select('
					b.*,
					c.title,
					d.category_name,
					e.first_name,
					e.last_name,
					f.brand_name,
					sum(a.quantity) as total_quantity,
					count(a.product_id) as total_product,
					pi.image_name,
					pct.trans_category_name
					')
				->from('seller_order a')
				->join('product_information b','a.product_id = b.product_id','left')
				->join('product_title c','c.product_id = b.product_id','left')
				->join('product_category d','d.category_id = b.category_id','left')
				->join('seller_information e','e.seller_id = b.seller_id','left')
				->join('brand f','f.brand_id = b.brand_id','left')
				->join('product_image pi','pi.product_id = b.product_id','left')
				->join('product_category_translation pct',"pct.category_id = d.category_id AND pct.lang='$this->lang_id'",'left')
				->where('b.status','2')
				->where('c.lang_id',$this->lang_id)
				->where($where)
				->group_by('a.product_id')
				->order_by('total_quantity','desc')
				->limit('15')
				->get()
				->result();

		}else {
		
			$where = "(b.quantity > 0 OR b.pre_order = 1)";
			$result = $this->db->select('
						b.*,
						c.title,
						d.category_name,
						e.first_name,
						e.last_name,
						f.brand_name,
						sum(a.quantity) as total_quantity,
						count(a.product_id) as total_product,
						pi.image_name
						')
					->from('seller_order a')
					->join('product_information b','a.product_id = b.product_id','left')
					->join('product_title c','c.product_id = b.product_id','left')
					->join('product_category d','d.category_id = b.category_id','left')
					->join('seller_information e','e.seller_id = b.seller_id','left')
					->join('brand f','f.brand_id = b.brand_id','left')
					->join('product_image pi','pi.product_id = b.product_id','left')
					->where('b.status','2')
					->where('c.lang_id',$this->lang_id)
					->where($where)
					->group_by('a.product_id')
					->order_by('total_quantity','desc')
					->limit('15')
					->get()
					->result();
			}

		if ($result) {
			return $result;
		}
		return false;
	}
	//Best merchant product
	public function best_merchant_product()
	{
		$lang_id = 0;
		$user_lang = $this->session->userdata('language');
		if (empty($user_lang)) {
			$lang_id = 'english';
		}else{
			$lang_id = $user_lang;
		}

		$result = $this->db->select('
			a.seller_id,
			SUM(a.quantity) as total_quantity,
			count(a.product_id) as total_product
			')
		->from('seller_order a')
		->group_by('a.seller_id')
		->order_by('total_quantity','desc')
		->limit('5')
		->get()
		->result_array();
		return $result;
		// if ($result) {
		// 	return $result;
		// }
		// return false;
	}
	//Get seller product by seller id
	public function get_seller_product($seller_id=null){


		if($this->lang_id != $this->default_lang) {

	        $where = "(a.quantity > 0 OR a.pre_order = 1)";
	        
			$this->db->select('
					a.*,
					b.category_name,
					c.first_name,
					c.last_name,
					c.seller_id,
					c.email,
					d.brand_name,
					e.title,
					pi.image_name,
					pct.trans_category_name
				');
			$this->db->from('product_information a');
			$this->db->join('product_category b','a.category_id = b.category_id','left');
			$this->db->join('seller_information c','a.seller_id = c.seller_id','left');
			$this->db->join('brand d','a.brand_id = d.brand_id','left');
			$this->db->join('product_title e','e.product_id = a.product_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->join('product_category_translation pct',"pct.category_id = b.category_id AND pct.lang='$this->lang_id'",'left');
			$this->db->where('e.lang_id',$this->lang_id);
			$this->db->where('a.status',2);
			$this->db->where('a.seller_id',$seller_id);
			$this->db->where($where);
			$this->db->group_by('a.product_id');
			$this->db->limit('3');
			$this->db->order_by('product_info_id','desc');
			$query = $this->db->get();
		}else{

	        $where = "(a.quantity > 0 OR a.pre_order = 1)";
	        
			$this->db->select('
					a.*,
					b.category_name,
					c.first_name,
					c.last_name,
					c.seller_id,
					c.email,
					d.brand_name,
					e.title,
					pi.image_name
				');
			$this->db->from('product_information a');
			$this->db->join('product_category b','a.category_id = b.category_id','left');
			$this->db->join('seller_information c','a.seller_id = c.seller_id','left');
			$this->db->join('brand d','a.brand_id = d.brand_id','left');
			$this->db->join('product_title e','e.product_id = a.product_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->join('product_category_translation pct',"pct.category_id = b.category_id AND pct.lang='$this->lang_id'",'left');
			$this->db->where('e.lang_id',$this->lang_id);
			$this->db->where('a.status',2);
			$this->db->where('a.seller_id',$seller_id);
			$this->db->where($where);
			$this->db->group_by('a.product_id');
			$this->db->limit('3');
			$this->db->order_by('product_info_id','desc');
			$query = $this->db->get();
		}
		return $query->result();
	}



	public function get_seller_product_list($seller_ids=null){

		if($this->lang_id != $this->default_lang) {
	        $where = "(a.quantity > 0 OR a.pre_order = 1)";
			$this->db->select('
					a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,a.quantity,
					b.category_name,
					c.first_name,
					c.last_name,
					c.seller_id,
					c.email,
					d.brand_name,
					e.title,
					pi.image_name,
					pct.trans_category_name
				');
			$this->db->from('product_information a');
			$this->db->join('product_category b','a.category_id = b.category_id','left');
			$this->db->join('seller_information c','a.seller_id = c.seller_id','left');
			$this->db->join('brand d','a.brand_id = d.brand_id','left');
			$this->db->join('product_title e','e.product_id = a.product_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->join('product_category_translation pct',"pct.category_id = b.category_id AND pct.lang='$this->lang_id'",'left');
			$this->db->where('e.lang_id',$this->lang_id);
			$this->db->where('a.status',2);
			if(!empty($seller_ids)){
			$this->db->where_in('a.seller_id',$seller_ids);
			}
			$this->db->where($where);
			$this->db->group_by('a.product_id');
			$this->db->limit('10');
			$this->db->order_by('a.product_info_id','desc');
			$query = $this->db->get();
		}else{

	        $where = "(a.quantity > 0 OR a.pre_order = 1)";

			$this->db->select('
					a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,a.quantity,
					b.category_name,
					c.first_name,
					c.last_name,
					c.seller_id,
					c.email,
					d.brand_name,
					e.title,
					pi.image_name
				');
			$this->db->from('product_information a');
			$this->db->join('product_category b','a.category_id = b.category_id','left');
			$this->db->join('seller_information c','a.seller_id = c.seller_id','left');
			$this->db->join('brand d','a.brand_id = d.brand_id','left');
			$this->db->join('product_title e','e.product_id = a.product_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->join('product_category_translation pct',"pct.category_id = b.category_id AND pct.lang='$this->lang_id'",'left');
			$this->db->where('e.lang_id',$this->lang_id);
			$this->db->where('a.status',2);
			if(!empty($seller_ids)){
			$this->db->where_in('a.seller_id',$seller_ids);
			}
			$this->db->where($where);
			$this->db->group_by('a.product_id');
			$this->db->limit('10');
			$this->db->order_by('a.product_info_id','desc');
			$query = $this->db->get();
		}
		return $query->result();
	}

    public function get_block_product($category_id)
    {
        if($this->lang_id != $this->default_lang) {



            $this->db->select('
					a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,a.quantity,
					(SELECT category_name FROM `product_category` WHERE `category_id` = "'.$category_id. '") AS category_name,					
					e.title,
					pi.image_name,
					pct.trans_category_name
				');
            $this->db->from('product_information a');
            $this->db->join('product_category b','a.category_id = b.category_id');
            $this->db->join('product_title e','e.product_id = a.product_id','left');
            $this->db->join('product_image pi','pi.product_id = a.product_id','left');
            $this->db->join('product_category_translation pct',"pct.category_id = b.category_id AND pct.lang='$this->lang_id'",'left');
            //$this->db->where('a.category_id', $category_id);
            $this->db->where('a.category_id IN ( SELECT category_id FROM product_category WHERE a.category_id = "'.$category_id. '" OR product_category.parent_category_id = "'.$category_id. '" AND product_category.status=1)');
            $this->db->where('e.lang_id',$this->lang_id);
            $this->db->where('a.status',2);
            $this->db->group_by('a.product_id');
            $this->db->order_by('rand()');
            $this->db->limit(10);
            $query = $this->db->get();
        }else{

            $this->db->select('
					a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,a.quantity,
					(SELECT category_name FROM `product_category` WHERE `category_id` = "'.$category_id. '") AS category_name,					
					e.title,
					pi.image_name,
					pct.trans_category_name
				');
            $this->db->from('product_information a');
            $this->db->join('product_category b','a.category_id = b.category_id');
            $this->db->join('product_title e','e.product_id = a.product_id','left');
            $this->db->join('product_image pi','pi.product_id = a.product_id','left');
            $this->db->join('product_category_translation pct',"pct.category_id = b.category_id AND pct.lang='$this->lang_id'",'left');
            //$this->db->where('a.category_id', $category_id);
            $this->db->where('a.category_id IN ( SELECT category_id FROM product_category WHERE a.category_id = "'.$category_id. '" OR product_category.parent_category_id = "'.$category_id. '" AND product_category.status=1)');
            $this->db->where('e.lang_id',$this->lang_id);
            $this->db->where('a.status',2);
            $this->db->group_by('a.product_id');
            $this->db->order_by('rand()');
            $this->db->limit(10);
            $query = $this->db->get();
        }
        return $query->result();
    }


	// Get Latest Product list
	//Best sales list
	public function latest_product()
	{
		if($this->lang_id != $this->default_lang) {
		
			$where = "(a.quantity > 0 OR a.pre_order = 1)";

			$this->db->select('a.*,b.*,c.category_name,d.first_name,d.last_name,e.brand_name, pi.image_name,pct.trans_category_name');
			$this->db->from('product_information a');
			$this->db->join('product_title b','a.product_id = b.product_id','left');
			$this->db->join('product_category c','a.category_id = c.category_id','left');
			$this->db->join('seller_information d','a.seller_id = d.seller_id','left');
			$this->db->join('brand e','a.brand_id = e.brand_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->join('product_category_translation pct',"pct.category_id = c.category_id AND pct.lang='$this->lang_id'",'left');
			$this->db->where('a.status','2');
			$this->db->where('b.lang_id',$this->lang_id);
			$this->db->where($where);
			$this->db->limit(15);
			$this->db->group_by('a.product_id');
			$this->db->order_by('a.product_info_id','desc');
			$query = $this->db->get();

		}else{

			$where = "(a.quantity > 0 OR a.pre_order = 1)";
			$this->db->select('a.*,b.*,c.category_name,d.first_name,d.last_name,e.brand_name, pi.image_name');
			$this->db->from('product_information a');
			$this->db->join('product_title b','a.product_id = b.product_id','left');
			$this->db->join('product_category c','a.category_id = c.category_id','left');
			$this->db->join('seller_information d','a.seller_id = d.seller_id','left');
			$this->db->join('brand e','a.brand_id = e.brand_id','left');
			$this->db->join('product_image pi','pi.product_id = a.product_id','left');
			$this->db->where('a.status','2');
			$this->db->where('b.lang_id',$this->lang_id);
			$this->db->where($where);
			$this->db->limit(15);
			$this->db->group_by('a.product_id');
			$this->db->order_by('a.product_info_id','desc');
			$query = $this->db->get();

		}
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}

	//Footer block
	public function footer_block()
	{
		$lang_id = $this->session->userdata('language');
		if(empty($lang_id)){
			$lang_id = 'english';
		}

		$this->db->select('*');
		$this->db->from('web_footer');
		$this->db->where('lang',$lang_id);
		$this->db->where('status',1);
		$this->db->order_by('position');
		$this->db->limit('4');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}		
	//Add Wishlist
	public function add_wishlist($data)
	{
		$user_id 	= $data['user_id'];
		$product_id = $data['product_id'];

		$this->db->select('*');
		$this->db->from('wishlist');
		$this->db->where('user_id',$data['user_id']);
		$this->db->where('product_id',$data['product_id']);
		$this->db->where('status',1);
		$query = $this->db->get();
		$r = $query->num_rows();

		if ($r > 0) {
			return false;
		}else{
			$result = $this->db->insert('wishlist',$data);
			return true;
		}
	}
	//Add Review
	public function add_review($data)
	{
		$reviewer_id = $data['reviewer_id'];
		$product_id  = $data['product_id'];

		$this->db->select('*');
		$this->db->from('product_review');
		$this->db->where('reviewer_id',$data['reviewer_id']);
		$this->db->where('product_id',$data['product_id']);
		$this->db->where('status',1);
		$query = $this->db->get();
		$r = $query->num_rows();

		if ($r > 0) {
			return false;
		}else{
			$this->db->insert('product_review',$data);
			return true;
		}
	}
	//Currency info
	public function currency_info()
	{
		$this->db->select('*');
		$this->db->from('currency_info');
		$this->db->order_by('currency_name');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	//Selected currency info
	public function selected_currency_info()
	{
		$cur_id = $this->session->userdata('currency_new_id');

		if (!empty($cur_id)) {
			$this->db->select('*');
			$this->db->from('currency_info');
			$this->db->where('currency_id',$cur_id);
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->row();	
			}
		}else{
			$this->db->select('*');
			$this->db->from('currency_info');
			$this->db->where('default_status','1');
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->row();	
			}
		}
		return false;
	}
	//Select default currency info
	function selected_default_currency_info()
	{
		$this->db->select('*');
		$this->db->from('currency_info');
		$this->db->where('default_status','1');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();	
		}
		return 0;
	}
	//Selecte country info
	public function selected_country_info()
	{
		$this->db->select('*');
		$this->db->from('countries');
		$this->db->order_by('id');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	//Selecte district info
	public function select_district_info($country_id)
	{

		$this->db->select('*');
		$this->db->from('states');
		$this->db->where('country_id',$country_id);
		$this->db->order_by('id');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	//Selecte district info
	public function select_city_info($state_id)
	{
		$this->db->select('*');
		$this->db->from('cities');
		$this->db->where('state_id',$state_id);
		$this->db->order_by('id');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	//shipping charge by city
	public function shippingChargeByCity($city_id)
	{

		$this->db->select('*');
		$this->db->from('shipping_method');
		$this->db->where('city',$city_id);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->row();
		}
		return false;

	}

	//Selecte shipping method
	public function select_shipping_method()
	{
		$this->db->select('*');
		$this->db->from('shipping_method');
//		$this->db->order_by('position');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	//Ship And Bill Entry
	public function ship_and_bill_entry($data)
	{
		$bill = $this->db->insert('customer_information',$data);
		if ($bill) {
			$result = $this->db->insert('shipping_info',$data);
			return true;	
		}
		return false;
	}
	//Billing Entry
	public function billing_entry($data)
	{
		$bill = $this->db->insert('customer_information',$data);
		if ($bill) {
			return true;	
		}
		return false;
	}
	//Shipping Entry
	public function shipping_entry($data,$order_id=NULL)
	{
		if($order_id != NULL){
			$data['order_id'] = $order_id;
		}
		$result = $this->db->insert('shipping_info',$data);
		if ($result) {
			return true;	
		}
		return false;
	}

	//Select country
	public function get_country($country_id)
	{
		$country = $this->db->select('*')
                ->from('countries')
                ->where('id', $country_id)
                ->get()
                ->row();
        return $country;
	}

	//Select state by country
	public function select_state_country($country_id=null)
	{
		//$country_id = 37;
		$this->db->select('*');
		$this->db->from('states');
		$this->db->where('country_id',$country_id);
		$this->db->order_by('name');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}

	//Select cities state
	public function select_cities_state($city_id=null)
	{
		//$country_id = 37;
		$this->db->select('state_id');
		$this->db->from('cities');
		$this->db->where('id',$city_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}

	//get city lists by states
	public function get_city_lists($state_id)
	{
		//$country_id = 37;
		$this->db->select('*');
		$this->db->from('cities');
		$this->db->where('state_id',$state_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}


	//Select ship state by country
	public function select_ship_state_country()
	{
		$ship_country = COUNTRY_ID;

		$this->db->select('*');
		$this->db->from('states');
		$this->db->where('country_id',$ship_country);
		$this->db->order_by('name');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	//Customer existing check
	public function check_customer($mobile){
		$this->db->select('*');
		$this->db->from('customer_information');
		$this->db->where('customer_mobile',$mobile);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();	
		}
		return false;
	}
	//Select home adds
	public function select_home_adds(){
		$this->db->select('*');
		$this->db->from('advertisement');
		$this->db->where('add_page','home');
		$this->db->where('status',1);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	
	//select_topAnimation
	public function select_topAnimation(){
		$this->db->select('*');
		$this->db->from('advertisement');
		$this->db->where('add_page','topAnimation');
		$this->db->where('status',1);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	
	//select_promotional_product
	public function select_promotional_product(){
		//last 3 product list added by harun 27-04-2021
		// $this->db->select('a.product_id as p_id, b.category_name as category_name, a.thumb_image_url as product_image, c.title as product_title');
		// $this->db->from('product_information a');
		// $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
		// $this->db->join('product_title c', 'a.product_id = c.product_id', 'left');
		// $this->db->where('c.lang_id', $this->lang_id);
		// $this->db->order_by('a.product_info_id', 'DESC');
		// $this->db->limit(30);
		//promotional product list
		$this->db->select('a.product_id as p_id, b.category_name as category_name, a.thumb_image_url as product_image, c.title as product_title');
		$this->db->from('product_information a');
		$this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
		$this->db->join('product_title c', 'a.product_id = c.product_id', 'left');
		$this->db->where('c.lang_id', $this->lang_id);
		$this->db->order_by('a.product_info_id', 'DESC');
		$this->db->where('a.on_promotion', 1);
		$this->db->where('a.promo_date >= ', date('Y-m-d'));
		$this->db->limit(30);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}

	public function select_home_add_by_block($position){
		$this->db->select('*');
		$this->db->from('advertisement');
		$this->db->where('add_page','home');
		$this->db->where('status',1);
		$this->db->where('adv_position',$position);
		$this->db->order_by('adv_position','desc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();
		}
		return false;
	}
	//Select Page ads
	public function select_page_ads($page = 'home'){
		$this->db->select('*');
		$this->db->from('advertisement');
		$this->db->where('add_page',$page);
		$this->db->where('status',1);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();	
		}
		return false;
	}
	//Product Details
	public function product_details($product_id){

		$lang_id   = 0;
		$user_lang = $this->session->userdata('language');
		if (empty($user_lang)) {
			$lang_id = 'english';
		}else{
			$lang_id = $user_lang;
		}

		$this->db->select('a.*,b.*,a.status as product_status');
		$this->db->from('product_information a');
		$this->db->join('product_title b','a.product_id = b.product_id','left');
		
		$this->db->where('a.product_id',$product_id);
		$this->db->where('b.lang_id',$user_lang);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();	
		}
		return false;
	}
	//Stock check 
	public function stock_check()
	{
		if ($this->cart->contents()) {
			foreach ($this->cart->contents() as $items){
				if ($items['pre_order']) {
					$stock = $this->db->select('*')
					->from('product_information')
					->where('product_id',$items['product_id'])
					->where('pre_order',1)
					->get()
					->row();

					if ($stock) {
						if ($stock->pre_order_quantity < $items['qty']) {
							return false;
						}
					}
				}else{
					$stock = $this->db->select('*')
					->from('product_information')
					->where('product_id',$items['product_id'])
					->get()
					->row();
					if ($stock) {
						if ($stock->quantity < $items['qty']) {
							return false;
						}
					}
				}
			}
		}
	}

	public function digital_entry($order_id, $customer_id, $total_amount=0, $paid_amount=0)
	{
		if ($this->cart->contents()) {

			$product_ids = array_column($this->cart->contents(), 'product_id');

			$products = $this->db->select('product_id, is_digital')
				->from('product_information')
				->where_in('product_id',$product_ids)
				->where('is_digital', '1')
				->get()
				->result();

			if ($products) {
				$status = (($paid_amount >= ($total_amount/2))?'1':'0');
				foreach ($products as $pinfo) {
					// Digital Entry
					if (($pinfo->is_digital == '1') && (!empty($order_id))) {
						$fdata = array(
							'order_id' => $order_id,
							'customer_id' => $customer_id,
							'product_id' => $pinfo->product_id,
							'status' => $status
						);
						$this->db->insert('product_digital_downloads', $fdata);
					}
				}
			}
		}
		return true;
	}

	//Order entry
	public function order_entry($customer_id=null,$order_id=null, $paid_amount=null, $bank_payment_discount=null){

		$vatsum = array_sum($this->session->userdata('vat_amount'));
		$vat = (!empty($vatsum)?$vatsum:0);

		// Shipping Cost into XAF
		$ship_cost = $this->session->userdata('cart_ship_cost');
		$cart_ship_cost = $ship_cost;

		if ($this->cart->contents()) {
			foreach ($this->cart->contents() as $items){ 
				if ($items['pre_order']) {
					$stock = $this->db->select('*')
					->from('product_information')
					->where('product_id',$items['product_id'])
					->where('pre_order',1)
					->get()
					->row();

					if ($stock) {
						if ($stock->pre_order_quantity < $items['qty']) {
							$this->session->set_userdata('error_message',display('you_can_not_order_more_than_stock'));
							redirect(base_url('view_cart'));
						}
					}
					
				}else{
					$stock = $this->db->select('*')
					->from('product_information')
					->where('product_id',$items['product_id'])
					->get()
					->row();
					if ($stock) {
						if ($stock->quantity < $items['qty']) {
							$this->session->set_userdata('error_message',display('you_can_not_order_more_than_stock'));
							redirect(base_url('view_cart'));
						}
					}
				}
			}
		}

		$found_key = array_search('1', array_column($this->cart->contents(), 'pre_order'));
		$payment_method = $this->session->userdata('payment_method');
		//Delivery order payment entry
		$data = array(
			'order_payment_id' => $this->auth->generator(15), 
			'payment_id' 	   => $this->session->userdata('payment_method'),
			'order_id' 		   => $order_id,
			'details'		   => $this->session->userdata('order_details'), 
		);
		$this->db->insert('order_payment',$data);

		//Insert order to order details
		if ($this->cart->contents()) {
			$quantity 	= 0;
			$pre_order 	= 0;
			$order 		= 0;
			$total_amount = 0;
			$total_discount = 0;
			$number_product = 0;
			$total_order_amt = 0;
			$total_order_dis = 0;
			$number_order_product = 0;

			foreach ($this->cart->contents() as $items){
				$vat_amount= $items['vat_amount'];
				$order_details = array(
					'order_id'				=>	$order_id,
					'seller_id'				=>	$items['seller_id'],
					'customer_id'			=>	$customer_id,
					'product_id'			=>	$items['product_id'],
					'variant_id'			=>	$items['variant'],
					'quantity'				=>	$items['qty'],
					'rate'					=>	$items['actual_price'],
					'total_price'       	=>	$items['actual_price'] * $items['qty'],
					'discount_per_product'	=>	$items['discount'],
				);
				if(!empty($items))
				{
					if ($found_key !== false) {

						$total_amount 	+= (($items['actual_price'] * $items['qty']) - ($items['discount'] * $items['qty']));
						$total_discount += ($items['discount'] * $items['qty']);
						$number_product += $items['qty'];
						
						$pre_order = array(
							'order_id' 	  	=> $order_id, 
							'customer_id' 	=> $customer_id, 
							'date' 		  	=> date("Y-m-d"), 
							'total_amount'	=> $total_amount, 
							'details'	  	=> $this->session->userdata('order_details'), 
							'total_discount'=> $total_discount,
							'number_product'=> $number_product, 
							'service_charge'=> $cart_ship_cost, 
						);

						//Insert data into pre-order details
						$this->db->insert('seller_pre_order',$order_details);

						//Product stock update
						$this->db->set('pre_order_quantity', 'pre_order_quantity-'.$items['qty'], FALSE);
						$this->db->where('product_id',$items['product_id']);
						$this->db->update('product_information');
					}else{
						$total_order_amt 	+= (($items['price'] * $items['qty']));
						$total_order_dis 	+= ($items['discount'] * $items['qty']);
						$number_order_product 	+= $items['qty'];

						//Seller percentage
						$comission_rate= $this->comission_info($items['product_id']);
						$category_id   = $this->category_id($items['product_id']);

						$order = array(
							'order_id' 	  	=> $order_id, 
							'customer_id' 	=> $customer_id,  
							'date' 		  	=> date("Y-m-d"), 
							'total_amount'	=> $total_order_amt, 
							'details'	  	=> $this->session->userdata('order_details'), 
							'total_discount'=> $total_order_dis,
							'number_product'=> $number_order_product, 
							'service_charge'=> $cart_ship_cost, 
						);

						$order_details = array(
							'order_id'				=>	$order_id,
							'seller_id'				=>	$items['seller_id'],
							'seller_percentage' 	=>  $comission_rate,
							'customer_id'			=>	$customer_id,
							'category_id'			=>	$category_id,
							'product_id'			=>	$items['product_id'],
							'variant_id'			=>	$items['variant'],
							'quantity'				=>	$items['qty'],
							'rate'					=>	$items['actual_price'],
							'total_price'       	=>	(($items['actual_price']  - $items['discount'])+$vat_amount)* $items['qty'],
							'discount_per_product'	=>	$items['discount'],
							'product_vat'			=>	$vat_amount*$items['qty'],
						);

						//Total quantity count
						$quantity += $items['qty'];
						$this->db->insert('seller_order',$order_details);

						//Product stock update
						$this->db->set('quantity', 'quantity-'.$items['qty'], FALSE);
						$this->db->where('product_id',$items['product_id']);
						$this->db->update('product_information');
					}

				}
				//CGST Tax summary
				$cgst_summary = array(
					'order_tax_col_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['cgst'] * $items['qty'], 
					'tax_id' 			=> 	$items['options']['cgst_id'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['cgst_id'])){
					$result= $this->db->select('*')
					->from('order_tax_col_summary')
					->where('order_id',$order_id)
					->where('tax_id',$items['options']['cgst_id'])
					->get()
					->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['cgst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['cgst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$cgst_summary);
					}
				}
				//CGST Summary End

				//IGST Tax summary
				$igst_summary = array(
					'order_tax_col_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['igst'] * $items['qty'], 
					'tax_id' 			=> 	$items['options']['igst_id'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['igst_id'])){
					$result= $this->db->select('*')
					->from('order_tax_col_summary')
					->where('order_id',$order_id)
					->where('tax_id',$items['options']['igst_id'])
					->get()
					->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['igst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['igst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$igst_summary);
					}
				}
				//IGST Tax summary end

				//SGST Tax summary
				$sgst_summary = array(
					'order_tax_col_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['sgst'] * $items['qty'], 
					'tax_id' 			=> 	$items['options']['sgst_id'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['sgst_id'])){
					$result= $this->db->select('*')
					->from('order_tax_col_summary')
					->where('order_id',$order_id)
					->where('tax_id',$items['options']['sgst_id'])
					->get()
					->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['sgst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['sgst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$sgst_summary);
					}
				}
				//SGST Tax summary end

				//CGST Details
				$cgst_details = array(
					'order_tax_col_de_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['cgst'] * $items['qty'], 
					'product_id' 		=> 	$items['product_id'], 
					'tax_id' 			=> 	$items['options']['cgst_id'],
					'variant_id'		=>	$items['variant'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['cgst_id'])){
					$this->db->insert('order_tax_col_details',$cgst_details);
				}
				//CGST Details End

				//IGST Details
				$igst_details = array(
					'order_tax_col_de_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['igst'] * $items['qty'], 
					'product_id' 		=> 	$items['product_id'], 
					'tax_id' 			=> 	$items['options']['igst_id'],
					'variant_id'		=>	$items['variant'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['igst_id'])){
					$this->db->insert('order_tax_col_details',$igst_details);
				}
				//IGST Details End

				//SGST Details
				$sgst_details = array(
					'order_tax_col_de_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['sgst'] * $items['qty'], 
					'product_id' 		=> 	$items['product_id'], 
					'tax_id' 			=> 	$items['options']['sgst_id'],
					'variant_id'		=>	$items['variant'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['sgst_id'])){
					$this->db->insert('order_tax_col_details',$sgst_details);
				}
				//SGST Details End
			}
		}

		$coupon_amnt = (!empty($this->session->userdata('coupon_amnt'))?$this->session->userdata('coupon_amnt'):0);
		$affiliate_id = @get_cookie('lim-affid');

		if ($pre_order) {
			$total_discount = $pre_order['total_discount'] + $coupon_amnt;
			$p_order = array(
				'order_id' 	  	=> $pre_order['order_id'], 
				'customer_id' 	=> $pre_order['customer_id'], 
				'shipping_id' 	=> $this->session->userdata('state'),
				'date' 		  	=> date("Y-m-d"), 
				'total_amount'	=> $pre_order['total_amount']+$cart_ship_cost+$this->session->userdata('total_tax')-$coupon_amnt,
				'affiliate_id'  => $affiliate_id,
				'details'	  	=> $this->session->userdata('order_details'),
				'total_discount'=> $total_discount,
				'number_product'=> $pre_order['number_product'],
				'service_charge'=> $cart_ship_cost,
				'vat' 			=> $vat
			);
			$this->db->insert('pre_order',$p_order);
		}

		if ($order) {
			//Data insert into order table
			$service_charge 	= $cart_ship_cost;
			$total_amount = $order['total_amount']+$service_charge+$this->session->userdata('total_tax')+$vat-$coupon_amnt-$bank_payment_discount;

			$n_order = array(
				'order_id' 	  	=> $order['order_id'],
				'order_no' 	  	=> $this->order_number_generator(),
				'customer_id' 	=> $order['customer_id'],
				'shipping_id' 	=> $this->session->userdata('state'),
				'date' 		  	=> date("Y-m-d"),
				'total_amount'	=> $total_amount,
				'affiliate_id'  => $affiliate_id,
				'details'	  	=> $this->session->userdata('order_details'),
				'paid_amount' 	=>($paid_amount)? $paid_amount:0,
				'total_discount'=> ($order['total_discount']+$coupon_amnt+$bank_payment_discount),
				'number_product'=> $order['number_product'],
				'service_charge'=> $service_charge,
				'vat' 			=> $vat,			
				'pending'		=> date("Y-m-d")
			);
			$this->db->insert('order',$n_order);

			//Order intsert info order tracking
			$order_tracking = array(
				'order_id' 	  	=> $order['order_id'], 
				'customer_id' 	=> $order['customer_id'], 
				'date' 		  	=> date("Y-m-d h:i a") 
			);
			$this->db->insert('order_tracking',$order_tracking);

			// Digital Item Entry
			$this->digital_entry($order_id, $customer_id, $total_amount, $paid_amount);
		}
		// Remove Coupon logs
		$this->db->delete('coupon_logs', array('customer_id' => $customer_id));
		$this->session->unset_userdata(array('coupon_id', 'coupon_amnt'));

		//Return order id 
		return $order_id;
	}


	//Order entry
	public function order_entry_new($customer_id=null,$order_id=null, $paid_amount=null){

		$vatsum = array_sum($this->session->userdata('vat_amount'));
		$vat = (!empty($vatsum)?$vatsum:0);

		// Shipping Cost into XAF
		$ship_cost = $this->session->userdata('cart_ship_cost');
		$cart_ship_cost = $ship_cost;
		

		if ($this->cart->contents()) {
			foreach ($this->cart->contents() as $items){ 
				if ($items['pre_order']) {
					$stock = $this->db->select('*')
					->from('product_information')
					->where('product_id',$items['product_id'])
					->where('pre_order',1)
					->get()
					->row();

					if ($stock) {
						if ($stock->pre_order_quantity < $items['qty']) {
							$this->session->set_userdata('error_message',display('you_can_not_order_more_than_stock'));
							redirect(base_url('view_cart'));
						}
					}
					
				}else{
					$stock = $this->db->select('*')
					->from('product_information')
					->where('product_id',$items['product_id'])
					->get()
					->row();
					if ($stock) {
						if ($stock->quantity < $items['qty']) {
							$this->session->set_userdata('error_message',display('you_can_not_order_more_than_stock'));
							redirect(base_url('view_cart'));
						}
					}
				}
			}
		}

		$found_key = array_search('1', array_column($this->cart->contents(), 'pre_order'));
		$payment_method = $this->session->userdata('payment_method');
		//Delivery order payment entry
		$data = array(
			'order_payment_id' => $this->auth->generator(15), 
			'payment_id' 	   => $this->session->userdata('payment_method'),
			'order_id' 		   => $order_id,
			'details'		   => $this->session->userdata('order_details'), 
		);
		$this->db->insert('order_payment',$data);

		//Insert order to order details
		if ($this->cart->contents()) {
			$quantity 	= 0;
			$pre_order 	= 0;
			$order 		= 0;
			$total_amount = 0;
			$total_discount = 0;
			$number_product = 0;
			$total_order_amt = 0;
			$total_order_dis = 0;
			$number_order_product = 0;

			foreach ($this->cart->contents() as $items){
				$vat_amount= $items['vat_amount'];
				$order_details = array(
					'order_id'				=>	$order_id,
					'seller_id'				=>	$items['seller_id'],
					'customer_id'			=>	$customer_id,
					'product_id'			=>	$items['product_id'],
					'variant_id'			=>	$items['variant'],
					'quantity'				=>	$items['qty'],
					'rate'					=>	$items['actual_price'],
					'total_price'       	=>	$items['actual_price'] * $items['qty'],
					'discount_per_product'	=>	$items['discount'],
				);
				if(!empty($items))
				{
					if ($found_key !== false) {

						$total_amount 	+= (($items['actual_price'] * $items['qty']) - ($items['discount'] * $items['qty']));
						$total_discount += ($items['discount'] * $items['qty']);
						$number_product += $items['qty'];
						
						$pre_order = array(
							'order_id' 	  	=> $order_id, 
							'customer_id' 	=> $customer_id, 
							'date' 		  	=> date("Y-m-d"), 
							'total_amount'	=> $total_amount, 
							'details'	  	=> $this->session->userdata('order_details'), 
							'total_discount'=> $total_discount,
							'number_product'=> $number_product, 
							'service_charge'=> $cart_ship_cost, 
						);

						//Insert data into pre-order details
						$this->db->insert('seller_pre_order',$order_details);

						//Product stock update
						$this->db->set('pre_order_quantity', 'pre_order_quantity-'.$items['qty'], FALSE);
						$this->db->where('product_id',$items['product_id']);
						$this->db->update('product_information');
					}else{
						$total_order_amt 	+= (($items['price'] * $items['qty']));
						$total_order_dis 	+= ($items['discount'] * $items['qty']);
						$number_order_product 	+= $items['qty'];

						//Seller percentage
						$comission_rate= $this->comission_info($items['product_id']);
						$category_id   = $this->category_id($items['product_id']);

						$order = array(
							'order_id' 	  	=> $order_id, 
							'customer_id' 	=> $customer_id,  
							'date' 		  	=> date("Y-m-d"), 
							'total_amount'	=> $total_order_amt, 
							'details'	  	=> $this->session->userdata('order_details'), 
							'total_discount'=> $total_order_dis,
							'number_product'=> $number_order_product, 
							'service_charge'=> $cart_ship_cost, 
						);

						$order_details = array(
							'order_id'				=>	$order_id,
							'seller_id'				=>	$items['seller_id'],
							'seller_percentage' 	=>  $comission_rate,
							'customer_id'			=>	$customer_id,
							'category_id'			=>	$category_id,
							'product_id'			=>	$items['product_id'],
							'variant_id'			=>	$items['variant'],
							'quantity'				=>	$items['qty'],
							'rate'					=>	$items['actual_price'],
							'total_price'       	=>	(($items['actual_price']  - $items['discount'])+$vat_amount)* $items['qty'],
							'discount_per_product'	=>	$items['discount'],
							'product_vat'			=>	$vat_amount*$items['qty'],
						);

						//Total quantity count
						$quantity += $items['qty'];
						$this->db->insert('seller_order',$order_details);

						//Product stock update
						$this->db->set('quantity', 'quantity-'.$items['qty'], FALSE);
						$this->db->where('product_id',$items['product_id']);
						$this->db->update('product_information');
					}

				}
				//CGST Tax summary
				$cgst_summary = array(
					'order_tax_col_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['cgst'] * $items['qty'], 
					'tax_id' 			=> 	$items['options']['cgst_id'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['cgst_id'])){
					$result= $this->db->select('*')
					->from('order_tax_col_summary')
					->where('order_id',$order_id)
					->where('tax_id',$items['options']['cgst_id'])
					->get()
					->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['cgst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['cgst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$cgst_summary);
					}
				}
				//CGST Summary End

				//IGST Tax summary
				$igst_summary = array(
					'order_tax_col_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['igst'] * $items['qty'], 
					'tax_id' 			=> 	$items['options']['igst_id'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['igst_id'])){
					$result= $this->db->select('*')
					->from('order_tax_col_summary')
					->where('order_id',$order_id)
					->where('tax_id',$items['options']['igst_id'])
					->get()
					->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['igst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['igst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$igst_summary);
					}
				}
				//IGST Tax summary end

				//SGST Tax summary
				$sgst_summary = array(
					'order_tax_col_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['sgst'] * $items['qty'], 
					'tax_id' 			=> 	$items['options']['sgst_id'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['sgst_id'])){
					$result= $this->db->select('*')
					->from('order_tax_col_summary')
					->where('order_id',$order_id)
					->where('tax_id',$items['options']['sgst_id'])
					->get()
					->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['sgst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['sgst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$sgst_summary);
					}
				}
				//SGST Tax summary end

				//CGST Details
				$cgst_details = array(
					'order_tax_col_de_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['cgst'] * $items['qty'], 
					'product_id' 		=> 	$items['product_id'], 
					'tax_id' 			=> 	$items['options']['cgst_id'],
					'variant_id'		=>	$items['variant'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['cgst_id'])){
					$this->db->insert('order_tax_col_details',$cgst_details);
				}
				//CGST Details End

				//IGST Details
				$igst_details = array(
					'order_tax_col_de_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['igst'] * $items['qty'], 
					'product_id' 		=> 	$items['product_id'], 
					'tax_id' 			=> 	$items['options']['igst_id'],
					'variant_id'		=>	$items['variant'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['igst_id'])){
					$this->db->insert('order_tax_col_details',$igst_details);
				}
				//IGST Details End

				//SGST Details
				$sgst_details = array(
					'order_tax_col_de_id'	=>	$this->auth->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['sgst'] * $items['qty'], 
					'product_id' 		=> 	$items['product_id'], 
					'tax_id' 			=> 	$items['options']['sgst_id'],
					'variant_id'		=>	$items['variant'],
					'date'				=>	date("Y-m-d"),
				);
				if(!empty($items['options']['sgst_id'])){
					$this->db->insert('order_tax_col_details',$sgst_details);
				}
				//SGST Details End
			}
		}

		$coupon_amnt = (!empty($this->session->userdata('coupon_amnt'))?$this->session->userdata('coupon_amnt'):0);

		$return_result = false;
		if ($pre_order) {
			$total_discount = $pre_order['total_discount'] + $coupon_amnt;
			$p_order = array(
				'order_id' 	  	=> $pre_order['order_id'], 
				'customer_id' 	=> $pre_order['customer_id'], 
				'shipping_id' 	=> $this->session->userdata('method_id'),
				'date' 		  	=> date("Y-m-d"), 
				'total_amount'	=> $pre_order['total_amount']+$cart_ship_cost+$this->session->userdata('total_tax')-$coupon_amnt,
				'details'	  	=> $this->session->userdata('order_details'),
				'total_discount'=> $total_discount,
				'number_product'=> $pre_order['number_product'],
				'service_charge'=> $cart_ship_cost,
				'vat' 			=> $vat
			);
			$return_result = $this->db->insert('pre_order',$p_order);
		}

		if ($order) {
			//Data insert into order table
			$service_charge 	= $cart_ship_cost;
			$total_amount = $order['total_amount']+$service_charge+$this->session->userdata('total_tax')+$vat-$coupon_amnt;

			$n_order = array(
				'order_id' 	  	=> $order['order_id'],
				'order_no' 	  	=> $this->order_number_generator(),
				'customer_id' 	=> $order['customer_id'],
				'shipping_id' 	=> $this->session->userdata('method_id'),
				'date' 		  	=> date("Y-m-d"),
				'total_amount'	=> $total_amount,
				'details'	  	=> $this->session->userdata('order_details'),
				'paid_amount' 	=>($paid_amount)? $paid_amount:0,
				'total_discount'=> ($order['total_discount']+$coupon_amnt),
				'number_product'=> $order['number_product'],
				'service_charge'=> $service_charge,
				'vat' 			=> $vat,			
				'pending'		=> date("Y-m-d")
			);
			$return_result = $this->db->insert('order',$n_order);

			// d($n_order);
			// dd($this);


			//Order intsert info order tracking
			$order_tracking = array(
				'order_id' 	  	=> $order['order_id'], 
				'customer_id' 	=> $order['customer_id'], 
				'date' 		  	=> date("Y-m-d h:i a"), 

			);
			$this->db->insert('order_tracking',$order_tracking);


			// Digital Item Entry
			// $this->digital_entry($order_id, $customer_id, $total_amount, $paid_amount);
		}

		if($return_result){

			// Remove Coupon logs
			$this->db->delete('coupon_logs', array('customer_id' => $customer_id));
			$this->session->unset_userdata(array('coupon_id', 'coupon_amnt'));

			//Return order id 
			return $order_id;

		} else {
			return false;
		}
		
	}

	//NUMBER GENERATOR
	public function order_number_generator()
	{
		$order_no = "EZ".mt_rand(100000000000,999999999999);
		// $order_no = "EZ".strtotime("now").mt_rand(10,99);
		$this->db->select('order_no');
		$this->db->where('order_no', $order_no);
		$query = $this->db->get('order');	
		$result = $query->num_rows();
		if ($result > 0) {
			$order_no = "EZ".mt_rand(100000000000,999999999999);	
		}
		
		return $order_no;		
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
	//Seller id by product id
	public function get_seller_id($product_id){
		$seller = $this->db->select('*')
		->from('product_information')
		->where('product_id',$product_id)
		->get()
		->row();

		if ($seller) {
			return $seller->seller_id;
		}else{
			return null;
		}
	}
	//Stock available check
	public function stock_available_check($product_id=null,$buy=null){
		$stock = $this->db->select('quantity')
		->from('product_information')
		->where('product_id',$product_id)
		->get()
		->row();

		if ($stock->quantity < $buy) {
			return false;
		}else{
			return true;
		}
	}
	//Retrieve order_html_data
	public function retrieve_order_html_data($order_id)
	{
		$lang_id   = 0;
		$user_lang = $this->session->userdata('language');
		if (empty($user_lang)) {
			$lang_id = 'english';
		}else{
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
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->join('seller_order c','c.order_id = a.order_id');
		$this->db->join('seller_information s','c.seller_id = s.seller_id and s.status=1','left');
		$this->db->join('shipping_info p','a.customer_id = p.customer_id and a.order_id = p.order_id','left');		
		$this->db->join('product_information d','d.product_id = c.product_id');
		$this->db->join('unit e','e.unit_id = d.unit','left');
		$this->db->join('variant f','f.variant_id = c.variant_id','left');
		$this->db->join('product_title g','g.product_id = d.product_id','left');
		$this->db->where('a.order_id',$order_id);
		$this->db->where('g.lang_id',$lang_id);
		$query = $this->db->get();
		//echo $this->db->last_query();

		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}
	//Retrieve pre order_html_data
	public function retrieve_pre_order_html_data($order_id)
	{
		$lang_id   = 0;
		$user_lang = $this->session->userdata('language');
		if (empty($user_lang)) {
			$lang_id = 'english';
		}else{
			$lang_id = $user_lang;
		}

		$this->db->select('
			a.*,
			b.*,
			c.*,
			d.product_id,
			d.product_model,d.unit,
			e.unit_short_name,
			f.variant_name,
			g.title as product_name,
			a.details
			');
		$this->db->from('pre_order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->join('seller_pre_order c','c.order_id = a.order_id');
		$this->db->join('product_information d','d.product_id = c.product_id');
		$this->db->join('unit e','e.unit_id = d.unit','left');
		$this->db->join('variant f','f.variant_id = c.variant_id','left');
		$this->db->join('product_title g','g.product_id = d.product_id','left');
		$this->db->where('a.order_id',$order_id);
		$this->db->where('g.lang_id',$lang_id);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}
	//Retrive all language
	public function languages()
	{ 
		if ($this->db->table_exists($this->table)) { 

			$fields = $this->db->field_data($this->table);

			$i = 1;
			foreach ($fields as $field)
			{  
				if ($i++ > 2)
					$result[$field->name] = ucfirst($field->name);
			}

			if (!empty($result)) return $result;

		} else {
			return false; 
		}
	}
	//Payment status
	public function get_payment_status($code = null){
		$this->db->select('*');
		$this->db->from('payment_gateway');
		if(!empty($code)){
			$this->db->where('code',$code);
		}

		// $this->db->where('status', '1');
		
		$result = $this->db->get()->result();
		return $result;
	}
	
    //Payment status
	public function payment_status($code = null){
		return $payeer_result= $this->db->select('*')
		->from('payment_gateway')
		->where('code',$code)	
		->get()
		->row();
	}
    //Customer email existing check
	public function customer_exist_check($email=null,$phone=null){

		$customer_exists = $this->db->select('*')
		->from('customer_login')
		->where('email',$email)
		->where('email !=',null)
		->or_where('phone',$phone)
		->get()
		->num_rows();
		if ($customer_exists > 0) {
			return true;
		}else{
			return false;
		}
	}    
    //Customer email existing check
	public function customer_email_exists($email=null){

		$customer_exists = $this->db->select('*')
		->from('customer_login')
		->where('email',$email)
		->where('email !=',null)
		->get()
		->num_rows();
		if ($customer_exists > 0) {
			return true;
		}else{
			return false;
		}
	}    
    //Seller email existing check
	public function seller_email_exists($email=null){
		$customer_exists = $this->db->select('*')
		->from('seller_information')
		->where('email',$email)
		->where('email !=',null)
		->get()
		->num_rows();
		if ($customer_exists > 0) {
			return true;
		}else{
			return false;
		}
	}
    //Temporary reset password
	public function temp_reset_password($temp_pass=null,$email=null){
		$result = $this->db->set('reset_pass', $temp_pass)
		->where('email', $email)
		->update('customer_login'); 
		if($result){
			return TRUE;
		}else{
			return FALSE;
		}
	}   
	//Temporary reset password seller
	public function temp_seller_reset_password($temp_pass=null,$email=null){
		$result = $this->db->set('reset_pass', $temp_pass)
		->where('email', $email)
		->update('seller_information'); 
		if($result){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	//IS temp pass valid
	public function is_temp_pass_valid($temp_pass=null){
		$this->db->where('reset_pass', $temp_pass);
		$query = $this->db->get('customer_login');
		if($query->num_rows() > 0){
			return TRUE;
		}
		else return FALSE;
	}	
	//IS seller temp pass valid
	public function is_seller_temp_pass_valid($temp_pass=null){
		$this->db->where('reset_pass', $temp_pass);
		$query = $this->db->get('seller_information');
		if($query->num_rows() > 0){
			return TRUE;
		}
		else return FALSE;
	}
//Password update
	public function update_password($password=null,$_token=null){

		$result=$this->db->update('customer_login', array('password' => $password), array('reset_pass' => $_token)); 
		return $result;
	}	
	//Seller password update
	public function seller_update_password($password=null,$_token=null){

		$result=$this->db->update('seller_information', array('password' => $password), array('reset_pass' => $_token)); 

		if ($result) {
			return true;
		}else{
			return false;
		}
	}
	//Product quantity check
	public function pro_qty_check($product_id='',$qnty='')
	{
		$found_product = array_search($product_id, array_column($this->cart->contents(), 'product_id'));
		if ($found_product !== false) {
			$total_apply_qty = 0;
			if ($this->cart->contents()) {
				foreach ($this->cart->contents() as $cart) {
					if ($cart['product_id'] == $product_id) {
						$total_apply_qty = $qnty + $cart['qty'];
						$in_stock = $this->db->select('*')
						->from('product_information')
						->where('product_id',$product_id)
						->get()
						->row();
						if ($total_apply_qty > $in_stock->quantity) {
							return false;
						}else{
							return true;
						}
					}
				}
			}
		}else{
			return true;
		}
	}
	//Pre Product quantity check
	public function pre_pro_qty_check($product_id='',$qnty='')
	{
		$found_product = array_search($product_id, array_column($this->cart->contents(), 'product_id'));
		if ($found_product !== false) {
			$total_apply_qty = 0;
			if ($this->cart->contents()) {
				foreach ($this->cart->contents() as $cart) {
					if (($cart['product_id'] == $product_id) && ($cart['pre_order'] == '1')) {
						$total_apply_qty = $qnty + $cart['qty'];
						$in_stock = $this->db->select('*')
						->from('product_information')
						->where('product_id',$product_id)
						->get()
						->row();
						if ($total_apply_qty > $in_stock->pre_order_quantity) {
							return false;
						}else{
							return true;
						}
					}
				}
			}else{
				return true;
			}
		}else{
			return true;
		}
	}

	// Retrieve Social Media
	public function retrieve_social_media()
	{
		return $this->db->get('social_medias')->result();
	}

	// get custom page data
	public function get_page_info($page_id)
	{

		$this->db->where('language_id', $this->lang_id);
		$this->db->where('page_id', $page_id);
	    $result = $this->db->get('link_page')->row_array();
	    return $result;
	}


	public function get_category_brands($block_catid)
	{
		$subcats = $this->db->select('category_id')->from('product_category')->where('status', 1)->where('parent_category_id',$block_catid)->get()->result();


		if(!empty($subcats)){
			$subcats = array_column($subcats,'category_id');
			// Sub2 category of sub category
			$subsubcats = $this->db->select('category_id')->from('product_category')->where('status', 1)->where_in('parent_category_id',$subcats)->get()->result();

			if(!empty($subsubcats)){
				$subsubcats = array_column($subsubcats,'category_id');
				$subcats = array_merge($subcats, $subsubcats);
			}
        	$subcats[] = $block_catid;

	        $catinfo = $this->db->select('brand_id')
	                    ->from('product_information')
	                    ->where_in('category_id',$subcats)
	                    ->group_by('brand_id')
	                    ->get()
	                    ->result();

           if(!empty($catinfo)){
	        $brands = array_map('trim',array_column($catinfo, 'brand_id'));

		        $brandinfo = $this->db->select('brand_id,brand_name,brand_image')
	                ->from('brand')
	                ->where_in('brand_id', $brands)
	                ->get()
	                ->result();
		        return $brandinfo;
	        } 
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
}