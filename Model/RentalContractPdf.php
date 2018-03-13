<?php

namespace SalesIgniter\RentalContract\Model;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Sales\Model\Order\Address\Renderer;

class RentalContractPdf extends \Magento\Framework\Model\AbstractModel {
	protected $vars;
	protected $filesystem;
	protected $pdf;
	protected $templateFactory;
	protected $maliciousCode;
	protected $storeManager;
	protected $scopeConfig;
	protected $template;
	protected $_storeManager;
	protected $orderRepository;
	protected $addressRenderer;
	protected $paymentHelper;
	protected $identityContainer;
	protected $countryInformation;
	protected $signature;
	protected $datehelper;
	protected $_filehelper;

	/**
	 * @var \SalesIgniter\Rental\Helper\Calendar
	 */
	protected $_calendarHelper;

	public function __construct(
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
		\Magento\Framework\View\Element\Template $template,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Email\Model\TemplateFactory $templateFactory,
		\Fooman\EmailAttachments\Model\AttachmentFactory $attachmentFactory,
		\Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Fooman\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainerInterface,
		\Magento\Payment\Helper\Data $paymentHelper,
		\Magento\Sales\Model\Order\Email\Container\OrderIdentity $identityContainer,
		\Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
		Renderer $addressRenderer,
		\SalesIgniter\RentalContract\Model\Signature $signature,
		\SalesIgniter\Rental\Helper\Calendar $calendarHelper,
		\SalesIgniter\RentalContract\Helper\Files $fileHelper
	) {
		$this->signature          = $signature;
		$this->_filehelper        = $fileHelper;
		$this->identityContainer  = $identityContainer;
		$this->paymentHelper      = $paymentHelper;
		$this->addressRenderer    = $addressRenderer;
		$this->scopeConfig        = $scopeConfig;
		$this->_storeManager      = $storeManager;
		$this->templateFactory    = $templateFactory;
		$this->maliciousCode      = $maliciousCode;
		$this->orderRepository    = $orderRepositoryInterface;
		$this->vars               = [];
		$this->countryInformation = $countryInformation;
		$this->_calendarHelper    = $calendarHelper;
		$this->template           = $this->templateFactory->create(
			[ 'data' => [ 'area' => \Magento\Framework\App\Area::AREA_FRONTEND ] ]
		);

		$this->template->setDesignConfig(
			[ 'area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->getStoreId() ]
		);

		$this->template->setTemplateType( TemplateTypesInterface::TYPE_HTML );
	}

	/**
	 * Generates contract.
	 *
	 * @param        $orderId
	 * @param string $returntype
	 *
	 * @return if $returntype is S then a string
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\MailException
	 *                                                            if $returntype is F then filepath to pdf
	 *                                                            if $returntype is I then the pdf
	 */
	public function renderContract( $orderId, $returntype = 'I' ) {

		/** @var $order \Magento\Sales\Model\Order */
		$order = $this->orderRepository->get( $orderId );

		// load all vars from order

		$this->vars['order']                    = $order;
		$this->vars['formattedBillingAddress']  = $this->getFormattedBillingAddress( $order );
		$this->vars['formattedShippingAddress'] = $this->getFormattedShippingAddress( $order );
		$this->vars['payment_html']             = $this->getPaymentHtml( $order );
		$this->vars['customerfirst']            = $order->getCustomerFirstname();
		$this->vars['customerlast']             = $order->getCustomerLastname();
		$this->vars['billingcompany']           = $order->getBillingAddress()->getCompany();
		//$terms               = $this->scopeConfig->getValue( 'salesigniter_rental/contracts/terms', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() );
		$this->vars['terms'] = $this->getTerms( $order );

		// load digital signature if it exists
		$this->vars['digitalsignature']         = $this->signature->getDigitalSignature( $order );
		$this->vars['include_digitalsignature'] = 1;
		$signaturedate                          = new \DateTime( $order->getSignatureDate() );
		$this->vars['signaturedate']            = $this->_calendarHelper->formatDate( $signaturedate );

		// load contract vars


		$contractTitle                = $this->scopeConfig->getValue( 'salesigniter_rental/contracts/contract_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() );
		$this->vars['contract_title'] = $this->processVars( $contractTitle );

		$headertext               = $this->scopeConfig->getValue( 'salesigniter_rental/contracts/header_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() );
		$this->vars['headertext'] = $this->processVars( $headertext );

		$footertext               = $this->scopeConfig->getValue( 'salesigniter_rental/contracts/footer_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() );
		$this->vars['footertext'] = $this->processVars( $footertext );

		$countryId                   = $this->scopeConfig->getValue( 'general/store_information/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() );
		$this->vars['store_country'] = 'None';

		if ( ! is_null( $countryId ) ) {
			/** @var $country \Magento\Directory\Api\Data\CountryInformationInterface */
			$country                     = $this->countryInformation->getCountryInfo( $countryId );
			$this->vars['store_country'] = $country->getFullNameLocale();
		}

		// process store config contract settings using vars

		$this->vars['include_manualsignature'] =
			( $this->scopeConfig->getValue( 'salesigniter_rental/contracts/include_manualsignature', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() ) ? 1 : null );

		// we have to first get the layout and pull in the template

		$templateText = $this->template->setTemplateText( $this->getTemplateText() );

		/*
		 * Here I need help with an issue the <br /> are being converted to &lt;br /&gt;
		 * in the contract terms after the template is processed.
		 * I have traced it to around here:
		 * vendor/magento/framework/Escaper.php:20
		 * vendor/magento/module-email/Model/Template/Filter.php:49
		 * vendor/magento/module-email/Model/Template/Filter.php:960
		 *
		 * but I have no idea how to fix this
		 */
		$templateText = $templateText->getProcessedTemplate( $this->vars );
		// run through the same process, this time to input the variables to the template

		$templateText = $this->template->setTemplateText( $templateText );
		$templateText = $templateText->getProcessedTemplate( $this->vars );
		//echo( $templateText );
		//die();
		/*
		 * Replace <dt>, </dt>, <dd> with nothing
		 * Replace </dd> with line break
		 * fixes formatting of rental start/end dates
		 */
		//$templateText = str_replace(['<dd>', '<dt>', '</dt>'], '', $templateText);
		//$templateText = str_replace('</dd>', '<br/>', $templateText);
		//$templateText = str_replace('%5C', '\\', $templateText);
		/*$templateTextObj = html5qp($templateText);
		$templateText = $templateTextObj->html();

		$templateText = preg_replace("/<html[^>]+\>/i", '', $templateText);
		$templateText = str_replace('<html>', '', $templateText);
		$templateText = str_replace('</html>', '', $templateText);
		$templateText = str_replace('<!DOCTYPE html>', '', $templateText);
		$templateText = str_replace('<br></br>', '<br />', $templateText);

		$this->pdf->writeHTML($templateText, false);
		$this->pdf->endPage(); */
		$mpdf = new \Mpdf\Mpdf( [ 'tempDir' => $this->_filehelper->getSignaturePath() ] );
		$mpdf->WriteHTML( $templateText );

		$orderfilename = $this->_filehelper->getContractFilename( $order );
		$filePath      = $this->_filehelper->getPdfFilePath() . $orderfilename;
		if ( $returntype == 'S' ) {
			return $mpdf->Output( $filePath, $returntype );
		} else {
			$mpdf->Output();
		}
		exit;
	}

	/**
	 * Runs content through template processor to replace {{var varname}}
	 * with variables from store or order.
	 *
	 * @param $var
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\MailException
	 */
	protected function processVars( $var ) {
		$this->template->setTemplateText( $var );

		return $this->template->getProcessedTemplate( $this->vars );
	}

	protected function getTemplateText() {
		return '{{layout handle="salesigniter_rentalcontract_pdf" order=$order}}';
	}

	protected function getStoreId() {
		$store = $this->_storeManager->getStore();

		return $store ? $store->getId() : null;
	}

	/**
	 * Function copied from vendor/magento/module-sales/Model/Order/Email/Sender.php.
	 *
	 * @param $order
	 */
	protected function getFormattedShippingAddress( $order ) {
		/* @var $order \Magento\Sales\Model\Order */
		return $order->getIsVirtual()
			? null
			: $this->addressRenderer->format( $order->getShippingAddress(), 'html' );
	}

	/**
	 * Function copied from vendor/magento/module-sales/Model/Order/Email/Sender.php.
	 *
	 * @param $order
	 */
	protected function getFormattedBillingAddress( $order ) {
		/* @var $order \Magento\Sales\Model\Order */
		return $this->addressRenderer->format( $order->getBillingAddress(), 'html' );
	}

	/**
	 * Function copied from vendor/magento/module-sales/Model/Order/Email/Sender/OrderSender.php.
	 *
	 * Get payment info block as html
	 *
	 * @param Order $order
	 *
	 * @return string
	 */
	protected function getPaymentHtml( $order ) {
		/* @var $order \Magento\Sales\Model\Order */
		return $this->paymentHelper->getInfoBlockHtml(
			$order->getPayment(),
			$this->identityContainer->getStore()->getStoreId()
		);
	}

	/**
	 * used by frontend and adminhtml blocks to get terms, not used on pdf generation actually.
	 *
	 * @param $order
	 *
	 * @return mixed
	 */
	public function getTerms( $order ) {
		$this->template = $this->templateFactory->create(
			[ 'data' => [ 'area' => \Magento\Framework\App\Area::AREA_FRONTEND ] ]
		);
		$this->template->setDesignConfig(
			[ 'area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->getStoreId() ]
		);
		$this->template->setTemplateType( TemplateTypesInterface::TYPE_HTML );

		$countryId                   = $this->scopeConfig->getValue( 'general/store_information/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() );
		$this->vars['store_country'] = 'None';

		if ( ! is_null( $countryId ) ) {
			/** @var $country \Magento\Directory\Api\Data\CountryInformationInterface */
			$country                     = $this->countryInformation->getCountryInfo( $countryId );
			$this->vars['store_country'] = $country->getFullNameLocale();
		}

		if ( $order->getCustomerFirstname() ) {
			$this->vars['customerfirst'] = $order->getCustomerFirstname();
			$this->vars['customerlast']  = $order->getCustomerLastname();
		}
		$signatureImage = $order->getSignatureImagefile();
		// $signatureFilePath usually pub/media/pdfs/signatures
		$signatureFilePath = $this->_filehelper->getSignaturePath();
		// decode signature and save to file in pub/media/pdfs/signatures
		$orderFilename = $this->_filehelper->getOrderFilename( $order );
		if ( $signatureImage !== null && $signatureImage !== '' && ! file_exists( $signatureFilePath . $orderFilename ) ) {
			$this->_filehelper->createDirIfNotExists( $signatureFilePath );
			$decoded_image = $this->_filehelper->decodeImage( $signatureImage );
			$this->_filehelper->saveImage( $signatureFilePath . $orderFilename, $decoded_image );
		}

		// process contract terms through template filter for variables substitution
		$this->template->setTemplateText( $this->scopeConfig->getValue( 'salesigniter_rental/contracts/terms', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId() ) );

		return $this->template->getProcessedTemplate( $this->vars );
		// return $this->escapeJsQuote(json_encode($this->template->getProcessedTemplate($this->vars)));
	}
}
