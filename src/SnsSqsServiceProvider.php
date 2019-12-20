<?php

namespace MaxGaurav\LaravelSnsSqsQueue;

use Illuminate\Support\ServiceProvider;
use MaxGaurav\LaravelSnsSqsQueue\Queue\Connectors\SnsSqsConnector;

class SnsSqsServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['queue']->extend('sns-sqs', function () {
           return new SnsSqsConnector;
        });
    }
}
