<?php

namespace Noveo\WebApi\Model;

use Noveo\WebApi\Api\OrdersEssentialInterface;
use Noveo\WebApi\Model\ResourceModel\OrdersEssential as OrdersEssentialResource;

class OrdersEssential implements OrdersEssentialInterface
{
    /**
     * @var OrdersEssentialResource
     */
    protected $resourceModel;

    /**
     * @var array
     */
    protected $list;

    /**
     * @param OrdersEssentialResource $ordersEssential
     */
    public function __construct(
        OrdersEssentialResource $ordersEssential
    ) {
        $this->resourceModel = $ordersEssential;
    }

    /**
     * API method.
     *
     * @return array|null
     */
    public function getList()
    {
        $list = $this->resourceModel->getResultList();
        if (!$list) {
            return null;
        }

        $this->setListData($list);
        $this->addProducts();

        return $this->getListData();
    }

    public function addProducts()
    {
        $products = $this->getProducts();
        if (!is_array($products)) {
            return false;
        }

        $idFieldName = OrdersEssentialResource::SELECT_ID_FIELD_NAME;

        foreach ($this->list as &$order) {
            foreach ($products as $product) {
                if ($order[$idFieldName] !== $product[$idFieldName]) {
                    continue;
                }
                if (!isset($order['products'])) {
                    $order['products'] = [$product['sku']];
                    continue;
                }
                $order['products'][] = $product['sku'];
            }
        }
        return true;
    }

    /**
     * Wrapper for getProducts() in resource model
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->resourceModel->getProducts();
    }

    /**
     * @param $list array
     */
    protected function setListData($list)
    {
        $this->list = $list;
    }

    public function getListData()
    {
        return $this->list;
    }
}