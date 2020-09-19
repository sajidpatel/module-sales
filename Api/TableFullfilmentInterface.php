<?php
namespace SajidPatel\Sales\Api;

use Magento\Framework\DB\Select;

interface TableFullfilmentInterface
{
    /**
     * @param Select $select
     * @param string $tableName
     */
    public function fillTable(Select $select, string $tableName): void ;
}
