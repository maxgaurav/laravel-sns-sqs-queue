<?php

namespace MaxGaurav\LaravelSnsSqsQueue\Queue\Jobs;

use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\Queue;
use Illuminate\Support\Str;
use MaxGaurav\LaravelSnsSqsQueue\DefaultJob;

class SnsSqsJob extends SqsJob
{
    protected $snsConfig;

    /**
     * SnsSqsJob constructor.
     * @param Container $container
     * @param SqsClient $sqs
     * @param array $job
     * @param $connectionName
     * @param $queue
     * @param array $topics
     * @param string $prefix
     */
    public function __construct(
        Container $container,
        SqsClient $sqs,
        array $job,
        $connectionName,
        $queue,
        array $snsConfig
    ) {
        parent::__construct($container, $sqs, $job, $connectionName, $queue);
        $this->snsConfig = $snsConfig;
        $this->job = $this->resolveSnsTopicJob($job, $snsConfig['topics']);
    }

    /**
     * Resolves the Job attached to SNS topic else forwards to default sns queue
     *
     * @param array $job
     * @param array $topics
     */
    protected function resolveSnsTopicJob(array $job, array $topics)
    {
        $body = $this->payload();

        // checking if message type is standard format of SNS topic message
        if (!isset($body['TopicArn']) && !isset($body['Message'])) {
            return $job;
        }

        // checking if there is a job class mapped to the topic
        $jobClass = $this->resolveTopicClass($topics, $body['TopicArn']);

        // if no mapped job class for topic is found then using the default topic
        if (!$jobClass) {
            $jobClass = $this->defaultJob();
        }

        // Serializing the job job instance and mapping it to the body of the message so that we can mimic a standard
        // queue message which are usually sent by laravel
        $jobInstance = new $jobClass(
            $this->plainTopicName($body['TopicArn']),
            is_string($body['Message']) ? json_decode($body['Message'], true) : $body['Message']
        );

        $job['Body'] = json_encode($this->createObjectPayload($jobInstance));

        return $job;
    }

    /**
     * Returns Job class namespace if topic is mapped to a job else returns false
     *
     * @param array $topics
     * @param string $topic
     * @return bool|mixed
     */
    public function resolveTopicClass(array $topics, string $topic)
    {
        $expectedMappedTopicName = $this->plainTopicName($topic);
        if (array_key_exists($expectedMappedTopicName, $topics)) {
            return $topics[$expectedMappedTopicName];
        }

        return false;
    }

    /**
     * Create a payload for an object-based queue handler.
     *
     * @param object $job
     * @param string $queue
     * @return array
     */
    protected function createObjectPayload($job)
    {
        $payload = [
            'displayName' => get_class($job),
            'job' => 'Illuminate\Queue\CallQueuedHandler@call',
            'maxTries' => $job->tries ?? null,
            'timeout' => $job->timeout ?? null,
            'data' => [
                'commandName' => $job,
                'command' => $job,
            ],
        ];

        return array_merge($payload, [
            'data' => [
                'commandName' => get_class($job),
                'command' => serialize(clone $job),
            ],
        ]);
    }

    /**
     * Returns default job to be used when not assigned job is found against a topic
     *
     * @return string
     */
    protected function defaultJob(): string
    {
        if (array_key_exists('default-job', $this->snsConfig) && !empty($this->snsConfig['default-job'])) {
            return $this->snsConfig['default-job'];
        }

        return DefaultJob::class;
    }

    /**
     * Returns base topic name after the arn prefix of sns
     *
     * @param string $topic
     * @return string
     */
    private function plainTopicName(string $topic): string
    {
        return Str::replaceFirst($this->snsConfig['prefix'] . ':', '', $topic);
    }
}
