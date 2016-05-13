<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
/**
 * CK框架
 *
 * ChaoKun系统初始化文件
 *
 * @package     
 * @author      菜鸟CK 
 * @copyright   
 * @license     
 * @link       
 * @since       Version 1.0
 */
// ------------------------------------------------------------------------

/**
 * 框架版本
 * @var string
 */
define('CK_VERSION', '1.0.0');

/**
 * 框架分支(Core = true, Reactor = false)
 * @var boolean
 */
define('CI_CORE', false);

/*
 * ------------------------------------------------------
 *  加载全局函数
 * ------------------------------------------------------
 */
require(BASEPATH.'core/Common.php');

/*
 * ------------------------------------------------------
 *  加载框架应用常量
 * ------------------------------------------------------
 */
if (defined('ENVIRONMENT') and file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php')){
    require(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
}else{
    require(APPPATH.'config/constants.php');
}

/*
 * ------------------------------------------------------
 *  检测版本处理
 * ------------------------------------------------------
 */
if (!is_php('5.3')){
    @set_magic_quotes_runtime(0);
}

/*
 * ------------------------------------------------------
 *  设置脚本执行时间限制
 * ------------------------------------------------------
 */
if (function_exists("set_time_limit") == true AND @ini_get("safe_mode") == 0){
     @set_time_limit(300);
}

/*
 * ------------------------------------------------------
 *  实例化URI
 * ------------------------------------------------------
 */
$URI =& load_class('URI', 'core');

/*
 * ------------------------------------------------------
 *  设置并初始化路由
 * ------------------------------------------------------
 */
$RTR =& load_class('Router', 'core');
$RTR->set_routing();

/*
 * ------------------------------------------------------
 *  加载控制器
 * ------------------------------------------------------
 */
require BASEPATH.'core/Controller.php';
function &get_instance()
{
    return CK_Controller::get_instance();
}
if(!file_exists(APPPATH.'controllers/'.$RTR->fetch_directory().$RTR->fetch_class().'.php')){
    show_error('无法加载您的默认控制器.请确保在你的routes.php文件指定的控制器是有效的.');
}
include(APPPATH.'controllers/'.$RTR->fetch_directory().$RTR->fetch_class().'.php');

/*
 * ------------------------------------------------------
 *  安全检查
 * ------------------------------------------------------
 */
$class  = $RTR->fetch_class();
$method = $RTR->fetch_method();
if(!class_exists($class) 
    or strncmp($method, '_', 1) == 0
    or in_array(strtolower($method), array_map('strtolower', get_class_methods('CK_Controller')))
){
    if(!empty($RTR->routes['404_override'])){
        $x = explode('/', $RTR->routes['404_override']);
        $class = $x[0];
        $method = (isset($x[1]) ? $x[1] : 'index');
        if (!class_exists($class)){
            if (!file_exists(APPPATH.'controllers/'.$class.'.php')){
                show_404("{$class}/{$method}");
            }
            include_once(APPPATH.'controllers/'.$class.'.php');
        }
    }else{
        show_404("{$class}/{$method}");
    }
}

/*
 * ------------------------------------------------------
 *  初始化请求的控制器
 * ------------------------------------------------------
 */
$CK = new $class();

/*
 * ------------------------------------------------------
 *  调用请求的方法
 * ------------------------------------------------------
 */
if(method_exists($CK, '_remap')){
    $CK->_remap($method, array_slice($URI->rsegments, 2));
}else{
    if(!in_array(strtolower($method), array_map('strtolower', get_class_methods($CK)))){
        if(!empty($RTR->routes['404_override'])){
            $x = explode('/', $RTR->routes['404_override']);
            $class = $x[0];
            $method = (isset($x[1]) ? $x[1] : 'index');
            if(!class_exists($class)){
                if(!file_exists(APPPATH.'controllers/'.$class.'.php')){
                    show_404("{$class}/{$method}");
                }
                include_once(APPPATH.'controllers/'.$class.'.php');
                unset($CK);
                $CK = new $class();
            }
        }else{
            show_404("{$class}/{$method}");
        }
    }
    call_user_func_array(array(&$CK, $method), array_slice($URI->rsegments, 2));
}