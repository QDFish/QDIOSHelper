<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-01-18 01:44:20 --> Severity: error --> Exception: Invalid binary string! /Project/AutoPacket/Webserver/vendor/rodneyrehm/plist/classes/CFPropertyList/CFBinaryPropertyList.php 534
ERROR - 2019-01-18 02:25:07 --> Severity: error --> Exception: Invalid binary string! /Project/AutoPacket/Webserver/vendor/rodneyrehm/plist/classes/CFPropertyList/CFBinaryPropertyList.php 534
ERROR - 2019-01-18 06:18:49 --> Severity: error --> Exception: Invalid binary string! /Project/AutoPacket/Webserver/vendor/rodneyrehm/plist/classes/CFPropertyList/CFBinaryPropertyList.php 534
ERROR - 2019-01-18 07:39:36 --> Query error: Duplicate column name 'ipa_name' - Invalid query: CREATE TABLE IF NOT EXISTS `packet_task`(
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
                )ENGINE=InnoDB DEFAULT CHARSET=utf8;
ERROR - 2019-01-18 08:22:52 --> Severity: error --> Exception: Invalid binary string! /Project/AutoPacket/Webserver/vendor/rodneyrehm/plist/classes/CFPropertyList/CFBinaryPropertyList.php 534
