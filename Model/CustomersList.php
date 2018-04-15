<?php

namespace Noveo\WebApi\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Noveo\WebApi\Api\CustomersListInterface;
use Noveo\WebApi\Helper\DataConverter;
use Noveo\WebApi\Model\Address;
use Magento\Customer\Api\Data\CustomerInterface;

class CustomersList implements CustomersListInterface
{
    /**
     * @var Address
     */
    private $addressModel;

    /**
     * @var ResourceModel\Customers
     */
    protected $customersModel;

    /**
     * @var array
     */
    protected $resultList = [];

    /**
     * @var array
     */
    protected $currentList = [];

    /**
     * @var int
     */
    protected $qtyOfCustomers = 0;

    /**
     * @var int
     */
    protected $pageSize = 100;

    /**
     * @var array
     */
    protected $entityIds = [];

    /**
     * @var DataConverter
     */
    private $dataConverterHelper;

    /**
     * CustomersList constructor.
     * @param ResourceModel\Customers $customersModel
     * @param \Noveo\WebApi\Model\Address $addressModel
     * @param DataConverter $dataConverter
     */
    public function __construct(
        ResourceModel\Customers $customersModel,
        Address $addressModel,
        DataConverter $dataConverter
    )
    {
        $this->customersModel = $customersModel;
        $this->addressModel = $addressModel;
        $this->dataConverterHelper = $dataConverter;
    }

    /**
     * Get list of all the customers for the site.
     * it returns data like:
     *  [
     *   entity_id  => (integer) customerId,
     *   erp_id => (string) customer erp id
     *   company => (string)
     *   firstname => (string)
     *   lastname => (string)
     *   address_shipping => (integer)
     *   address_billing => (integer)
     * ]
     *
     * @return array
     */
    public function execute()
    {
        $dataList = $this->customersModel->getFetchedList();
        $listSize = count($dataList);
        $pageSize = $this->getDefaultPageSize();

        if ($listSize < $pageSize) {
            $this->setCurrentList($dataList);
            $ids = $this->getAddressIds($dataList);
            if (!$ids) {
                return $dataList;
            }
            $this->setEntityIds($ids);
            $this->addAddresses();
            return $this->getDataList();
        }

        $this->setQtyOfCustomers($listSize);
        $this->runBunches();

        return $this->getDataList();
    }

    protected function runBunches()
    {
        $customerSelect = $this->customersModel->getSelect();
        $pageSize = $this->getDefaultPageSize();
        $iterations = ceil($this->getQtyOfCustomers() / $pageSize);

        for ($i = 0; $i < $iterations; $i++) {
            $customerSelect->limit($pageSize, $pageSize * $i);
            $list = $this->customersModel->getFetchedListBySelect($customerSelect);
            $ids = $this->getAddressIds($list);
            $this->setCurrentList($list);
            if (!$ids) {
                $this->addToDataList($list);
                continue;
            }
            $this->setEntityIds($ids);
            $this->addAddresses();
        }

    }

    public function addAddresses()
    {
        $dataList = $this->getCurrentList();
        $addressList = $this->getAddressListByIds();

        foreach ($dataList as &$resultItem) {
            if ($resultItem[CustomerInterface::DEFAULT_SHIPPING] && !is_array($resultItem[CustomerInterface::DEFAULT_SHIPPING])) {
                $resultItem[CustomerInterface::DEFAULT_SHIPPING] = $this->addAddressDataById($resultItem[CustomerInterface::DEFAULT_SHIPPING], $addressList);
            }

            if ($resultItem[CustomerInterface::DEFAULT_BILLING] && !is_array($resultItem[CustomerInterface::DEFAULT_BILLING])) {
                $resultItem[CustomerInterface::DEFAULT_BILLING] = $this->addAddressDataById($resultItem[CustomerInterface::DEFAULT_BILLING], $addressList);
            }
        }

        $this->addToDataList($dataList);
    }

    public function getAddressListByIds()
    {
        $ids = $this->getEntityIds();
        $addressList = $this->addressModel->getItemsByIds($ids);
        $addressList = $this->dataConverterHelper->addressesToArray($addressList);

        return $addressList;
    }

    /**
     * @param $entityId
     * @param $addresses
     * @return array | null
     */
    public function addAddressDataById($entityId, $addresses)
    {
        foreach ($addresses as $address) {
            if ($address[AddressInterface::ID] === $entityId) {
                return $address;
            }
        }
        return null;
    }

    /**
     * @param array $dataList
     * @return array
     */
    public function getAddressIds($dataList)
    {
        $ids = [];

        foreach ($dataList as $item) {
            if ($item[CustomerInterface::DEFAULT_SHIPPING]) {
                $ids[] = $item[CustomerInterface::DEFAULT_SHIPPING];
            }
            if ($item[CustomerInterface::DEFAULT_BILLING]) {
                $ids[] = $item[CustomerInterface::DEFAULT_BILLING];
            }
        }
        return array_unique($ids);
    }

    public function getEntityIds()
    {
        return $this->entityIds;
    }

    /**
     * @param array $data
     */
    protected function setEntityIds($data)
    {
        $this->entityIds = $data;
    }

    public function getDataList()
    {
        return $this->resultList;
    }

    /**
     * @param $dataList
     */
    public function addToDataList($dataList)
    {
        $this->resultList = array_merge($this->getDataList(), $dataList);
    }

    /**
     * @return int
     */
    public function getQtyOfCustomers()
    {
        return $this->qtyOfCustomers;
    }

    /**
     * @param int $num
     */
    public function setQtyOfCustomers($num)
    {
        $this->qtyOfCustomers = $num;
    }

    protected function getCurrentList()
    {
        return $this->currentList;
    }

    /**
     * @param array $list
     */
    protected function setCurrentList($list)
    {
        $this->currentList = $list;
    }

    public function getDefaultPageSize()
    {
        return $this->pageSize;
    }
}
