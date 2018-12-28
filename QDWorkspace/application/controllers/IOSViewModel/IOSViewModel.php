<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/26
 * Time: 8:16 PM
 */
class IOSViewModel extends CI_Controller
{
    public static $base_path = 'IOSViewModel/';

    public function index() {
        $this->load->helper('url');
        $this->load->view('NormalHtmlBegin');
        $this->load->view(self::$base_path . 'IOSViewHeader');
        $this->load->view(self::$base_path . 'IOSViewHtmlBody');
        $this->load->view('NormalHtmlEnd');
    }
}