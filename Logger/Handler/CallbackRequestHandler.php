<?php

namespace FlipForBusiness\Checkout\Logger\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

/**
 * Class CallbackRequestHandler
 * Custom log handler for Flip payment module that logs callback request messages.
 *
 * This class extends Magento's `BaseHandler` and sets up a custom file for logging
 * callback request messages at the INFO log level. This helps track incoming requests.
 */
class CallbackRequestHandler extends StreamHandler
{
    /**
     * CallbackRequestHandler constructor.
     *
     * Initializes a StreamHandler to write callback logs to flip_callback.log
     * and sets the formatter to LineFormatter for proper log formatting.
     */
    public function __construct()
    {
        parent::__construct(BP . '/var/log/flip_callback.log', \Monolog\Logger::INFO);
        $this->setFormatter(new LineFormatter(null, null, true, true));
    }
}
