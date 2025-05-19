<?php

namespace FlipForBusiness\Checkout\Logger\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

/**
 * Class ApiRequestHandler
 * Custom log handler for Flip payment module that logs API request messages to the `flip_api_requests.log` file.
 *
 * This class extends Magento's `BaseHandler` and sets up a custom file (`flip_api_requests.log`) for logging
 * API request messages at the INFO log level. This helps track outgoing API requests to external services.
 *
 * @package FlipForBusiness\Checkout\Logger\Handler
 */
class ApiRequestHandler extends StreamHandler
{
    public function __construct()
    {
        parent::__construct(BP . '/var/log/flip_api_requests.log', \Monolog\Logger::INFO);
        $this->setFormatter(new LineFormatter(null, null, true, true));
    }
}
