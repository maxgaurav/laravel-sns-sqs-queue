<?php

namespace MaxGaurav\LaravelSnsSqsQueue\Exceptions;

use Exception;
use Throwable;

class NoJobMapped extends Exception
{
    /**
     * NoJobMapped constructor.
     * @param string $topic
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($topic = "", $message = "", $code = 0, Throwable $previous = null)
    {
        $message = "No job mapped to topic [$topic]";
        parent::__construct($message, $code, $previous);
    }

}
