<?php
/**
 * Created by PhpStorm.
 * User: many
 * Date: 2017/11/25
 * Time: 13:28
 */

namespace Phphc\Gearman;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueInterface;
use Phphc\Gearman\Jobs\GearmanJob;
use GearmanException;
use GearmanWorker;
use Exception;
use GearmanClient;
use Log;

class GearmanQueue extends Queue implements QueueInterface
{
    /**
     * The gearmand worker object
     *
     * @var GearmanWorker
     */
    protected $worker;
    /**
     * The gearmand client object
     *
     * @var GearmanClient
     */
    protected $client;
    /**
     * The name of the queue to which the jobs will be written or pulled for processing
     *
     * @var string
     */
    protected $queue;

    /**
     * @param GearmanClient $client
     * @param GearmanWorker $worker
     * @param $queue
     */
    public function __construct(GearmanClient $client, GearmanWorker $worker, $queue)
    {
        $this->client = $client;
        $this->worker = $worker;
        $this->queue = $queue;
    }

    public function size($queue = null)
    {
        return 0;
    }

    public function push($job, $data = '', $queue = null)
    {
        if (!$queue) {
            $queue = $this->queue;
        }
        $payload = $this->createPayload($job, $data);
        $this->doBackgroundAndHandleException($queue, $payload);
        return $this->client->returnCode();
    }


    public function pushRaw($payload, $queue = null, array $options = [])
    {
        throw new Exception('Gearman driver do not support the method pushRaw');
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        throw new Exception('Gearman driver do not support the method later');
    }


    public function pop($queue = null)
    {
        if (!$queue) {
            $queue = $this->queue;
        }
        return new GearmanJob($this->container, $this->worker, $queue);
    }


    private function doBackgroundAndHandleException($queue, $payload)
    {
        try {
            Log::error($queue);
            Log::error($payload);
            $this->client->doBackground($queue, $payload);
        } catch (Exception $e) {
            throw new GearmanException($e);
        }
    }
}