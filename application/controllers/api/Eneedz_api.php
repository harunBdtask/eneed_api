<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Eneedz_api extends CI_Controller
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

    #=============
    #   url = api/eneedz_api/autocancelorder
    #=============
    public function AutoCancelOrder()
    {
        $this->db->where('order_status', 1);
        $result_array = $this->db->get('order')->result_array();
        if (!empty($result_array)) {
            $target = date_create(date('Y-m-d H:i:s'));

            $update_list = [];
            foreach ($result_array as $value) {
                $origin = date_create($value['date']);
                $interval = date_diff($origin, $target);


                if ((int)$value['paid_amount'] == 0 && ($interval->d) >= 3) { 

                    $update_list[]=array(
                        'order_status' => 6,
                        'order_id' => $value['order_id']
                    );
                }
            }

            // dd($update_list);

            if(!empty($update_list)){
                $this->db->update_batch('order', $update_list, 'order_id');
            }
            
        }        
    }
















}