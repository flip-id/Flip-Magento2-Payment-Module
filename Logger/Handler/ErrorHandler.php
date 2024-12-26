<?php

namespace Flip\Checkout\Logger\Handler;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ErrorHandler extends StreamHandler
{
    public function __construct()
    {
        parent::__construct(BP . '/var/log/flip_error.log', Logger::ERROR);
        $this->setLevel(Logger::ERROR); // Ensures only error-level logs are recorded
    }
}
