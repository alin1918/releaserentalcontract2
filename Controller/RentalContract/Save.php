<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\RentalContract\Controller\RentalContract;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $_RentalContractModel;

    protected $datetime;

    /**
     * @var \SalesIgniter\RentalContract\Model\Signature
     */
    protected $signature;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \SalesIgniter\RentalContract\Model\Signature $signature,
        PageFactory $resultPageFactory

    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->signature = $signature;
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
        // return $this->_authorization->isAllowed('Magento_Cms::page');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $orderid = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderid);
        $signaturetext = $this->getRequest()->getParam('signature_name');
        $signatureimage = $this->getRequest()->getParam('signature_image');

        $this->signature->saveSignature($order,$signaturetext,$signatureimage);
        $this->messageManager->addSuccess( __('Signature has been saved to the rental contract') );

        // redirect to order view page
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view',['order_id'=>$orderid]);
        return $resultRedirect;
    }
}
