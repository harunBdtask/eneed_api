<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//dhaka time set
date_default_timezone_set('Asia/Dhaka');
/*
|-------------------------------
|	Api Success output
|-------------------------------
*/
if (!function_exists('JSONSuccessOutput')) {
    function JSONSuccessOutput($response=NULL, $msg='')
    {
        header('Content-Type: application/json');
        $data['response_status'] = 200;
        $data['message'] = $msg;
        $data['status'] = 'success';
        $data['data'] = $response;
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
/*
|-------------------------------
|	Api Error output
|-------------------------------
*/
if (!function_exists('JSONErrorOutput')) {
    function JSONErrorOutput($errorMessage = 'Unknown Error')
    {
        header('Content-Type: application/json');
        $data['response_status'] = 0;
        $data['message'] = $errorMessage;
        $data['status'] = 'failed';
        $data['data'] = null;
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
/*
|-------------------------------
|	Api Error output
|-------------------------------
*/
if (!function_exists('JSONNoOutput')) {
    function JSONNoOutput($errorMessage = 'Unknown Error')
    {
        header('Content-Type: application/json');
        $data['response_status'] = 204;
        $data['message'] = $errorMessage;
        $data['status'] = 'failed';
        $data['data'] = null;
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
/*
|----------------------------------------------
|    check api key authentication
|----------------------------------------------
*/
if (!function_exists('checkAuth')) {
    function checkAuth($key)
    {
        // get the api request
        $api_key_check = $key;
        //encription
        // $nowDateTime = date("Y-m-d H:i"); 
        // $strNowDateTime = strtotime($nowDateTime);
        // $api_key = $strNowDateTime.'harunApp';
        $api_key = 'harunApp';
        // check the api username
        if (!$api_key_check || empty($api_key_check)) {
            JSONErrorOutput(display('api_key_required'));
        } elseif ($api_key_check != $api_key) {
            JSONErrorOutput(display('api_key_invalid'));
        // }elseif ($api_key_check == $api_key) {
        //     JSONErrorOutput('Maintenance break!!!!!');
        }
        return true;
    }
}
/*
|-------------------------------
|	Input Data Filtering
|-------------------------------
*/
if (!function_exists('filter_input_data')) {
    function filter_input_data($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
/*
|---------------------------
|   generator
|---------------------------
*/
if (!function_exists('generator')) {
    function generator($lenth)
    {
        $number = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "N", "M", "O", "P", "Q", "R", "S", "U", "V", "T", "W", "X", "Y", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");

        for ($i = 0; $i < $lenth; $i++) {
            $rand_value = rand(0, 34);
            $rand_number = $number["$rand_value"];

            if (empty($con)) {
                $con = $rand_number;
            } else {
                $con = "$con" . "$rand_number"; //working perfectly 
            }
        }
        return $con;
    }
}
/*
|---------------------------------------------------
|	save api response history
|---------------------------------------------------
*/
if (!function_exists('api_response_history')) {
    function api_response_history()
    {
        //print_r($_SERVER);exit;
        if ($_SERVER['HTTP_HOST'] != 'localhost') {
            //for live server
            $client_info = array(
                'remote_addr' => $_SERVER['REMOTE_ADDR'],
                //'http_cf_connecting_ip' => $_SERVER['HTTP_CF_CONNECTING_IP'],
                //'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                //'http_cf_ipcountry' => $_SERVER['HTTP_CF_IPCOUNTRY'],
            );
        } else {
            //for localhost
            $client_info = array(
                'remote_addr' => $_SERVER['REMOTE_ADDR'],
            );
        }
        $server_json_data = json_encode($client_info);
        $api_response = array(
            'request_uri' => $_SERVER['REQUEST_URI'],
            'client_info' => $server_json_data,
            'request_time' => date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']),
        );
        $CI = &get_instance();
        $CI->db->insert('api_response_history', $api_response);
        return true;
    }
}
/*
|---------------------------------------------------
|	check_api_key
|---------------------------------------------------
*/
if (!function_exists('check_api_key')) {
    function check_api_key()
    {
        //encription
        // $nowDateTime = date("Y-m-d H:i"); 
        // $strNowDateTime = strtotime($nowDateTime);
        // $api_key = $strNowDateTime.'harunApp';
        //$api_key = 'harunApp';
        $CI = &get_instance();
        $api_key = filter_input_data($CI->input->post('api_key'));
        return $api_key;
    }
}
/*
|---------------------------------------------------
|	check_api_token
|---------------------------------------------------
*/
if (!function_exists('check_api_token')) {
    function check_api_token()
    {
        // $nowDateTime = date("Y-m-d H:i"); 
        // $strNowDateTime = strtotime($nowDateTime);
        // $api_token = $strNowDateTime."apiToken";
        $CI = &get_instance();
        //$api_token = $CI->input->post('api_token');
        $api_token = 'apiToken';
        return $api_token;
    }
}
/*
|---------------------------------------------------
|	null_check
|---------------------------------------------------
*/
if (!function_exists('null_check')) {
    //
    function null_check($v)
    {
        return is_null($v) ? "0" : $v;
    }
}
