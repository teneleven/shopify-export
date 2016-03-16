<?php

namespace Shopify\Api;

use Shopify\Request;

class PageRequest extends Request
{
    protected function filterParams(array $params)
    {
        return ['json' => ['page' => $params]];
    }
}
