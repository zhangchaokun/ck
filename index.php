<?php
header("Content-Type:text/html;charset=utf-8;");
define('APPPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
require 'core/Common.php';

$URI =& load_class('URI'); 
$RTR =& load_class('Router');

$RTR->set_routing();

$class = $RTR->fetch_class();
$method = $RTR->fetch_method();

require('core/Controller.php');
function &get_instance() {
    return CK_Controller::get_instance();
}

require('controllers/'.$class.'.php');
$CK = new $class();
call_user_func_array(array(&$CK, $method), array_slice($URI->rsegments, 2));