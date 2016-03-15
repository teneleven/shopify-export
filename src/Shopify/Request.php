<?php

namespace Shopify;

/**
 * Shopify API request.
 */
class Request
{
    private $method;
    private $endpoint;
    private $params;

    public function __construct($method = 'GET', $endpoint, $params = [])
    {
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    public function nextPage()
    {
        if (array_key_exists('form_params', $this->params) && array_key_exists('page', $this->params['form_params'])) {
            $this->params['form_params']['page'] ++;
        } elseif (array_key_exists('page', $this->params)) {
            $this->params['page'] ++;
        } else {
            throw new \RuntimeException('Paginator cannot find page request param');
        }
    }
}
