<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/15
 * Time: 8:23 PM
 */
class TaskQueue extends CI_Model {
    private static $database_name = 'qdworkspace';
    private static $table_name = 'packet_task';
    public $database_enable = false;

    public function __construct() {
        $this->load->database();
        $this->init_queue_database();
    }
    
    public function init_queue_database() {
        $res = $this->db->query("show databases");
        $result_array = [];
        foreach ($res->result_array() as $database) {
            $result_array[] = $database['Database'];
        }

        if (!in_array(self::$database_name, $result_array)) {
            $this->database_enable =  $this->db->query('create database ' . self::$database_name);
        } else {
            $this->database_enable = true;
        }

        if ($this->database_enable) {
            $this->db->query('use ' . self::$database_name);

            $table_name = self::$table_name;
            $table_query =
                "CREATE TABLE IF NOT EXISTS `$table_name`(
                  `task_id` VARCHAR(100) NOT NULL,
                  `ipa_name` VARCHAR(100) NOT NULL,                 
                  `create_date` TIMESTAMP NOT NULL,
                  `ipa_name` VARCHAR(100) NOT NULL,
                  `base_path` VARCHAR(300) NOT NULL,
                  `version` VARCHAR(100) NOT NULL,
                  `build` VARCHAR(100) NOT NULL,
                  `is_gray` VARCHAR(100) NOT NULL,
                  `select_branch` VARCHAR(100) NOT NULL,
                  `status` enum('undefine','success','failed') NOT NULL DEFAULT 'undefine',
                  PRIMARY KEY ( `task_id` )
                )ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $res = $this->db->query($table_query);
        }
    }
}