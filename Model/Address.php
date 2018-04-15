<?php

namespace Noveo\WebApi\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Address
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;



    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $ids
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getItemsByIds($ids)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $ids, 'in' )->create();
        $addressesResult = $this->addressRepository->getList($searchCriteria);

        return $addressesResult->getItems();
    }
}