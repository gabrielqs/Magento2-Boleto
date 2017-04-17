<?php

namespace Gabrielqs\Boleto\Setup;

use \Magento\Framework\Setup\UpgradeSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $this->_createRemittanceTables($setup, $context);
        $this->_createReturnTables($setup, $context);

    }

    /**
     * Create Remittance tables
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    protected function _createRemittanceTables(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'boleto_remittance_file'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('boleto_remittance_file')
        )->addColumn(
            'remittance_file_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Remittance File ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Remittance File Name'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Remittance File Processing Status'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Remittance File Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Remittance File Modification Time'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file'),
                ['name'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['name'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file'),
                ['status'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['status'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file'),
                ['creation_time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['creation_time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file'),
                ['update_time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['update_time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->setComment(
            'Remittance File Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'boleto_remittance_file_event'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('boleto_remittance_file_event')
        )->addColumn(
            'remittance_file_event_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Remittance File Event ID'
        )->addColumn(
            'remittance_file_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Remittance File ID'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            1000,
            ['nullable' => false],
            'Remittance File Processing Status'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Remittance File Creation Time'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file_event'),
                ['remittance_file_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['remittance_file_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file_event'),
                ['creation_time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['creation_time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName('boleto_remittance_file_event', 'remittance_file_id',
                $installer->getTable('boleto_remittance_file'), 'remittance_file_id'
            ),
            'remittance_file_id',
            $installer->getTable('boleto_remittance_file'),
            'remittance_file_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Remittance File - Events Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'boleto_remittance_file_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('boleto_remittance_file_order')
        )->addColumn(
            'remittance_file_order_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Remittance File x Order ID'
        )->addColumn(
            'remittance_file_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Remittance File ID'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Order ID'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file_order'),
                ['remittance_file_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['remittance_file_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_remittance_file_order'),
                ['order_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['order_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName('boleto_remittance_file_order', 'remittance_file_id',
                $installer->getTable('boleto_remittance_file'), 'remittance_file_id'),
            'remittance_file_id',
            $installer->getTable('boleto_remittance_file'),
            'remittance_file_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('boleto_remittance_file_order', 'remittance_file_id',
                $installer->getTable('sales_order'), 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Remittance File - Orders Table'
        );
        $installer->getConnection()->createTable($table);
    }


    protected function _createReturnTables(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'boleto_returns_file'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('boleto_returns_file')
        )->addColumn(
            'returns_file_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Returns File ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Returns File Name'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Returns File Processing Status'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Returns File Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Returns File Modification Time'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file'),
                ['name'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['name'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file'),
                ['status'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['status'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file'),
                ['creation_time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['creation_time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file'),
                ['update_time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['update_time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->setComment(
            'Returns File Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'boleto_returns_file_event'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('boleto_returns_file_event')
        )->addColumn(
            'returns_file_event_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Returns File Event ID'
        )->addColumn(
            'returns_file_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Returns File ID'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            1000,
            ['nullable' => false],
            'Returns File Processing Status'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Returns File Creation Time'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file_event'),
                ['returns_file_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['returns_file_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file_event'),
                ['creation_time'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['creation_time'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName('boleto_returns_file_event', 'returns_file_id',
                $installer->getTable('boleto_returns_file'), 'returns_file_id'),
            'returns_file_id',
            $installer->getTable('boleto_returns_file'),
            'returns_file_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Returns File - Events Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'boleto_returns_file_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('boleto_returns_file_order')
        )->addColumn(
            'returns_file_order_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Returns File x Order ID'
        )->addColumn(
            'returns_file_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Returns File ID'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Order ID'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file_order'),
                ['returns_file_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['returns_file_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('boleto_returns_file_order'),
                ['order_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['order_id'],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName('boleto_returns_file_order', 'returns_file_id',
                $installer->getTable('boleto_returns_file'), 'returns_file_id'),
            'returns_file_id',
            $installer->getTable('boleto_returns_file'),
            'returns_file_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('boleto_returns_file_order', 'returns_file_id',
                $installer->getTable('sales_order'), 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Returns File - Orders Table'
        );
        $installer->getConnection()->createTable($table);
    }
}