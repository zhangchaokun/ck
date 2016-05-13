<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
/**
 * CK框架
 *
 * URI类
 * 解析URI和确定路由
 *
 * @package     
 * @author      菜鸟CK 
 * @copyright   
 * @license     
 * @link       
 * @since       Version 1.0
 */
class CK_URI{

    /**
     * 原始URL的分段信息
     * @var array
     * @access public
     */
    public $segments = array();

    /**
     * 经过路由后的分段信息
     * @var array
     * @access public
     */
    public $rsegments = array();

    /**
     * URL路径信息
     * @var string
     * @access public
     */
    public $uri_string;

    // --------------------------------------------------------------------
    /**
     * 获取URI串
     * @access  public
     * @return  null
     * @author  菜鸟CK
     */
    public function fetch_uri_string(){
        if($uri = $this->detect_uri()){
            $this->set_uri_string($uri);
            return;
        }
    }

    // --------------------------------------------------------------------
    /**
     * 设置URI串
     * @access  private
     * @param   string
     * @author  菜鸟CK
     */
    private function set_uri_string($str){
        $this->uri_string = ($str == '/') ? '' : $str;
    }

    // --------------------------------------------------------------------
    /**
     * URI检测
     * @access  private
     * @return  string
     * @author  菜鸟CK
     */
    private function detect_uri(){

        if(!isset($_SERVER['REQUEST_URI']) or !isset($_SERVER['SCRIPT_NAME'])){
            return '';
        }

        $uri = $_SERVER['REQUEST_URI'];
        if(strpos($uri,$_SERVER['SCRIPT_NAME']) === 0){
            $uri = substr($uri,strlen($_SERVER['SCRIPT_NAME']));
        }
        if($uri == '/' || empty($uri)){
            return '/';
        }

        $uri = parse_url($uri,PHP_URL_PATH);
        return str_replace(array('//','../'),'/',trim($uri,'/'));
    }

    // --------------------------------------------------------------------
    /**
     * 提取URI中的分段信息
     * @access  public
     * @author  菜鸟CK
     */
    public function explode_uri(){
        foreach(explode('/',preg_replace('|/*(.+?)/*$|','\\1',$this->uri_string)) as $val){
            $val = trim($val);
            if($val != ''){
                $this->segments[] = $val;
            }
        }
    }




    
}