<?php

namespace SalesIgniter\RentalContract\Model\Plugin;

class AddSalesOrderButton
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        $buttonList
    )
    {
        if ($subject->getRequest()->getFullActionName() == 'sales_order_view') {

            $buttonList->add(
                'sign_contract',
                [
                    'label' => __('Sign Contract'),
//                    'onclick' => "setLocation('window.location.href')",
                    'class' => 'ship'
                ]
            );

            $viewContractUri = $this->urlBuilder->getUrl('salesigniter_rentalcontract/rentalcontract/pdf',['order_id'=>$subject->getRequest()->getParam('order_id')]);

            $buttonList->add(
                'view_contract',
                [
                    'label' => __('View Contract'),
                    'onclick' => "setLocation('{$viewContractUri}')",
                    'class' => 'ship'
                ]
            );
        }

        return $buttonList;
    }
}