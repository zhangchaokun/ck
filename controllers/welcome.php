<?php
class welcome extends CK_Controller {

    public function hello() {
        echo 'CK框架';
    }
    public function index(){
        echo "hehe";
    }

    public function saysomething($str) {
        $this->load->model('test_model');
        $info = $this->test_model->get_test_data();
        $data['info'] = $info;
        echo $str;
        $this->load->view("test_view",$data);
    }
}