<?php

namespace FlipForBusiness\Checkout\Logger\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

/**
 * Class CallbackRequestHandler
 * Custom log handler for Flip payment module that logs callback request messages to the `flip_callback_requests.log` file.
 *
 * This class extends Magento's `BaseHandler` and sets up a custom file (`flip_callback_requests.log`) for logging
 * callback request messages at the INFO log level. This helps track incoming callback requests from external services.
 *
 * @package FlipForBusiness\Checkout\Logger\Handler
 */
class CallbackRequestHandler extends StreamHandler
{
    public function __construct()
    {
        parent::__construct(BP . '/var/log/flip_callback.log', \Monolog\Logger::INFO);
        $this->setFormatter(new LineFormatter(null, null, true, true));
    }
}
