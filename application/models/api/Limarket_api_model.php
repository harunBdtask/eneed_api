<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Limarket_api_model extends CI_Model
{
    private $table  = "language";
    public $lang_id;
    public $default_lang = 'english';

    public function __construct()
    {
        parent::__construct();
        $this->lang_id = filter_input_data($this->input->post('language', TRUE));
        if (empty($this->lang_id)) {
            $this->lang_id = $this->default_lang;
        }
    }
    //retrieve_company_editdata
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
    //Parent Category List from model website/Homes
    public function parent_category_list($per_page = null, $page = null, $cat_type = null, $category_id = null, $parent_category_id = null, $category_name = null, $top_menu = null, $menu_pos = null, $featured = null, $home_page = null, $on_promotion = null, $promo_date_start = null, $promo_date_end = null, $on_promo_image = null, $home_promo = null)
    {
        if ($this->lang_id != $this->default_lang) {
            $this->db->select("pc.*, (SELECT trans_category_name FROM product_category_translation WHERE lang='" . $this->lang_id . "' AND category_id = pc.category_id) as trans_category_name");
            $this->db->from('product_category pc');
            $this->db->where('pc.status', 1);
            if ($cat_type != '' && $cat_type != null) {
                $this->db->where('pc.cat_type', $cat_type);
            }
            if (!empty($category_id)) {
                $this->db->where('pc.category_id', $category_id);
            }
            if (!empty($parent_category_id)) {
                $this->db->where('pc.parent_category_id', $parent_category_id);
            }
            if (!empty($category_name)) {
                $this->db->where('pc.category_name', $category_name);
            }
            if ($top_menu != '' && $top_menu != null) {
                $this->db->where('pc.top_menu', $top_menu);
            }
            if ($menu_pos != '' && $menu_pos != null) {
                $this->db->where('pc.menu_pos', $menu_pos);
            }
            if ($featured != '' && $featured != null) {
                $this->db->where('pc.featured', $featured);
            }
            if ($home_page != '' && $home_page != null) {
                $this->db->where('pc.home_page', $home_page);
            }
            if ($on_promotion != '' && $on_promotion != null) {
                $this->db->where('pc.on_promotion', $on_promotion);
            }
            if (!empty($promo_date_start) && !empty($promo_date_end)) {
                $this->db->where(" pc.promo_date BETWEEN '$promo_date_start' AND '$promo_date_end' ");
            }
            if ($on_promo_image != '' && $on_promo_image != null) {
                $this->db->where('pc.on_promo_image', $on_promo_image);
            }
            if ($home_promo != '' && $home_promo != null) {
                $this->db->where('pc.home_promo', $home_promo);
            }
            $this->db->order_by('pc.menu_pos');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            //echo $this->db->last_query(); exit;
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        } else {
            $this->db->select('*');
            $this->db->from('product_category');
            $this->db->where('status', 1);
            if ($cat_type != '' && $cat_type != null) {
                $this->db->where('cat_type', $cat_type);
            }
            if (!empty($category_id)) {
                $this->db->where('category_id', $category_id);
            }
            if (!empty($parent_category_id)) {
                $this->db->where('parent_category_id', $parent_category_id);
            }
            if (!empty($category_name)) {
                $this->db->where('category_name', $category_name);
            }
            if ($top_menu != '' && $top_menu != null) {
                $this->db->where('top_menu', $top_menu);
            }
            if ($menu_pos != '' && $menu_pos != null) {
                $this->db->where('menu_pos', $menu_pos);
            }
            if ($featured != '' && $featured != null) {
                $this->db->where('featured', $featured);
            }
            if ($home_page != '' && $home_page != null) {
                $this->db->where('home_page', $home_page);
            }
            if ($on_promotion != '' && $on_promotion != null) {
                $this->db->where('on_promotion', $on_promotion);
            }
            if (!empty($promo_date_start) && !empty($promo_date_end)) {
                $this->db->where(" promo_date BETWEEN '$promo_date_start' AND '$promo_date_end' ");
            }
            if ($on_promo_image != '' && $on_promo_image != null) {
                $this->db->where('on_promo_image', $on_promo_image);
            }
            if ($home_promo != '' && $home_promo != null) {
                $this->db->where('home_promo', $home_promo);
            }
            $this->db->order_by('menu_pos');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            //echo $this->db->last_query(); exit;
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        }
        return false;
    }
    //Top Category List from model website/Homes
    public function top_category_list($per_page = null, $page = null)
    {
        if ($this->lang_id != $this->default_lang) {
            $this->db->select("pc.*, (SELECT trans_category_name FROM product_category_translation WHERE lang='" . $this->lang_id . "' AND category_id = pc.category_id) as trans_category_name");
            $this->db->from('product_category pc');
            $this->db->where('pc.cat_type', 1);
            $this->db->where('pc.status', 1);
            $this->db->where('pc.top_menu', 1);
            $this->db->order_by('pc.menu_pos');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        } else {
            $this->db->select('*');
            $this->db->from('product_category');
            $this->db->where('cat_type', 1);
            $this->db->where('status', 1);
            $this->db->where('top_menu', 1);
            $this->db->order_by('menu_pos');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        }
        return false;
    }
    //Category list from model website/Homes
    public function category_list($per_page = null, $page = null)
    {
        if ($this->lang_id != $this->default_lang) {
            $this->db->select("pc.*, (SELECT trans_category_name FROM product_category_translation WHERE lang='" . $this->lang_id . "' AND category_id = pc.category_id) as trans_category_name");
            $this->db->from('product_category pc');
            $this->db->order_by('pc.category_name', 'asc');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        } else {
            $this->db->select('*');
            $this->db->from('product_category');
            $this->db->order_by('category_name', 'asc');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        }

        return false;
    }
    //Get featured category from model website/Homes
    public function featured_cat_list($per_page = null, $page = null)
    {
        $featured_cat = $this->db->select('*')
            ->from('product_category')
            ->order_by('menu_pos')
            ->where('featured', 1)
            ->limit($per_page, $page)
            ->get()
            ->result();
        return $featured_cat;
    }
    //Get home category from model website/Homes
    public function home_cat_list($per_page = null, $page = null)
    {
        if ($this->lang_id != $this->default_lang) {
            $featured_cat =  $this->db->select("a.*, b.trans_category_name")
                ->from('product_category a')
                ->join('product_category_translation b', "b.category_id=a.category_id AND b.lang='$this->lang_id'", 'left')
                ->order_by('a.menu_pos')
                ->where('a.home_page', 1)
                ->limit($per_page, $page)
                ->get()
                ->result();
        } else {
            $featured_cat = $this->db->select('*')
                ->from('product_category')
                ->order_by('menu_pos')
                ->where('home_page', 1)
                ->limit($per_page, $page)
                ->get()
                ->result();
        }
        return $featured_cat;
    }
    //Get promotional category from model website/Homes
    public function promo_cat_list($per_page = null, $page = null)
    {
        $promo_cat_list = $this->db->select('*')
            ->from('product_category')
            ->order_by('promo_date', 'ASC')
            ->where('on_promotion', 1)
            ->where('home_promo', 1)
            ->where('promo_date >', date('Y-m-d'))
            ->limit($per_page, $page)
            ->get()
            ->result();
        return $promo_cat_list;
    }
    //Select Page ads from model website/Homes
    public function select_page_ads($page = 'home')
    {
        $this->db->select('*');
        $this->db->from('advertisement');
        $this->db->where('add_page', $page);
        $this->db->where('status', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Select default currency info from model website/Homes
    function selected_default_currency_info()
    {
        $this->db->select('*');
        $this->db->from('currency_info');
        $this->db->where('default_status', '1');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return 0;
    }
    //Retrive promotion product from model website/Products_model
    public function promotion_product($per_page = null, $page = null, $category_id = null)
    {
        $this->db->select('a.*,b.*,c.image_name');
        $this->db->from('product_information a');
        $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
        $this->db->join('product_image c', 'c.product_id = a.product_id', 'left');
        $this->db->where('b.lang_id', $this->lang_id);
        $this->db->where('a.on_promotion', 1);
        if (!empty($category_id)) {
            $this->db->where('a.category_id', $category_id);
        }
        $this->db->group_by('a.product_id');
        $this->db->limit($per_page, $page);
        $this->db->order_by('a.product_info_id', 'desc');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return array();
    }
    //Brand List from model Brands
    public function brand_list($per_page = null, $page = null)
    {
        $this->db->select('*');
        $this->db->from('brand');
        $this->db->order_by('brand_name', 'asc');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Best sales list from model website/Homes
    public function best_sales($per_page = null, $page = null)
    {
        if ($this->lang_id != $this->default_lang) {
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            $this->db->select('a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,b.product_id,b.title,c.category_name,d.first_name,d.last_name,e.brand_name, pi.image_name,pct.trans_category_name');
            $this->db->from('product_information a');
            $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
            $this->db->join('product_category c', 'a.category_id = c.category_id', 'left');
            $this->db->join('seller_information d', 'a.seller_id = d.seller_id', 'left');
            $this->db->join('brand e', 'a.brand_id = e.brand_id', 'left');
            $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
            $this->db->join('product_category_translation pct', "pct.category_id = c.category_id AND pct.lang='$this->lang_id'", 'left');
            $this->db->where('a.status', '2');
            $this->db->where('a.best_sale', '1');
            $this->db->where('b.lang_id', $this->lang_id);
            $this->db->where($where);
            $this->db->group_by('a.product_id');
            $this->db->order_by('a.product_info_id', 'desc');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        } else {
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            $this->db->select('a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,b.product_id,b.title,c.category_name,d.first_name,d.last_name,e.brand_name, pi.image_name');
            $this->db->from('product_information a');
            $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
            $this->db->join('product_category c', 'a.category_id = c.category_id', 'left');
            $this->db->join('seller_information d', 'a.seller_id = d.seller_id', 'left');
            $this->db->join('brand e', 'a.brand_id = e.brand_id', 'left');
            $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
            $this->db->where('a.status', '2');
            $this->db->where('a.best_sale', '1');
            $this->db->where('b.lang_id', $this->lang_id);
            $this->db->where($where);
            $this->db->group_by('a.product_id');
            $this->db->order_by('a.product_info_id', 'desc');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result();
            }
        }
        return false;
    }
    //Best merchant product from model website/Homes
    public function best_merchant_product($per_page = null, $page = null)
    {
        $result = $this->db->select('
			a.seller_id,
			SUM(a.quantity) as total_quantity,
			count(a.product_id) as total_product
			')
            ->from('seller_order a')
            ->group_by('a.seller_id')
            ->order_by('total_quantity', 'desc')
            ->limit($per_page, $page)
            ->get()
            ->result();
        if ($result) {
            return $result;
        }
        return false;
    }
    //get_seller_info from model website/Homes
    public function get_seller_info($per_page = null, $page = null, $seller_ids = null)
    {
        $this->db->select('
                a.*
            ');
        $this->db->from('seller_information a');
        $this->db->join('seller_order b', 'a.seller_id = b.seller_id', 'left');
        if (!empty($seller_ids)) {
            $this->db->where_in('a.seller_id', $seller_ids);
        }
        $this->db->order_by('a.id', 'desc');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        return $query->result();
    }
    //get_seller_product_list from model website/Homes
    public function get_seller_product_list($per_page = null, $page = null, $seller_ids = null)
    {
        if ($this->lang_id != $this->default_lang) {
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            $this->db->select('
					a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,
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
            $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
            $this->db->join('seller_information c', 'a.seller_id = c.seller_id', 'left');
            $this->db->join('brand d', 'a.brand_id = d.brand_id', 'left');
            $this->db->join('product_title e', 'e.product_id = a.product_id', 'left');
            $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
            $this->db->join('product_category_translation pct', "pct.category_id = b.category_id AND pct.lang='$this->lang_id'", 'left');
            $this->db->where('e.lang_id', $this->lang_id);
            $this->db->where('a.status', 2);
            if (!empty($seller_ids)) {
                $this->db->where_in('a.seller_id', $seller_ids);
            }
            $this->db->where($where);
            $this->db->group_by('a.product_id');
            $this->db->order_by('a.product_info_id', 'desc');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
        } else {
            $where = "(a.quantity > 0 OR a.pre_order = 1)";
            $this->db->select('
					a.product_id,a.category_id,a.price,a.offer_price,a.thumb_image_url,a.on_sale,
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
            $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
            $this->db->join('seller_information c', 'a.seller_id = c.seller_id', 'left');
            $this->db->join('brand d', 'a.brand_id = d.brand_id', 'left');
            $this->db->join('product_title e', 'e.product_id = a.product_id', 'left');
            $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
            $this->db->join('product_category_translation pct', "pct.category_id = b.category_id AND pct.lang='$this->lang_id'", 'left');
            $this->db->where('e.lang_id', $this->lang_id);
            $this->db->where('a.status', 2);
            if (!empty($seller_ids)) {
                $this->db->where_in('a.seller_id', $seller_ids);
            }
            $this->db->where($where);
            $this->db->group_by('a.product_id');
            $this->db->order_by('a.product_info_id', 'desc');
            $this->db->limit($per_page, $page);
            $query = $this->db->get();
        }
        return $query->result();
    }
    //Footer block from model website/Homes
    public function footer_block($per_page = null, $page = null)
    {
        $this->db->select('*');
        $this->db->from('web_footer');
        $this->db->where('lang', $this->lang_id);
        $this->db->where('status', 1);
        $this->db->order_by('position');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Slider list from model web_settings
    public function slider_list($per_page = null, $page = null, $status = null)
    {
        $this->db->select('*');
        $this->db->from('slider');
        if ($status != '' && $status != null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('slider_position');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Slider list from model web_settings
    public function slider_list_home($per_page = null, $page = null)
    {
        $this->db->select('*');
        $this->db->from('slider');
        $this->db->where('status', 1);
        $this->db->order_by('slider_position');
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Retrive all language from model website/Homes
    public function languages()
    {
        if ($this->db->table_exists($this->table)) {
            $fields = $this->db->field_data($this->table);
            $i = 1;
            foreach ($fields as $field) {
                if ($i++ > 2)
                    $result[$field->name] = ucfirst($field->name);
            }
            if (!empty($result)) return $result;
        } else {
            return false;
        }
    }
    //Currency info from model website/Homes
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
    //Selected currency info from model website/Homes
    public function selected_currency_info()
    {
        $cur_id = $this->session->userdata('currency_new_id');
        if (!empty($cur_id)) {
            $this->db->select('*');
            $this->db->from('currency_info');
            $this->db->where('currency_id', $cur_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->row();
            }
        } else {
            $this->db->select('*');
            $this->db->from('currency_info');
            $this->db->where('default_status', '1');
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->row();
            }
        }
        return false;
    }
    //Select home adds from model website/Homes
    public function select_home_adds()
    {
        $this->db->select('*');
        $this->db->from('advertisement');
        $this->db->where('add_page', 'home');
        $this->db->where('status', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Retrieve Data from model web_settings
    public function retrieve_setting_editdata()
    {
        $this->db->select('*');
        $this->db->from('web_setting');
        $this->db->where('setting_id', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //block List from model Blocks
    public function block_list()
    {
        $this->db->select('block.*,product_category.category_name');
        $this->db->from('block');
        $this->db->join('product_category', 'block.block_cat_id = product_category.category_id');
        $this->db->order_by('block_position');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //active_block_list from model Blocks
    public function active_block_list()
    {
        $this->db->select('block.*,product_category.category_name');
        $this->db->from('block');
        $this->db->join('product_category', 'block.block_cat_id = product_category.category_id');
        $this->db->where('block.status', 1);
        $this->db->order_by('block_position');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Retrieve currency info from model Soft_settings
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
    //Retrieve soft_setting edit data from model Soft_settings
    public function retrieve_soft_setting_editdata()
    {
        $this->db->select('*');
        $this->db->from('soft_setting');
        $this->db->where('setting_id', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Company List from model Companies
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
    //Product info from model website/Products_model
    public function product_info($per_page = null, $page = null, $product_id = null, $seller_id = null, $category_id = null, $price_start = null, $price_end = null, $best_sale = null, $product_status = null)
    {
        $this->db->select('a.*,
			a.status as product_status,
			b.category_name,
			c.*,
			d.brand_name,
			e.business_name,e.seller_guarantee,e.first_name,e.last_name,e.seller_store_name,f.description,pct.trans_category_name,t.meta_title meta_title_trans,t.meta_keyword meta_keyword_trans,t.meta_description meta_description_trans');
        $this->db->from('product_information a');
        $this->db->join('product_information_translation t', "a.product_id = t.product_id AND t.lang='$this->lang_id'", 'left');
        $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
        $this->db->join('product_title c', 'a.product_id = c.product_id', 'left');
        $this->db->join('brand d', 'a.brand_id = d.brand_id', 'left');
        $this->db->join('seller_information e', 'a.seller_id = e.seller_id', 'left');
        $this->db->join('product_description f', "a.product_id = f.product_id AND f.lang_id='$this->lang_id' AND f.description_type=1", 'left');
        $this->db->join('product_category_translation pct', "pct.category_id = b.category_id AND pct.lang='$this->lang_id'", 'left');
        //$this->db->where('a.product_id', $p_id);
        if (!empty($product_id)) {
            $this->db->where('a.product_id', $product_id);
        }
        if (!empty($seller_id)) {
            $this->db->where('a.seller_id', $seller_id);
        }
        if (!empty($category_id)) {
            //$this->db->where('a.category_id', $category_id);
            $this->db->where('a.category_id IN ( SELECT category_id FROM product_category WHERE a.category_id = "'.$category_id. '" OR product_category.parent_category_id = "'.$category_id. '" AND product_category.status=1)');
        }
        if (!empty($price_start) && !empty($price_end)) {
            $this->db->where(" a.price BETWEEN '$price_start' AND '$price_end' ");
        }
        if ($best_sale != '' && $best_sale != null) {
            $this->db->where('a.best_sale', $best_sale);
        }
        if ($product_status != '' && $product_status != null) {
            $this->db->where('a.status', $product_status);
        }
        $this->db->where('c.lang_id', $this->lang_id);
        $this->db->limit($per_page, $page);
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Category wise best product from model website/Products_model
    public function best_sales_category($p_id)
    {
        $this->db->select('a.*,c.*,d.first_name,d.last_name,e.brand_name, pi.image_name, b.category_name,pct.trans_category_name');
        $this->db->from('product_information a');
        $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
        $this->db->join('product_title c', 'a.product_id = c.product_id', 'left');
        $this->db->join('seller_information d', 'a.seller_id = d.seller_id', 'left');
        $this->db->join('brand e', 'a.brand_id = e.brand_id', 'left');
        $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
        $this->db->join('product_category_translation pct', "pct.category_id = b.category_id AND pct.lang='$this->lang_id'", 'left');
        $this->db->where('a.best_sale', 1);
        $this->db->group_by('a.product_id');
        $this->db->order_by('a.product_info_id', 'desc');
        $this->db->where('c.lang_id', $this->lang_id);
        $this->db->where_not_in('a.product_id', $p_id);
        $query = $this->db->get();
        return $query->result();
    }
    //Product gallery image from model website/Products_model
    public function get_thumb_image($p_id)
    {
        $this->db->select('*');
        $this->db->from('product_image');
        $this->db->where('product_id', $p_id);
        $this->db->order_by('product_image_id');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Category wise related product from model website/Products_model
    public function related_product($cat_id, $p_id)
    {
        $this->db->select('a.*,b.category_id,b.category_name,c.*,d.first_name,d.last_name,e.brand_name, pi.image_name,pct.trans_category_name');
        $this->db->from('product_information a');
        $this->db->join('product_category b', 'a.category_id = b.category_id', 'left');
        $this->db->join('product_title c', 'a.product_id = c.product_id', 'left');
        $this->db->join('seller_information d', 'a.seller_id = d.seller_id', 'left');
        $this->db->join('brand e', 'a.brand_id = e.brand_id', 'left');
        $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
        $this->db->join('product_category_translation pct', "pct.category_id = b.category_id AND pct.lang='$this->lang_id'", 'left');
        $this->db->where('a.category_id', $cat_id);
        $this->db->where('c.lang_id', $this->lang_id);
        $this->db->where_not_in('a.product_id', $p_id);
        $this->db->group_by('a.product_id');
        $query = $this->db->get();
        //
        return $query->result();
    }
    //Product review list from model website/Products_model
    public function review_list($p_id)
    {
        $this->db->select('*');
        $this->db->from('product_review');
        $this->db->where('product_id', $p_id);
        $this->db->order_by('product_review_id', 'desc');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    //Retrieve retrive refund policy from model website/Products_model
    public function retrieve_refund_policy()
    {
        $this->db->select('*');
        $this->db->from('link_page');
        $this->db->where('page_id', 7);
        $this->db->where('language_id', $this->lang_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }
    //Stock Report Single Product from model website/Products_model
    public function stock_report_single_item($p_id)
    {
        $this->db->select("sum(a.quantity) as totalPurchaseQnty");
        $this->db->from('product_information a');
        $this->db->where('a.product_id', $p_id);
        $this->db->where(array('a.status' => 2));
        $query = $this->db->get();
        $purchase = $query->row();

        $this->db->select("sum(a.quantity) as totalSalesQnty");
        $this->db->from('invoice_details a');
        $this->db->where('a.product_id', $p_id);
        $query = $this->db->get();
        $sale = $query->row();

        return $purchase->totalPurchaseQnty - $sale->totalSalesQnty;
    }
    //variant List from model Variants
    public function variant_list()
    {
        $this->db->select('*');
        $this->db->from('variant');
        $this->db->order_by('variant_name', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Multidimention array sorting from model website/Categories
    public function array_msort($array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) {
                $colarr[$col]['_' . $k] = strtolower($row[$col]);
            }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
        }
        $eval = substr($eval, 0, -1) . ');';
        eval($eval);
        $ret = array();
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k, 1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }
        return $ret;
    }
    //Select single category from model website/Categories
    public function select_single_category($cat_id)
    {
        $this->db->select('a.*,b.trans_category_name,b.meta_title meta_title_trans,b.meta_keyword meta_keyword_trans,b.meta_description meta_description_trans');
        $this->db->from('product_category a');
        $this->db->join('product_category_translation b', "b.category_id = a.category_id AND b.lang='$this->lang_id'", 'left');
        $this->db->where('a.category_id', $cat_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Category brand product from model website/Categories
    public function category_product($cat_id = null, $price_range = null, $size = null, $brand = null, $sort = null, $rate = null, $seller_rate = null)
    {

        $all_brand     = (explode("--", $brand));
        $arr2             = array();

        // $lang_id 	   = 0;
        // $user_lang 	   = $this->session->userdata('language');
        // if (empty($user_lang)) {
        // 	$lang_id = 'english';
        // }else{
        // 	$lang_id = $user_lang;
        // }

        $where = "(c.quantity > 0 OR c.pre_order = 1)";

        if ($sort == 'best_sale') {
            $this->db->select('
				c.*,
				d.title,
				e.brand_name,
				f.first_name,
				f.last_name,
				COUNT(`b`.`product_id`) as countval,
				pi.image_name
				');
            $this->db->from('order a');
            $this->db->join('seller_order b', 'a.order_id = b.order_id');
            $this->db->join('product_information c', 'c.product_id = b.product_id', 'left');
            $this->db->join('product_title d', 'd.product_id = c.product_id', 'left');
            $this->db->join('brand e', 'e.brand_id = c.brand_id', 'left');
            $this->db->join('seller_information f', 'f.seller_id = c.seller_id', 'left');
            $this->db->join('product_image pi', 'pi.product_id = c.product_id', 'left');
            if ($price_range) {
                $ex = explode("-", $price_range);
                $from = trim($ex[0]);
                $to = trim($ex[1]);
                $this->db->where('c.price >=', $from);
                $this->db->where('c.price <=', $to);
            }

            if ($brand) {
                $this->db->where_in('c.brand_id', $all_brand);
            }

            if ($size) {
                $this->db->where('c.variant_id', $size);
            }

            $this->db->where('b.category_id', $cat_id);
            $this->db->where('c.status', '2');
            $this->db->where($where);
            $this->db->group_by('b.product_id');
            $this->db->order_by('countval', 'desc');
            $this->db->group_by('c.product_id');
            $this->db->limit(32);
            $query = $this->db->get();
            $w_cat_pro = $query->result_array();
        } else {
            $where = "(a.quantity > 0 OR a.pre_order = 1)";

            $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name,
				pi.image_name');
            $this->db->from('product_information a');
            $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
            $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
            $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
            $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
            $this->db->where('b.lang_id', $this->lang_id);
            $this->db->where('a.category_id', $cat_id);
            $this->db->where('a.status', 2);


            if ($price_range) {
                $ex = explode("-", $price_range);
                $from = trim($ex[0]);
                $to = trim($ex[1]);
                $this->db->where('price >=', $from);
                $this->db->where('price <=', $to);
            }

            if ($brand) {
                $this->db->where_in('a.brand_id', $all_brand);
            }

            if ($size) {
                $this->db->where('a.variant_id', $size);
            }

            $this->db->where($where);

            $this->db->group_by('a.product_id');

            //$this->db->limit($per_page,$page);
            $query = $this->db->get();
            $w_cat_pro = $query->result_array();

            //First category
            $first_cat = $this->db->select('*')
                ->from('product_category')
                ->where('parent_category_id', $cat_id)
                ->where('cat_type', 2)
                ->get()
                ->result();
            if ($first_cat) {
                foreach ($first_cat as $f_cat) {

                    $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name,pi.image_name');
                    $this->db->from('product_information a');
                    $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                    $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                    $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                    $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
                    $this->db->where('b.lang_id', $this->lang_id);
                    $this->db->where('a.category_id', $f_cat->category_id);
                    $this->db->where('a.status', 2);

                    if ($price_range) {
                        $ex = explode("-", $price_range);
                        $from = trim($ex[0]);
                        $to = trim($ex[1]);
                        $this->db->where('price >=', $from);
                        $this->db->where('price <=', $to);
                    }

                    if ($brand) {
                        $this->db->where_in('a.brand_id', $all_brand);
                    }

                    if ($size) {
                        $this->db->where('a.variant_id', $size);
                    }

                    $this->db->where($where);

                    $this->db->group_by('a.product_id');

                    //$this->db->limit($per_page,$page);
                    $query = $this->db->get();
                    $first_cat_pro = $query->result_array();

                    if ($first_cat_pro) {
                        foreach ($first_cat_pro as $f_cat_pro) {
                            array_push($w_cat_pro, $f_cat_pro);
                        }
                    }

                    // Second category
                    $second_cat = $this->db->select('*')
                        ->from('product_category')
                        ->where('parent_category_id', $f_cat->category_id)
                        ->where('cat_type', 2)
                        ->get()
                        ->result();
                    if ($second_cat) {
                        foreach ($second_cat as $s_cat) {

                            $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name,pi.image_name');
                            $this->db->from('product_information a');
                            $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                            $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                            $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                            $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
                            $this->db->where('b.lang_id', $this->lang_id);
                            $this->db->where('a.category_id', $s_cat->category_id);
                            $this->db->where('a.status', 2);
                            if ($price_range) {
                                $ex = explode("-", $price_range);
                                $from = trim($ex[0]);
                                $to = trim($ex[1]);
                                $this->db->where('price >=', $from);
                                $this->db->where('price <=', $to);
                            }

                            if ($brand) {
                                $this->db->where_in('a.brand_id', $all_brand);
                            }

                            if ($size) {
                                $this->db->where('a.variant_id', $size);
                            }
                            $this->db->where($where);

                            $this->db->group_by('a.product_id');

                            //$this->db->limit($per_page,$page);
                            $query = $this->db->get();
                            $sec_cat_pro = $query->result_array();


                            if ($sec_cat_pro) {
                                foreach ($sec_cat_pro as $s_cat_pro) {
                                    array_push($w_cat_pro, $s_cat_pro);
                                }
                            }

                            //Third category
                            $third_cat = $this->db->select('*')
                                ->from('product_category')
                                ->where('parent_category_id', $s_cat->category_id)
                                ->where('cat_type', 2)
                                ->get()
                                ->result();

                            if ($third_cat) {
                                foreach ($third_cat as $t_cat) {

                                    $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name,pi.image_name');
                                    $this->db->from('product_information a');
                                    $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                                    $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                                    $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                                    $this->db->join('product_image pi', 'pi.product_id = a.product_id', 'left');
                                    $this->db->where('b.lang_id', $this->lang_id);
                                    $this->db->where('a.category_id', $t_cat->category_id);
                                    $this->db->where('a.status', 2);
                                    if ($price_range) {
                                        $ex = explode("-", $price_range);
                                        $from = $ex[0];
                                        $to = $ex[1];
                                        $this->db->where('price >=', $from);
                                        $this->db->where('price <=', $to);
                                    }
                                    if ($brand) {
                                        $this->db->where_in('a.brand_id', $all_brand);
                                    }
                                    if ($size) {
                                        $this->db->where('a.variant_id', $size);
                                    }
                                    $this->db->where($where);

                                    $this->db->group_by('a.product_id');

                                    //$this->db->limit($per_page,$page);
                                    $query = $this->db->get();
                                    $thrd_cat_pro = $query->result_array();

                                    if ($thrd_cat_pro) {
                                        foreach ($thrd_cat_pro as $t_cat_pro) {
                                            array_push($w_cat_pro, $t_cat_pro);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($rate) {
            $w_cat_pro = $this->get_rating_product($w_cat_pro, $rate);
        }

        if ($seller_rate) {
            $w_cat_pro = $this->get_product_by_seller_rate($w_cat_pro, $seller_rate);
        }

        if ($sort != null && $w_cat_pro != null) {
            if ($sort == 'new') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('product_info_id' => SORT_DESC));
            } elseif ($sort == 'discount') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('offer_price' => SORT_ASC));
            } elseif ($sort == 'low_to_high') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('price' => SORT_ASC));
            } elseif ($sort == 'high_to_low') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('price' => SORT_DESC));
            }
        } else {
            $w_cat_pro = $this->array_msort($w_cat_pro, array('product_info_id' => SORT_DESC));
        }

        return $w_cat_pro;
    }
    //Get rating product by rate from model website/Categories
    public function get_rating_product($w_cat_pro = null, $rate = '')
    {
        $rate = explode('-', $rate);
        $rate = $rate[0];

        $n_cat_pro = array();
        $lang_id = 0;
        $user_lang = $this->session->userdata('language');
        if (empty($user_lang)) {
            $lang_id = 'english';
        } else {
            $lang_id = $user_lang;
        }

        if ($w_cat_pro) {
            foreach ($w_cat_pro as $product) {
                $rater  = $this->get_total_rater_by_product_id($product['product_id']);
                $result = $this->get_total_rate_by_product_id($product['product_id']);
                if ($rater) {
                    $total_rate = $result->rates / $rater;
                    if ($total_rate >= $rate) {
                        $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name');
                        $this->db->from('product_information a');
                        $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                        $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                        $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                        $this->db->where('b.lang_id', $lang_id);
                        $this->db->where('a.product_id', $product['product_id']);
                        $this->db->where('a.status', 2);
                        $query = $this->db->get();
                        $thrd_cat_pro = $query->result_array();

                        if ($thrd_cat_pro) {
                            foreach ($thrd_cat_pro as $t_cat_pro) {
                                array_push($n_cat_pro, $t_cat_pro);
                            }
                        }
                    }
                }
            }
            return $n_cat_pro;
        } else {
            return $w_cat_pro;
        }
    }
    //Get product by seller score from model website/Categories
    public function get_product_by_seller_rate($w_cat_pro = null, $rate = '')
    {
        $rate = explode('-', $rate);
        $rate = $rate[0];

        $n_cat_pro = array();
        $lang_id = 0;
        $user_lang = $this->session->userdata('language');
        if (empty($user_lang)) {
            $lang_id = 'english';
        } else {
            $lang_id = $user_lang;
        }

        $rater = 0;
        $rates = 0;

        if ($w_cat_pro) {
            foreach ($w_cat_pro as $product) {
                $rater  = $this->get_total_rater_by_seller_id($product['seller_id']);
                $result = $this->get_total_rate_by_seller_id($product['seller_id']);
                if ($rater) {
                    $total_rate = $result->rates / $rater;
                    if ($total_rate >= $rate) {
                        $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name');
                        $this->db->from('product_information a');
                        $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                        $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                        $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                        $this->db->where('b.lang_id', $lang_id);
                        $this->db->where('a.product_id', $product['product_id']);
                        $this->db->where('a.status', 2);
                        $query = $this->db->get();
                        $thrd_cat_pro = $query->result_array();

                        if ($thrd_cat_pro) {
                            foreach ($thrd_cat_pro as $t_cat_pro) {
                                array_push($n_cat_pro, $t_cat_pro);
                            }
                        }
                    }
                }
            }
            if (count($n_cat_pro) == 0) {
                return $w_cat_pro;
            } else {
            }
        } else {
            return $w_cat_pro;
        }
    }
    //Get total rater by seller id from model website/Categories
    public function get_total_rater_by_seller_id($seller_id = null)
    {
        $rater = $this->db->select('rate')
            ->from('seller_review')
            ->where('seller_id', $seller_id)
            ->get()
            ->num_rows();
        return $rater;
    }
    //Get total rate by seller id from model website/Categories
    public function get_total_rate_by_seller_id($seller_id = null)
    {
        $rate = $this->db->select('sum(rate) as rates')
            ->from('seller_review')
            ->where('seller_id', $seller_id)
            ->get()
            ->row();
        return $rate;
    }
    //Get total rater by product id from model website/Categories
    public function get_total_rater_by_product_id($product_id = null)
    {
        $rater = $this->db->select('rate')
            ->from('product_review')
            ->where('product_id', $product_id)
            ->where('status', 1)
            ->get()
            ->num_rows();
        return $rater;
    }
    //Get total rate by product id from model website/Categories
    public function get_total_rate_by_product_id($product_id = null)
    {
        $rate = $this->db->select('sum(rate) as rates')
            ->from('product_review')
            ->where('product_id', $product_id)
            ->get()
            ->row();
        return $rate;
    }
    //Select all sub category product from model website/Categories
    public function select_total_sub_cat_pro($cat_id = null, $brand = null)
    {

        $all_brand = (explode("--", $brand));
        $total_pro = 0;
        $lang_id   = 0;
        $user_lang = $this->session->userdata('language');
        if (empty($user_lang)) {
            $lang_id = 'english';
        } else {
            $lang_id = $user_lang;
        }

        $price_range = filter_input_get('price');
        $size        = filter_input_get('size');
        $sort        = filter_input_get('sort');
        $rate        = filter_input_get('rate');
        $seller_rate = filter_input_get('seller_rate');

        $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name');
        $this->db->from('product_information a');
        $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
        $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
        $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
        $this->db->where('b.lang_id', $lang_id);
        $this->db->where('a.category_id', $cat_id);
        $this->db->where('a.status', 2);

        if ($price_range) {
            $ex = explode("-", $price_range);
            $from = $ex[0];
            $to = $ex[1];
            $this->db->where('price >=', $from);
            $this->db->where('price <=', $to);
        }

        if ($brand) {
            $this->db->where_in('a.brand_id', $all_brand);
        }

        if ($size) {
            $this->db->where('a.variant_id', $size);
        }
        $query = $this->db->get();
        $w_cat_pro = $query->result_array();

        //First category
        $first_cat = $this->db->select('*')
            ->from('product_category')
            ->where('parent_category_id', $cat_id)
            ->where('cat_type', 2)
            ->get()
            ->result();
        if ($first_cat) {
            foreach ($first_cat as $f_cat) {

                $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name');
                $this->db->from('product_information a');
                $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                $this->db->where('b.lang_id', $lang_id);
                $this->db->where('a.category_id', $f_cat->category_id);
                $this->db->where('a.status', 2);

                if ($price_range) {
                    $ex = explode("-", $price_range);
                    $from = $ex[0];
                    $to = $ex[1];
                    $this->db->where('price >=', $from);
                    $this->db->where('price <=', $to);
                }

                if ($brand) {
                    $this->db->where_in('a.brand_id', $all_brand);
                }

                if ($size) {
                    $this->db->where('a.variant_id', $size);
                }
                $query = $this->db->get();
                $first_cat_pro = $query->result_array();

                if ($first_cat_pro) {
                    foreach ($first_cat_pro as $f_cat_pro) {
                        array_push($w_cat_pro, $f_cat_pro);
                    }
                }

                // Second category
                $second_cat = $this->db->select('*')
                    ->from('product_category')
                    ->where('parent_category_id', $f_cat->category_id)
                    ->where('cat_type', 2)
                    ->get()
                    ->result();
                if ($second_cat) {
                    foreach ($second_cat as $s_cat) {

                        $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name');
                        $this->db->from('product_information a');
                        $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                        $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                        $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                        $this->db->where('b.lang_id', $lang_id);
                        $this->db->where('a.category_id', $s_cat->category_id);
                        $this->db->where('a.status', 2);
                        if ($price_range) {
                            $ex = explode("-", $price_range);
                            $from = $ex[0];
                            $to = $ex[1];
                            $this->db->where('price >=', $from);
                            $this->db->where('price <=', $to);
                        }

                        if ($brand) {
                            $this->db->where_in('a.brand_id', $all_brand);
                        }

                        if ($size) {
                            $this->db->where('a.variant_id', $size);
                        }
                        $query = $this->db->get();
                        $sec_cat_pro = $query->result_array();


                        if ($sec_cat_pro) {
                            foreach ($sec_cat_pro as $s_cat_pro) {
                                array_push($w_cat_pro, $s_cat_pro);
                            }
                        }

                        //Third category
                        $third_cat = $this->db->select('*')
                            ->from('product_category')
                            ->where('parent_category_id', $s_cat->category_id)
                            ->where('cat_type', 2)
                            ->get()
                            ->result();

                        if ($third_cat) {
                            foreach ($third_cat as $t_cat) {

                                $this->db->select('a.*,b.*,c.brand_name,d.first_name,d.last_name');
                                $this->db->from('product_information a');
                                $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                                $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                                $this->db->join('seller_information d', 'd.seller_id = a.seller_id', 'left');
                                $this->db->where('b.lang_id', $lang_id);
                                $this->db->where('a.category_id', $t_cat->category_id);
                                $this->db->where('a.status', 2);
                                if ($price_range) {
                                    $ex = explode("-", $price_range);
                                    $from = $ex[0];
                                    $to = $ex[1];
                                    $this->db->where('price >=', $from);
                                    $this->db->where('price <=', $to);
                                }

                                if ($brand) {
                                    $this->db->where_in('a.brand_id', $all_brand);
                                }
                                if ($size) {
                                    $this->db->where('a.variant_id', $size);
                                }
                                $query = $this->db->get();
                                $thrd_cat_pro = $query->result_array();

                                if ($thrd_cat_pro) {
                                    foreach ($thrd_cat_pro as $t_cat_pro) {
                                        array_push($w_cat_pro, $t_cat_pro);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($rate) {
            $w_cat_pro = $this->get_rating_product($w_cat_pro, $rate);
        }

        if ($seller_rate) {
            $w_cat_pro = $this->get_product_by_seller_rate($w_cat_pro, $seller_rate);
        }

        if ($sort != null && $w_cat_pro != null) {
            if ($sort == 'new') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('product_info_id' => SORT_DESC));
            } elseif ($sort == 'discount') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('offer_price' => SORT_ASC));
            } elseif ($sort == 'low_to_high') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('price' => SORT_ASC));
            } elseif ($sort == 'high_to_low') {
                $w_cat_pro = $this->array_msort($w_cat_pro, array('price' => SORT_DESC));
            }
        } else {
            $w_cat_pro = $this->array_msort($w_cat_pro, array('product_info_id' => SORT_DESC));
        }
        return count($w_cat_pro);
    }
    //Select max price of a category product from model website/Categories
    public function select_max_value_of_cat_pro($cat_id = null, $val = null)
    {
        $arr2      = array();

        $sort = filter_input_data($this->input->post('sort', TRUE));

        if ($sort == 'best_sale') {
            $this->db->select('
				c.*,
				d.title,
				e.brand_name,
				f.first_name,
				f.last_name
				');
            $this->db->from('order a');
            $this->db->join('seller_order b', 'a.order_id = b.order_id');
            $this->db->join('product_information c', 'c.product_id = b.product_id', 'left');
            $this->db->join('product_title d', 'd.product_id = c.product_id', 'left');
            $this->db->join('brand e', 'e.brand_id = c.brand_id', 'left');
            $this->db->join('seller_information f', 'f.seller_id = c.seller_id', 'left');
            $this->db->where('b.category_id', $cat_id);
            $this->db->group_by('b.product_id');
            $query = $this->db->get();
            $w_cat_pro = $query->result_array();
        } else {
            $this->db->select('a.*,b.*');
            $this->db->from('product_information a');
            $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
            $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
            $this->db->where('b.lang_id', $this->lang_id);
            $this->db->where('a.category_id', $cat_id);
            $this->db->where('a.status', 2);
            $query = $this->db->get();
            $w_cat_pro = $query->result_array();

            //First category
            $first_cat = $this->db->select('*')
                ->from('product_category')
                ->where('parent_category_id', $cat_id)
                ->where('cat_type', 2)
                ->get()
                ->result();
            if ($first_cat) {
                foreach ($first_cat as $f_cat) {

                    $this->db->select('a.*,b.*');
                    $this->db->from('product_information a');
                    $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                    $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                    $this->db->where('b.lang_id', $this->lang_id);
                    $this->db->where('a.category_id', $f_cat->category_id);
                    $this->db->where('a.status', 2);
                    $query = $this->db->get();
                    $first_cat_pro = $query->result_array();

                    if ($first_cat_pro) {
                        foreach ($first_cat_pro as $f_cat_pro) {
                            array_push($w_cat_pro, $f_cat_pro);
                        }
                    }

                    // Second category
                    $second_cat = $this->db->select('*')
                        ->from('product_category')
                        ->where('parent_category_id', $f_cat->category_id)
                        ->where('cat_type', 2)
                        ->get()
                        ->result();
                    if ($second_cat) {
                        foreach ($second_cat as $s_cat) {

                            $this->db->select('a.*,b.*');
                            $this->db->from('product_information a');
                            $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                            $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                            $this->db->where('b.lang_id', $this->lang_id);
                            $this->db->where('a.category_id', $s_cat->category_id);
                            $this->db->where('a.status', 2);
                            $query = $this->db->get();
                            $sec_cat_pro = $query->result_array();


                            if ($sec_cat_pro) {
                                foreach ($sec_cat_pro as $s_cat_pro) {
                                    array_push($w_cat_pro, $s_cat_pro);
                                }
                            }

                            //Third category
                            $third_cat = $this->db->select('*')
                                ->from('product_category')
                                ->where('parent_category_id', $s_cat->category_id)
                                ->where('cat_type', 2)
                                ->get()
                                ->result();

                            if ($third_cat) {
                                foreach ($third_cat as $t_cat) {

                                    $this->db->select('a.*,b.*');
                                    $this->db->from('product_information a');
                                    $this->db->join('product_title b', 'a.product_id = b.product_id', 'left');
                                    $this->db->join('brand c', 'c.brand_id = a.brand_id', 'left');
                                    $this->db->where('b.lang_id', $this->lang_id);
                                    $this->db->where('a.category_id', $t_cat->category_id);
                                    $this->db->where('a.status', 2);
                                    $query = $this->db->get();
                                    $thrd_cat_pro = $query->result_array();

                                    if ($thrd_cat_pro) {
                                        foreach ($thrd_cat_pro as $t_cat_pro) {
                                            array_push($w_cat_pro, $t_cat_pro);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($val == 1) {
            return $pro = $this->maxValueInArray($w_cat_pro, 'price');
        } else {
            return $pro = $this->min_by_key($w_cat_pro, 'price');
        }
    }
    //Select min value of product from model website/Categories
    public function maxValueInArray($array, $keyToSearch)
    {
        $currentMax = NULL;
        foreach ($array as $arr) {
            foreach ($arr as $key => $value) {
                if ($key == $keyToSearch && ($value >= $currentMax)) {
                    $currentMax = $value;
                }
            }
        }

        return $currentMax;
    }
    //Minvalue by array key from model website/Categories
    public function min_by_key($arr = null, $key = null)
    {
        $min = array();
        foreach ($arr as $val) {
            if (!isset($val[$key]) and is_array($val)) {
                $min2 = $this->min_by_key($val, $key);
                $min[$min2] = 1;
            } elseif (!isset($val[$key]) and !is_array($val)) {
                return false;
            } elseif (isset($val[$key])) {
                $min[$val[$key]] = 1;
            }
        }
        if (count($min) > 0) {
            return min(array_keys($min));
        } else {
            return 0;
        }
    }
    //Shipping Entry from model website\Homes.php
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
    //Billing Entry from model website\Homes.php
    public function customer_information_insert($data)
    {
        $bill = $this->db->insert('customer_information', $data);
        if ($bill) {
            return true;
        }
        return false;
    }
    
    //	payment getway for liplimal
    public function payment_by_liplimal($confirm, $email, $cart_contents, $ship_cost = 0, $cart_total_amount = 0, $coupon_amt = 0, $payment_history_id = 0, $liyeplimal_data = 0, $cart_details = null, $order_details_info = null, $costing_info = null, $paid_amount = null)
    {




        //set up value of different variable start
        $coupon_amnt = 0;
        $paid_amount = (!empty($paid_amount) ? $paid_amount : 0);
        //costing_info
        $vat            = (!empty($costing_info['vat_amount']) ? $costing_info['vat_amount'] : 0);
        $cart_ship_cost = (!empty($costing_info['ship_cost']) ? $costing_info['ship_cost'] : 0);
        $discount       = (!empty($costing_info['discount']) ? $costing_info['discount'] : 0);
        $totalAmount    = (!empty($costing_info['totalAmount']) ? $costing_info['totalAmount'] : 0);
        //order_details_info
        $customer_id = $order_details_info['customer_id'];
        $order_id = $order_details_info['order_id'];
        $order_details = $order_details_info['order_details'];
        $payment_method = $order_details_info['payment_method'];
        //set up value of different variable end


        //checking stock quantity start
        if (!empty($cart_details)) {
            foreach ($cart_details as $items) {
                $stock = $this->db->select('*')
                    ->from('product_information')
                    ->where('product_id', $items->product_id)
                    //->where('pre_order',1)
                    ->get()
                    ->row();
                if (!empty($stock)) {
                    if ($stock->quantity < $items->qty) {
                        JSONErrorOutput(display('you_can_not_order_more_than_stock'));
                    }
                }
            }
        }


        //checking stock quantity end
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
        if ($cart_details) {
            $quantity = 0;
            foreach ($cart_details as $items) {
                if (!empty($items)) {
                    //Seller percentage
                    $comission_rate = $this->comission_info($items->product_id);
                    $category_id   = $this->category_id($items->product_id);
                    //seller_order_data
                    $seller_order_data = array(
                        'order_id'                =>    $order_id,
                        'seller_id'                =>    '',
                        'seller_percentage'     =>  $comission_rate,
                        'customer_id'            =>    $customer_id,
                        'category_id'            =>    $category_id,
                        'product_id'            =>    $items->product_id,
                        'variant_id'            =>    '',
                        'quantity'                =>    $items->qty,
                        'rate'                    =>    $items->price,
                        'discount_per_product'    =>    '',
                        'product_vat'            =>    '',
                        'total_price'           => ($items->price * $items->qty),
                    );
                    //Total quantity count
                    $quantity += $items->qty;
                    $this->db->insert('seller_order', $seller_order_data);
                    //Product stock update
                    $this->db->set('quantity', 'quantity-' . $items->qty, FALSE);
                    $this->db->where('product_id', $items->product_id);
                    $this->db->update('product_information');
                }
            }
        }
        //Insert order to seller_order table and update quantity in product_information start

        // if ($pre_order) {
        // 	$total_discount = $pre_order['total_discount'] + $coupon_amnt;
        // 	$p_order = array(
        // 		'order_id' 	  	=> $pre_order['order_id'], 
        // 		'customer_id' 	=> $pre_order['customer_id'], 
        // 		'shipping_id' 	=> $this->session->userdata('method_id'),
        // 		'date' 		  	=> date("Y-m-d"), 
        // 		'total_amount'	=> $pre_order['total_amount']+$cart_ship_cost+$this->session->userdata('total_tax')-$coupon_amnt,
        // 		'affiliate_id'  => $affiliate_id,
        // 		'details'	  	=> $this->session->userdata('order_details'),
        // 		'total_discount'=> $total_discount,
        // 		'number_product'=> $pre_order['number_product'],
        // 		'service_charge'=> $cart_ship_cost,
        // 		'vat' 			=> $vat
        // 	);
        // 	$this->db->insert('pre_order',$p_order);
        // }

        // if ($order) {
        //Data insert into order table
        $n_order = array(
            'order_id'           => $order_id,
            'order_no'           => $this->order_number_generator(),
            'customer_id'     => $customer_id,
            'shipping_id'     => 'city',
            'date'               => date("Y-m-d"),
            'total_amount'    => $totalAmount,
            'affiliate_id'  => 'ordered_from_apk',
            'details'          => $order_details,
            'paid_amount'     => $paid_amount,
            'total_discount' => ($discount + $coupon_amnt),
            'number_product' => '',
            'service_charge' => $cart_ship_cost,
            'vat'             => $vat,
            'pending'        => date("Y-m-d")
        );
        $this->db->insert('order', $n_order);

        //Order intsert in order_tracking table
        $order_tracking = array(
            'order_id'           => $order_id,
            'customer_id'        => $customer_id,
            'date'               => date("Y-m-d h:i a"),

        );
        $this->db->insert('order_tracking', $order_tracking);

        // Digital Item Entry
        //$this->digital_entry($order_id, $customer_id, $totalAmount, $paid_amount);
        // }
        // Remove Coupon logs
        //$this->db->delete('coupon_logs', array('customer_id' => $customer_id));
        //$this->session->unset_userdata(array('coupon_id', 'coupon_amnt'));

        //Return order_id 
        //return $order_id;







        $send_data = array(
            'confirm' => $confirm,
            'email' => $email,
            'cart_contents' => $cart_contents,
            'ship_cost' => $ship_cost,
            'cart_total_amount' => $cart_total_amount,
            'coupon_amt' => $coupon_amt,
            'payment_history_id' => $payment_history_id,

        );
        //print_r($liyeplimal_data['cart_total_amount']);exit;
        //echo 'liplimal2';
        $CI = &get_instance();
        // Currency Check
        $currency_new_id  =  $CI->session->userdata('currency_new_id');

        $usd_cur  =  $CI->db->select('*')
            ->from('currency_info')
            ->where('currency_id', '8UD4F1XGKHV7UDX')
            ->get()
            ->row();

        $con_rate = $usd_cur->convertion_rate;


        if ($usd_cur->currency_id == $currency_new_id) {
            $con_total_price = $cart_total_amount;
            $shipping_charge = $ship_cost;
            $coupon_total_amt = $coupon_amt;
        } else {
            $con_total_price = $cart_total_amount * $usd_cur->convertion_rate;
            $shipping_charge = $ship_cost * $usd_cur->convertion_rate;
            $coupon_total_amt = $coupon_amt * $usd_cur->convertion_rate;
        }
        $send_data2 = array(
            'con_total_price' => $con_total_price,
            'shipping_charge' => $shipping_charge,
            'coupon_total_amt' => $coupon_total_amt,
            
        );
        // print_r($send_data2);exit;
        //var_dump($cart_contents);exit;
        $cart_details = '';
        $total_price = $total_order_dis = 0;
        if ($cart_contents) {

            foreach ($cart_contents as $items_data) {
                $items = (array)$items_data;
                $itemprice = ($items['price'] * $con_rate);
                $vatamount = (0 * $con_rate);
                $total_order_dis += (0 * $items['qty']);

                $total_price += (($items['price'] * $items['qty'])) * $con_rate; //-$total_order_dis

                $cart_details .= '@name= ' . ' @price=' . $items['price'] . ' @qty=' . $items['qty'] . ' @vat_amount=0' . ' @discount=0' . ' ## ';

                $products[] = array(
                    'product_name'     => '',
                    'product_qty'     => $items['qty'],
                    'product_model'  => '',
                    'product_price'     => $itemprice - $vatamount,

                );
            }
        }
        $final_total_price = floatval($total_price + $shipping_charge - $coupon_total_amt);

        // d($total_price);
        // d($shipping_charge);
        // d($coupon_total_amt);
        // dd($final_total_price);

        // $final_total_price = floatval($con_total_price);

        $jsonencode_products = json_encode($products);
        $products       = $jsonencode_products;
        $total_qty      = 1;
        //$product_model  = "SIMTREX";

        //$gateway        = $this->input->post('gateway');
        $token          = md5(@$email . @$products[0]['product_model'] . time());
        $old_token      = $token;
        $new_token = hash('sha256', "VXTNULL". $token . 'TaReQ');
        $callback       = base_url('limoney_confirm_apk/?apk=yes&order_id='.$liyeplimal_data['order_id'].'&customer_id='.$liyeplimal_data['customer_id'].'&cart_total_amount='.$liyeplimal_data['cart_total_amount'].'&old_token='.$new_token.'&');

        $CI->session->set_userdata('token', $token);
        $used_data = array(
            'first_token' => $token, 
            'amount' => $final_total_price, 
            'con_rate' => $con_rate, 
            'shipping_charge' => $shipping_charge, 
            'coupon_total_amount' => $coupon_total_amt, 
            'cart_details' => $cart_details
        );
        //print_r($used_data);exit;
        $CI->db->update('payment_history', array('first_token' => $token, 'amount' => $final_total_price, 'con_rate' => $con_rate, 'shipping_charge' => $shipping_charge, 'coupon_total_amount' => $coupon_total_amt, 'cart_details' => $cart_details), array('id' => $payment_history_id));

        if ($confirm) {
            //exit;

            //set POST variables
            $url    = "https://www.liyeplimal.net/limoney/authcheck";
            $fields = array(
                'email'         => urlencode($email),
                'products'      => urlencode($jsonencode_products),
                'total_qty'     => urlencode($total_qty),
                //'product_model' => urlencode($product_model),
                // 'total_price'   => urlencode($total_price),
                'total_price'   => urlencode($final_total_price),
                //'gateway'       => urlencode($gateway),
                'callback'      => urlencode($callback),
                'token'         => urlencode($token),
                'domain'        => urlencode('limarket'), //simtrex or limarket
                'shipping_charge'  => urlencode($shipping_charge) //Shipping Charge
            );
            //print_r($fields);exit();


            $fields_string = '';
            //url-ify the data for the POST
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            $fields_string = rtrim($fields_string, '&');

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);
            $info = curl_getinfo($ch);

            //05-04-2021 Harun
            // $message = "Limo Payment API Error: " . $info['http_code'] . " Service Not Available";
            // //header('Location: ' . $_SERVER['HTTP_REFERER']);
            // $CI->session->set_userdata('error_message', $message);
            // redirect(base_url('checkout'));
            // exit;
            //echo "<pre>";echo$info['http_code'];exit;
            //echo "<pre>";echo$result;exit;
            if (curl_errno($ch)) {
                print "Error: " . curl_error($ch);
            } else if ($info['http_code'] == 200) {
                //close connection
                curl_close($ch);

                //$new_token = hash('sha256', "VXTNULL". $token . 'TaReQ');
                $CI->session->set_userdata('token', $new_token);

                $CI->db->update('payment_history', array('token' => $new_token), array('id' => $payment_history_id));

                // print $result ;
                // redirect("https://www.liyeplimal.net/limoney/login/?token=$old_token");
                $response = ("https://www.liyeplimal.net/limoney/login/?token=$old_token");
                JSONSuccessOutput($response, check_api_token());
            } else {
                $message = "Limo Payment API Error: " . $info['http_code'] . " Service Not Available";
                JSONErrorOutput($message);
                //header('Location: ' . $_SERVER['HTTP_REFERER']);
                //$CI->session->set_userdata('error_message', $message);
                //redirect(base_url('checkout'));
            }
        }
    }
    //Order entry from model website\Homes.php
    public function order_entry($cart_details = null, $order_details_info = null, $costing_info = null, $paid_amount = null)
    {
        //set up value of different variable start
        $coupon_amnt = 0;
        $paid_amount = (!empty($paid_amount) ? $paid_amount : 0);
        //costing_info
        $vat            = (!empty($costing_info['vat_amount']) ? $costing_info['vat_amount'] : 0);
        $cart_ship_cost = (!empty($costing_info['ship_cost']) ? $costing_info['ship_cost'] : 0);
        $discount       = (!empty($costing_info['discount']) ? $costing_info['discount'] : 0);
        $totalAmount    = (!empty($costing_info['totalAmount']) ? $costing_info['totalAmount'] : 0);
        //order_details_info
        $customer_id = $order_details_info['customer_id'];
        $order_id = $order_details_info['order_id'];
        $order_details = $order_details_info['order_details'];
        $payment_method = $order_details_info['payment_method'];
        //set up value of different variable end
        //checking stock quantity start
        if (!empty($cart_details)) {
            foreach ($cart_details as $items) {
                $stock = $this->db->select('*')
                    ->from('product_information')
                    ->where('product_id', $items->product_id)
                    //->where('pre_order',1)
                    ->get()
                    ->row();
                if (!empty($stock)) {
                    if ($stock->quantity < $items->qty) {
                        JSONErrorOutput(display('you_can_not_order_more_than_stock'));
                    }
                }
            }
        }
        //checking stock quantity end
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
        if ($cart_details) {
            $quantity = 0;
            foreach ($cart_details as $items) {
                if (!empty($items)) {
                    //Seller percentage
                    $comission_rate = $this->comission_info($items->product_id);
                    $category_id   = $this->category_id($items->product_id);
                    //seller_order_data
                    $seller_order_data = array(
                        'order_id'                =>    $order_id,
                        'seller_id'                =>    '',
                        'seller_percentage'     =>  $comission_rate,
                        'customer_id'            =>    $customer_id,
                        'category_id'            =>    $category_id,
                        'product_id'            =>    $items->product_id,
                        'variant_id'            =>    '',
                        'quantity'                =>    $items->qty,
                        'rate'                    =>    $items->price,
                        'discount_per_product'    =>    '',
                        'product_vat'            =>    '',
                        'total_price'           => ($items->price * $items->qty),
                    );
                    //Total quantity count
                    $quantity += $items->qty;
                    $this->db->insert('seller_order', $seller_order_data);
                    //Product stock update
                    $this->db->set('quantity', 'quantity-' . $items->qty, FALSE);
                    $this->db->where('product_id', $items->product_id);
                    $this->db->update('product_information');
                }
            }
        }
        //Insert order to seller_order table and update quantity in product_information start

        // if ($pre_order) {
        // 	$total_discount = $pre_order['total_discount'] + $coupon_amnt;
        // 	$p_order = array(
        // 		'order_id' 	  	=> $pre_order['order_id'], 
        // 		'customer_id' 	=> $pre_order['customer_id'], 
        // 		'shipping_id' 	=> $this->session->userdata('method_id'),
        // 		'date' 		  	=> date("Y-m-d"), 
        // 		'total_amount'	=> $pre_order['total_amount']+$cart_ship_cost+$this->session->userdata('total_tax')-$coupon_amnt,
        // 		'affiliate_id'  => $affiliate_id,
        // 		'details'	  	=> $this->session->userdata('order_details'),
        // 		'total_discount'=> $total_discount,
        // 		'number_product'=> $pre_order['number_product'],
        // 		'service_charge'=> $cart_ship_cost,
        // 		'vat' 			=> $vat
        // 	);
        // 	$this->db->insert('pre_order',$p_order);
        // }

        // if ($order) {
        //Data insert into order table
        $n_order = array(
            'order_id'           => $order_id,
            'order_no'           => $this->order_number_generator(),
            'customer_id'     => $customer_id,
            'shipping_id'     => 'city',
            'date'               => date("Y-m-d"),
            'total_amount'    => $totalAmount,
            'affiliate_id'  => 'ordered_from_apk',
            'details'          => $order_details,
            'paid_amount'     => $paid_amount,
            'total_discount' => ($discount + $coupon_amnt),
            'number_product' => '',
            'service_charge' => $cart_ship_cost,
            'vat'             => $vat,
            'pending'        => date("Y-m-d")
        );
        $this->db->insert('order', $n_order);

        //Order intsert in order_tracking table
        $order_tracking = array(
            'order_id'           => $order_id,
            'customer_id'     => $customer_id,
            'date'               => date("Y-m-d h:i a"),

        );
        $this->db->insert('order_tracking', $order_tracking);

        // Digital Item Entry
        //$this->digital_entry($order_id, $customer_id, $totalAmount, $paid_amount);
        // }
        // Remove Coupon logs
        //$this->db->delete('coupon_logs', array('customer_id' => $customer_id));
        //$this->session->unset_userdata(array('coupon_id', 'coupon_amnt'));

        //Return order_id 
        return $order_id;
    }
    //Retrieve order_html_data from model website\Homes.php
    public function retrieve_order_html_data($order_id)
    {

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
        $this->db->where('g.lang_id', $this->lang_id);
        $query = $this->db->get();
        echo $this->db->last_query();
        exit;

        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //from model website\Homes.php
    public function digital_entry($order_id, $customer_id, $total_amount = 0, $paid_amount = 0)
    {
        if ($this->cart->contents()) {

            $product_ids = array_column($this->cart->contents(), 'product_id');

            $products = $this->db->select('product_id, is_digital')
                ->from('product_information')
                ->where_in('product_id', $product_ids)
                ->where('is_digital', '1')
                ->get()
                ->result();

            if ($products) {
                $status = (($paid_amount >= ($total_amount / 2)) ? '1' : '0');
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
    //NUMBER GENERATOR from model website\Homes.php
    public function order_number_generator()
    {
        $this->db->select_max('order_no');
        $query = $this->db->get('order');
        $result = $query->result_array();
        $order_no = $result[0]['order_no'];
        if ($order_no != '') {
            $order_no = $order_no + 1;
        } else {
            $order_no = 100000;
        }
        return $order_no;
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
    //Select country from model website\Homes.php
    public function get_country($country_id)
    {
        $country = $this->db->select('*')
            ->from('countries')
            ->where('id', $country_id)
            ->get()
            ->row();
        return $country;
    }
}
