<?php
namespace CryptoCore\CryptoPayment\Helper;

class DataHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $quoteRepository;

    protected $_storeManager;
    protected $_iteratorFactory;
    protected $_blockMenu;
    protected $_url;
    /* @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
    public $_scopeConfig;
    public $_checkoutSession;
    protected $_countryHelper;
    protected $_resolver;
    public $_originalOrderSender;
    public $_objectManager;
    public $_configLoader;
    public $_customerMetadata;
    public $_communicator;

    function saveLog()
    {
        //TODO: log
    }

      public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Backend\Block\Menu $blockMenu,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Config\Source\Country $countryHelper,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $originalOrderSender,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \CryptoCore\CryptoPayment\Helper\Api\CryptoCoreCommunicator $communicator
    )
    {

        parent::__construct($context);
        $this->_customerMetadata = $customerMetadata;
        $this->_configLoader = $configLoader;
        $this->_objectManager = $objectManager;
        $this->_resolver = $resolver;
        $this->_countryHelper = $countryHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_iteratorFactory = $iteratorFactory;
        $this->_blockMenu = $blockMenu;
        $this->_url = $url;
        $this->quoteRepository = $quoteRepository;
        $this->_communicator = $communicator;
        $this->_originalOrderSender = $originalOrderSender;
    }

    function createNewOrder(\Magento\Sales\Model\Order $order, $selectedCrypto)
    {
        $cccommunicator = $this->_communicator;
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $ccorder = new \CryptoCore\CryptoPayment\Helper\Api\CryptoCoreNewOrder();
        $ccorder->amount = (float)$order->getGrandTotal();
        $ccorder->currency_code = $order->getOrderCurrencyCode();
        if (!empty($selectedCrypto)) {
            $ccorder->payment_currency_code = $selectedCrypto;
        }
        $ccorder->order_id = $order->getIncrementId();
        $ccorder->result_url = $baseUrl."cryptocore/checkout/statuspayment";
        $ccorder->user_return_url = $baseUrl."cryptocore/checkout/finishpayment";
        $ccorder->user_id = intval($this->_scopeConfig->getValue('ccoresettings/ccoresetup/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $signature = $cccommunicator->newOrderSignature($ccorder, $this->_scopeConfig->getValue('ccoresettings/ccoresetup/userssecretkey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $ccorder->usersignature = $signature;
        return json_encode($ccorder);
    }
}