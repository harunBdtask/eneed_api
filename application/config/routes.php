<?php
defined('BASEPATH') or exit('No direct script access allowed');


//Front end routing start
$route['default_controller'] = 'Welcome';
$route['home'] = 'Welcome';

$route['404_override'] = 'my404';
$route['translate_uri_dashes'] = FALSE;


$route['nagad_api_response_react_customer_dashboard'] = 'api/react/customer_dashboard/nagad_customer_dashboard';
$route['nagad_api_response_react_website_checkout'] = 'api/react/customer_dashboard/nagad_api_response_web';