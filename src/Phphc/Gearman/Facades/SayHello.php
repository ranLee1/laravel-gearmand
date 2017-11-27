<?php

namespace Phphc\Gearman\Facades;
use Illuminate\Support\Facades\Facade;
class SayHello extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sayhello';
    }
}