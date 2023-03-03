<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Test_api extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('api/test_api_model');
		// api key value
		$this->api_key = 'harunApp';
	}
	/*
	|-------------------------------
	|	Api Success output
	|-------------------------------
	*/
	public function JSONSuccessOutput($response, $token = null)
	{
		header('Content-Type: application/json');
		if ($token != null) {
			$response['status'] = 200;
			$data = $response;
		} else {
			$data['status'] = 200;
			$data['data'] = $response;
		}

		echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		exit;
	}
	/*
	|-------------------------------
	|	Api Error output
	|-------------------------------
	*/
	public function JSONErrorOutput($errorMessage = 'Unknown Error')
	{
		header('Content-Type: application/json');
		$data['status'] = 0;
		$data['message'] = $errorMessage;
		echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		exit;
	}
	/*
    |----------------------------------------------
    |	check api key authentication
    |----------------------------------------------
    */
	public function checkAuth($key)
	{
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
	/*
	|-------------------------------
	|	Input Data Filtering
	|-------------------------------
	*/
	public function filter_input($data)
	{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
	/*
	|-------------------------------
	|	insert data
	|	post method 
	|	route = api/test_api/testApi
	|-------------------------------
	*/
	public function testApi()
	{
		header('Content-Type: application/json');
		$get_api_key = $this->input->post('api_key');
		if ($this->checkAuth($get_api_key)) {
			$data = array(
				'title' => $this->filter_input($this->input->post('title')),
				'description' => $this->filter_input($this->input->post('description')),
			);
			$response = $this->test_api_model->test_insert($data);
			if ($response) {
				$res = array(
					'success' => true,
					'message' => 'Inserted Successfully!'
				);
			} else {
				$res = array(
					'success' => false,
					'message' => 'Failed!'
				);
			}
			echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit();
		}
	}
	/*
	|-------------------------------
	|	get data
	|	post method 
	|	route = api/test_api/index_get
	|-------------------------------
	*/
	public function index_get()
	{
		//$get_api_key = 'harunApp';
		$get_api_key = $this->input->post('api_key');
		//$id = null;
		if ($this->checkAuth($get_api_key)) {
			if (!empty($id)) {
				$data = $this->db->get_where("items", ['id' => $id])->row_array();
			} else {
				$data = $this->db->get("items")->result();
			}
			if (!empty($data)) {
				$response = $data;
				$this->JSONSuccessOutput($response);
			} else {
				$this->JSONErrorOutput();
			}
		}
	}
	/*
	|-------------------------------
	|	update data
	|	post method 
	|	route = api/test_api/index_put/$1
	|-------------------------------
	*/
	public function index_put($id)
	{
		header('Content-Type: application/json');
		$get_api_key = $this->input->post('api_key');
		if ($this->checkAuth($get_api_key)) {
			$data = array(
				'title' => $this->filter_input($this->input->post('title')),
				'description' => $this->filter_input($this->input->post('description')),
			);
			$response = $this->db->update('items', $data, array('id' => $id));

			if ($response) {
				$res = array(
					'success' => true,
					'message' => 'Updated Successfully!'
				);
			} else {
				$res = array(
					'success' => false,
					'message' => 'Failed!'
				);
			}
			echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit();
		}
	}
	/*
	|-------------------------------
	|	delete data
	|	post method 
	|	route = api/test_api/index_delete
	|-------------------------------
	*/
	public function index_delete()
	{
		$id = $this->input->post('data_id');
		$get_api_key = $this->input->post('api_key');
		if ($this->checkAuth($get_api_key)) {
			$response = $this->db->delete('items', array('id' => $id));
			if ($response) {
				$res = array(
					'success' => true,
					'message' => 'Deleted Successfully!'
				);
			} else {
				$res = array(
					'success' => false,
					'message' => 'Failed!'
				);
			}
			echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit();
		}
	}
}
