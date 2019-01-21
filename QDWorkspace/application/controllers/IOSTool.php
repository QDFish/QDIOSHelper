<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/20
 * Time: 9:16 PM
 */
class IOSTool extends CI_Controller
{
    public function index() {
        $this->load->helper('url');
        $this->load->view('NormalHtmlBegin');
        $this->load->view('IOSToolHeader', ['title' => 'IOS Tool']);
        $this->load->view('IOSToolBody');
        $this->load->view('NormalHtmlEnd');
    }
}