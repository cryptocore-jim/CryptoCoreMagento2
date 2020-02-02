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
        return null;
    }
}