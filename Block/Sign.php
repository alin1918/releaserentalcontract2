<?php

namespace SalesIgniter\RentalContract\Block;

use Magento\Framework\App\TemplateTypesInterface;

class Sign extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $template;
    protected $templateFactory;
    protected $vars = [];
    protected $scopeConfig;
    protected $datetime;
    protected $rentalcontractpdf;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \SalesIgniter\RentalContract\Model\RentalContractPdf $rentalcontractpdf,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rentalcontractpdf = $rentalcontractpdf;
        $this->formKey = $context->getFormKey();
        $this->scopeConfig = $context->getScopeConfig();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_coreRegistry = $registry;
        $this->datetime = $datetime;
        $this->template = $template;
        $this->templateFactory = $templateFactory;
        $this->countryInformation = $countryInformation;
    }

    public function getDate(){
        return $this->datetime->date('F d, Y');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getOrderId()
    {
        return $this->getOrder()->getId();
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
            return $this->_coreRegistry->registry('current_order');
    }

    public function getTerms(){
        return $this->rentalcontractpdf->getTerms($this->getOrder());
    }

    public function getSignUri()
    {
        return $this->_urlBuilder->getUrl('salesigniter_rentalcontract/rentalcontract/save');
    }


    protected function getStoreId()
    {
        $store = $this->_storeManager->getDefaultStoreView();
        return $store ? $store->getId() : null;
    }

    public function alreadySigned()
    {
        if($this->getOrder()->getSignatureImagefile()){
            return '<div class="messages"><div class="message message-notice"><div>' . __('Rental contract has already been signed on  %1. You may update the signature below.',$this->datetime->date('F d, Y', $this->getOrder()->getSignatureDate())) . '</div></div></div>';
        }
    }
}