<?php 
/**
 * @package	CodeIgniter api
 * @author	BDtask Limited
 * @copyright	Copyright (c) 2020, BDtask Limited. (https://bdtask.com/)
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH."models/api/Manager_api.php";

class Visitors extends Manager_api {
	protected $pusher_options = array();
	// table name
	private $_setting  = 'setting';
	private $_agent      = 'agents';
    private $_visitor    = 'visitors';
    private $_user       ='users';
    private $_DMap       ='user_department_mapping';
    private $_chat       = 'chat_history';
    private $_pusher     = 'pusher';
    private $_dpartment  = 'agents';
    private $_liveR  = 'live_chat_request';
    // load contructor
    public function __construct(){
        parent::__construct();
        $this->db->query('SET SESSION sql_mode = ""');
    	// api key value
        $this->api_key = $this->db->select('api_key')->get('setting')->row()->api_key;
        $this->load->library('email');
        $this->load->library('dialogflow');
    }

	public function index(){
		$response = 'testing';
		$this->JSONErrorOutput($response);
	}

	/*
	|---------------------------------------------------
	|	get api key Data 
	|---------------------------------------------------
	*/
	public function getApiKey(){
		$postdata = file_get_contents('php://input');
        $getData = json_decode(@$postdata);
		// check authentication
		$where['get_token']  = $getData->get_token;
		$apiKey = $this->getInfoById($this->_apikey, $where);
		if (!empty($apiKey->apps_api_key)) {
			$response['api_key'] = $apiKey->apps_api_key;
			$this->JSONSuccessOutput($response);
		}else{
			$this->JSONErrorOutput('Access token is not valid!');
		}
	}

	/*
	|---------------------------------------------------
	|	Web	Settings Data 
	|---------------------------------------------------
	*/
	public function webSetting(){
		$postdata = file_get_contents('php://input');
        $getData = json_decode(@$postdata);
		// check authentication
		if($this->checkAuth($getData->api_key)){
			$settings = $this->db->select('*')->from('setting')->get()->row();
			if (!empty($settings)) {
				$response = $settings;
				$this->JSONSuccessOutput($response);
			}else{
				$this->JSONErrorOutput('No results found...');
			}
		}
		
	}

	/*
	|---------------------------------------------------
	| Settings Data 
	|---------------------------------------------------
	*/
	public function settings(){
		return $this->getInfoById($this->_setting, array('id'=>1));
	}

	//Input Data Filtering
	public function test_input($data) {
	  	$data = trim($data);
	  	$data = stripslashes($data);
	  	$data = htmlspecialchars($data);
	  	return $data;
	}


	/*
	|---------------------------------------------------
	|	Get Login info
	|---------------------------------------------------
	*/
	public function login(){ 
        // check authentication
		$username = $this->test_input($this->input->post('username'));
		$email = $this->test_input($this->input->post('email'));
		$phone = $this->test_input($this->input->post('phone'));
		$device_id = $this->test_input($this->input->post('device_id'));
		// check empty value
		if (empty($username) || empty($email) || empty($phone)) {
				$this->JSONErrorOutput('Missing required data!');
		} else {
			// Check exists email address and get data
			$checkExistEmail = $this->checkExistData($this->_visitor, array('email'=>$email));
			if(!empty($checkExistEmail)){
				$this->update($this->_visitor, array('device_id'=>$device_id), array('visitor_id'=>$checkExistEmail->visitor_id));
				$data['data'] = $this->getInfoById($this->_visitor, array('visitor_id'=>$checkExistEmail->visitor_id));;
				$data['api_key'] = $this->api_key;
				$this->JSONSuccessOutput($data, 1);
			}else{
				$acronym = "";
				if(!empty($username)){
					// Delimit by multiple spaces, hyphen, underscore, comma
					$words = preg_split("/[\s,_-]+/", $username);
					foreach ($words as $w) {
					  $acronym .= ucfirst($w[0]);
					}
				}
				
				$fname = (!empty($words[0])?$words[0]:'');
				$lname = (!empty($words[1])?$words[1]:'').(!empty($words[2])?' '.$words[2]:'');

				$ip = $this->input->post('ip_address', true);
			
		        $info = (object)json_decode(file_get_contents("http://ip-api.com/json/{$ip}")); 

				// Get post data
				$postdata = array(
					'first_name' => $fname,
					'last_name'  => $lname,
					'acronyms'   => $acronym,
					'email'      => $email,
					'phone'      => $phone, 
					'ip_address' => $ip,
					'country'    => @$info->country,
					'city' 		 => @$info->city,
					'time_zone'  => @$info->timezone,
					'visit_date' => date('Y-m-d'),
					'visit_time' => date('h:s:i'),
					'device_id'  => $device_id
				);
				// insert visitor data
				$this->create($this->_visitor, $postdata);
				$id = $this->db->insert_id();
				$data['data'] = $this->getInfoById($this->_visitor, array('visitor_id'=>$id));
				$data['api_key'] = $this->api_key;
				$this->JSONSuccessOutput($data, 1);
			}
		}
	}

	/*
	|----------------------------------------
	| Get Visitor Data
	|----------------------------------------
	*/
	public function getVisitorById(){
		$visitorId = $this->test_input($this->input->post('visitor_id'));
		$data = $this->getInfoById($this->_visitor, array('visitor_id'=>$visitorId));
		if (!empty($data)) {
			$this->JSONSuccessOutput($data);
		}else{
			$this->JSONErrorOutput('No results found...');
		}
	}

	/*
	|----------------------------------------
	| Get Visitor Data
	|----------------------------------------
	*/
	public function updateVisitor(){
		$visitor_id = $this->test_input($this->input->post('visitor_id'));
		$fname = $this->test_input($this->input->post('first_name'));
		$lname = $this->test_input($this->input->post('last_name'));
		$email = $this->test_input($this->input->post('email'));
		$phone = $this->test_input($this->input->post('phone'));
		// check empty value
		if (empty($fname) || empty($email) || empty($phone)) {
				$this->JSONErrorOutput('Missing required data!');
		} else {
			$username = $fname.' '.$lname;
			$acronym = "";
			if(!empty($username)){
				// Delimit by multiple spaces, hyphen, underscore, comma
				$words = preg_split("/[\s,_-]+/", $username);
				foreach ($words as $w) {
				  $acronym .= ucfirst($w[0]);
				}
			}

			//picture upload
			$picture = $this->fileupload->do_upload(
				'assets/images/visitor/',
				'image'
			);
			// if logo is uploaded then resize the logo
			if ($picture !== false && $picture != null) {
				$this->fileupload->do_resize(
					$picture, 
					250,
					260
				);
			}
	
			// Get post data
			$postdata = array(
				'visitor_id' => $visitor_id,
				'first_name' => $fname,
				'last_name'  => $lname,
				'acronyms'   => $acronym,
				'email'      => $email,
				'phone'      => $phone, 
				'image'      => $picture,
			);
			// insert visitor data
			$this->update($this->_visitor, $postdata, array('visitor_id'=>$visitor_id));
			$data['data'] = $this->getInfoById($this->_visitor, array('visitor_id'=>$visitor_id));
			$data['api_key'] = $this->api_key;
			$this->JSONSuccessOutput($data, 1);
			
		}
	}

	/*
	|----------------------------------------
	| upload attach file 
	|----------------------------------------
	*/
	public function uploadAttachFile(){
		require_once("./vendor/autoload.php");
        // get pusher setting
	    $this->pusher_options['app_key']    = bd_get_pusher_app_setting()->app_key;
        $this->pusher_options['app_secret'] = bd_get_pusher_app_setting()->app_secret;
        $this->pusher_options['app_id']     = bd_get_pusher_app_setting()->app_id;

        if (!isset($this->pusher_options['cluster']) && bd_get_pusher_app_setting() != '') {
            $this->pusher_options['cluster'] = bd_get_pusher_app_setting()->cluster;
        }
        $this->pusher = new Pusher\Pusher(
            $this->pusher_options['app_key'],
            $this->pusher_options['app_secret'],
            $this->pusher_options['app_id'],
            array('cluster' => $this->pusher_options['cluster'])
        );
		$apikey = $this->test_input($this->input->post('api_key'));
		$visitor_id = $this->test_input($this->input->post('visitor_id'));
		$user_id = $this->test_input($this->input->post('user_id'));

        // check authentication
		if($this->checkAuth($apikey)){
			ini_set('memory_limit', '200M');
	        ini_set('upload_max_filesize', '200M');  
	        ini_set('post_max_size', '200M');  
	        ini_set('max_input_time', 3600);  
	        ini_set('max_execution_time', 3600);

	        if (($_SERVER['REQUEST_METHOD']) == "POST") { 
	            $filename = $_FILES['attach_file']['name'];
	            /*-----------------------------*/

	            $config['upload_path']   = FCPATH .'./uploads/';
	            $config['allowed_types'] = 'pdf|doc|docx|bmp|gif|jpg|jpeg|jpe|png|xlsx|zip';
	            $config['max_size']      = 0;
	            $config['max_width']     = 0;
	            $config['max_height']    = 0;
	            $config['overwrite']     = false;

	            $this->load->library('upload', $config);

	            $name = 'attach_file';
	            if ( ! $this->upload->do_upload($name) ) {
	                 $this->JSONErrorOutput($this->upload->display_errors());
	            } else {
	                $upload =  $this->upload->data();
	                $data['filepath'] = 'uploads/'.$upload['file_name'];

	                // Get extensions
                    if(preg_match("/\.(gif|png|jpg|jpeg|jpe)$/", $data['filepath'])){
                    	$type = 'image';
                    }else{
                    	$type = 'file';
                    }

	                $where['visitor_id'] = $visitor_id;
					$column = ['first_name', 'last_name', 'acronyms'];
					$row = $this->getSingleSpecificRow($this->_visitor, $column, $where);
					// pusher data
					$pushData = array(
						'visitor_id'   => $visitor_id,
						'fullname'     => $row->first_name.' '.@$row->last_name,
						'acronyms'     => $row->acronyms,
						'message'      => $data['filepath'],
						'user_id'      => $user_id,
						'date_time'    => date('Y-m-d H:i:s'),
						'type'         => $type,
						'action_type'  => 2
					);

					$this->pusher->trigger('presence-mychanel', 'sent_v_event', $pushData);
					$postData = array(
						'visitor_id'   => $visitor_id,
						'message'      => $data['filepath'],
						'department_id'=> $this->input->post('department_id'),
						'chat_date'    => date('Y-m-d'),
						'user_id'      => $user_id,
						'type'         => $type,
						'action_type'  => 2
					);
					$response = $this->create($this->_chat, $postData);
					if ($response) {
						$info['message'] = 'Successfully uploaded!';
						$info['filepath'] = $data['filepath'];
						$this->JSONSuccessInsert($info);
					}else{
						$this->JSONErrorOutput('Fill not uploaded!');
					}
	            } 
	        }
		}
	}

	/*
	|----------------------------------------
	| Pusher authentication
	|----------------------------------------
	*/
	public function pusherAuth($visitor_id, $name){
		require_once("./vendor/autoload.php");
		$pushData = $this->getInfoById($this->_pusher, array('id'=>1));

		$pusher = new Pusher\Pusher(
					$pushData->app_key,
                    $pushData->app_secret,
                    $pushData->app_id);
		$presence_data = array('name' => $name);

		echo $pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $visitor_id, $presence_data);
	}

	/*
	|----------------------------------------
	| get country Data 
	|----------------------------------------
	*/
	public function getUserByDept(){
		$apikey = $this->test_input($this->input->post('api_key'));
        // check authentication
		$department_id = $this->test_input($this->input->post('department_id'));
		// check authentication
		if($this->checkAuth($apikey)){
			$users = $this->getUserByDeptment($department_id);
			
			if (!empty($users)) {
				$this->JSONSuccessOutput($users);
			}else{
				$this->JSONErrorOutput('No results found...');
			}
		}
	}

	/*
	|----------------------------------------
	| get country Data 
	|----------------------------------------
	*/
	public function getDepartmentList(){
		$list = $this->getList($this->_dpartment);
		
		if (!empty($list)) {
			$this->JSONSuccessOutput($list);
		}else{
			$this->JSONErrorOutput('No results found...');
		}
	}

	/*
	|---------------------------------------------------
	| Get messages Data
	|---------------------------------------------------
	*/
	public function getMessages(){
		$apikey = $this->test_input($this->input->post('api_key'));
		$userId = $this->test_input($this->input->post('user_id'));
		$visitorId = $this->test_input($this->input->post('visitor_id'));

        // check authentication
		if($this->checkAuth($apikey)){
			$where['user_id'] = $userId; 
			$where['visitor_id'] = $visitorId; 
			$messages['data'] = $this->getAllWhere($this->_chat, $where);
			$messages['manualCode'] = '*123#';//$this->settings()->manual_agent_code;
			if (!empty($messages['data'])) {
				$this->JSONSuccessOutput($messages, 1);
			}else{
				$query = $this->welcomeQueryText();
				if(!empty($query)){
					$data = array(
						'visitor_id'   => $visitorId,
						'message'      => $query->phrase,
						'chat_date'    => date('Y-m-d'),
						'user_id'      => $userId,
						'action_type'  => 0,
						'type'         => 'text',
						'status'       => 1
					);
					$this->db->insert('chat_history', $data);
				}
				$message['manualCode'] = '*123#';
				$message['data'] = $this->getAllWhere($this->_chat, $where);
				$this->JSONSuccessOutput($message, 1);
			}
		}
	}

	/*
	|---------------------------------------------------
	| Autobot response messages
	|---------------------------------------------------
	*/
	public function autoBotMessage(){
		require_once("./vendor/autoload.php");
        // get pusher setting
	    $this->pusher_options['app_key']    = bd_get_pusher_app_setting()->app_key;
        $this->pusher_options['app_secret'] = bd_get_pusher_app_setting()->app_secret;
        $this->pusher_options['app_id']     = bd_get_pusher_app_setting()->app_id;

        if (!isset($this->pusher_options['cluster']) && bd_get_pusher_app_setting() != '') {
            $this->pusher_options['cluster'] = bd_get_pusher_app_setting()->cluster;
        }
        $this->pusher = new Pusher\Pusher(
            $this->pusher_options['app_key'],
            $this->pusher_options['app_secret'],
            $this->pusher_options['app_id'],
            array('cluster' => $this->pusher_options['cluster'])
        );

		$apikey = $this->test_input($this->input->post('api_key'));
		$message = $this->test_input($this->input->post('message'));

		$department_id = $this->test_input($this->input->post('department_id'));
		$user_id = $this->test_input($this->input->post('user_id'));
		$visitor_id   = $this->test_input($this->input->post('visitor_id'));
		
		// check authentication
		if($this->checkAuth($apikey)){
			$where['visitor_id'] = $visitor_id;
			$column = ['first_name', 'last_name', 'acronyms'];
			$row = $this->getSingleSpecificRow($this->_visitor, $column, $where);
			// pusher data
			$pushData = array(
				'visitor_id'   => $this->test_input($this->input->post('visitor_id')),
				'fullname'     => $row->first_name.' '.@$row->last_name,
				'acronyms'     => $row->acronyms,
				'message'      => $message,
				'date_time'    => date('Y-m-d H:i:s'),
				'user_id'      => $this->test_input($this->input->post('user_id')),
				'action_type'  => 2
			);

			$this->pusher->trigger('presence-mychanel', 'sent_v_event', $pushData);

			$postData = array(
				'visitor_id'   => $this->test_input($this->input->post('visitor_id')),
				'message'      => $message,
				'department_id'=> $this->test_input($this->input->post('department_id')),
				'chat_date'    => date('Y-m-d'),
				'user_id'      => $this->test_input($this->input->post('user_id')),
				'action_type'  => 2,
				'status'       => 1
			);
			$this->create($this->_chat, $postData);

			$off = '';
			if($message=='*123#'){
				$this->update($this->_visitor, array('auto_bot'=> 0), array('visitor_id'=>$visitor_id));
				// pusher data
				$requestData = array(
					'visitor_id'   => $visitor_id,
					'user_id'      => $user_id
				);
				$this->create($this->_liveR, $requestData);
				$requestData['fullname'] = $this->session->userdata('full_name');
				$requestData['notify_date'] = date('Y-m-d h:s:i');
				$this->pusher->trigger('presence-mychanel', 'live_chat_request', $requestData);
				$off = 'off';
			}

			$dialogflow = $this->getInfoById('dialogflow', array('default_set'=>1, 'status'=>1));
			// call dialogflow api
			$i = file_get_contents(base_url($dialogflow->credentials));
		    $json = json_decode($i, true);
			$fulfilment = $this->dialogflow->request($dialogflow->projectId, $message, '!5f3ca45gd23ff', $dialogflow->languageCode, $json);
			$answer = $fulfilment['fulfilment']; //response data from dialogflow

			$where1['user_id'] = $user_id;
			$column1 = ['fname', 'lname', 'acronyms'];
			$row1 = $this->getSingleSpecificRow($this->_user, $column1, $where1);

			$UserText = array(
				'visitor_id'   => $visitor_id,
				'message'      => $answer,
				'department_id'=> $department_id,
				'chat_date'    => date('Y-m-d'),
				'type'         => 'text',
				'user_id'      => $user_id,
				'action_type'  => 0,
				'status'  => 1
			);
			// pusher data
			$ResPushData = array(
				'visitor_id'   => $visitor_id,
				'fullname'     => $row1->fname.' '.$row1->lname,
				'acronyms'     => $row1->acronyms,
				'message'      => $answer,
				'date_time'    => date('Y-m-d H:i:s'),
				'type'         => 'text',
				'user_id'      => $user_id,
				'action_type'  => 0
			);
			$this->pusher->trigger('presence-mychanel', 'auto_resp_event', $ResPushData);
			// insert ans response
			$this->create($this->_chat, $UserText);
			$response = ['success'=> true, 'auto_bot'=> $off];
			
			$this->JSONSuccessOutput((object)$response);
		}
		
	}

	/*
	|---------------------------------------------------
	| Get pusher Data
	|---------------------------------------------------
	*/
	public function getPusher(){
		$apikey = $this->test_input($this->input->post('api_key'));

        // check authentication
		if($this->checkAuth($apikey)){
			$where['id'] = 1; 
			$messages = $this->getAllWhere($this->_pusher, $where);
			
			if (!empty($messages)) {
				$this->JSONSuccessOutput($messages);
			}else{
				$this->JSONErrorOutput('No results found...');
			}
		}
	}

	/*
	|---------------------------------------------------
	| Send messages
	|---------------------------------------------------
	*/
	public function sendMessage(){
		require_once("./vendor/autoload.php");
        // get pusher setting
	    $this->pusher_options['app_key']    = bd_get_pusher_app_setting()->app_key;
        $this->pusher_options['app_secret'] = bd_get_pusher_app_setting()->app_secret;
        $this->pusher_options['app_id']     = bd_get_pusher_app_setting()->app_id;

        if (!isset($this->pusher_options['cluster']) && bd_get_pusher_app_setting() != '') {
            $this->pusher_options['cluster'] = bd_get_pusher_app_setting()->cluster;
        }
        $this->pusher = new Pusher\Pusher(
            $this->pusher_options['app_key'],
            $this->pusher_options['app_secret'],
            $this->pusher_options['app_id'],
            array('cluster' => $this->pusher_options['cluster'])
        );
		$apikey = $this->test_input($this->input->post('api_key'));
		$auto_bot = $this->test_input($this->input->post('auto_bot'));
		$message = $this->test_input($this->input->post('message'));
		$department_id = $this->test_input($this->input->post('department_id'));
		$user_id = $this->test_input($this->input->post('user_id'));
		$visitor_id   = $this->test_input($this->input->post('visitor_id'));

        // check authentication
		if($this->checkAuth($apikey)){
			if($auto_bot==0){
				$where['visitor_id'] = $visitor_id;
				$column = ['first_name', 'last_name', 'acronyms'];
				$row = $this->getSingleSpecificRow($this->_visitor, $column, $where);
				// pusher data
				$pushData = array(
					'visitor_id'   => $visitor_id,
					'fullname'     => $row->first_name.' '.@$row->last_name,
					'acronyms'     => $row->acronyms,
					'message'      => $message,
					'date_time'    => date('Y-m-d H:i:s'),
					'user_id'      => $user_id,
					'action_type'  => 2
				);

				$this->pusher->trigger('presence-mychanel', 'sent_v_event', $pushData);

				$postData = array(
					'visitor_id'   => $visitor_id,
					'message'      => $message,
					'department_id'=> $this->test_input($this->input->post('department_id')),
					'chat_date'    => date('Y-m-d'),
					'user_id'      => $user_id,
					'action_type'  => 2
				);
				$this->create($this->_chat, $postData);

				$response = ['success'=> true, 'live_request'=> 0];
				$this->JSONSuccessOutput((object)$response);
			}else{
				$where['visitor_id'] = $visitor_id;
				$column = ['first_name', 'last_name', 'acronyms'];
				$row = $this->getSingleSpecificRow($this->_visitor, $column, $where);
				// pusher data
				$pushData = array(
					'visitor_id'   => $visitor_id,
					'fullname'     => $row->first_name.' '.@$row->last_name,
					'acronyms'     => $row->acronyms,
					'message'      => $message,
					'date_time'    => date('Y-m-d H:i:s'),
					'user_id'      => $user_id,
					'action_type'  => 2
				);

				$this->pusher->trigger('presence-mychanel', 'sent_v_event', $pushData);

				$postData = array(
					'visitor_id'   => $visitor_id,
					'message'      => $message,
					'department_id'=> $department_id,
					'chat_date'    => date('Y-m-d'),
					'user_id'      => $user_id,
					'action_type'  => 2,
					'status'       => 1
				);
				$this->create($this->_chat, $postData);
				$off = 0;
				if($message=='*123#'){
					$this->update($this->_visitor, array('auto_bot'=> 0), array('visitor_id'=>$visitor_id));
					// pusher data
					$requestData = array(
						'visitor_id'   => $visitor_id,
						'user_id'      => $user_id
					);
					$this->create($this->_liveR, $requestData);
					$requestData['fullname'] = $this->session->userdata('full_name');
					$requestData['notify_date'] = date('Y-m-d h:s:i');
					$this->pusher->trigger('presence-mychanel', 'live_chat_request', $requestData);
					$off = 1;
				}

				$dialogflow = $this->getInfoById('dialogflow', array('default_set'=>1, 'status'=>1));
				// call dialogflow api
				$i = $this->file_get_contents_curl(base_url($dialogflow->credentials));
			    $json = json_decode($i, true);
				$fulfilment = $this->dialogflow->request($dialogflow->projectId, $message, '!5f3ca45gd23ff', $dialogflow->languageCode, $json);
				$answer = $fulfilment['fulfilment']; //response data from dialogflow

				$where1['user_id'] = $user_id;
				$column1 = ['fname', 'lname', 'acronyms'];
				$row1 = $this->getSingleSpecificRow($this->_user, $column1, $where1);

				$UserText = array(
					'visitor_id'   => $visitor_id,
					'message'      => $answer,
					'department_id'=> $department_id,
					'chat_date'    => date('Y-m-d'),
					'type'         => 'text',
					'user_id'      => $user_id,
					'action_type'  => 0,
					'status'  => 1
				);
				// pusher data
				$ResPushData = array(
					'visitor_id'   => $visitor_id,
					'fullname'     => $row1->fname.' '.$row1->lname,
					'acronyms'     => $row1->acronyms,
					'message'      => $answer,
					'date_time'    => date('Y-m-d H:i:s'),
					'type'         => 'text',
					'user_id'      => $user_id,
					'action_type'  => 0
				);
				$this->pusher->trigger('presence-mychanel', 'auto_resp_event', $ResPushData);
				// insert ans response
				$this->create($this->_chat, $UserText);
				$response = ['success'=> true, 'live_request'=> $off];
				
				$this->JSONSuccessOutput((object)$response);
			}
		}
	}

	public function ttt(){
        $cxContext = stream_context_create($aContext);
		$dialogflow = $this->getInfoById('dialogflow', array('default_set'=>1, 'status'=>1));
		// call dialogflow api
		$i = $this->file_get_contents_curl(base_url($dialogflow->credentials));
	    $json = json_decode($i, true);
		$fulfilment = $this->dialogflow->request($dialogflow->projectId, 'hi', '!5f3ca45gd23ff', $dialogflow->languageCode, $json);
		print_r($fulfilment);
	}

	public function file_get_contents_curl($url) {
	  $ch = curl_init();
	  curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE );
	  curl_setopt( $ch, CURLOPT_HEADER, 0 );
	  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	  curl_setopt( $ch, CURLOPT_URL, $url );
	  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );

	  $data = curl_exec( $ch );
	  curl_close( $ch );

	  return $data;

	}

	/*
	|----------------------------------------
	| Get chat history
	|----------------------------------------
	*/
	public function getChatHistory(){
		$apikey = $this->test_input($this->input->post('api_key'));
		$visitor_id = $this->test_input($this->input->post('visitor_id'));
		// check authentication
		if($this->checkAuth($apikey)){
			$where['visitor_id'] = $visitor_id;
			$users = $this->getChatHistoryData($this->_chat, $where);
			
			if (!empty($users)) {
				$this->JSONSuccessOutput($users);
			}else{
				$this->JSONErrorOutput('No results found...');
			}
		}
	}


    /*
    |----------------------------------------------
    |        check api key authentication
    |----------------------------------------------
    */
    public function checkAuth($key) {
        // get the api request
        $api_key = $key;

        // check the api username
        if (!$api_key || empty($api_key)) {
            $this->JSONErrorOutput('API Access key required!');
        } elseif ($api_key != $this->api_key) {
            $this->JSONErrorOutput('API Access Key is invalid !!!');
        }
        return true;
    }


}