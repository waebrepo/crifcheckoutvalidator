<?php
/**
 * Diglin GmbH - Switzerland.
 *
 * @author    Gautier Masdupuy <support@diglin.com>
 * @category  shopware6-crif-rest-api
 * @copyright 2021 - Diglin (https://www.diglin.com)
 */

declare(strict_types=1);

namespace Geschenkkoerbe\CrifCheckoutValidator\Service;

use Geschenkkoerbe\CrifCheckoutValidator\Enum\Sex;

final class BillingAddress
{
    private string $lastName;
    private string $firstName;
    private string $street;
    private string $zip;
    private string $countryIsoCode;
    private ?string $company;
    private string $city;
    private string $sex;
    private string $countryId;

    public function __construct(
        string $lastName,
        string $firstName,
        string $street,
        string $city,
        string $zip,
        string $countryId,
        string $countryIsoCode,
        ?string $company
    ) {
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->street = $street;
        $this->zip = $zip;
        $this->countryIsoCode = $countryIsoCode;
        $this->countryId = $countryId;
        $this->company = $company;
        $this->city = $city;
        $this->sex = Sex::UNKNOWN;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @return string
     */
    public function getCountryIsoCode(): string
    {
        return $this->countryIsoCode;
    }

    /**
     * @return string
     */
    public function getCountryId(): string
    {
        return $this->countryId;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

}
