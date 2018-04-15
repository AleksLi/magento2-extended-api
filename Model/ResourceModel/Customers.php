<?php

namespace Noveo\WebApi\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class Customers
{
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

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect()
    {
        $resource = $this->resource;
        $connection = $resource->getConnection();

        $storeId = $this->storeManager->getStore()->getId();

        $select = $connection->select()->from(
            ['customer' => $resource->getTableName('customer_entity')],
            [
                'entity_id',
                'erp_id',
                'company' => 'customer_entity_varchar.value',
                'firstname',
                'lastname',
                CustomerInterface::DEFAULT_SHIPPING,
                CustomerInterface::DEFAULT_BILLING
            ]
        )->joinLeft(
            ['eav_entity_type' => $resource->getTableName('eav_entity_type')],
            $connection->quoteInto('eav_entity_type.entity_type_code = ?', CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER),
            []
        )->joinLeft(
            ['eav_attribute' => $resource->getTableName('eav_attribute')],
            $connection->quoteInto('eav_attribute.entity_type_id = eav_entity_type.entity_type_id AND eav_attribute.attribute_code = ?', 'company'),
            []
        )->joinLeft(
            ['customer_entity_varchar' => $resource->getTableName('customer_entity_varchar')],
            'customer_entity_varchar.attribute_id = eav_attribute.attribute_id AND customer_entity_varchar.entity_id = customer.entity_id',
            []
        )->where(
            $connection->quoteInto('customer.website_id = ?', $storeId)
        );

        return $select;
    }

    /**
     * @return array
     */
    public function getFetchedList()
    {
        return $this->resource->getConnection()->fetchAll($this->getSelect());
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @return array
     */
    public function getFetchedListBySelect($select)
    {
        return $this->resource->getConnection()->fetchAll($select);
    }
}