<?php

namespace Shopify;

use Psr\Http\Message\ResponseInterface as ApiResponse;

/**
 * Wraps Shopify API response.
 */
class Response
{
    /**
     * @var ApiResponse
     */
    private $apiResponse;

    public function __construct(ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function __toString()
    {
        return (string) $this->apiResponse->getBody();
    }

    public function getResults()
    {
        $results = (array) json_decode((string) $this->apiResponse->getBody());

        if (!count($results)) {
            return [];
        }

        return array_shift($results);
    }
}
