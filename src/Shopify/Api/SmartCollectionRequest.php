<?php

namespace Shopify\Api;

use Shopify\Request;

class SmartCollectionRequest extends Request
{
    protected function filterParams(array $params)
    {
        if ($this->method === 'GET') {
            return parent::filterParams($params);
        }

        if (!array_key_exists('smart_collection', $params)) {
            $collectionParams = $params;
        } else {
            $collectionParams = $params['smart_collection'];
        }

        if (isset($collectionParams['rules']) && !is_array($collectionParams['rules'])) {
            $collectionParams['rules'] = (array) $collectionParams['rules'];
        }

        return ['json' => ['smart_collection' => $collectionParams]];
    }
}
