<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
/**
 * CK框架
 *
 * 公共函数
 * 加载基类和执行请求
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
* 注册类
* 对类进行实例化注册加载
* @access   public
* @param    string
* @return   object
* @author   菜鸟CK
*/
if(!function_exists('load_class')){
    function &load_class($class,$directory = 'libraries', $prefix = 'CK_')
    {
        static $_classes = array();
        if(isset($_classes[$class])){
            return $_classes[$class];
        }
        $name = false;
        //从app/libraries和system/libraries文件夹中查找类
        foreach(array(APPPATH, BASEPATH) as $path){
            if(file_exists($path.$directory.'/'.$class.'.php')){
                $name = $prefix.$class;
                if (class_exists($name) === false){
                    require($path.$directory.'/'.$class.'.php');
                }
                break;
            }
        }

        //加载类扩展如果请求的是它的话
        if(file_exists(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php')){
            $name = config_item('subclass_prefix').$class;
            if(class_exists($name) === false){
                require(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php');
            }
        }
        //如果没有找到类
        if($name === false){
            exit('无法找到指定的类: '.$class.'.php');
        }
        is_loaded($class);

        $_classes[$class] = new $name();
        return $_classes[$class];
    }
}

// ------------------------------------------------------------------------
/**
* 保存库
* 对已经加载的库进行保存
* @access   public
* @param    string
* @return   array
* @author   菜鸟CK
*/
if(!function_exists('is_loaded')){
    function &is_loaded($class = '')
    {
        static $_is_loaded = array();
        if($class !== ''){
            $_is_loaded[strtolower($class)] = $class;
        }
        return $_is_loaded;
    }
}

// ------------------------------------------------------------------------
/**
* 加载主config.php文件
* @access   private
* @param    array
* @return   array
* @author   菜鸟CK
*/
if(!function_exists('get_config')){
    function &get_config($replace = array())
    {
        static $_config;
        if (isset($_config)){
            return $_config[0];
        }

        if (!defined('ENVIRONMENT') or !file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/config.php')){
            $file_path = APPPATH.'config/config.php';
        }

        if (!file_exists($file_path)){
            exit('配置文件不存在');
        }

        require($file_path);

        if (!isset($config) or !is_array($config)){
            exit('配置文件格式不正确');
        }

        if (count($replace) > 0){
            foreach ($replace as $key => $val){
                if (isset($config[$key])){
                    $config[$key] = $val;
                }
            }
        }
        return $_config[0] =& $config;
    }
}


// ------------------------------------------------------------------------
/**
* 返回指定的配置项
* @access   public
* @param    string
* @return   mixed
* @author   菜鸟CK
*/
if(!function_exists('config_item')){
    function config_item($item)
    {
        static $_config_item = array();
        if (!isset($_config_item[$item])){
            $config =& get_config();
            if (!isset($config[$item])){
                return false;
            }
            $_config_item[$item] = $config[$item];
        }
        return $_config_item[$item];
    }
}

// ------------------------------------------------------------------------
/**
 * 错误页处理函数
 * @access   public
 * @return   void
 * @author   菜鸟CK
 */
if(!function_exists('show_error')){
    function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
    {
        $_error =& load_class('Exceptions', 'core');
        echo $_error->show_error($heading, $message, 'error_general', $status_code);
        exit;
    }
}

// ------------------------------------------------------------------------
/**
 * 404处理函数
 * @access   public
 * @return   void
 * @author   菜鸟CK
 */
if(!function_exists('show_404')){
    function show_404($page = '', $log_error = TRUE)
    {
        $_error =& load_class('Exceptions', 'core');
        $_error->show_404($page, $log_error);
        exit;
    }
}

// ------------------------------------------------------------------------
/**
 * 设置HTTP状态头
 * @access  public
 * @param   int     状态码
 * @param   string
 * @return  void
 */
if(!function_exists('set_status_header')){
    function set_status_header($code = 200, $text = '')
    {
        $stati = array(
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',

            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        if($code == '' or ! is_numeric($code)){
            show_error('状态码必须是数字', 500);
        }

        if(isset($stati[$code]) and $text == ''){
            $text = $stati[$code];
        }

        if($text == ''){
            show_error('无对应的状态文本,请检查您的状态码或编写自己的状态文本.', 500);
        }

        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : false;

        if(substr(php_sapi_name(), 0, 3) == 'cgi'){
            header("Status: {$code} {$text}", true);
        }elseif($server_protocol == 'HTTP/1.1' or $server_protocol == 'HTTP/1.0'){
            header($server_protocol." {$code} {$text}", true, $code);
        }else{
            header("HTTP/1.1 {$code} {$text}", true, $code);
        }
    }
}

// ------------------------------------------------------------------------
/**
 * 确定使用的PHP版本大于该值
 * @access   public
 * @param    string
 * @return   bool
 * @author   菜鸟CK
 */
if(!function_exists('is_php')){
    function is_php($version = '5.0.0')
    {
        static $_is_php;
        $version = (string)$version;
        if(!isset($_is_php[$version])){
            $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? false : true;
        }
        return $_is_php[$version];
    }
}

// ------------------------------------------------------------------------
/**
 * HTML转义
 * @access   public
 * @param    mixed
 * @return   mixed
 * @author   菜鸟CK
 */
if(!function_exists('html_escape')){
    function html_escape($var)
    {
        if(is_array($var)){
            return array_map('html_escape', $var);
        }else{
            return htmlspecialchars($var, ENT_QUOTES, config_item('charset'));
        }
    }
}