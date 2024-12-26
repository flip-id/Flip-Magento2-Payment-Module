<?php

namespace Flip\Checkout\Controller\Payment;

use Flip\Checkout\Model\Order\CreditMemoRepository;
use Flip\Checkout\Logger\FlipLogger;
use Flip\Checkout\Model\Config\Payment\ModuleConfig;
use Flip\Checkout\Model\Order\InvoiceRepository;
use Flip\Checkout\Model\Order\OrderRepository;
use Flip\Checkout\Model\Payment\RequestFactory;
use Flip\Checkout\Service\FlipService;
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
 * AbstractAction class for handling payment-related actions that implement ActionApp Magento in the Flip Checkout module.
 *
 * This abstract class serves as the base for payment-related controllers in the Flip Checkout module.
 * It provides common dependencies, initialization, and utility methods to simplify specific actions.
 *
 * @package Flip\Checkout\Controller\Payment
 */
abstract class AbstractAction implements ActionApp
{
    /**
     * @var FlipLogger Logger instance for logging actions, requests, and errors.
     */
    public FlipLogger $logger;

    /**
     * @var FlipService Service class for handling payment creation and Flip API communication.
     */
    public FlipService $flipService;

    /**
     * @var OrderRepository Repository class for managing order data.
     */
    public OrderRepository $orderRepository;

    /**
     * @var InvoiceRepository Repository for managing Magento invoices.
     */
    public InvoiceRepository $invoiceRepository;

    /**
     * @var CreditMemoRepository Repository for managing credit memo data.
     */
    public CreditMemoRepository $creditMemoRepository;

    /**
     * @var Session Checkout session for managing cart and order session data.
     */
    protected Session $_checkoutSession;

    /**
     * @var Context Action context for accessing controller parameters and settings.
     */
    private Context $_context;

    /**
     * @var JsonFactory Factory for creating JSON response results.
     */
    public JsonFactory $_resultJsonFactory;

    /**
     * @var Json Serializer for encoding and decoding JSON data.
     */
    public Json $jsonSerializer;

    /**
     * @var ResultFactory Factory for generating various result types.
     */
    protected ResultFactory $_resultFactory;

    /**
     * @var ModuleConfig Configuration class to retrieve module-specific settings.
     */
    public ModuleConfig $flipModuleConfig;

    /**
     * @var RequestFactory Factory for creating payment request payloads.
     */
    protected RequestFactory $requestFactory;

    /**
     * @var RequestInterface Interface for managing HTTP requests.
     */
    public RequestInterface $requestInterface;

    /**
     * @var RedirectFactory Factory for generating redirect results.
     */
    public RedirectFactory $redirectFactory;

    /**
     * @var PageFactory Factory for creating page results.
     */
    public PageFactory $pageFactory;

    /**
     * @var Http HTTP request object for handling incoming requests.
     */
    protected Http $request;

    /**
     * Constructor for AbstractAction class.
     *
     * @param Context $context The context for the action, containing request and response objects.
     * @param Session $checkoutSession Session object for managing the checkout process.
     * @param JsonFactory $resultJsonFactory Factory for generating JSON responses.
     * @param FlipService $flipService Service for interacting with Flip API.
     * @param FlipLogger $logger Logger instance for recording events and errors.
     * @param ModuleConfig $flipModuleConfig Config class for Flip Checkout module settings.
     * @param OrderRepository $orderRepository Repository for managing orders.
     * @param InvoiceRepository $invoiceRepository Repository for managing invoices.
     * @param CreditMemoRepository $creditMemoRepository Repository for managing credit memos.
     * @param RequestFactory $requestFactory Factory for generating payment requests.
     * @param PageFactory $pageFactory Factory for creating view pages.
     * @param Http $request HTTP request object for retrieving request data.
     * @param Json $jsonSerializer JSON serializer for encoding and decoding data.
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
     * Get the HTTP request object.
     *
     * @return Http The HTTP request object.
     */
    public function getRequest(): Http
    {
        return $this->request;
    }
}
