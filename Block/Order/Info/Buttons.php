<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block of links in Order view page
 */
namespace SalesIgniter\RentalContract\Block\Order\Info;

use Magento\Customer\Model\Context;

class Buttons extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $signature;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \SalesIgniter\RentalContract\Model\Signature $signature,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->signature = $signature;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }


    public function getViewUrl($order)
    {
        // your custom url path here
        return $this->getUrl('salesigniter_rentalcontract/rentalcontract/pdf', ['order_id' => $order->getId()]);
    }

    public function getSignUrl($order)
    {
        // your custom url path here
        return $this->getUrl('salesigniter_rentalcontract/rentalcontract/sign', ['order_id' => $order->getId()]);
    }

    public function signatureEnabled($order){
        return $this->signature->enabledSignature($order,'order');
    }

    public function hasSignature($order){
        return $this->signature->hasSignature($order);
    }

}
