<?php

namespace Phphc\Gearman;
use Illuminate\Queue\QueueServiceProvider as ServiceProvider;
use Phphc\Gearman\Connectors\GearmanConnector;

class GearmanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Service' => base_path('app/Service'),
            __DIR__.'/Commands/Workers.php' => base_path('app/Console/Commands/Workers.php'),
        ],'gearman');
    }
    /**
     * Register the connectors on the queue manager.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    public function registerConnectors($manager)
    {
        parent::registerConnectors($manager);

        $this->registerGearmanConnector($manager);
    }
    /**
     * Register the Gearman queue connector.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    public function registerGearmanConnector($manager)
    {
        $manager->addConnector('gearman', function() {
            return new GearmanConnector();
        });
    }
}
