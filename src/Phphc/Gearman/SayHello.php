<?php

namespace Phphc\Gearman;

class SayHello
{
    public $config = array();

    public function __construct($_config)
    {
        $this->config = $_config;
    }

    public function world()
    {
        return $this->config;
    }

    public function say()
    {
        echo $this->config['gearman']['host'];
    }
}