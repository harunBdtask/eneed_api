<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Space {

	public $spaceobj;
	public  $key = "2IOYOJ6PUKMS2DBIJNYB";
	public  $secret = "YvffQu5HQ7SBZJ1IJUvpGefoQWXxk7vGfMPfPu/iqso";
	public  $space_name = "eneedz";
	public  $region = "sgp1";

	public function index()
	{
		$this->connect_space();

		$result = $this->spaceobj->ListObjects();

		return $result;

	}
	public function connect_space()
	{
		require_once(APPPATH.'libraries/Spaces-API-master/spaces.php');

		$this->spaceobj = new SpacesConnect($this->key, $this->secret, $this->space_name, $this->region);
	}

	public function get_space_files()
	{
		$this->connect_space();

		$result = $this->spaceobj->ListObjects();

		return $result;

	}

	public function get_spacefile_by_filename($file_name = 'newimage.jpg')
	{
		$this->connect_space();

		$result = $this->spaceobj->GetObject($file_name);

		return $result;

	}

	public function  upload_to_space ($path_to_file = false, $save_as = false)
	{
		$this->connect_space();

		$result = $this->spaceobj->UploadFile($path_to_file, "public", $save_as);

		return $result;

	}

	public function delete_space_file($file_url) {

		$this->connect_space();

		$result = $this->spaceobj->DeleteObject($file_url);

		return $result;
	}

	// Downlaod File
	public function download_space_file($filepath, $save_path = false)
	{
		$this->connect_space();

		$result = $this->spaceobj->DownloadFile($filepath, $save_path);

		return $result;
	}
	

}