<?php

namespace FlipForBusiness\Checkout\Service;

use FlipForBusiness\Checkout\Gateway\Http\RequestFactory;
use FlipForBusiness\Checkout\Gateway\Http\Client;
use FlipForBusiness\Checkout\Model\Config\Payment\ModuleConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class FlipService
 * Provides services for interacting with the Flip API, such as creating a bill.
 *
 * @package FlipForBusiness\Checkout\Service
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
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * FlipService constructor.
     *
     * @param RequestFactory $requestFactory Factory for creating HTTP requests.
     * @param Client $client HTTP client for making requests to the Flip API.
     * @param ModuleConfig $moduleConfig Configuration for the module.
     * @param Json $jsonSerializer JSON serializer for processing API responses.
     */
    public function __construct(
        RequestFactory $requestFactory,
        Client $client,
        ModuleConfig $moduleConfig,
        Json $jsonSerializer
    ) {
        $this->requestFactory = $requestFactory;
        $this->client = $client;
        $this->moduleConfig = $moduleConfig;
        $this->jsonSerializer = $jsonSerializer;
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

    /**
     * Gets the payment status for a bill from the Flip API.
     *
     * This method retrieves the payment status for a specific bill by its link ID.
     * It makes a GET request to the `v2/pwf/{linkId}/payment` endpoint and returns the response data.
     *
     * @param string $linkId The link ID of the bill to check status for.
     * @return array The payment status data from the API.
     * @throws LocalizedException If there is an error retrieving the payment status.
     */
    public function getBillStatus(string $linkId): array
    {
        try {
            // Create transfer object for the GET request
            $transfer = $this->requestFactory->create(
                method: 'GET',
                apiEndpoint: 'v2/pwf/' . $linkId . '/payment',
                request: []
            );

            // Send the request
            $response = $this->client->placeRequest($transfer);

            // Validate the response
            if (!isset($response['data']) || empty($response['data'])) {
                throw new LocalizedException(
                    __('Invalid or empty response from Flip API for bill status request.' . $response)
                );
            }

            return $response;
        } catch (ClientException|ConverterException $e) {
            // Catch and rethrow exceptions as a LocalizedException
            throw new LocalizedException(
                __('An error occurred while retrieving bill status from Flip API: %1', $e->getMessage())
            );
        }
    }

    /**
     * Verifies a transaction status from callback data against the Flip API.
     * 
     * This method takes callback data from Flip and verifies its accuracy by
     * making a direct API call to Flip's servers to confirm the transaction status.
     *
     * @param array $callbackData Data received from the Flip callback webhook.
     * @return bool True if status is verified, false otherwise.
     * @throws LocalizedException If there is an error during verification.
     */
    public function verifyTransactionStatus(array $callbackData): bool
    {
        if (empty($callbackData['bill_link_id'])) {
            throw new LocalizedException(__('Missing bill_link_id in callback data.'));
        }

        try {
            // Get the latest status from Flip API
            $apiResponse = $this->getBillStatus($callbackData['bill_link_id']);
            
            // If we have data in the response
            if (!empty($apiResponse['data'][0])) {
                $paymentData = $apiResponse['data'][0];
                
                // Verify transaction ID and status match
                $isIdMatch = $paymentData['id'] === $callbackData['id'];
                $isStatusMatch = $paymentData['status'] === $callbackData['status'];
                $isAmountMatch = (int)$paymentData['amount'] === (int)$callbackData['amount'];
                
                return $isIdMatch && $isStatusMatch && $isAmountMatch;
            }
            
            return false;
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Failed to verify transaction status: %1', $e->getMessage())
            );
        }
    }
}
