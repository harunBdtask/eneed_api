<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('display')) {

    function display($text = null)
    {
        $ci =& get_instance();
        $ci->load->database();
        $table  = 'language';
        $phrase = 'phrase';

        $default_lang  = 'english';

        $user_lang = $ci->session->userdata('language');

        //set language 
        if (!empty($user_lang)) {
            $language = $user_lang; 
        } else {
            $language = $default_lang; 
            $ci->session->set_userdata('language',$language);
        } 
 
        if (!empty($text)) {

            if ($ci->db->table_exists($table)) { 

                if ($ci->db->field_exists($phrase, $table)) { 

                    if ($ci->db->field_exists($language, $table)) {

                        $row = $ci->db->select($language)
                              ->from($table)
                              ->where($phrase, $text)
                              ->get()
                              ->row(); 

                        if (!empty($row->$language)) {
                            return html_escape($row->$language);
                        } else {
                            return false;
                        }

                    } else {
                        return false;
                    }

                } else {
                    return false;
                }

            } else {
                return false;
            }            
        } else {
            return false;
        }  

    }
 
}


/**
 * Make Translate String
 **/
if (!function_exists("makeString")) {
    function makeString ($data = [])
    {
        $output = "";
        $i = 0;
        foreach ($data as $val) {
            $output .= ($i>0?" ":"");
            $output .= display("$val");
            $i++;
        }

        return $output;
        
    }
}

if (!function_exists('language_list')) {

    function language_list()
    {
        $ci =& get_instance();
        $ci->load->database();
        $table  = 'language';
        $phrase = 'phrase';

        $fields = $ci->db->field_data($table);
        //$langs = array_column(array_slice($fields, 2), 'name');
        $names = array();
        foreach($fields as $field){
            $names[] = $field->name;
        }
        $langs = array_slice($names, 2);

        return $langs;
    }
}

if (!function_exists('trans')) {

    function trans($string1, $string2 = NULL)
    {
        $CI =& get_instance();
        $lang_id  = $CI->session->userdata('language');
        $default = 'english';

        if(empty($lang_id)){
            $lang_id = $default;
       }

        return (($lang_id != $default)?$string2:$string1);

    }
}

 

// $autoload['helper'] =  array('language_helper');

/*display a language*/
// echo display('helloworld'); 

/*display language list*/
// $lang = languageList(); 
