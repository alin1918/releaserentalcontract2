<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\RentalContract\Controller\Adminhtml\RentalContract;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action as BackendAction;

class Pdf extends BackendAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $_RentalContractModel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \SalesIgniter\RentalContract\Model\RentalContractPdf $RentalContractModel,
        Context $context,
        PageFactory $resultPageFactory

    ) {
        parent::__construct($context);
        $this->_RentalContractModel = $RentalContractModel;
        $this->resultPageFactory = $resultPageFactory;
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
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage = $this->_RentalContractModel->renderContract($this->getRequest()->getParam('order_id'));

        return $resultPage;
    }
}
