<?php

namespace Shopify;

/**
 * Shopify API request.
 */
class Request
{
    protected $method;
    protected $endpoint;
    protected $params;

    public function __construct($method = 'GET', $endpoint, $params = [])
    {
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->params = $this->filterParams($params);
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
            ++$this->params['form_params']['page'];
        } elseif (array_key_exists('page', $this->params)) {
            ++$this->params['page'];
        } else {
            throw new \RuntimeException('No page request param found in Request');
        }
    }

    /**
     * Override in child class.
     */
    protected function filterParams(array $params)
    {
        if (array_key_exists('form_params', $params)) {
            return $params;
        }

        return ['form_params' => $params];
    }
}
