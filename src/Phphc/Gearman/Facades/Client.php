<?php

namespace Phphc\Gearman\Facades;
use Illuminate\Support\Facades\Facade;
class Client extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'gearmanclient';
    }
}