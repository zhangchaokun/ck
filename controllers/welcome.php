<?php
class welcome extends CK_Controller {

    public function hello() {
        echo 'CK框架';
    }

    public function saysomething($str) {
        $this->load->model('test_model');
        $info = $this->test_model->get_test_data();
        echo $info;
    }
}