<?php

namespace FlipForBusiness\Checkout\Logger\Handler;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class ErrorHandler
 *
 * Handler for error-level log messages related to the Flip payment module.
 * Writes error logs to a dedicated file.
 */
class ErrorHandler extends StreamHandler
{
    /**
     * Constructor for the ErrorHandler.
     *
     * Initializes a StreamHandler to write error logs to flip_error.log
     * and sets the minimum log level to ERROR.
     */
    public function __construct()
    {
        parent::__construct(BP . '/var/log/flip_error.log', Logger::ERROR);
        $this->setLevel(Logger::ERROR); // Ensures only error-level logs are recorded
    }
}
