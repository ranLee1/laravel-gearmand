<?php
/**
 * Created by PhpStorm.
 * User: many
 * Date: 2017/11/25
 * Time: 10:19
 */

namespace Phphc\Gearman;


class GearmanClient
{
    protected $Config;
    public function __construct($config)
    {
//        if(!extension_loaded('gearman')){
//            trigger_error('当前Gearman服务尚未开启', E_USER_ERROR);
//        }
        $this->Config=$config;
    }
    public function name()
    {
        echo $this->Config->get('gearman.host');
    }
}