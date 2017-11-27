<?php
/**
 * Created by PhpStorm.
 * User: many
 * Date: 2017/11/24
 * Time: 17:52
 */
return [
    'hosts' => array('host' => '127.0.0.1', 'port' => 4730),//单机部署用一维数组，多机用二维数组
    'queue' => 'default',//队列类型
    'port' => 4730,//端口号
    'timeout' => 300 //单位为秒
];