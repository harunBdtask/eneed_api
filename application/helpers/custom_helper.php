<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!function_exists('remoteFileExists')){
    function remoteFileExists($url) {
        $curl = curl_init($url);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

            if ($statusCode == 200) {
                $ret = true;   
            }
        }

        curl_close($curl);

        return $ret;
    }
}

if(!function_exists('is_valid_extension')){
    function is_valid_extension($fields, $allowed=array()){
        foreach($fields as $field){
            if(empty($_FILES[$field]['name'])){
                continue;
            }
            if(empty($allowed)){
                $allowed = array('gif', 'png', 'jpg', 'jpeg');
            }
            $filename = $_FILES[$field]['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed)) {
                return false;
            }            
        }    
        return true;
    }
}

if(!function_exists('filter_input_post')){
    function filter_input_post($field, $optional = FALSE){

         $CI =& get_instance();

         $value = $CI->input->post($field, TRUE);
        if(!empty($value) && is_array($value)){
            foreach($value as $key => $val){
                $value[$key] = trim($val);
            }
        } else if(!empty($value)){
            $value = trim($value);
        }
         return $value;//$CI->db->escape_str($value);
        
    }
}

if(!function_exists('filter_input_get')){
    function filter_input_get($field, $optional = FALSE){

         $CI =& get_instance();

         $value = $CI->input->get($field, TRUE);
         if(!empty($value)){
            $value = trim($value);
        }

         return $value;//$CI->db->escape_str($value);
        
    }
}

if ( ! function_exists('remove_space'))
{
    function remove_space($var = '')    {
       $string = str_replace(' ','-', $var);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }
}

if(!function_exists('clean')){
    function clean($str) {
        $title = strip_tags($str);
        $title = str_replace(array('(',')','-',"'"), ' ', $title);
        $title = filter_var($title, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_AMP);
        $title = preg_replace('/[^A-Za-z0-9\-]/', ' ', $title);
        return $title;
    }
}


if(!function_exists('d')){
    function d($data=''){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}

if(!function_exists('dd')){
    function dd($data=''){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit();
    }
}

if(!function_exists('product_url')){
    function product_url($category, $product, $id, $affid = false){

         $CI =& get_instance();
         $base_url = $CI->config->base_url();

         $ref = (!empty($affid)?"?ref=$affid":"");

         if(empty($category) || empty($product)){
            return  $base_url. 'product_details' . '/' . $id.$ref;
            }
        return  $base_url .'product/'. remove_space($category) . '/' . remove_space($product) . '/' . $id.$ref;
        
    }
}

if(!function_exists('website_cproduct')){
    function website_cproduct($category_name, $category_id){

        $CI =& get_instance();
        $base_url = $CI->config->base_url();

        $CI->db->select('*');
        $CI->db->from('product_category');
        $CI->db->where('status',1);
        $CI->db->where('category_id',$category_id);
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            return  $base_url . 'c/' . remove_space($category_name) . '/' . remove_space($category_id);
        }else{
            $CI->session->set_flashdata('error_message', 'Not Available Now!!');
            redirect($base_url) ;	
        }
        
        
    }
}

if(!function_exists('get_sub_category_brands')){
    function get_sub_category_brands($block_catid){
        $CI =& get_instance();
        $CI->load->model('homes');
        $result = $CI->homes->get_category_brands($block_catid);
        return $result;
    }
}

// Get Currency Amount
if(!function_exists('get_amount')){
    function get_amount($amount){
        $CI =& get_instance();

        $currency_new_id = $CI->session->userdata('currency_new_id');

        if (empty($currency_new_id)) {
            $result = $cur_info = $CI->db->select('*')
                ->from('currency_info')
                ->where('default_status', '1')
                ->get()
                ->row();
            $currency_new_id = $result->currency_id;
        }

        if (!empty($currency_new_id)) {
            $cur_info = $CI->db->select('*')
                ->from('currency_info')
                ->where('currency_id', $currency_new_id)
                ->get()
                ->row();

            $target_con_rate = $cur_info->convertion_rate;
            $position1 = $cur_info->currency_position;
            $currency1 = $cur_info->currency_icon;
        }

        $result = (($position1 == 0) ? $currency1 . " " . number_format($amount*$target_con_rate, 2, '.', ',') : number_format($amount*$target_con_rate, 2, '.', ',') . " " . $currency1);
        return $result;
    }
}




