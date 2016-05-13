<?php if(!defined('BASEPATH')) exit('禁止直接访问脚本!');
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
     * 已加载类列表
     * @var array
     * @access protected
     */
    protected $_ck_classes = array();

    /**
     * 加载文件数组
     * @var array
     * @access protected
     */
    protected $_ck_loaded_files = array();

    /**
     * 加载库的路径数组
     * @var array
     * @access protected
     */
    protected $_ck_library_paths = array();

    /**
     * 负载模型的路径数组
     * @var array
     * @access protected
     */
    protected $_ck_model_paths = array();

    /**
     * 负载助手函数文件列表
     *
     * @var array
     * @access protected
     */
    protected $_ck_helper_paths     = array();

    /**
     * 已加载的模型数组
     * @var array
     * @access protected
     */
    protected $_ck_models = array();

    /**
     * 负载助手函数数组
     * @var array
     * @access protected
     */
    protected $_ck_helpers = array();

    /**
     *控制器类设置的加载基类列表
     * @var array
     * @access protected
     */
    protected $_base_classes = array();

    /**
     * 类名称映射数组
     * @var array
     * @access protected
     */
    protected $_ck_varmap = array('unit_test' => 'unit','user_agent' => 'agent');

    // --------------------------------------------------------------------
    /**
     * 构造方法
     * @access  public
     * @return  void
     * @author  菜鸟CK
     */
    public function __construct(){

        $this->_ck_ob_level = ob_get_level();
        $this->_ck_library_paths = array(APPPATH, BASEPATH);
        $this->_ck_helper_paths = array(APPPATH, BASEPATH);
        $this->_ck_model_paths = array(APPPATH);
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
        $this->_ck_classes = array();
        $this->_ck_loaded_files = array();
        $this->_ck_models = array();
        $this->_base_classes =& is_loaded();
        return $this;
    }

    // --------------------------------------------------------------------
    /**
     * 加载和实例化类库
     * @param   mixed  类名或包含类名数组
     * @param   mixed   可选参数
     * @param   string  可选对象名称
     * @return  void
     * @author  菜鸟CK
     */
    public function library($library = '',$params = null,$object_name = null){

        if(is_array($library)){
            foreach($library as $class){
                $this->library($class,$params);
            }
            return;
        }

        if(empty($library) or isset($this->_base_classes[$library])){
            return false;
        }

        if(!is_null($params) && !is_array($params)){
            return null;
        }

        $this->_ck_load_class($library,$params,$object_name);
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
     * 加载指定的辅助文件
     * @access  public
     * @param   mixed 要加载的辅助文件或文件列表
     * @return  void
     * @author  菜鸟CK
     */
    public function helper($helpers = array()){

        foreach ($this->_ck_prep_filename($helpers, '_helper') as $helper){
            if(isset($this->_ck_helpers[$helper])){
                continue;
            }

            $ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix').$helper.'.php';

            //确认是否是帮助扩展请求
            if (file_exists($ext_helper)){
                $base_helper = BASEPATH.'helpers/'.$helper.'.php';
                if (!file_exists($base_helper)){
                    exit('无法加载请求的文件: helpers/'.$helper.'.php');
                }
                include_once($ext_helper);
                include_once($base_helper);
                $this->_ck_helpers[$helper] = true;
                continue;
            }

            //尝试加载辅助函数文件
            foreach($this->_ck_helper_paths as $path){
                if(file_exists($path.'helpers/'.$helper.'.php')){
                    include_once($path.'helpers/'.$helper.'.php');
                    $this->_ck_helpers[$helper] = true;
                    break;
                }
            }

            //无法加载辅助函数文件
            if(!isset($this->_ck_helpers[$helper])){
                exit('无法加载辅助函数文件: helpers/'.$helper.'.php');
            }
        }
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

        //短标签处理
        if((bool) @ini_get('short_open_tag') === false and config_item('rewrite_short_tags') == true){
            echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ck_path))));
        }else{
            include($_ck_path);
        }

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

    // --------------------------------------------------------------------
    /**
     * 加载请求的类
     * @param   string  要加载的类
     * @param   mixed   可选参数
     * @param   string  可选对象名称
     * @return  void
     * @author  菜鸟CK
     */
    protected function _ck_load_class($class,$params,$object_name){

        $class = str_replace('.php','',trim($class,'/'));

        $subdir = '';
        if(($last_slash = strrpos($class,'/')) !== false){
            $subdir = substr($class, 0, $last_slash + 1);
            $class = substr($class, $last_slash + 1);
        }

        foreach(array(ucfirst($class), strtolower($class)) as $class){
            $subclass = APPPATH.'libraries/'.$subdir.config_item('subclass_prefix').$class.'.php';
            if(file_exists($subclass)){
                $baseclass = BASEPATH.'libraries/'.ucfirst($class).'.php';
                if(!file_exists($baseclass)){
                    exit("无法加载请求的类: ".$class);
                }
                //类是否加载过
                if(in_array($subclass, $this->_ck_loaded_files)){
                    if(!is_null($object_name)){
                        $CK =& get_instance();
                        if(!isset($CK->$object_name)){
                            return $this->_ck_init_class($class, config_item('subclass_prefix'), $params, $object_name);
                        }
                    }
                    $is_duplicate = true;
                    return;
                }
                include_once($baseclass);
                include_once($subclass);
                $this->_ck_loaded_files[] = $subclass;
                return $this->_ck_init_class($class, config_item('subclass_prefix'), $params, $object_name);
            }
            $is_duplicate = false;
            foreach($this->_ck_library_paths as $path){
                $filepath = $path.'libraries/'.$subdir.$class.'.php';
                if(!file_exists($filepath)){
                    continue;
                }
                //是否加载过
                if (in_array($filepath, $this->_ck_loaded_files)){
                    if(!is_null($object_name)){
                        $CK =& get_instance();
                        if(!isset($CK->$object_name)){
                            return $this->_ck_init_class($class, '', $params, $object_name);
                        }
                    }
                    $is_duplicate = true;
                    return;
                }
                include_once($filepath);
                $this->_ck_loaded_files[] = $filepath;
                return $this->_ck_init_class($class, '', $params, $object_name);
            }
        }
        if($subdir == ''){
            $path = strtolower($class).'/'.$class;
            return $this->_ck_load_class($path, $params);
        }
        if($is_duplicate == false) {
            exit("无法加载请求的类: ".$class);
        }
    }

    // --------------------------------------------------------------------
    /**
     * 实例化一个类
     * @access  protected
     * @param   string
     * @param   string
     * @param   bool
     * @param   string
     * @return  void
     * @author  菜鸟CK
     */
    protected function _ck_init_class($class, $prefix = '', $config = false, $object_name = null){

        if($prefix == ''){
            if(class_exists('CK_'.$class)){
                $name = 'CK_'.$class;
            }elseif(class_exists(config_item('subclass_prefix').$class)){
                $name = config_item('subclass_prefix').$class;
            }else{
                $name = $class;
            }
        }else{
            $name = $prefix.$class;
        }

        if(!class_exists($name)) {
            exit("不存在的类: ".$class);
        }

        $class = strtolower($class);

        if(is_null($object_name)){
            $classvar = (!isset($this->_ck_varmap[$class])) ? $class : $this->_ck_varmap[$class];
        }else{
            $classvar = $object_name;
        }
        $this->_ck_classes[$class] = $classvar;

        $CK =& get_instance();
        if($config !== null) {
            $CK->$classvar = new $name($config);
        }else{
            $CK->$classvar = new $name;
        }
    }

    // --------------------------------------------------------------------
    /**
     * helper的辅助函数,对文件名进行预处理
     * @param   mixed   文件名
     * @param   string  文件后缀
     * @return  array
     * @author  菜鸟CK
     */
    protected function _ck_prep_filename($filename, $extension){

        if(!is_array($filename)){
            return array(strtolower(str_replace('.php', '', str_replace($extension, '', $filename)).$extension));
        }else{
            foreach($filename as $key => $val){
                $filename[$key] = strtolower(str_replace('.php', '', str_replace($extension, '', $val)).$extension);
            }
            return $filename;
        }
    }

}