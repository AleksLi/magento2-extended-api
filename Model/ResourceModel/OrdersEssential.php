<?php

namespace Noveo\WebApi\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesRule\Model\Rule;

class OrdersEssential
{
    const SELECT_ID_FIELD_NAME = 'id';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    public function getResultList()
    {
        return $this->getFetchedList($this->getSelect());
    }

    public function getProducts()
    {
        $resource = $this->resource;
        $connection = $resource->getConnection();

        $productsSelect = $connection->select()->from(
            ['order' => $resource->getTableName('sales_order')],
            [
                self::SELECT_ID_FIELD_NAME => 'order.entity_id',
                'sku' => 'products.sku'
            ]
        )->join(
            ['products' => $resource->getTableName('sales_order_item')],
            'products.order_id = order.entity_id',
            []
        )->where(
            $connection->quoteInto('order.store_id = ?',  $this->storeManager->getStore()->getId())
        )->distinct();

        return $this->getFetchedList($productsSelect);
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect()
    {
        $resource = $this->resource;
        $connection = $resource->getConnection();

        $select = $connection->select()->from(
            ['order' => $resource->getTableName('sales_order')],
            [
                self::SELECT_ID_FIELD_NAME => 'order.entity_id',
                'customer_id' => 'order.customer_id',
                'status',
                'total_price' => 'order.grand_total',
                'creation_date' => 'order.created_at',
                'order_date' => 'order.created_at',
                'shipping_date' => 'sales_shipment.created_at',
                'source' => '',
                'type' => '',
                'season' => '',
                'order_validated' => '',
                'notes' => '',
                'comments' => '',
                'products' => NULL,
                'total_quantity' => 'total_qty_ordered',
                'discount_value' => 'order.discount_amount',
                'discount_percent' => new \Zend_Db_Expr('IF(order.coupon_code IS NOT NULL, salesrule.discount_amount, NULL)'),
                'total_without_vat' => 'order.subtotal',
                'vat' => 'order.tax_amount',
                'total_with_vat' => 'order.grand_total',
                'shipping' => 'order.shipping_amount',
                'Net_to_pay' => 'order.total_paid',
            ]
        )->joinLeft(
            ['sales_shipment' => $resource->getTableName('sales_shipment')],
            'order.entity_id = sales_shipment.order_id',
            []
        )->joinLeft(
            ['salesrule_coupon' => $resource->getTableName('salesrule_coupon')],
            new \Zend_Db_Expr('salesrule_coupon.code = order.coupon_code'),
            []
        )->joinLeft(
            ['salesrule' => $resource->getTableName('salesrule')],
            $connection->quoteInto('salesrule_coupon.rule_id = salesrule.rule_id AND salesrule.simple_action = ?', Rule::BY_PERCENT_ACTION),
            []
        )->where(
            $connection->quoteInto('order.store_id = ?', $this->storeManager->getStore()->getId())
        );

        return $select;
    }

    /**
     * @param $select \Magento\Framework\DB\Select
     * @return array
     */
    public function getFetchedList($select)
    {
        return $this->resource->getConnection()->fetchAll($select);
    }
}
