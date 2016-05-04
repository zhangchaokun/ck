<?php
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
    function &load_class($class,$directory = 'core')
    {
        static $_classes = array();
        if(isset($_classes[$class])){
            return $_classes[$class];
        }
        $name = 'CK_'.$class;
        if(file_exists($directory.DIRECTORY_SEPARATOR.$class.'.php')){
            require($class.'.php');
        }else{
            exit("无法找到{$class}这个类!");
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