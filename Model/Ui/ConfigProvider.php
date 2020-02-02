<?php
namespace CryptoCore\CryptoPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
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

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        PaymentHelper $paymentHelper,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->methodInstanceCryptoCore = $paymentHelper->getMethodInstance(self::CODE_PAYMENT);
        $this->_scopeConfig = $scopeConfig;
        $this->_resolver = $resolver;
    }

    private function getCryptoCoreLogo()
    {
        $logo = 'https://monitor.ccore.online/logo/logo32.png';
        return $logo;
    }

    public function getConfig()
    {
        $cryptoAvailable = Array(
            Array('value' => 'BTC_TST', 'text' => 'Bitcoin (Testnet)', 'amount' => 0.01),
            Array('value' => 'OTO', 'text' => 'Otocash', 'amount' => 1.01)
        );

        return [
            'payment' => [
                self::CODE_PAYMENT => [
                    'redirectUrl' => $this->methodInstanceCryptoCore->getConfigData('order_place_redirect_url'),
                    'cryptocurrencies' => $cryptoAvailable,
                    'default_crypto' => "BTC_TST",
                    'logo' => $this->getCryptoCoreLogo()
                ]
            ]
        ];
    }
}
