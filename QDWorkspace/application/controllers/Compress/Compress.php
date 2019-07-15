<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/7/1
 * Time: 10:15 AM
 */
class Compress extends CI_Controller
{
    public static $base_path = 'Compress/';
    public function index() {
        $this->load->helper('url');
        $this->load->view('NormalHtmlBegin', ["title" => "Compress TOOL"]);
        $this->load->view(self::$base_path . 'IOSDataHeader');
        $this->load->view(self::$base_path . 'IOSDataBody');
        $this->load->view('NormalHtmlEnd');
    }
}