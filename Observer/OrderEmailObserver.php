<?php

namespace SalesIgniter\RentalContract\Observer;

use Fooman\EmailAttachments\Observer\AbstractObserver as FoomanAbstractObserver;

class OrderEmailObserver extends FoomanAbstractObserver {
	protected $attachmentFactory;

	protected $scopeConfig;

	protected $rentalContractPdf;

	protected $signature;

	protected $filehelper;
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */

	protected $storeManager;
	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	private $helperRental;

	/**
	 * OrderEmailObserver constructor.
	 *
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
	 * @param \Fooman\EmailAttachments\Model\AttachmentFactory     $attachmentFactory
	 * @param \SalesIgniter\RentalContract\Model\Signature         $signature
	 * @param \SalesIgniter\RentalContract\Model\RentalContractPdf $rentalContractPdf
	 * @param \SalesIgniter\RentalContract\Helper\Files            $filehelper
	 * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
	 * @param  \Magento\Store\Model\StoreManagerInterface          $storeManager
	 */
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Fooman\EmailAttachments\Model\AttachmentFactory $attachmentFactory,
		\SalesIgniter\RentalContract\Model\Signature $signature,
		\SalesIgniter\RentalContract\Model\RentalContractPdf $rentalContractPdf,
		\SalesIgniter\RentalContract\Helper\Files $filehelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\SalesIgniter\Rental\Helper\Data $helperRental,
        \Fooman\EmailAttachments\Model\TermsAndConditionsAttacher $termsAttacher,
        \Fooman\EmailAttachments\Model\ContentAttacher $contentAttacher
	) {
		$this->rentalContractPdf = $rentalContractPdf;
		$this->signature         = $signature;
		$this->scopeConfig       = $scopeConfig;
		$this->attachmentFactory = $attachmentFactory;
		$this->filehelper        = $filehelper;
		$this->helperRental      = $helperRental;
		$this->storeManager      = $storeManager;
        
        $this->termsAttacher = $termsAttacher;
        $this->contentAttacher = $contentAttacher;        
	}

	protected function getStoreId() {
		$store = $this->storeManager->getStore();

		return $store ? $store->getId() : null;
	}

	public function execute( \Magento\Framework\Event\Observer $observer ) {

		/** @var \Magento\Sales\Api\Data\OrderInterface $order */
		$order = $observer->getOrder();
		// check if order has rentals and attach pdf to order is enabled
		if ( (bool) $this->scopeConfig->getValue( 'salesigniter_rental/contracts/attachorder', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId() ) && $this->helperRental->orderContainsRentals( $order ) ) {
			$this->attachPdf(
				$this->rentalContractPdf->renderContract( $order->getId(), 'S' ),
				$this->filehelper->getContractFilename( $order ),
				$observer->getAttachmentContainer()
			);
		}
	}
}
