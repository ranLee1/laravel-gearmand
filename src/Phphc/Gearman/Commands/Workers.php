<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use GearmanClient;
use GearmanTask;

class Workers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gearman:test';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'show gearman test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->seedWork();
        return '';
    }

    public function seedWork()
    {
        $sum = 0;
        $client = new GearmanClient();
        $client->addServers('127.0.0.1:4730'); //默认参数主机地址：127.0.0.1 端口：4730

//可以通过setCompleteCallback函数给计算结果返回给客户端
        $client->setCompleteCallback(function (GearmanTask $task) use (&$sum) {
            $sum = $task->data();
            $this->info("计算结果为:" . $sum . "\n");
        });
//添加5个需要累加和的任务
        $data = ["displayName" => "App\\Services\\SumServer", "job" => "App\\Services\\SumServer", "maxTries" => null, "timeout" => null, "data" => [1, 2]];

        $client->addTask('default', json_encode($data, 1));

//运行队列中的任务，do系列不需要runTask()
        $client->runTasks();
    }
}
