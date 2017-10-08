<?php
/**
 * Created by PhpStorm.
 * User: eugen
 * Date: 27.11.2015
 * Time: 17:53
 */

namespace SalesIgniter\RentalContract\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveDeliveryDateToOrderObserver
 *
 * @package Oye\Deliverydate\Model\Observer
 */
class SaveSignatureObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $quoteRepository;

    protected $filehelper;

    private $orderRepository;

    protected $datetime;

    protected $signature;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param \Magento\Quote\Model\QuoteRepository         $quoteRepository
     * @param \Magento\Sales\Model\OrderRepository         $orderRepository
     * @param \Magento\Checkout\Model\Session              $checkoutSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime  $datetime
     * @param \SalesIgniter\RentalContract\Helper\Files    $filehelper
     * @param \SalesIgniter\RentalContract\Model\Signature $signature
     *
     * @internal param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \SalesIgniter\RentalContract\Helper\Files $filehelper,
        \SalesIgniter\RentalContract\Model\Signature $signature
    ) {
        $this->signature = $signature;
        $this->datetime = $datetime;
        $this->quoteRepository = $quoteRepository;
        $this->filehelper = $filehelper;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Copy signature text to order from quote and rename quote signature to order signature
     * and save filename to order
     *
     * @param EventObserver $observer
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function execute(EventObserver $observer)
    {
        $orderid = $observer->getOrderIds()[0];
        $order = $this->orderRepository->get($orderid);
        if ($this->signature->enabledSignature($order, 'order')) {
            // rename signature file to have order id

            $order->setSignatureText($this->checkoutSession->getSignatureText());

            // rename signature file to have order id instead of quote id
            $orderSignaturePath = $this->filehelper->getOrderSignaturePath($order);
            rename($this->filehelper->getQuoteSignaturePath($order->getQuoteId()), $orderSignaturePath);

            $order->setSignatureImagefile($this->filehelper->getOrderFilename($order));
            $order->setSignatureDate($this->datetime->date());
            $order->save();
        }
        return $this;
    }
}
