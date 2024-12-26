<?php

namespace Flip\Checkout\Service;

use Flip\Checkout\Gateway\Http\RequestFactory;
use Flip\Checkout\Gateway\Http\Client;
use Flip\Checkout\Model\Config\Payment\ModuleConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;

/**
 * Class FlipService
 * Provides services for interacting with the Flip API, such as creating a bill.
 *
 * @package Flip\Checkout\Service
 */
class FlipService
{
    /**
     * @var RequestFactory
     */
    private RequestFactory $requestFactory;

    /**
     * @var ModuleConfig
     */
    private ModuleConfig $moduleConfig;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * FlipService constructor.
     *
     * @param RequestFactory $requestFactory Factory for creating HTTP requests.
     * @param Client $client HTTP client for making requests to the Flip API.
     * @param ModuleConfig $moduleConfig Configuration for the module.
     */
    public function __construct(
        RequestFactory $requestFactory,
        Client $client,
        ModuleConfig $moduleConfig
    ) {
        $this->requestFactory = $requestFactory;
        $this->client = $client;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Sends a request to the Flip API to create a bill.
     *
     * This method prepares the request using the RequestFactory and sends it to the Flip API endpoint
     * `v2/pwf/bill`. If successful, the response is returned as an array. In case of an error, an exception
     * is thrown.
     *
     * @param array $request The request data to be sent to the API.
     * @return array|null The response data from the API or null if the request fails.
     * @throws ClientException If the request fails due to client-side issues (e.g., connection problems).
     * @throws ConverterException If there is an issue converting the response.
     */
    public function createBill(array $request): ?array
    {
        // Create transfer object with necessary details for the API request
        $transfer = $this->requestFactory->create(
            method: 'POST',
            apiEndpoint: 'v2/pwf/bill',
            request: $request
        );

        try {
            // Send the request
            $response = $this->client->placeRequest($transfer);

            // Check if the status code is 200
            if ($response['status'] > 200 && $response['status'] !== 'ACTIVE') {
                throw new LocalizedException(
                    __('Unexpected response from Flip API with status: %1. Message: %2', $response['status'], $response['message'] ?? __('No message provided'))
                );
            }

            return $response;
        } catch (ClientException|ConverterException $e) {
            // Catch and rethrow exceptions as a LocalizedException
            throw new LocalizedException(
                __('An error occurred while creating the bill to Flip API: %1', $e->getMessage())
            );
        }
    }
}
