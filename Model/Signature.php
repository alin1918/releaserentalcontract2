<?php

namespace SalesIgniter\RentalContract\Model;

class Signature extends \Magento\Framework\Model\AbstractModel {
	protected $vars;
	protected $filesystem;
	protected $templateFactory;
	protected $storeManager;
	protected $scopeConfig;
	protected $template;
	protected $_storeManager;
	protected $identityContainer;
	protected $countryInformation;
	protected $filehelper;
	protected $datetime;
	protected $rentalhelper;
	protected $quoteFactory;

	/**
	 * Signature constructor.
	 *
	 * @param \Magento\Sales\Api\OrderRepositoryInterface        $orderRepositoryInterface
	 * @param \Magento\Framework\View\Element\Template           $template
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Framework\Filter\Input\MaliciousCode      $maliciousCode
	 * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime        $datetime
	 * @param \Magento\Payment\Helper\Data                       $paymentHelper
	 * @param \SalesIgniter\Rental\Helper\Data                   $rentalhelper
	 * @param \SalesIgniter\RentalContract\Helper\Files          $filehelper
	 * @param \Magento\Quote\Model\QuoteRepository               $quoteFactory
	 */
	public function __construct(
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
		\Magento\Framework\View\Element\Template $template,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		\Magento\Payment\Helper\Data $paymentHelper,
		\SalesIgniter\Rental\Helper\Data $rentalhelper,
		\SalesIgniter\RentalContract\Helper\Files $filehelper,
		\Magento\Quote\Model\QuoteRepository $quoteFactory
	) {
		$this->quoteFactory    = $quoteFactory;
		$this->filehelper      = $filehelper;
		$this->scopeConfig     = $scopeConfig;
		$this->_storeManager   = $storeManager;
		$this->rentalhelper    = $rentalhelper;
		$this->orderRepository = $orderRepositoryInterface;
		$this->datetime        = $datetime;
		$this->vars            = [];
	}

	public function saveSignature( $order, $signaturetext, $signatureimage ) {
		$order->setSignatureText( $signaturetext );

		// $signatureFilePath usually pub/media/pdfs/signatures
		$signatureFilePath = $this->filehelper->getSignaturePath();
		$this->filehelper->createDirIfNotExists( $signatureFilePath );

		// decode signature and save to file in pub/media/pdfs/signatures
		$orderFilename = $this->filehelper->getOrderFilename( $order );
		$decoded_image = $this->filehelper->decodeImage( $signatureimage );
		$this->filehelper->saveImage( $signatureFilePath . $orderFilename, $decoded_image );
		$order->setSignatureImagefile( $orderFilename );
		$order->setSignatureDate( $this->datetime->date() );
		$order->save();
	}

	/**
	 * Checks based on config setting always show signature, and if that is set to no
	 * then checks if the order or quote contains rental products
	 *
	 * Returns true if signature panel should be shown, false if not
	 *
	 * @param        $quoteOrOrder
	 * @param string $isQuoteOrOrder
	 *
	 * @return bool|int
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */

	public function enabledSignature( $quoteOrOrder, $isQuoteOrOrder = 'quote' ) {
		if ( $isQuoteOrOrder == 'quote' ) {
			$containsRentals = $this->rentalhelper->quoteContainsRentals( $quoteOrOrder );
		} else {
			$containsRentals = $this->rentalhelper->orderContainsRentals( $quoteOrOrder );
		}
		$showRentalContract = false;
		if ( (bool) $this->scopeConfig->getValue( 'salesigniter_rental/contracts/checkoutsignature' ) ) {
			if ( $containsRentals || (bool) $this->scopeConfig->getValue( 'salesigniter_rental/contracts/showwithoutrentals', \Magento\Store\Model\ScopeInterface::SCOPE_STORE ) ) {
				$showRentalContract = true;
			}
		}

		return $showRentalContract;
	}

	/**
	 * Return path to signature file for order
	 *
	 * @param $order
	 *
	 * @return string
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */

	public function getDigitalSignature( $order ) {
		// load quote from order
		$quoteId = $order->getQuoteId();
		$quote   = $this->quoteFactory->get( $quoteId );
		// first check if there is a signature filename in the order, if so return that file

		//if ( $this->hasSignature( $order ) ) {
		$imageFile = $this->filehelper->getContractSignatureImageFile( $order );
		if ( file_exists( $imageFile ) ) {
			return $imageFile;
		}
		// if no order signature, then it is a quote signature since order signature is not generated
		// until the checkout success event
		//} elseif ( file_exists( $this->filehelper->getQuoteSignaturePath( $quote ) ) ) {
		//	return $this->filehelper->getQuoteSignaturePath( $quote );
		//}

		return null;

	}

	public function hasSignature( $order ) {
		if ( $order->getSignatureImagefile() == null || $order->getSignatureImagefile() == '' ) {
			return false;
		} else {
			return true;
		}
	}
}