if(!function_exists('sms_template')){
    function sms_template($template_type=null){
        $CI =& get_instance();
        $CI->db->select('*');
		$CI->db->from('sms_template');
        if (!empty($template_type)) {
            $CI->db->where('type', $template_type);
        }
        $CI->db->where('status', 1);
		$query = $CI->db->get();
        $result = $query->result_array();	
        return $result;
    }
    
        
}



if(!function_exists('sms_gateway')){
    function sms_gateway($gateway=null){
        $CI =& get_instance();
        $CI->db->select('*');
		$CI->db->from('sms_configuration');
        if (!empty($gateway)) {
            $CI->db->where('gateway', $gateway);
        }
        $CI->db->where('status', 1);
		$query = $CI->db->get();
        $result = $query->result_array();	
        return $result;
    }
}



if(!function_exists('send_sms')){
    function send_sms($contacts, $msg){
        $contacts = '88'.$contacts;
        $CI =& get_instance();
        $sms_credential = sms_gateway('BdtaskSMS');

        if (count($sms_credential) == 1) {
            // $url = "https://sms.bdtask.com/smsapi";
            $url = $sms_credential[0]['link'];
            $data = [
                "api_key" => $sms_credential[0]['user_name'],
                "type" => 'text/unicode',
                "contacts" => $contacts,
                "senderid" => $sms_credential[0]['sms_from'],
                "msg" => ($msg)
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }else{
            // echo 'Please Specify SMS Gateway Name';exit;
            $CI->session->set_flashdata('error_message', 'Please Specify SMS Gateway Name');
            redirect(base_url());
        }
    }
}




// Get Currency Amount
if(!function_exists('get_amount_value')){
    function get_amount_value($amount){
        $CI =& get_instance();

        $currency_new_id = $CI->session->userdata('currency_new_id');

       
        if (!empty($currency_new_id)) {
            $cur_info = $CI->db->select('*')
                ->from('currency_info')
                ->where('currency_id', $currency_new_id)
                ->get()
                ->row();

        }else{

            $cur_info = $CI->db->select('*')
                ->from('currency_info')
                ->where('default_status', '1')
                ->get()
                ->row();
        }

        $target_con_rate = $cur_info->convertion_rate;


        $result = number_format($amount*$target_con_rate, 2, '.', ',');
        return $result;
    }
}

// Get Currency Amount
if(!function_exists('get_xaf_currency')){
    function get_xaf_currency($amount){
        $CI =& get_instance();

        $currency_new_id = $CI->session->userdata('currency_new_id');

        if (!empty($currency_new_id)) {
            $cur_info = $CI->db->select('*')
                ->from('currency_info')
                ->where('currency_id', $currency_new_id)
                ->get()
                ->row();

            if($cur_info->default_status != '1')
            {
                $amount = ($amount / $cur_info->convertion_rate);
            }

        }

        $result = floatval($amount);
        return $result;
    }
}

// Get Payment method name
// Get Currency Amount
if(!function_exists('get_payment_method_name')){
    function get_payment_method_name($method_id){

        switch ($method_id) {
            case '1':
                return display('cash_on_delivery');
                break;
            case '2':
                return '';
                break;
             case '3':
                return display('bitcoin');
                break;
             case '4':
                return display('payeer');
                break;
             case '5':
                return display('paypal');
                break;
             case '6':
                return display('liyeplimal');
                break;
             case '7':
                return display('credit_or_debit_card');
                break;
            case '8':
                return display('orange_money');
                break;
            default:
                return '';
                break;
        }
    }
}

if(!function_exists('event_calendar')){
    function event_calendar($activity_time){
        $CI =& get_instance();
        
        //current month holiday query
        $query = $CI->db->from('todolist')->where("DATE(activity_time) >=", $activity_time)->order_by("activity_time", 'asc')->get();
        $events = $query->result();
        if (!empty($events)) {
            foreach ($events as $event_data) {
                $activity_time = date("Y-m-d", strtotime($event_data->activity_time));//2021-08-18
                $end = date("Y-m-d", strtotime($event_data->end));//2021-08-20
                if (!empty($event_data->end)) {
                    $interval = new DateInterval('P1D');
                    $start = new DateTime($activity_time);
                    $realEnd = new DateTime($end);
                    $realEnd->add($interval);
                    $period = new DatePeriod($start, $interval, $realEnd);
                    foreach ($period as $value) {
                        // d($value->format('Y-m-d'));//2021-08-18
                        $holiday_array[] = (count((array)$value->format('Y-m-d')));
                    }
                }else{
                    $holiday_array[] = (count((array)$event_data->activity_time));
                }
            }
            $holiday = array_sum($holiday_array);
            return $holiday;
            

        }
        
    }
}

if(!function_exists('get_category_brands')){
    function get_category_brands($block_catid){
        $CI =& get_instance();
        $CI->load->model('homes');
        $result = $CI->homes->get_category_brands($block_catid);
        return $result;
    }
}



if(!function_exists('get_percent')){
    function get_percent($old_price, $current_price){
        if($current_price == 0){
            $current_price = $old_price;
        }
        $percent_off = ceil((($old_price - $current_price)*100)/$old_price);
        return $percent_off;
    }
}


if(!function_exists('get_status')){
    function get_status($status){
        if($status == 2){
            $curent_status= "No";
        }else{
            $curent_status= "Yes";
        }

        return $curent_status;
    }
}



if(!function_exists('update_earning_total')){
    function update_earning_total($total_amount,$affiliate_id){
        $CI =& get_instance();
        $data=[
          'affiliate_id'=>$affiliate_id,
          'total_sold_amount'=>$total_amount
        ];

        $CI->db->where('affiliate_id',$affiliate_id);
        $q = $CI->db->get('aff_earning_total');

        if ( $q->num_rows() > 0 )
        {
            $affiliate_total_amount = $q->row();
            $data=[
                'affiliate_id'=>$affiliate_id,
                'total_sold_amount'=>$total_amount+$affiliate_total_amount->total_sold_amount
            ];

            $CI->db->where('affiliate_id',$affiliate_id);
            $result = $CI->db->update('aff_earning_total',$data);
        } else {
            $result = $CI->db->insert('aff_earning_total',$data);
        }
        return $result;
    }


}

if (!function_exists("get_current_week_dates")) {
    function get_current_week_dates() {

        $monthdates = [];
            $serial = 'last monday';
            $monday = strtotime($serial);
            $monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;

            $tuesday = date('Y-m-d', strtotime(date("Y-m-d",$monday)." +1 days"));
            $wednesday = date('Y-m-d', strtotime(date("Y-m-d",$monday)." +2 days"));
            $thursday = date('Y-m-d', strtotime(date("Y-m-d",$monday)." +3 days"));
            $friday = date('Y-m-d', strtotime(date("Y-m-d",$monday)." +4 days"));
            $saturday = date('Y-m-d', strtotime(date("Y-m-d",$monday)." +5 days"));
            $sunday = date('Y-m-d', strtotime(date("Y-m-d",$monday)." +6 days"));
            $monday = date('Y-m-d', $monday);

            $monthdates = [$monday,$tuesday,$wednesday,$thursday,$friday,$saturday,$sunday];

        return $monthdates;
    }
}
if (!function_exists("short_desc")) {
    function short_desc($text, $chars_limit =100, $more = false)
    {
        $new_text = strip_tags($text);
        if (strlen($new_text) > $chars_limit)
        {
            $new_text = substr($new_text, 0, $chars_limit);
            $new_text = '<p>'.trim($new_text).'</p>';

            if($more){
                $new_text .= '<button type="button"  class="more-link">'.display('more_details').'</button>';
            }
            return $new_text;
        }else{
        return $new_text;
        }
    }
}
// Text filtering
if (!function_exists("get_filter_text")) {
    function get_filter_text($text)
    {
        $text = htmlspecialchars_decode($text);
        $text = strip_tags($text);
        $text = trim($text);
        $text = strval($text);
        return $text;
    }
}
// link_page
if (!function_exists("active_link_page")) {
    function active_link_page($page_id)
    {
        $CI =& get_instance();
        $CI->db->where('page_id', $page_id);
        $query = $CI->db->get('link_page');
        $result = $query->num_rows();
        return $result;
    }
}
//file_path
if (!function_exists("file_path")) {
    function file_path($path)
    {
        $file = FCPATH.$path;
        // $file = base_url($path);
        return $file;
    }
}
// bKash Config.json
if (!function_exists("bkash_config")) {
    function bkash_config()
    {
        // $bkash_config = FCPATH.'config.json';
        $bkash_config = base_url('config.json');
        return $bkash_config;
    }
}
//read_db database load section
// if (!function_exists("read_db")) {
//     function read_db()
//     {
//         $CI =& get_instance();
// 		$read_db = $CI->load->database('read_db', TRUE); 
//         return $read_db;
//     }
// }