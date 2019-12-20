<?php

namespace MaxGaurav\LaravelSnsSqsQueue\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;
use MaxGaurav\LaravelSnsSqsQueue\Queue\SnsSqsQueue;

class SnsSqsConnector extends SqsConnector implements ConnectorInterface
{

    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        return new SnsSqsQueue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'sns-config', ['topics' => [], 'prefix' => ''])
        );
    }
}
