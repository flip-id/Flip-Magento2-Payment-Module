<?php

namespace Flip\Checkout\Model\Config\Payment;

use Flip\Checkout\Logger\FlipLogger;
use Flip\Checkout\Logger\Handler\ApiRequestHandler;
use Flip\Checkout\Logger\Handler\CallbackRequestHandler;
use Flip\Checkout\Logger\Handler\DebugHandler;
use Flip\Checkout\Logger\Handler\ErrorHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ModuleConfig
 * Provides methods for retrieving configuration values related to the Flip payment module.
 * Handles settings such as live mode, business ID, API secret keys, and validation keys.
 *
 * @package Flip\Checkout\Model\Config\Payment
 */
class ModuleConfig
{
    /**
     * Base configuration path for Flip payment settings.
     */
    protected const BASE_CONFIG_PATH = 'payment/flip/settings/';

    /**
     * @var ScopeConfigInterface ScopeConfigInterface instance for accessing Magento's configuration.
     */
    protected ScopeConfigInterface $_scopeConfig;

    /**
     * @var StoreManagerInterface StoreManagerInterface instance for managing stores.
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var EncryptorInterface EncryptorInterface instance for decrypting sensitive keys.
     */
    protected EncryptorInterface $encryptor;

    public FlipLogger $logger;


    /**
     * ModuleConfig constructor.
     *
     * Initializes the configuration manager with the given dependencies for scope configuration,
     * store management, and encryption.
     *
     * @param ScopeConfigInterface $scopeConfig Instance of ScopeConfigInterface for accessing configuration values.
     * @param StoreManagerInterface $storeManager Instance of StoreManagerInterface for store management.
     * @param EncryptorInterface $encryptor Instance of EncryptorInterface for handling sensitive data.
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Retrieves a specific configuration value from the Magento configuration scope.
     *
     * @param string $pathXML The XML path of the configuration value to retrieve.
     * @return mixed The value of the configuration at the given path.
     */
    protected function getDataConfig(string $pathXML): mixed
    {
        return $this->_scopeConfig->getValue(self::BASE_CONFIG_PATH . $pathXML, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Checks if the payment module is in live mode.
     *
     * @return bool True if the payment module is in live mode, false otherwise.
     */
    public function isLive(): bool
    {
        $isLiveMode = $this->getDataConfig('is_live_mode');
        return (int)$isLiveMode === 1;
    }

    /**
     * Checks if the payment method should redirect the user.
     *
     * @return bool True if the payment method should perform a redirect, false otherwise.
     */
    public function isRedirect(): bool
    {
        $isRedirect = $this->getDataConfig('is_redirect');
        return (int)$isRedirect === 1;

    }

    /**
     * Retrieves the business ID configured for the payment method.
     *
     * @return string The configured Flip business ID.
     */
    public function getBusinessId(): string
    {
        return $this->getDataConfig('flip_business_id');
    }

    /**
     * Retrieves the API secret key for the payment method, either from the live or test mode.
     *
     * If the module is in live mode, it retrieves the live API secret key; otherwise, it retrieves the test API secret key.
     *
     * @return string The decrypted API secret key for the configured mode (live or test).
     */
    public function getApiSecretKey(): string
    {
        if ($this->isLive()) {
            return $this->getDataConfig('live_api_secret_key');
        } else
            return $this->getDataConfig('test_api_secret_key');
    }


    /**
     * Retrieves the validation key for the payment method, either from the live or test mode.
     *
     * If the module is in live mode, it retrieves the live validation key; otherwise, it retrieves the test validation key.
     *
     * @return string The decrypted validation key for the configured mode (live or test).
     */
    public function getValidationKey(): string
    {
        if ($this->isLive()) {
            return $this->getDataConfig('live_validation_key');
        } else {
            return $this->getDataConfig('test_validation_key');
        }
    }

    /**
     * Check if callback logging is enabled.
     *
     * This method retrieves the `callback_log` configuration setting
     * to determine if logging for callbacks is enabled.
     *
     * @return bool True if callback logging is enabled; otherwise, false.
     */
    public function isCallbackLogEnable(): bool
    {
        $isCallbackLogEnable = $this->getDataConfig('log/callback');
        return (int)$isCallbackLogEnable === 1;
    }

    /**
     * Check if request logging is enabled.
     *
     * This method retrieves the `request_log` configuration setting
     * to determine if logging for API requests is enabled.
     *
     * @return bool True if request logging is enabled; otherwise, false.
     */
    public function isRequestLogEnable(): bool
    {
        $isRequestLogEnable = $this->getDataConfig('log/request');
        return (int)$isRequestLogEnable === 1;
    }

    /**
     * Check if debug logging is enabled.
     *
     * This method retrieves the `debug_log` configuration setting
     * to determine if debug-level logging is enabled.
     *
     * @return bool True if debug logging is enabled; otherwise, false.
     */
    public function isDebugLogEnable(): bool
    {
        $isDebugLogEnable = $this->getDataConfig('log/debug');
        return (int)$isDebugLogEnable === 1;
    }

    /**
     * Check if error logging is enabled.
     *
     * This method retrieves the `exception` configuration setting
     * to determine if logging for exceptions and errors is enabled.
     *
     * @return bool True if error logging is enabled; otherwise, false.
     */
    public function isErrorLogEnable(): bool
    {
        $isErrorLogEnable = $this->getDataConfig('log/flip_exception');
        return (int)$isErrorLogEnable === 1;
    }

    /**
     * @return string
     */
    public function getCallbackUrl(): string
    {
        try {
            return $this->_storeManager->getStore()->getBaseUrl() . 'flip/payment/callback';
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

}
