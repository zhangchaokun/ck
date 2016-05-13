<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
/**
 * CK框架
 *
 * Roter类
 * 解析URI和确定路由
 *
 * @package     
 * @author      菜鸟CK 
 * @copyright   
 * @license     
 * @link       
 * @since       Version 1.0
 */
class CK_Router{

    /**
     * uri对象
     * @var    object
     * @access public
     */
    public $uri;

    /**
     * 路由配置数组
     * @var    array
     * @access public
     */
    public $routes = array();

    /**
     * 当前类名
     * @var    string
     * @access public
     */
    public $class = '';

    /**
     * 当前方法名
     * @var    string
     * @access public
     */
    public $method = 'index';

    /**
     * 控制器类子目录
     * @var string
     * @access public
     */
    public $directory = '';

    /**
     * 默认控制器
     * @var    string
     * @access public
     */
    public $default_controller;

    // --------------------------------------------------------------------
    /**
     * 构造方法
     * @access  public
     * @author  菜鸟CK
     */
    public function __construct(){
        $this->uri =& load_class('URI');
    }

    // --------------------------------------------------------------------
    /**
     * 设置路由映射
     * @access  public
     * @return  void
     * @author  菜鸟CK
     */
    public function set_routing(){
        if(is_file(APPPATH.'config/routes.php')){
            include(APPPATH.'config/routes.php');
        }
        $this->routes = (!isset($route) or !is_array($route)) ? array() : $route;
        $this->default_controller = (!isset($this->routes['default_controller']) or $this->routes['default_controller'] == '') ? false : $this->routes['default_controller'];
        $this->uri->fetch_uri_string();
        if($this->uri->uri_string == ''){
            return $this->set_default_controller();
        }
        $this->uri->explode_uri();
        $this->parse_routes();
    }

    // --------------------------------------------------------------------
    /**
     * 设置默认控制器
     * @access  private
     * @return  void
     * @author  菜鸟CK
     */    
    private function set_default_controller(){

    }

    // --------------------------------------------------------------------
    /**
     * 解析路由
     * @access  private
     * @return  void
     * @author  菜鸟CK
     */ 
    private function parse_routes(){
        $uri = implode('/',$this->uri->segments);
        if(isset($this->routes[$uri])){
            $rsegments = explode('/',$this->routes[$uri]);
            return $this->set_request($rsegments);
        }
    }

    // --------------------------------------------------------------------
    /**
     * 设置路由
     * @access  private
     * @param   array/bool
     * @return  void
     * @author  菜鸟CK
     */    
    private function set_request($segments = array()){
        if(count($segments) == 0){
            return $this->set_default_controller();
        }
        $this->set_class($segments[0]);
        if(isset($segments[1])){
            $this->set_method($segments[1]);
        }else{
            $segments[1] = 'index';
        }
        $this->uri->rsegments = $segments;
    }

    // --------------------------------------------------------------------
    /**
     * 设置类名
     * @access  public
     * @param   string
     * @return  void
     * @author  菜鸟CK
     */
    public function set_class($class){
        $this->class = str_replace(array('/','.'), '', $class);
    }

    // --------------------------------------------------------------------
    /**
     * 取出当前类
     * @access  public
     * @return  string
     * @author  菜鸟CK
     */
    public function fetch_class(){
        return $this->class;
    }

    // --------------------------------------------------------------------
    /**
     * 设置方法名
     * @access  public
     * @param   string
     * @return  void
     * @author  菜鸟CK
     */
    public function set_method($method){
        $this->method = $method;
    }

    // --------------------------------------------------------------------
    /**
     * 取出当前方法
     * @access  public
     * @return  string
     * @author  菜鸟CK
     */
    public function fetch_method(){
        return $this->method;
    }


    // --------------------------------------------------------------------
    /**
     * 设置目录名称
     * @access  public
     * @param   string
     * @return  void
     * @author  菜鸟CK
     */
    public function set_directory($dir){
        $this->directory = str_replace(array('/', '.'), '', $dir).'/';
    }

    // --------------------------------------------------------------------
    /**
     * 获取包含请求的控制器类的子目录(如果有的话)
     * @access  public
     * @return  string
     * @author  菜鸟CK
     */
    public function fetch_directory(){
        return $this->directory;
    }



}