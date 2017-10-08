<?php

namespace SalesIgniter\RentalContract\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();
        if(version_compare($context->getVersion(), '1.0.20160421') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'signature_date',
                [
                    'type' => DdlTable::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Signature Date',
                ]
            );
        }
        $setup->endSetup();
    }
}