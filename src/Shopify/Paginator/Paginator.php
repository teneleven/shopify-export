<?php

namespace Shopify\Paginator;

use GuzzleHttp\ClientInterface;
use Shopify\Request;
use Shopify\Response;

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
        return Response::fromApiResponse($this->client->request(
            $this->request->getMethod(),
            $this->request->getEndpoint(),
            $this->request->getParams()
        ));
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function hasResults()
    {
        return count($this->getResponse()->getResults());
    }

    public function nextPage()
    {
        $this->request->nextPage();
    }
}
