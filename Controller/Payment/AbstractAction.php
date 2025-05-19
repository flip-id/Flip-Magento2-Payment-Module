<?php

namespace FlipForBusiness\Checkout\Controller\Payment;

use FlipForBusiness\Checkout\Model\Order\CreditMemoRepository;
use FlipForBusiness\Checkout\Logger\FlipLogger;
use FlipForBusiness\Checkout\Model\Config\Payment\ModuleConfig;
use FlipForBusiness\Checkout\Model\Order\InvoiceRepository;
use FlipForBusiness\Checkout\Model\Order\OrderRepository;
use FlipForBusiness\Checkout\Model\Payment\RequestFactory;
use FlipForBusiness\Checkout\Service\FlipService;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface as ActionApp;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * AbstractAction class for handling payment-related actions
 * 
 * This abstract class serves as the base for payment-related controllers in the Flip Checkout module.
 * It provides common dependencies, initialization, and utility methods to simplify specific actions.
 *
 * @package FlipForBusiness\Checkout\Controller\Payment
 * @api
 */
abstract class AbstractAction implements ActionApp
{
    /**
     * Logger instance for logging actions, requests, and errors
     *
     * @var FlipLogger
     */
    public FlipLogger $logger;

    /**
     * Service class for handling payment creation and Flip API communication
     *
     * @var FlipService
     */
    public FlipService $flipService;

    /**
     * Repository class for managing order data
     *
     * @var OrderRepository
     */
    public OrderRepository $orderRepository;

    /**
     * Repository for managing Magento invoices
     *
     * @var InvoiceRepository
     */
    public InvoiceRepository $invoiceRepository;

    /**
     * Repository for managing credit memo data
     *
     * @var CreditMemoRepository
     */
    public CreditMemoRepository $creditMemoRepository;

    /**
     * Checkout session for managing cart and order session data
     *
     * @var Session
     */
    protected Session $_checkoutSession;

    /**
     * Action context for accessing controller parameters and settings
     *
     * @var Context
     */
    private Context $_context;

    /**
     * Factory for creating JSON response results
     *
     * @var JsonFactory
     */
    public JsonFactory $_resultJsonFactory;

    /**
     * Serializer for encoding and decoding JSON data
     *
     * @var Json
     */
    public Json $jsonSerializer;

    /**
     * Factory for generating various result types
     *
     * @var ResultFactory
     */
    protected ResultFactory $_resultFactory;

    /**
     * Configuration class to retrieve module-specific settings
     *
     * @var ModuleConfig
     */
    public ModuleConfig $flipModuleConfig;

    /**
     * Factory for creating payment request payloads
     *
     * @var RequestFactory
     */
    protected RequestFactory $requestFactory;

    /**
     * Interface for managing HTTP requests
     *
     * @var RequestInterface
     */
    public RequestInterface $requestInterface;

    /**
     * Factory for generating redirect results
     *
     * @var RedirectFactory
     */
    public RedirectFactory $redirectFactory;

    /**
     * Factory for creating page results
     *
     * @var PageFactory
     */
    public PageFactory $pageFactory;

    /**
     * HTTP request object for handling incoming requests
     *
     * @var Http
     */
    protected Http $request;

    /**
     * Constructor for AbstractAction class
     *
     * @param Context $context The context for the action, containing request and response objects
     * @param Session $checkoutSession Session object for managing the checkout process
     * @param JsonFactory $resultJsonFactory Factory for generating JSON responses
     * @param FlipService $flipService Service for interacting with Flip API
     * @param FlipLogger $logger Logger instance for recording events and errors
     * @param ModuleConfig $flipModuleConfig Config class for Flip Checkout module settings
     * @param OrderRepository $orderRepository Repository for managing orders
     * @param InvoiceRepository $invoiceRepository Repository for managing invoices
     * @param CreditMemoRepository $creditMemoRepository Repository for managing credit memos
     * @param RequestFactory $requestFactory Factory for generating payment requests
     * @param PageFactory $pageFactory Factory for creating view pages
     * @param Http $request HTTP request object for retrieving request data
     * @param Json $jsonSerializer JSON serializer for encoding and decoding data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        JsonFactory $resultJsonFactory,
        FlipService $flipService,
        FlipLogger $logger,
        ModuleConfig $flipModuleConfig,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        CreditMemoRepository $creditMemoRepository,
        RequestFactory $requestFactory,
        PageFactory $pageFactory,
        Http $request,
        Json $jsonSerializer
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_context = $context;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultFactory = $context->getResultFactory();
        $this->flipService = $flipService;
        $this->logger = $logger;
        $this->flipModuleConfig = $flipModuleConfig;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditMemoRepository = $creditMemoRepository;
        $this->requestFactory = $requestFactory;
        $this->pageFactory = $pageFactory;
        $this->requestInterface = $context->getRequest();
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->request = $request;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get the HTTP request object
     *
     * @return Http The HTTP request object
     */
    public function getRequest(): Http
    {
        return $this->request;
    }
}
