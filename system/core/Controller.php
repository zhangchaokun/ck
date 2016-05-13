<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
/**
 * CK框架
 *
 * 框架应用程序控制器类
 *
 * @package     
 * @author      菜鸟CK 
 * @copyright   
 * @license     
 * @link       
 * @since       Version 1.0
 */
class CK_Controller{
    
    /**
     * 引用CK单例模式
     * @var object
     * @access private
     */
    private static $instance;

    // --------------------------------------------------------------------
    /**
     * 构造方法
     * @access  public
     * @return void
     * @author  菜鸟CK
     */
    public function __construct(){
        self::$instance =& $this;
        foreach (is_loaded() as $var => $class) {
            $this->$var =& load_class($class);
        }
        $this->load =& load_class('Loader','core');
        $this->load->initialize();
    }

    // --------------------------------------------------------------------
    /**
     * 获取CK单例
     * @access  public
     * @static
     * @return  object
     * @author  菜鸟CK
     */
    public static function &get_instance(){
        return self::$instance;
    }


}