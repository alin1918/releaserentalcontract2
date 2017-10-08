<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\RentalContract\Controller\RentalContract;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Pdf extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $rentalcontractmodel;

    /**
     * @param Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \SalesIgniter\RentalContract\Model\RentalContractPdf $RentalContractModel,
        PageFactory $resultPageFactory
    ) {
        $this->rentalcontractmodel = $RentalContractModel;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Print Order Action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage = $this->rentalcontractmodel->renderContract($this->getRequest()->getParam('order_id'));

        return $resultPage;
    }
}
