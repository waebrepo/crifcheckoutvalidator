<?php

declare(strict_types=1);

namespace Geschenkkoerbe\CrifCheckoutValidator\Service;

use Geschenkkoerbe\CrifCheckoutValidator\Enum\Decision;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class CrifApi
{
    private GeneralConfig $generalConfig;

    public function __construct(
        GeneralConfig $generalConfig
    ) {
        $this->generalConfig = $generalConfig;
    }

    /**
     * @throws \Exception
     */
    public function checkNaturalPerson(BillingAddress $billingAddress, string $sex, string $reportType, string $targetReportFormat): bool {
        $baseUrl = $this->generalConfig->getBaseUrl();
        $url = $baseUrl . '/reports/personAddress';

        $username = $this->generalConfig->getUserName();
        $password = $this->generalConfig->getPassword();

        $auth = base64_encode($username . ':' . $password);

        $client = new Client();

        $headers = [
            'api-version' => '1',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $auth
        ];
        $body = [
            'personIdentity' => [
                'lastName' => $billingAddress->getLastName(),
                'firstName' => $billingAddress->getFirstName(),
                'sex' => $sex,
                'postalAddress' => [
                    'street' => $billingAddress->getStreet(),
                    'zip' => $billingAddress->getZip(),
                    'city' => $billingAddress->getCity(),
                    'country' => $billingAddress->getCountryIsoCode()
                ],
            ],
            'reportType' => $reportType,
            'targetReportFormat' => $targetReportFormat
        ];

        $bodyJson = json_encode($body);

        $request = new Request('POST', $url, $headers, $bodyJson);
        $response = $client->sendAsync($request)->wait();

        $responseBody = $response->getBody()->getContents();
        $responseData = json_decode($responseBody);

        if (isset($responseData->decisionMatrix->decision)) {
            return $responseData->decisionMatrix->decision == Decision::RED;
        } else {
            return false;
        }
    }
}