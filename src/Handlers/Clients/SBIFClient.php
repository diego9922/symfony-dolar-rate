<?php

namespace App\Handlers\Clients;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;

final class SBIFClient
{
    private const ENDPOINT_BY_YEAR_MONTH = "/api-sbifv3/recursos_api/dolar/<year>/<month>";
    private const YEAR_RAPLACE = "<year>";
    private const MONTH_RAPLACE = "<month>";
    private const FORMAT = "json";
    
    private const PARAM_API_KEY = "apikey";
    private const PARAM_FORMAT = "formato";

    private const RESPONSE_DOLLARS = "Dolares";

    private string $domain;
    private array $standarQueryParams;

    public function __construct(
        private HttpClientInterface $client,
    ) {
        $this->domain = $_ENV['SBIF_DOMAIN'];
        $this->standarQueryParams = [
            self::PARAM_API_KEY => $_ENV['SBIF_API_KEY'] ,
            self::PARAM_FORMAT => self::FORMAT
        ];
    }

    public function getDolarRatesByYearAndMonth(int $year, int $month): array
    {
        $endpoint = str_replace(
            [self::YEAR_RAPLACE, self::MONTH_RAPLACE],
            [$year, $month],
            self::ENDPOINT_BY_YEAR_MONTH
        );
        $response = $this->client->request(
            Request::METHOD_GET,
            $this->domain.$endpoint,
            [
                'query' => [
                    ...$this->standarQueryParams
                ],
            ]
        );

        if ($response->getStatusCode() != 200) {
            return [];
        }

        return $response->toArray()[self::RESPONSE_DOLLARS];
    }
}
