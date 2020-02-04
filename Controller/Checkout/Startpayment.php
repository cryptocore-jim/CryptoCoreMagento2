<?php
namespace CryptoCore\CryptoPayment\Controller\Checkout;

use CryptoCore\CryptoPayment\Helper\DataHelper;
use Magento\Framework\App\Action\Action;

class Startpayment extends Action
{
    protected $_config;
    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \CryptoCore\CryptoPayment\Helper\DataHelper $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        DataHelper $helper
    )
    {
        $this->_dataHelper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->_dataHelper->_checkoutSession->getLastRealOrder();
        if ($order->getId() == null) {
            exit();
        }
        /* @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $order->getPayment();
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $ccorder = $this->_dataHelper->createNewOrder($order, $payment->getAdditionalInformation('selected_crypto'));
            $response = $this->_dataHelper->_communicator->sendRequest($ccorder,
                $this->_dataHelper->_scopeConfig->getValue('ccoresettings/ccoresetup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if ((int)$response[0] == 200 && $response[1] != '') {
                $ccpayment = json_decode($response[1]);
                if (!empty($ccpayment->payment_id)) {
                    $redirectUrl = $this->_dataHelper->_communicator->getRedirectUrl($ccpayment->payment_id);
                    $resultRedirect->setUrl($redirectUrl);
                } else {
                    throw new \Exception("payment_id is empty");
                }

            } else {
                throw new \Exception("Status response: " . $response[0]);
            }
        } catch (\Exception $e) {
            $order = $this->_dataHelper->_checkoutSession->getLastRealOrder();
            $error = __("Unexpected error");
            $order->registerCancellation($error)->save();
            $this->restoreQuote();
            $this->messageManager->addExceptionMessage(new \Exception("ex"), $error);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
        }
        return $resultRedirect;
    }

    private function restoreQuote()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_dataHelper->_checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            try {
                $quote = $this->_dataHelper->quoteRepository->get($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->_dataHelper->quoteRepository->save($quote);
                $this->_dataHelper->_checkoutSession->replaceQuote($quote)->unsLastRealOrderId();
                return true;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }
        return false;
    }
}