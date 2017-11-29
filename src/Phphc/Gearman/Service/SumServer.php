<?php

namespace App\Services;

class SumServer
{

    public function fire($job, $data)
    {
        $result = $data[0] + $data[1];
        //模拟任务消耗时间
        sleep(2);
        echo "Client results:{$result}\n";
        return $result;
    }

}