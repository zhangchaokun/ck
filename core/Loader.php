<?php
/**
 * CK框架
 *
 * 加载框架组件类
 *
 * @package     
 * @author      菜鸟CK 
 * @copyright   
 * @license     
 * @link       
 * @since       Version 1.0
 */
class CK_Loader{

    /**
     * 输出缓冲机制的嵌套级别
     * @var int
     * @access protected
     */
    protected $_ck_ob_level;

    /**
     * 加载视图的路径数组
     * @var array
     * @access protected
     */
    protected $_ck_view_paths = array();

    /**
     * 已缓存的变量数组
     * @var array
     * @access protected
     */
    protected $_ck_cached_vars = array();

    /**
     * 负载模型的路径数组
     * @var array
     * @access protected
     */
    protected $_ck_model_paths = array();

    /**
     * 已加载的模型数组
     * @var array
     * @access protected
     */
    protected $_ck_models = array();

    // --------------------------------------------------------------------
    /**
     * 构造方法
     * @access  public
     * @return  void
     * @author  菜鸟CK
     */
    public function __construct(){
        $this->_ck_ob_level = ob_get_level();
        $this->_ck_model_paths = array('');
        $this->_ck_view_paths = array(APPPATH.'views/' => true);

    }

    // --------------------------------------------------------------------
    /**
     * 初始化器
     * @access  public
     * @return  void
     * @author  菜鸟CK
     */
    public function initialize(){
        return;
    }

    // --------------------------------------------------------------------
    /**
     * 加载和实例化模型
     * @access  public
     * @param string $model    类名
     * @param string $name     模型名
     * @param bool   $db_conn  数据库连接
     * @return  void
     * @author  菜鸟CK
     */
    public function model($model,$name = '',$db_conn = false){

        //一次加载多个模型
        if(is_array($model)){
            foreach ($model as $base) {
                $this->model($base);
            }
            return;
        }
        if($model == ''){
            return;
        }
        $path = '';

        //模型是否在子文件夹，如果是，解析文件名和路径
        if(($last_slash = strrpos($model,'/')) !== false){
            $path = substr($model,0,$last_slash + 1);
            $model = substr($model,$last_slash + 1);
        }
        if($name == ''){
            $name = $model;
        }

        //如果模型已经加载则直返返回VOID
        if(in_array($name,$this->_ck_models,true)){
            return;
        }

        $CK =& get_instance();
        if(isset($CK->$name)){
            exit($name."该模型已经被加载使用的模型.");
        }
        $model = strtolower($model);

        foreach ($this->_ck_model_paths as $mod_path) {
            if(!file_exists($mod_path.'models/'.$path.$model.'.php')){
                continue;
            }
            if(!class_exists('CK_Model')){
                load_class('Model','core');
            }
            require_once($mod_path.'models/'.$path.$model.'.php');
            $model = ucfirst($model);
            $CK->$name = new $model();
            $this->_ck_models[] = $name;
            return;
        }
        exit("无法找到你指定的模型!");
    }

    // --------------------------------------------------------------------
    /**
     * 加载视图
     * @access  public
     * @param   string $view     被包括的“视图”文件的名称
     * @param   array  $vars     用于在视图中提取的数据的关联数组
     * @param   bool   $return   true/false - 返回数据或加载它
     * @return  void
     * @author  菜鸟CK
     */
    public function view($view, $vars = array(), $return = false){
        return $this->_ck_load(array('_ck_view' => $view, '_ck_vars' => $this->_ck_object_to_array($vars), '_ck_return' => $return));
    }

    // --------------------------------------------------------------------
    /**
     * 返回对象属性的关联数组
     * @access  protected
     * @param   object   $object    
     * @return  array
     * @author  菜鸟CK
     */
    protected function _ck_object_to_array($object){
        return (is_object($object)) ? get_object_vars($object) : $object;
    }

    // --------------------------------------------------------------------
    /**
     * 加载视图和文件
     * @access  protected
     * @param   array   $_ck_data    
     * @return  void
     * @author  菜鸟CK
     */
    protected function _ck_load($_ck_data){

        //设置默认的数据变量
        foreach (array('_ck_view', '_ck_vars', '_ck_path', '_ck_return') as $_ck_val){
            $$_ck_val = (!isset($_ck_data[$_ck_val])) ? false : $_ck_data[$_ck_val];
        }

        $file_exists = false;

        //设置请求文件路径
        if($_ck_path != ''){
            $_ck_x = explode('/', $_ck_path);
            $_ck_file = end($_ck_x);
        }else{
            $_ck_ext = pathinfo($_ck_view,PATHINFO_EXTENSION);
            $_ck_file = ($_ck_ext == '') ? $_ck_view.'.php': $_ck_view;
            foreach($this->_ck_view_paths as $view_file => $cascade){
                if(file_exists($view_file.$_ck_file)){
                    $_ck_path = $view_file.$_ck_file;
                    $file_exists = true;
                    break;
                }
                if(!$cascade){
                    break;
                }
            }
        }

        if(!$file_exists && !file_exists($_ck_path)){
            exit("无法加载请求的文件".$_ck_file);
        }

        if(is_array($_ck_vars)){
            $this->_ck_cached_vars = array_merge($this->_ck_cached_vars,$_ck_vars);
        }
        extract($this->_ck_cached_vars);

        ob_start();
        include($_ck_path);

        //直接返回数据
        if($_ck_return === true){
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        //刷新缓冲区
        if(ob_get_level() > $this->_ck_ob_level + 1){
            ob_end_flush();
        }

    }

}