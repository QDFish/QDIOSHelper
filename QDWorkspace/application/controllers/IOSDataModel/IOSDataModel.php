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
        $this->load->view('NormalHtmlBegin', ["title" => "IOS Data HELP TOOL"]);
        $this->load->view(self::$base_path . 'IOSDataHeader');
        $this->load->view(self::$base_path . 'IOSDataBody');
        $this->load->view('NormalHtmlEnd');
    }

    public function textFlock() {
        echo php_sapi_name();

        
//        $json = json_decode($_POST['ddd'], true);
//        echo json_encode($json['ff']);
        
//        $this->load->helper('url');

//        $fp = fopen(base_url() .  "application/controllers/IOSDataModel/lock.txt", "r+");
//
//        if (flock($fp, LOCK_EX)) {  // 进行排它型锁定
//            sleep(6);
////            ftruncate($fp, 0);      // truncate file
////            fwrite($fp, "Write something here\n");
////            fflush($fp);            // flush output before releasing the lock
//            echo "hhh";
////            flock($fp, LOCK_UN);    // 释放锁定
//        } else {
//            echo "Couldn't get the lock!";
//        }

//        fclose($fp);


    }
}