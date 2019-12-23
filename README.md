# Process SNS Messages as SQS Jobs

This package allows you to process sns messages posted on various topics subscribed by an SQS to be processed as 
a job.

This package is a great use cases for applications beings deployed to microservices.

## Requirements

* PHP >= 7.2
* Laravel 6
* SQS driver for laravel
* SQS in AWS
* SNS in AWS

## Installation

Install using composer
```sh
composer require maxgaurav/laravel-sns-sqs-queue
```

The package will automatically register its service provider.

## Configuration
In **queue.php** add the following driver setup

```php
return [
    //...

    'sqs' => [
        'driver' => 'sqs',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
        'queue' => env('SQS_QUEUE', 'your-queue-name'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
     ],

    'sns-sqs' => [
        'driver' => 'sns-sqs',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
        'queue' => env('SQS_QUEUE', 'your-queue-name'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'sns-config' => [
            'topics' => [
                'topicName' => \App\Jobs\YourJob::class,
                'topic2Name' => \App\Jobs\YourOtherJob::class
                //...
            ],
            'default-job' => '', // if you want to override the default job executed for non matching topics
            'prefix' => env('SNS_PREFIX', ''), // SNS Topic Prefix
        ],
    ],

```

## Prerequisites

* First setup your SQS queue in AWS
* The SQS must be subscribed to all the topics of SNS you want to process through your job.

## Usage
Each job created must have two constructor inputs. First input is for the topic name the job is executing for and second is the array of json decoded data sent in the message.


```php

class SampleJob {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public $topic;

    /**
     * @var array
     */
    public $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($topic, $body)
    {
        $this->topic = $topic;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // do your handling
    }

}

```

## Default Job
The package uses a default job instance **\MaxGaurav\LaravelSnsSqsQueue\DefaultJob** for topics not mapped in the
configuration. The job has a default functionality to fail for such topics. This is done to allow the application
to tell that topics are not mapped.

The DefaultJob class can be replaced with a custom default job handler using key **default-job**. Pass your class instance and it would be used instead. 

