<?php

namespace Shopify;

use Psr\Http\Message\ResponseInterface as ApiResponse;

/**
 * Wraps Shopify API response.
 */
class Response
{
    /**
     * @var mixed
     */
    private $results;

    public function __construct($results)
    {
        $this->results = $results;
    }

    public static function fromApiResponse(ApiResponse $response)
    {
        $results = (array) json_decode((string) $response->getBody());

        if (!count($results)) {
            return new static([]);
        }

        return new static(array_shift($results));
    }

    public function __toString()
    {
        return json_encode($this->results);
    }

    public function getResults()
    {
        return $this->results;
    }

    /**
     * Lookup specific result by key.
     */
    public function getResult($key, $value)
    {
        if (!is_array($this->results)) {
            return;
        }

        foreach ($this->results as $result) {
            if (isset($result->$key) && $result->$key === $value) {
                return $result;
            }
        }
    }
}
