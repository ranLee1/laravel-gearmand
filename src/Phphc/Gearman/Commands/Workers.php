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
        //实例化gearman服务的客户端
        $client = new GearmanClient();
        //默认参数主机地址：127.0.0.1 端口：4730
        $config = config('queue.gearman');
        if (isset($config['hosts'])) {
            foreach ($config['hosts'] as $server) {
                $client->addServer($server['host'], $server['port']);
            }
        } else {
            $client->addServer($config['host'], $config['port']);
        }

        //可以通过setCompleteCallback函数给计算结果返回给客户端
        $client->setCompleteCallback(function (GearmanTask $task) use (&$sum) {
            $sum = $task->data();
            $this->info("Server results:" . $sum . "\n");
        });

        for ($i = 0; $i < 2; $i++) {
            //后期版本会封装单独的类来匹配数据项
            $data = [
                "displayName" => "App\\Services\\SumServer",
                "job" => "App\\Services\\SumServer",
                "maxTries" => null,
                "timeout" => null,
                "data" => [1 + $i, 2 + $i]
            ];
            //添加任务
            $client->addTask('default', json_encode($data, 1));
        }
        //添加任务

        $client->runTasks();
    }
}
