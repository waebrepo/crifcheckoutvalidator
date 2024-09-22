<?php

declare(strict_types=1);

namespace Geschenkkoerbe\CrifCheckoutValidator\Service;

use Exception;
use Geschenkkoerbe\CrifCheckoutValidator\Enum\ReportType;
use Geschenkkoerbe\CrifCheckoutValidator\Enum\Sex;
use Geschenkkoerbe\CrifCheckoutValidator\Enum\TargetReportFormat;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Country\CountryEntity;

class CrifValidator
{
    private CrifApi $crifApi;

    private EntityRepository $countryRepository;

    private GeneralConfig $generalConfig;

    public function __construct(
        CrifApi $crifApi,
        EntityRepository $countryRepository,
        GeneralConfig $generalConfig
    ) {
        $this->crifApi = $crifApi;
        $this->countryRepository = $countryRepository;
        $this->generalConfig = $generalConfig;
    }

    /**
     * @throws Exception
     */
    public function validateCustomer(array $convertedCart, CartConvertedEvent &$event): bool
    {
        $billingAddress = $this->getBillingAddress($convertedCart, $event->getContext());
        if ($this->isCountryConcerned($billingAddress->getCountryId()) === false) {
            return true;
        }

        if ($billingAddress->getCompany() !== null) {
            $decision = $this->crifApi->checkNaturalPerson($billingAddress, Sex::UNKNOWN, ReportType::QUICK_CHECK_CONSUMER, TargetReportFormat::VALUE_NONE);
        } else {
            $decision = $this->crifApi->checkNaturalPerson($billingAddress, Sex::UNKNOWN, ReportType::QUICK_CHECK_CONSUMER, TargetReportFormat::VALUE_NONE);
        }

        return $decision;
    }

    /**
     * @throws Exception
     */
    private function getBillingAddress(array $convertedCart, Context $context): BillingAddress
    {
        if (!array_key_exists('deliveries', $convertedCart)) {
            throw new Exception('deliveries is missing from the cart data');
        }
        $deliveries = $convertedCart['deliveries'];
        if (count($deliveries) === 0) {
            throw new Exception('deliveries data is missing from the cart data');
        }
        if (!array_key_exists(0, $deliveries)) {
            throw new Exception('No delivery address are registered for this order');
        }
        $delivery = $deliveries[0];
        if (!array_key_exists('shippingOrderAddress', $delivery)) {
            throw new Exception('No shipping address are registered for this delivery');
        }
        $shippingAddress = $delivery['shippingOrderAddress'];
        $shippingAddressId = $shippingAddress['id'];
        $billingAddressId = $convertedCart['billingAddressId'];
        if ($shippingAddressId === $billingAddressId) {
            $company = null;
            if (array_key_exists('company', $shippingAddress)) {
                $company = $shippingAddress['company'];
            }
            $countryCode = $this->getCountryCodeByCountryId($shippingAddress['countryId'], $context);
            return new BillingAddress(
                $shippingAddress['lastName'],
                $shippingAddress['firstName'],
                $shippingAddress['street'],
                $shippingAddress['city'],
                $shippingAddress['zipcode'],
                $shippingAddress['countryId'],
                $countryCode,
                $company
            );
        }
        if (!array_key_exists('addresses', $convertedCart)) {
            throw new Exception('No billing address are registered for this order');
        }
        $addresses = $convertedCart['addresses'];
        if (count($addresses) === 0) {
            throw new Exception('Billing addresses data is missing from the cart data');
        }
        if (!array_key_exists(0, $addresses)) {
            throw new Exception('No billing addresses are registered for this order');
        }
        $company = null;
        if (array_key_exists('company', $addresses[0])) {
            $company = $addresses[0]['company'];
        }
        $countryCode = $this->getCountryCodeByCountryId($addresses[0]['countryId'], $context);
        return new BillingAddress(
            $addresses[0]['lastName'],
            $addresses[0]['firstName'],
            $addresses[0]['street'],
            $addresses[0]['city'],
            $addresses[0]['zipcode'],
            $addresses[0]['countryId'],
            $countryCode,
            $company
        );
    }

    /**
     * @throws Exception
     */
    private function getCountryCodeByCountryId(string $countryId, Context $context): string
    {
        $country = $this->countryRepository->search((new Criteria([$countryId])), $context)->first();
        if (!$country instanceof CountryEntity) {
            throw new Exception(sprintf('CrifCheckoutValidator: Country with id: %s is not found', $countryId));
        }
        return $country->getIso();
    }

    private function isCountryConcerned($countryId): bool
    {
        $countriesToCheck = $this->generalConfig->getCheckSpecificCountryIds();
        return in_array($countryId, $countriesToCheck);
    }
}