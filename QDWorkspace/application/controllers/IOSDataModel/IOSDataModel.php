<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/7
 * Time: 9:13 PM
 */
class IOSDataModel extends CI_Controller {

    public static $base_path = 'IOSDataModel/';
    public function index() {
        $this->load->helper('url');
        $this->load->view('NormalHtmlBegin', ["title" => "IOS VIEW HELP TOOL"]);
        $this->load->view(self::$base_path . 'IOSDataHeader');
        $this->load->view(self::$base_path . 'IOSDataBody');
        $this->load->view('NormalHtmlEnd');
    }
}