<?php

namespace SajidPatel\Sales\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    /**
     * @var OptimizationQuery
     */
    private $optimizationQuery;

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param OptimizationQuery $optimizationQuery
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        OptimizationQuery $optimizationQuery,
        $mainTable = 'sales_order_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->optimizationQuery = $optimizationQuery;
    }

    /**
     * @return $this|Collection
     */
    protected function _renderOrders()
    {
        $this->optimizationQuery->renderOrders($this->getSelect());
        return $this;
    }

    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        parent::_renderOrders();
        $this->optimizationQuery->optimize($this->getSelect());
        parent::_renderFiltersBefore();
    }
}
