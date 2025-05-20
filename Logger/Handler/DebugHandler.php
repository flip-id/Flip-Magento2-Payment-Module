<?php

namespace FlipForBusiness\Checkout\Logger\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

/**
 * Class DebugHandler
 * Custom log handler for Flip payment module that writes debug-level log messages to the `flip_debug.log` file.
 *
 * This class extends Magento's `BaseHandler` and sets up a custom file (`flip_debug.log`) for logging debug messages.
 * It uses Monolog's `Logger::DEBUG` log level to capture and store debug-level logs.
 */
class DebugHandler extends StreamHandler
{
    /**
     * DebugHandler constructor.
     *
     * Initializes a StreamHandler to write debug logs to flip_debug.log
     * and sets the formatter to LineFormatter for proper log formatting.
     */
    public function __construct()
    {
        parent::__construct(BP . '/var/log/flip_debug.log', \Monolog\Logger::DEBUG);
        $this->setFormatter(new LineFormatter(null, null, true, true));
    }
}
