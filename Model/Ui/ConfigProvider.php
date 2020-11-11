<?php
namespace CryptoCore\CryptoPayment\Model\Ui;

use CryptoCore\CryptoPayment\Helper\DataHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    protected $_resolver;
    const CODE_PAYMENT = 'crypto_payment';
    /* @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $methodInstanceCryptoCore;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $methodInstanceInstallment;
    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        PaymentHelper $paymentHelper,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Checkout\Model\Session $checkoutSession,
        DataHelper $helper
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->methodInstanceCryptoCore = $paymentHelper->getMethodInstance(self::CODE_PAYMENT);
        $this->_scopeConfig = $scopeConfig;
        $this->_resolver = $resolver;
        $this->_dataHelper = $helper;
    }

    public function getConfig()
    {
        $select_currency = $this->_scopeConfig->getValue("ccoresettings/ccoresetup/select_currency", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $logo = $this->_scopeConfig->getValue("ccoresettings/ccoresetup/logo", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $allowed = $this->_scopeConfig->getValue("ccoresettings/ccoresetup/allowed", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $curr = explode(",", $allowed);
        $cryptoAvailable = array();
        $default_crypto = '';
        $quote = $this->_checkoutSession->getQuote();
        $total = $quote->getGrandTotal();
        $currency = $quote->getQuoteCurrencyCode();
        $userId = $this->_scopeConfig->getValue('ccoresettings/ccoresetup/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $secretKey = $this->_scopeConfig->getValue('ccoresettings/ccoresetup/userssecretkey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $signature = $this->_dataHelper->_communicator->newExchangeSignature($userId, $secretKey);
        $url = "https://gateway.ccore.online/exchange/userrates?from=".$currency."&to=".$allowed."&userid=".$userId."&signature=".$signature;
        $jsonString = file_get_contents($url);

        $json = json_decode($jsonString, true);
        if ($json == null || !is_array($json)) {
            return [];
        }
        foreach($curr as $c) {
            if ($default_crypto == '') {
                $default_crypto = $c;
            }
            foreach($json as $js) {
                if ($js["to_currency"] == $c && $currency == $js["from_currency"]) {
                    $cryptoAvailable[] =  Array('value' => $c, 'text' => $js["name"], 'rate' => $js["rate"], 'logo' => $js["logo"], "decimals_amount" => $js["decimals_amount"], "volatility" => $js["volatility"]);
                    break;
                }
            }
        }
        return [
            'payment' => [
                self::CODE_PAYMENT => [
                    'redirectUrl' => $this->methodInstanceCryptoCore->getConfigData('order_place_redirect_url'),
                    'cryptocurrencies' => $cryptoAvailable,
                    'default_crypto' => $default_crypto,
                    'logo' => $logo,
                    'select_currency' => $select_currency
                ]
            ]
        ];
    }
}
