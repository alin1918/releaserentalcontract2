<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\RentalContract\Model\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Persistent\Helper\Data as PersistentHelper;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Quote\Model\QuoteIdMaskFactory;

class DefaultConfigProviderPlugin
{
    /**
     * @var PersistentSession
     */
    private $persistentSession;

    /**
     * @var PersistentHelper
     */
    private $persistentHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    protected $scopeConfig;

    protected $session;

    protected $signature;

    /**
     * @param PersistentHelper                                           $persistentHelper
     * @param PersistentSession                                          $persistentSession
     * @param CheckoutSession                                            $checkoutSession
     * @param QuoteIdMaskFactory                                         $quoteIdMaskFactory
     * @param CustomerSession                                            $customerSession
     * @param \Magento\Framework\View\Element\Template                   $template
     * @param \Magento\Framework\App\Config\ScopeConfigInterface         $scopeConfig
     * @param \Magento\Email\Model\TemplateFactory                       $templateFactory
     * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                $datetime
     * @param \Magento\Checkout\Model\Session                            $session
     * @param \SalesIgniter\RentalContract\Model\Signature               $signature
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface       $timezone
     */
    public function __construct(
        PersistentHelper $persistentHelper,
        PersistentSession $persistentSession,
        CheckoutSession $checkoutSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CustomerSession $customerSession,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Checkout\Model\Session $session,
        \SalesIgniter\RentalContract\Model\Signature $signature,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->signature = $signature;
        $this->session = $session;
        $this->timezone = $timezone;
        $this->datetime = $datetime;
        $this->countryInformation = $countryInformation;
        $this->_storeManager = $storeManager;
        $this->template = $template;
        $this->templateFactory = $templateFactory;
        $this->scopeConfig = $scopeConfig;
        $this->persistentHelper = $persistentHelper;
        $this->persistentSession = $persistentSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->customerSession = $customerSession;
        $this->template = $this->templateFactory->create(
            ['data' => ['area' => \Magento\Framework\App\Area::AREA_FRONTEND]]
        );
        $this->template->setDesignConfig(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->getStoreId()]
        );
        $this->template->setTemplateType(TemplateTypesInterface::TYPE_HTML);
    }

    //todo this part is accessed on shopping cart view

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array                                         $result
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        // set general variables for the template filter
        $countryId = $this->scopeConfig->getValue('general/store_information/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
        $this->vars['store_country'] = 'None';

        if (!is_null($countryId)) {
            /** @var $country \Magento\Directory\Api\Data\CountryInformationInterface */
            $country = $this->countryInformation->getCountryInfo($countryId);
            $this->vars['store_country'] = $country->getFullNameLocale();
        }
        if ($this->customerSession->isSessionExists()) {
            $this->vars['customerfirst'] = $this->customerSession->getCustomerFirstname();
            $this->vars['customerlast'] = $this->customerSession->getCustomerLastname();
        } else {
            $this->vars['customerfirst'] = __('Client First Name');
            $this->vars['customerlast'] = __('Client Last Name');
        }
        // process contract terms through template filter for variables substitution
        $this->template->setTemplateText($this->scopeConfig->getValue('salesigniter_rental/contracts/terms', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()));
        $result['dateNow'] = $this->datetime->date('F d, Y');
        $result['rentalContract'] = $this->template->getProcessedTemplate($this->vars);
        // only show rental contract signing under certain conditions

        // don't show if checkout signature is set to false
        $result['showRentalContract'] = $this->signature->enabledSignature($this->session->getQuote(), 'quote');
        return $result;
    }

    protected function getStoreId()
    {
        $store = $this->_storeManager->getStore();

        return $store ? $store->getId() : null;
    }
}
