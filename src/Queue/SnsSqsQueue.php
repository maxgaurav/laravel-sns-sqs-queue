<?php

namespace MaxGaurav\LaravelSnsSqsQueue\Queue;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Str;
use MaxGaurav\LaravelSnsSqsQueue\Queue\Jobs\SnsSqsJob;

class SnsSqsQueue extends SqsQueue
{
    /**
     * @var array
     */
    protected $topics;

    /**
     * @var string
     */
    protected $snsPrefix;

    /**
     * Create new Amazon SNS SQS subscription queue instance
     *
     * @param SqsClient $sqs
     * @param $default
     * @param string $prefix
     * @param array $topics
     */
    public function __construct(SqsClient $sqs, $default, $prefix = '', $topics = [],  $snsPrefix = '')
    {
        parent::__construct($sqs, $default, $prefix);
        $this->topics = $topics;
        $this->snsPrefix = $snsPrefix;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     * @return \Illuminate\Contracts\Queue\Job|void
     */
    public function pop($queue = null)
    {
        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue = $this->getQueue($queue),
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if (!is_null($response['Messages']) && count($response['Messages']) > 0) {
            return new SnsSqsJob(
                $this->container,
                $this->sqs,
                $response['Messages'][0],
                $this->connectionName,
                $queue,
                $this->topics,
                $this->snsPrefix
            );

        }
    }
}
