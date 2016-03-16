<?php

namespace Shopify\Api;

use Shopify\Request;

class OrderRequest extends Request
{
    protected function filterParams(array $params)
    {
        if ($this->method === 'GET') {
            return parent::filterParams($params);
        }

        if (!array_key_exists('order', $params)) {
            $orderParams = $params;
        } else {
            $orderParams = $params['order'];
        }

        if (isset($orderParams['source_name'])) {
            unset($orderParams['source_name']);
        }

        // line_items should contain tax info
        if (isset($orderParams['tax_lines'])) {
            unset($orderParams['tax_lines']);
        }

        if (isset($orderParams['user_id'])) {
            unset($orderParams['user_id']);
        }

        // reset relations
        $orderParams['fulfillments'] = $this->resetRelations($orderParams['fulfillments']);
        $orderParams['line_items'] = $this->resetRelations($orderParams['line_items']);
        $orderParams['shipping_lines'] = $this->resetRelations($orderParams['shipping_lines']);
        $orderParams['customer'] = $this->resetRelation($orderParams['customer']);

        // FIXME fulfillments getting 404 error
        if (isset($orderParams['fulfillments'])) {
            unset($orderParams['fulfillments']);
        }

        return ['json' => ['order' => $orderParams]];
    }

    private function resetRelations(array $objects)
    {
        foreach ($objects as $key => $value) {
            $objects[$key] = $this->resetRelation($value);
        }

        return $objects;
    }

    private function resetRelation($object)
    {
        if (isset($object->order_id)) {
            unset($object->order_id);
        }

        if (isset($object->id)) {
            unset($object->id);
        }

        if (isset($object->product_id)) {
            unset($object->product_id);
        }

        if (isset($object->variant_id)) {
            unset($object->variant_id);
        }

        if (isset($object->last_order_id)) {
            unset($object->last_order_id);
        }

        if (isset($object->line_items)) {
            $object->line_items = $this->resetRelations($object->line_items);
        }

        if (isset($object->default_address)) {
            $object->default_address = $this->resetRelation($object->default_address);
        }

        if (isset($object->addresses)) {
            $object->addresses = $this->resetRelations($object->addresses);
        }

        if (isset($object->destination_location)) {
            $object->destination_location = $this->resetRelation($object->destination_location);
        }

        if (isset($object->origin_location)) {
            $object->origin_location = $this->resetRelation($object->origin_location);
        }

        return (array) $object;
    }
}
