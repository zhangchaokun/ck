<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
/**
 * CK框架
 *
 * 日志类
 *
 * @package     
 * @author      菜鸟CK 
 * @copyright   
 * @license     
 * @link       
 * @since       Version 1.0
 */
class CK_Log{

    protected $_log_path;
    protected $_threshold = 1;
    protected $_date_fmt = 'Y-m-d H:i:s';
    protected $_enabled = true;
    protected $_levels  = array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

    // --------------------------------------------------------------------
    /**
     * 构造方法
     * @access  public
     * @return void
     * @author  菜鸟CK
     */
    public function __construct(){
        $config =& get_config();
        $this->_log_path = ($config['log_path'] != '') ? $config['log_path'] : APPPATH.'logs/';
        if(!is_dir($this->_log_path) or ! is_really_writable($this->_log_path)){
            $this->_enabled = false;
        }
        if(is_numeric($config['log_threshold'])){
            $this->_threshold = $config['log_threshold'];
        }
        if($config['log_date_format'] != ''){
            $this->_date_fmt = $config['log_date_format'];
        }
    }

    // --------------------------------------------------------------------
    /**
     * 写日志文件
     * @access  public
     * @param   string  错误级别
     * @param   string  错误消息
     * @param   bool    错误是否是一个PHP的错误
     * @return  bool
     * @author  菜鸟CK
     */
    public function write_log($level = 'error', $msg, $php_error = false){

        if($this->_enabled === false){
            return false;
        }
        $level = strtoupper($level);
        if(!isset($this->_levels[$level]) or ($this->_levels[$level] > $this->_threshold)){
            return false;
        }

        $filepath = $this->_log_path.'log-'.date('Y-m-d').'.php';
        $message  = '';

        if(!file_exists($filepath)){
            $message .= "<"."?php  if(!defined('BASEPATH')) exit('禁止直接访问脚本!'); ?".">\n\n";
        }

        if(!$fp = @fopen($filepath, FOPEN_WRITE_CREATE)){
            return false;
        }

        $message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($filepath, FILE_WRITE_MODE);
        return true;
    }

}