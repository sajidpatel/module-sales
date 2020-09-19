<?php

namespace SajidPatel\Sales\Model\ResourceModel\Order\Grid;

use SajidPatel\Sales\Api\TableFullfilmentInterface;
use SajidPatel\Sales\Api\TablesProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

class OptimizationQuery
{
    private const TEMPORARY_SOI_TABLE = 'tt';
    private const TEMPORARY_FROM_TT_TABLE = 'tt2';
    const OPTIMIZED_FIELD_NAME = 'product_name_and_sku';

    /**
     * @var TablesProcessorInterface
     */
    private $tablesProcessor;

    /**
     * @var TableFullfilmentInterface
     */
    private $tableFullfilment;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * OptimizationQuery constructor.
     * @param TablesProcessorInterface $tablesProcessor
     * @param ResourceConnection $resourceConnection
     * @param TableFullfilmentInterface $tableFullfilment
     */
    public function __construct(
        TablesProcessorInterface $tablesProcessor,
        ResourceConnection $resourceConnection,
        TableFullfilmentInterface $tableFullfilment
    ) {
        $this->tablesProcessor = $tablesProcessor;
        $this->tableFullfilment = $tableFullfilment;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return Select
     */
    private function getTt2TableSelect()
    {
        $select = $this->resourceConnection->getConnection()
            ->select();
        $select->from(self::TEMPORARY_SOI_TABLE);
        return $select;
    }

    /**
     * @param Select $select
     */
    public function optimize(Select $select): void
    {
        //Adding data to table tt
        $this->tablesProcessor->createTable(self::TEMPORARY_SOI_TABLE);
        $this->tableFullfilment->fillTable($select, self::TEMPORARY_SOI_TABLE);
        //Add int data to table tt2
        $this->tablesProcessor->createTable(self::TEMPORARY_FROM_TT_TABLE);
        $this->tableFullfilment->fillTable($this->getTt2TableSelect(), self::TEMPORARY_FROM_TT_TABLE);
        //Joining second temporary table
        $select->joinInner(
            ['tt2' => self::TEMPORARY_FROM_TT_TABLE],
            'tt2.entity_id=main_table.entity_id'
        );
        $select->join(
            ['soi' => $this->prepareDerivedTable()],
            'main_table.entity_id = soi.order_id'
        );
    }

    /**
     * @param Select $select
     * @return Select
     */
    public function renderOrders(Select $select)
    {
        //We don`t need to render orders,
        //as they are applied to temporary tables
        return $select;
    }

    /**
     * @return Select
     */
    private function prepareDerivedTable(): Select
    {
        $derivedQuery = $this->resourceConnection->getConnection()->select();
        $derivedQuery->from(['tl' => 'sales_order_item'], [
            'order_id',
            self::OPTIMIZED_FIELD_NAME => new Expression('GROUP_CONCAT(DISTINCT sku, " / ", name)')
        ]);
        $derivedQuery->joinInner(
            ['tt' => self::TEMPORARY_SOI_TABLE],
            'tt.entity_id=tl.order_id AND tl.product_type="simple"');
        $derivedQuery->group('order_id');
        return $derivedQuery;
    }
}
