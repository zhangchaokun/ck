<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
/**
 * CK框架
 *
 * 异常处理类
 *
 * @package     
 * @author      菜鸟CK 
 * @copyright   
 * @license     
 * @link       
 * @since       Version 1.0
 */
class CK_Exceptions{
    public $action;
    public $severity;
    public $message;
    public $filename;
    public $line;

    /**
     * 输出缓冲机制的嵌套级别
     * @var int
     * @access public
     */
    public $ob_level;

    /**
     * 可用错误级别
     * @var array
     * @access public
     */
    public $levels = array(
        E_ERROR             =>  'Error',
        E_WARNING           =>  'Warning',
        E_PARSE             =>  'Parsing Error',
        E_NOTICE            =>  'Notice',
        E_CORE_ERROR        =>  'Core Error',
        E_CORE_WARNING      =>  'Core Warning',
        E_COMPILE_ERROR     =>  'Compile Error',
        E_COMPILE_WARNING   =>  'Compile Warning',
        E_USER_ERROR        =>  'User Error',
        E_USER_WARNING      =>  'User Warning',
        E_USER_NOTICE       =>  'User Notice',
        E_STRICT            =>  'Runtime Notice'
    );

    // --------------------------------------------------------------------
    /**
     * 构造方法
     * @access  public
     * @return  void
     * @author  菜鸟CK
     */
    public function __construct(){
        $this->ob_level = ob_get_level();
    }

    // --------------------------------------------------------------------
    /**
     * 异常日志
     * @access  public
     * @param   string  错误严重性
     * @param   string  错误字符串
     * @param   string  错误文件路径
     * @param   string  错误所在行
     * @return  string
     * @author  菜鸟CK
     */
    public function log_exception($severity, $message, $filepath, $line){

        $severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
    }

    // --------------------------------------------------------------------
    /**
     * 404页处理
     * @access  public
     * @param   string  404页面
     * @param   string  是否写日志
     * @return  string
     * @author  菜鸟CK
     */
    public function show_404($page = '', $log_error = false){

        $heading = "404页面";
        $message = "没有找到你请求的页面";
        echo $this->show_error($heading, $message, 'error_404', 404);
        exit;
    }

    // --------------------------------------------------------------------
    /**
     * 一般错误页处理
     * @access  public
     * @param   string  标题
     * @param   string  消息体
     * @param   string  模板名
     * @param   int     状态码
     * @return  string
     * @author  菜鸟CK
     */
    public function show_error($heading, $message, $template = 'error_general', $status_code = 500){

        set_status_header($status_code);
        $message = '<p>'.implode('</p><p>', (!is_array($message)) ? array($message) : $message).'</p>';

        if (ob_get_level() > $this->ob_level + 1){
            ob_end_flush();
        }
        ob_start();
        include(APPPATH.'errors/'.$template.'.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    // --------------------------------------------------------------------
    /**
     * PHP的错误处理
     * @access  public
     * @param   string  错误严重性
     * @param   string  错误字符串
     * @param   string  错误文件路径
     * @param   string  错误所在行
     * @return  string
     * @author  菜鸟CK
     */
    public function show_php_error($severity, $message, $filepath, $line){

        $severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

        $filepath = str_replace("\\", "/", $filepath);

        //出于安全原因,不显示完整的文件路径
        if (false !== strpos($filepath, '/')){
            $x = explode('/', $filepath);
            $filepath = $x[count($x)-2].'/'.end($x);
        }

        if (ob_get_level() > $this->ob_level + 1){
            ob_end_flush();
        }
        ob_start();
        include(APPPATH.'errors/error_php.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }


}