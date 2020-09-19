<?php
namespace SajidPatel\Sales\Model;

use SajidPatel\Sales\Api\TableFullfilmentInterface;
use SajidPatel\Sales\Model\ResourceModel\Order\Grid\OptimizationQuery;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class TemporaryTableFullfilment implements TableFullfilmentInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * TmpTableProvider constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Select $select
     */
    private function resetColumns(Select $select): void
    {
        $select->reset('columns');
        $select->columns(['entity_id']);
    }

    /**
     * @param Select $select
     * @throws \Zend_Db_Select_Exception
     */
    private function removeOptimizedFieldFromQuery(Select $select): void
    {
        $wherePart = $select->getPart('where');

        foreach ($wherePart as $index => $whereClause) {
            //For temporary table we need to get rid off product_name_and_sku field
            if (strpos($whereClause, OptimizationQuery::OPTIMIZED_FIELD_NAME) !== false) {
                unset($wherePart[$index]);
            }
        }

        $select->setPart('where', $wherePart);
    }

    /**
     * @param Select $select
     * @param string $tableName
     * @throws \Zend_Db_Select_Exception
     */
    public function fillTable(Select $select, string $tableName): void
    {
        $preparedSelect = clone $select;
        $this->resetColumns($preparedSelect);
        $this->removeOptimizedFieldFromQuery($preparedSelect);
        $appConnection = $this->resourceConnection->getConnection();
        $insertFromSelectQuery = $appConnection->insertFromSelect($preparedSelect, $tableName);
        $appConnection->query($insertFromSelectQuery);
    }
}
