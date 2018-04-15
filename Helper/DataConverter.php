<?php

namespace Noveo\WebApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Model\Data\Address;
use Magento\Customer\Api\Data\AddressInterface;

class DataConverter extends AbstractHelper
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);

    }

    /**
     * InputData should be an array of \Magento\Customer\Model\Data\Address objects
     *
     * @param array $inputData
     * @return array
     */
    public function addressesToArray($inputData)
    {
        $result = [];
        foreach ($inputData as $address) {
            /** @var Address $address */
            $result[] = $this->getAddressData($address);
        }
        return $result;
    }

    /**
     * @param Address $address
     * @return array
     */
    public function getAddressData(Address $address)
    {
        return [
            AddressInterface::ID => $address->getId(),
            AddressInterface::REGION => $this->addRegion($address),
            AddressInterface::COUNTRY_ID => $address->getCountryId(),
            AddressInterface::COMPANY => $address->getCompany(),
            AddressInterface::STREET => $address->getStreet(),
            AddressInterface::TELEPHONE => $address->getTelephone(),
            AddressInterface::POSTCODE => $address->getPostcode(),
            AddressInterface::CITY => $address->getCity(),
            AddressInterface::FIRSTNAME => $address->getFirstname(),
            AddressInterface::LASTNAME => $address->getLastname(),
            AddressInterface::PREFIX => $address->getPrefix(),
            AddressInterface::DEFAULT_SHIPPING => $address->isDefaultShipping(),
        ];
    }

    /**
     * @param Address $address
     * @return array|null
     */
    public function addRegion(Address $address)
    {
        $region = $address->getRegion();
        if (!$region) {
            return null;
        }
        return [
            RegionInterface::REGION_CODE => $region->getRegionCode(),
            RegionInterface::REGION => $region->getRegion(),
            RegionInterface::REGION_ID => $region->getRegionId()
        ];
    }

}