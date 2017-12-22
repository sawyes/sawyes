<?php

return [
    /*
    |--------------------------------------------------------------------------
    | to start monitor sql listing
    |--------------------------------------------------------------------------
    |
    | sql log file save in storage_path/log
    |
    */
    'debug_log' => env('APP_DEBUG_LOG', false),

     /*
    |--------------------------------------------------------------------------
    | to show schedule list or insert lists in database
    |--------------------------------------------------------------------------
    |
    | to show/create schedule lists
    |
    */
    'schedule' => [
        // database connection
        'connection' => 'default',

        // CREATE TABLE `schedule` (
        //   `id` int(11) NOT NULL AUTO_INCREMENT,
        //   `mutex_name` varchar(64) NOT NULL DEFAULT '',
        //   `short_command` varchar(128) NOT NULL DEFAULT '' COMMENT '短命令',
        //   `full_command` varchar(255) NOT NULL DEFAULT '' COMMENT '运行命令',
        //   `expression` varchar(64) NOT NULL DEFAULT '' COMMENT 'crontab expression',
        //   `without_overlapping` varchar(8) NOT NULL DEFAULT '' COMMENT '命令是否运行重复运行',
        //   `expires_at` smallint(6) NOT NULL DEFAULT '0' COMMENT '释放时间',
        //   `run_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '命令运行日期',
        //   `jobs_total` smallint(6) NOT NULL DEFAULT '0' COMMENT '命令在运行日期预计运行多少次',
        //   `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        //   `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        //   PRIMARY KEY (`id`)
        // ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
        'schedule_info_table' => 'schedule',
        // CREATE TABLE `schedule_detail` (
        //   `id` int(11) NOT NULL AUTO_INCREMENT,
        //   `mutex_name` varchar(64) NOT NULL DEFAULT '' COMMENT '唯一锁键',
        //   `short_command` varchar(128) NOT NULL DEFAULT '' COMMENT '短命令',
        //   `full_command` varchar(255) NOT NULL DEFAULT '' COMMENT '运行命令',
        //   `expression` varchar(64) NOT NULL DEFAULT '' COMMENT 'crontab expression',
        //   `schedule_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '命令运行具体时间',
        //   `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        //   `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        //   PRIMARY KEY (`id`)
        // ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='schedule list detail';
        'schedule_detail_table' => 'schedule_detail'
    ]

];
