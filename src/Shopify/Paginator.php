<?php

namespace Shopify;

use GuzzleHttp\ClientInterface;

/**
 * Wraps Shopify API results with pagination.
 */
class Paginator
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array|null
     */
    private $results;

    public function __construct(ClientInterface $client, Request $request)
    {
        $this->client = $client;
        $this->request = $request;
    }

    public function __toString()
    {
        return $this->getResults();
    }

    public function getResponse()
    {
        return new Response($this->client->request(
            $this->request->getMethod(),
            $this->request->getEndpoint(),
            $this->request->getParams()
        ));
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResults()
    {
        if ($this->results) {
            return $this->results;
        }

        return $this->getResponse()->getResults();
    }

    public function hasResults()
    {
        return count($this->results) || count($this->getResults());
    }

    public function nextPage()
    {
        $this->request->nextPage();
    }

    /**
     * Lookup specific result by key.
     */
    public function getResult($key, $value)
    {
        if (null === $this->results) {
            $this->fetchResults();
        }

        foreach ($this->results as $result) {
            if (isset($result->$key) && $result->$key === $value) {
                return $result;
            }
        }
    }

    /**
     * Paginate entire listing. This is expensive, but currently only way to search resources.
     */
    public function fetchResults()
    {
        if (null === $this->results) {
            $this->results = [];
        }

        if (count($results = $this->getResponse()->getResults())) {
            $this->results += $results;
            sleep(0.5); // avoid rate limit
            $this->nextPage();

            return $this->fetchResults();
        }

        return $this->results;
    }
}
