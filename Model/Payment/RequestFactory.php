<?php

namespace FlipForBusiness\Checkout\Model\Payment;

use FlipForBusiness\Checkout\Model\Config\Payment\ModuleConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RequestFactory
 *
 * Factory class for creating the payload to send to the Flip API for payment creation.
 * This class is responsible for formatting order data according to Flip API requirements.
 *
 * @api
 */
class RequestFactory
{
    /**
     * Store manager instance
     *
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * RequestFactory constructor
     *
     * @param StoreManagerInterface $storeManager Store manager instance
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Creates the payload array for sending to the Flip API.
     *
     * This method prepares an array with the necessary details of the order, such as the order ID, amount,
     * customer information, and payment redirect URL. The data is formatted as required by the Flip API for
     * creating a payment link.
     *
     * @param Order $order The order object containing details about the purchase
     * @return array<string, string> The prepared payload to be sent to the Flip API
     * @throws NoSuchEntityException When store information cannot be retrieved
     */
    public function createPayload(Order $order): array
    {
        $finishUrl = $this->storeManager->getStore()->getUrl() . 'flipforbusiness/payment/finish?state=' . $order->getRealOrderId();
        return [
            'title' => $order->getRealOrderId(),
            'type' => "SINGLE",
            'amount' => (string)round($order->getGrandTotal()), // The amount for the payment
            'step' => 2, // Payment step, could be a value required by the Flip API
            'redirect_url' => $this->bypassFlipUrlValidation($finishUrl),
            'sender_name' => $order->getCustomerName(), // Customer's full name
            'sender_email' => $order->getCustomerEmail(), // Customer's email address
        ];
    }

    /**
     * Bypasses Flip URL validation for non-production environments
     *
     * For local/development URLs, route through GitHub redirection
     * For production URLs, return the URL directly
     *
     * @param string $url The URL to process
     * @return string The processed URL
     */
    private function bypassFlipUrlValidation(string $url): string
    {
        // List of local/development domain patterns
        $localDomains = [
            'localhost',
            '127.0.0.1',
            ':8888',  // Common local port
            '.local',
            '.test',
            '.dev',
            'dev.',
            'staging.'
        ];
        
        // Check if the URL is a local/development URL
        $isLocalUrl = false;
        foreach ($localDomains as $domain) {
            if (strpos($url, $domain) !== false) {
                $isLocalUrl = true;
                break;
            }
        }
        
        // Use GitHub redirection for local URLs, return direct URL for production
        if ($isLocalUrl) {
            return 'https://flip-id.github.io/checkout-redirection/?url=' . $url;
        }
        
        return $url;
    }
}
