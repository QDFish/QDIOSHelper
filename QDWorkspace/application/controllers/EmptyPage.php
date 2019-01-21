<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/12
 * Time: 10:28 AM
 */
class EmptyPage extends CI_Controller
{
    public function index() {
        show_404();
    }
}