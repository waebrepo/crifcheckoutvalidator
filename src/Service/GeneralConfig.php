<?php

declare(strict_types=1);

namespace Geschenkkoerbe\CrifCheckoutValidator\Service;

use Exception;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class GeneralConfig
{
    private const COUNTRIES_TO_CHECK = 'CrifCheckoutValidator.config.countriesToCheck';
    private const PAYMENT_METHODS_TO_CHECK = 'CrifCheckoutValidator.config.paymentMethodsToCheck';
    private const IS_ENABLED = 'CrifCheckoutValidator.config.enabled';
    private const BASE_URL = 'CrifCheckoutValidator.config.baseUrl';
    private const USERNAME = 'CrifCheckoutValidator.config.username';
    private const PASSWORD = 'CrifCheckoutValidator.config.password';

    private SystemConfigService $configService;

    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * @throws Exception
     */
    public function getBaseUrl(): string
    {
        $login = $this->configService->get(self::BASE_URL);
        if ($login === null) {
            throw new Exception('The CrifCheckoutValidator configuration "baseUrl" is missing');
        }
        return $login;
    }

    /**
     * @throws Exception
     */
    public function getUserName(): string
    {
        $login = $this->configService->get(self::USERNAME);
        if ($login === null) {
            throw new Exception('The CrifCheckoutValidator configuration "username" is missing');
        }
        return $login;
    }

    /**
     * @throws Exception
     */
    public function getPassword(): string
    {
        $password = $this->configService->get(self::PASSWORD);
        if ($password === null) {
            throw new Exception('The CrifCheckoutValidator configuration "password" is missing');
        }
        return $password;
    }

    public function getCheckSpecificCountryIds(): array
    {
        $countries = $this->configService->get(self::COUNTRIES_TO_CHECK);
        if ($countries === null || count($countries) === 0) {
            return [];
        }
        return $countries;
    }

    public function getPaymentMethodsToCheck(): array
    {
        $paymentMethods = $this->configService->get(self::PAYMENT_METHODS_TO_CHECK);
        if ($paymentMethods === null || count($paymentMethods) === 0) {
            return [];
        }
        return $paymentMethods;
    }

    public function getIsEnabled(): bool
    {
        return $this->configService->get(self::IS_ENABLED) ? $this->configService->get(self::IS_ENABLED) : false;
    }
}
