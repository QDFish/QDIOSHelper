<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/24
 * Time: 8:59 PM
 */
class Blog extends CI_Controller
{
    public function index() {
        $data = [
            'title' => 'qdfish_title',
            'content' => 'qdfish_content'
        ];
        $data['todo_list'] = array('Clean House', 'Call Mom', 'Run Errands');
        $this->load->view('blogview', $data);
    }
    
  

}