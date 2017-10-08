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
class SaveBeforeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $quoteRepository;

    protected $fileHelper;

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
        $this->fileHelper = $filehelper;
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
        if ($this->signature->enabledSignature($this->checkoutSession->getQuote())) {
            $paymentData = $observer->getEvent()->getInput()->getAdditionalData();
            $extAttributes = $paymentData['extension_attributes'];
            $signatureImage = $extAttributes->getSignatureImage();

            // $signatureFilePath usually pub/media/pdfs/signatures
            $signatureFilePath = $this->fileHelper->getSignaturePath();
            $this->fileHelper->createDirIfNotExists($signatureFilePath);

            // decode signature and save to file in pub/media/pdfs/signatures
            $decoded_image = $this->fileHelper->decodeImage($signatureImage);
            $this->fileHelper->saveImage($signatureFilePath . 'signature_quote' . $this->checkoutSession->getQuote()->getId() . '.png', $decoded_image);

            // save signature name text to quote. Not necessary to save signature quote filename as it will change
            $signatureText = $extAttributes->getSignatureName();
            $this->checkoutSession->setSignatureText($signatureText);
        }
        return $this;
    }
}
