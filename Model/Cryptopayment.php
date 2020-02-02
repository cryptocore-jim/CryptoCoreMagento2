<?php


namespace CryptoCore\CryptoPayment\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;


/**
 * Pay In Store payment method model
 */
class Cryptopayment extends \CryptoCore\CryptoPayment\Model\Cryptocorepayment
{

    protected $_executed;
    protected $_dataHelper;
	public function setId($id)
    {
		//Magento bug https://github.com/magento/magento2/issues/5413
    }
    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param CommandPoolInterface $commandPool
     * @param ValidatorPoolInterface $validatorPool
     * @param CommandManagerInterface $commandExecutor
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
        $this->eventManager = $eventManager;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $state =  $objectManager->get('Magento\Framework\App\State');
        if ($state->getAreaCode() == "adminhtml") {
            $this->_checkoutSession = $objectManager->get('Magento\Backend\Model\Session\Quote');
        } else {
            $this->_checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        }
        $this->_state = $state;
        $this->_eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
      //  $this->_dataHelper =  $objectManager->get('\CryptoCore\CryptoPayment\Helper\DataHelper');
        $this->_executed = false;
    }

    public function getConfigData($field, $storeId = null)
    {
        if ($field == 'order_place_redirect_url') {
            return 'cryptocore/checkout/startpayment';
        }
        return parent::getConfigData($field, $storeId);
    }

    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }

    public function getTitle()
    {
        return "XXXX";//$this->_scopeConfig->getValue("cryptocoresettings/cryptocore_setup/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        return $this;
    }

    public function validate()
    {
        return $this;
    }

    public function order(InfoInterface $payment, $amount)
    {
        return $this;
    }


    public function authorize(InfoInterface $payment, $amount)
    {
		return $this;
    }

}