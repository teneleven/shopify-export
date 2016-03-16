<?php

namespace Shopify\Paginator;

use Shopify\Request;
use Shopify\Response;

/**
 * Walks a Paginator, and turns it into a Response with the full resultset.  Paginate entire listing. This is fairly
 * expensive, but currently only way to search resources in Shopify (other than ID lookup).
 */
class PaginationWalker
{
    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var mixed
     */
    private $results;

    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->paginator->getRequest();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if (null === $this->results) {
            $this->fetchResults();
        }

        return new Response($this->results);
    }

    /**
     * Paginate entire listing. This is expensive, but currently only way to search resources.
     */
    public function fetchResults()
    {
        if (null === $this->results) {
            $this->results = [];
        }

        if (count($results = $this->paginator->getResponse()->getResults())) {
            $this->results = array_merge($this->results, $results);
            usleep(500000); // wait half second to avoid rate limit
            $this->paginator->nextPage();

            return $this->fetchResults();
        }

        return $this->results;
    }
}
