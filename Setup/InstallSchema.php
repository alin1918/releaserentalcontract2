<?php

namespace SalesIgniter\RentalContract\Setup;

use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'signature_text',
            [
                'type' => DdlTable::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Signature Text',
            ]
        );


        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'signature_text',
            [
                'type' => DdlTable::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Signature Text',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'signature_imagefile',
            [
                'type' => DdlTable::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Signature Image Filename',
            ]
        );

        $installer->endSetup();
    }
}
