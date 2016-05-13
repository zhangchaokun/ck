<?php
header("Content-Type:text/html;charset=utf-8;");
define('ENVIRONMENT', 'development');

if(defined('ENVIRONMENT')){
    switch(ENVIRONMENT){
        case 'development':
            error_reporting(E_ALL);
            break;
        case 'testing':
        case 'production':
            error_reporting(0);
        default:
            exit('应用环境没有正确设置');
            break;
    }
}

$system_path = 'system';
$app_folder = 'app';

if(defined('STDIN')){
    chdir(dirname(__FILE__));
}

if(realpath($system_path) !== false) {
    $system_path = realpath($system_path).DIRECTORY_SEPARATOR;
}

$system_path = rtrim($system_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

if(!is_dir($system_path)){
    exit("您的系统文件夹路径设置不正确,请打开下面的文件并更正: ".pathinfo(__FILE__, PATHINFO_BASENAME));
}

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('EXT', '.php');
define('BASEPATH', str_replace("\\", "/", $system_path));
define('FCPATH', str_replace(SELF, '', __FILE__));
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));
echo "<pre>";
var_dump(trim(BASEPATH, '/'));
echo "</pre>";
exit();

if(is_dir($app_folder)){
    define('APPPATH', $app_folder.'/');
}else{
    if(!is_dir(BASEPATH.$app_folder.'/')) {
        exit("您的系统文件夹路径设置不正确,请打开下面的文件并更正: ".SELF);
    }
    define('APPPATH', BASEPATH.$app_folder.'/');
}
require_once BASEPATH.'core/ChaoKun.php';