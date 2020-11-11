<?php
namespace CryptoCore\CryptoPayment\Controller\Checkout;

use CryptoCore\CryptoPayment\Helper\DataHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Statuspayment extends Action implements CsrfAwareActionInterface
{
    protected $_config;
    /**
     * @var DataHelper
     */
    protected $_dataHelper;
    protected $_searchCriteriaBuilder;
    protected $_orderRepository;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \CryptoCore\CryptoPayment\Helper\DataHelper $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        DataHelper $helper
    )
    {
        $this->_dataHelper = $helper;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_orderRepository = $orderRepository;
        parent::__construct($context);
    }

    private function getOrderIdByIncrementId($incrementId)
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)->create();
        $orderData = null;
        try {
            $order = $this->_orderRepository->getList($searchCriteria);
            if ($order->getTotalCount()) {
                $orderData = $order->getItems();
            }
        } catch (\Exception $exception)  {
            header('HTTP/1.0 403 Forbidden');
            return;
        }
        return $orderData;
    }

    public function execute()
    {		
        $post = $this->getRequest()->getContent();
        $jsonData = json_decode($post);
        if (empty($jsonData->result) || empty($jsonData->order_id) || empty($jsonData->signature)) {
            $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_403);
            return;
        }
        $result = $jsonData->result;
        if ($result != "FAIL" && $result != "SUCCESS") {
            $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_403);
            return;
        }
        $order_id = $jsonData->order_id;
        $signature = $jsonData->signature;
        $userssecretkey = $this->_dataHelper->_scopeConfig->getValue('ccoresettings/ccoresetup/userssecretkey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (sha1($result.$order_id.$userssecretkey) == $signature) {
            $orderData = $this->getOrderIdByIncrementId($order_id);
            foreach ($orderData as $order) {
                if ($order->getStatus() != "pending_cryptocore") {
                    $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_403);
                    return;
                }
                if ($result == "FAIL") {
                    $order->registerCancellation("Failed to pay with order")->save();
                } else if ($result == "SUCCESS") {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->setStatus("cryptocore_confirmed");
                    $order->save();
                    try {
                        $this->_dataHelper->_originalOrderSender->send($order);
                    } catch (\Exception $e) {

                    }
                }
            }
            $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_200);
            return;
        }
        else {
            $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_403);
            return;
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}