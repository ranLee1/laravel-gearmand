laravel-gearman
===========

一个简单的laravel for gearman 服务 

[![Latest Stable Version](https://poser.pugx.org/many/gearman/v/stable)](https://packagist.org/packages/many/gearman)
[![Build Status](https://travis-ci.org/pengbd3/laravel-gearmand.svg?branch=master)](https://travis-ci.org/pengbd3/laravel-gearmand)
[![Latest Unstable Version](https://poser.pugx.org/many/gearman/v/unstable)](https://packagist.org/packages/many/gearman)
[![License](https://poser.pugx.org/many/gearman/license)](https://packagist.org/packages/many/gearman)

# 描述:
该包是gearmn服务对laravel的一个适配方案,参照 [laravel-gearman](https://github.com/pafelin/laravel-gearman) 
进行了扩展，安装之前确保php gearman扩展安装以及gearman服务已正确安装，具体的安装方案可以参考 [gearman服务安装](https://pengbd3.github.io/2017/11/08/gearman1/)

# 安装:

使用composer安装 `composer require many/gearman`  
* 进入`laravel`项目，修改配置文件 `config/app.php` ,首先需要注释掉原有队列服务器提供者的代码:

    //'Illuminate\Queue\QueueServiceProvider::class',

    然后添加自定义的服务器提供者:

    'Phphc\Gearman\GearmanServiceProvider::class',

* 接着在 `config/queue.php` 文件中添加:

      'connections' => array(
        //追加内容 start
        'gearman' => array(
            'driver' => 'gearman',
            'host'   => '127.0.0.1',
            'queue'  => 'default',
            'port'   => 4730,
            'timeout' => 300
        )
      )

    如果你有多个Gearman服务器:

      'connections' => array(
        'gearman' => array(
            'driver' => 'gearman',
            'hosts'  => array(
                array('host' => '127.0.0.1', 'port' => 4730),
                array('host' => '127.0.0.2', 'port' => 4730),
            ),
            'queue'  => 'default',
            'timeout' => 300
        )
      )

* 修改 `laravel` 项目根目录 `.evn` 的配置为:
  
      `QUEUE_DRIVER=gearman`  

* 接下来执行发布命令 ` php artisan vendor:publish --tag=gearman --force ` 
* 然后使用composer 自动加载命令 `composer dump-autoload`
* 启动gearman服务 ` gearmand -d -l /var/run/log/gearmand.log `
* 在终端执行服务端：`php artisan queue:work` 客户端： `php artisan gearman:test`  
最终效果如下图:  
![success](http://oih4t7o53.bkt.clouddn.com/composer/laravel-gearman/gearman1.jpg)  
* 多个终端同时运行 `php artisan queue:work` 可以达到并行处理任务的效果。服务端的进程守护建议使用 `laravel` 官方推荐的 [supervisor]('https://laravel.com/docs/5.5/queues#supervisor-configuration')。
# 说明:

在执行命令 ` php artisan vendor:publish --tag=gearman --force ` 导入了两个文件到项目中  
-  job 脚本:`app/Service/SumServer.php` 和 client 脚本:`app/Console/Commands/Workers.php`
    - SumServer.php 核心代码:
        ```php
           
           public function fire($job, $data)
            {
                $result=$data[0] + $data[1];
                //模拟任务消耗时间
                sleep(2);
                echo "Client results:{$result}\n";
                return $result;
            }
        
        ```
    - Workers.php 核心代码:
        ```php
        
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
        ```
- 当laravel队列监听任务启动时，默认将名为 `default` 的函数 注册到gearman服务中，在终端可以通过命令 `gearadmin --status` 查看，在 `$client->addTask` 添加任务的时候第一个参数为函数名,第二个参数为需要提交到job中的数据。`$data1` 数组的 `displayName` 和 `job` 项是需要处理任务的命名空间名,`data`项才是最终 `SumServer` 接收到的值。
- 值得注意的是 `SumServer` 中的 `fire` 函数包含两个参数 `$job` 和 `$data`接收到客户端的数据 `array(1, 2)`
- 服务端的进程守护建议使用 `laravel` 官方推荐的 [supervisor]('https://laravel.com/docs/5.5/queues#supervisor-configuration')
# 使用方法:

在 `app/Service` 文件夹下新增一个名为: `SendMail.php` 的类

```php
<?php
    namespace App\Services;

    class SendMail {

        public function fire($job, $data)
        {
            mail('pavel@taskprocess.com', 'gearman test', $data['message']);
        }

    }
```
在你的web路由中添加:
```php
    
    Route::get('/gearman', function() {
        foreach (array(1,2,3) as $row) {
            Queue::push('App\Services\SendMail', array('message' => 'Message' . $row));
        }
    });
    
```

# Bugs
如果发现有任何问题，请提交issue