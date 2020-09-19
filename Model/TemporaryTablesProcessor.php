<?php
namespace SajidPatel\Sales\Model;

use SajidPatel\Sales\Api\TablesProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;

class TemporaryTablesProcessor implements TablesProcessorInterface
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
     * @param string $tableName
     * @param array $options
     * @throws \Zend_Db_Exception
     */
    public function createTable(string $tableName, array $options = []): void
    {
        $appConnection = $this->resourceConnection->getConnection();
        $appConnection->dropTemporaryTable($tableName);
        $table = new Table();
        $table->addColumn('entity_id', 'integer');
        $table->setName($tableName);
        $table->setComment('Temporary table for sales_order_grid table');
        $appConnection->createTemporaryTable($table);
    }
}
