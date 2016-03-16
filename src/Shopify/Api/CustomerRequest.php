<?php

namespace Shopify\Api;

use Shopify\Request;

class CustomerRequest extends Request
{
    protected function filterParams(array $params)
    {
        if ($this->method === 'GET') {
            return parent::filterParams($params);
        }

        if (!array_key_exists('customer', $params)) {
            $customerParams = $params;
        } else {
            $customerParams = $params['customer'];
        }

        if (isset($customerParams['addresses']) && count($customerParams['addresses'])) {
            foreach ($customerParams['addresses'] as $key => $address) {
                if (isset($address->id)) {
                    unset($address->id);
                }

                $customerParams['addresses'][$key] = (array) $address;
            }
        } else {
            unset($customerParams['addresses']);
        }

        return ['json' => ['customer' => $customerParams]];
    }
}
