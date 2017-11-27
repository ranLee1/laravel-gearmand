<?php

namespace Phphc\Gearman;

use Illuminate\Support\ServiceProvider;

class SayHelloProvider extends ServiceProvider
{
    /**
     * 是否延时加载提供器。
     *
     * @var bool
     */
    protected $defer = true;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/gearman.php' => config_path('gearman.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
       $this->app->singleton('sayhello',function ($app) {
            return new SayHello($app['config']);
        });
        $this->app->singleton('gearmanclient',function ($app) {
            return new GearmanClient($app['config']);
        });
    }

    /**
     * 如果你的提供器仅在 服务容器 中注册绑定，就可以选择推迟其注册，直到当它真正需要注册绑定。
     * 推迟加载这种提供器会提高应用程序的性能，因为它不会在每次请求时都从文件系统中加载。
     *
     * @return array
     */
    public function provides()
    {
        return ['sayhello','gearmanclient'];
    }
}
