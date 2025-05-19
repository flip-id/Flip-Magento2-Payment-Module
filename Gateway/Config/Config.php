<?php

namespace FlipForBusiness\Checkout\Gateway\Config;

use FlipForBusiness\Checkout\Model\Config\Payment\ModuleConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Config\Config as MagentoPaymentGatewayConfig;

/**
 * FLip Payment Configuration Class
 *
 * Handles the configuration settings for the Flip Checkout seamless payment method.
 * Provides methods to retrieve the correct base URL (live or sandbox) and access module-specific configurations.
 *
 * @package FlipForBusiness\Checkout\Gateway\Config
 */
class Config extends MagentoPaymentGatewayConfig
{
    /**
     * Payment method code
     */
    const CODE = 'flipforbusiness_checkout_seamless';

    /**
     * Base URL for test mode
     */
    const TEST_MODE_BASE_URL = 'https://bigflip.id/big_sandbox_api';

    /**
     * Base URL for live mode
     */
    const LIVE_MODE_BASE_URL = 'https://bigflip.id/api';

    /**
     * Module configuration instance
     *
     * @var ModuleConfig
     */
    protected ModuleConfig $_moduleConfig;

    /**
     * Config constructor.
     *
     * @param ModuleConfig $moduleConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ModuleConfig         $moduleConfig,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface   $encryptor,
        ?string              $methodCode = null,
        string               $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern, $encryptor);
        $this->_moduleConfig = $moduleConfig;
    }

    /**
     * Retrieve the base URL based on the mode (live or sandbox)
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->_moduleConfig->isLive() ? self::LIVE_MODE_BASE_URL : self::TEST_MODE_BASE_URL;
    }

    /**
     * Retrieve the module configuration instance
     *
     * @return ModuleConfig
     */
    public function getModuleConfig(): ModuleConfig
    {
        return $this->_moduleConfig;
    }
}
