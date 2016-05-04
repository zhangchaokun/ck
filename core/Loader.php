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
        $this->_ck_model_paths = array('');
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





}