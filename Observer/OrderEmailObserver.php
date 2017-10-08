<?php

namespace SalesIgniter\RentalContract\Observer;

use Magento\Framework\Event\ObserverInterface;
use Fooman\EmailAttachments\Observer\AbstractObserver as FoomanAbstractObserver;

class OrderEmailObserver extends FoomanAbstractObserver
{

    protected $attachmentFactory;

    protected $scopeConfig;

    protected $rentalContractPdf;

    protected $signature;

    protected $filehelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Fooman\EmailAttachments\Model\AttachmentFactory $attachmentFactory,
        \SalesIgniter\RentalContract\Model\Signature $signature,
        \SalesIgniter\RentalContract\Model\RentalContractPdf $rentalContractPdf,
        \SalesIgniter\RentalContract\Helper\Files $filehelper

    ) {
        $this->rentalContractPdf = $rentalContractPdf;
        $this->signature = $signature;
        $this->scopeConfig = $scopeConfig;
        $this->attachmentFactory = $attachmentFactory;
        $this->filehelper = $filehelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /**
         * @var $order \Magento\Sales\Api\Data\OrderInterface
         */
        $order = $observer->getOrder();
        // check if order has rentals and attach pdf to order is enabled
        if($this->signature->enabledSignature($order,'order') && $this->scopeConfig->getValue('salesigniter_rental/contracts/attachorder')) {
            $this->attachPdf(
                $this->rentalContractPdf->renderContract($order->getId(),'S'),
                $this->filehelper->getContractFilename($order),
                $observer->getAttachmentContainer()
            );
        }
    }
}
