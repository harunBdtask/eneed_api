<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Test_api_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function test_insert($data)
    {
        $items = array(
            'title'         => $data['title'],
            'description'   => $data['description'],
        );
        return $this->db->insert('items', $items);
    }
}
