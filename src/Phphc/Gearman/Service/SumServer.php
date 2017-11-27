<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SumServer
{

    public function fire($job, $data)
    {
        return $data[0] + $data[1];
    }

}