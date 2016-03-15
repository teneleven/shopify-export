<?php

namespace Shopify\Api;

use Shopify\Request;

class ProductRequest extends Request
{
    protected function filterParams(array $params)
    {
        if ($this->method === 'GET') {
            return parent::filterParams($params);
        }

        if (!array_key_exists('product', $params)) {
            $productParams = $params;
        } else {
            $productParams = $params['product'];
        }

        if (isset($productParams['variants'])) {
            // remove relations from variants
            foreach ($productParams['variants'] as $key => $variant) {
                $variant = (array) $variant;

                if (isset($variant['fulfillment_service'])) {
                    unset($variant['fulfillment_service']);
                }

                if (isset($variant['product_id'])) {
                    unset($variant['product_id']);
                }

                if (isset($variant['image_id'])) {
                    unset($variant['image_id']);
                }

                $productParams['variants'][$key] = $variant;
            }
        }

        if (isset($productParams['images'])) {
            // remove relations from images
            foreach ($productParams['images'] as $key => $image) {
                $image = (array) $image;

                if (isset($image['product_id'])) {
                    unset($image['product_id']);
                }

                if (isset($image['image_id'])) {
                    unset($image['image_id']);
                }

                $productParams['images'][$key] = $image;
            }
        }

        if (isset($productParams['image'])) {
            // remove relations from image
            $image = (array) $productParams['image'];

            if (isset($image['product_id'])) {
                unset($image['product_id']);
            }

            if (isset($image['image_id'])) {
                unset($image['image_id']);
            }

            $productParams['image'] = $image;
        }

        if (isset($productParams['options'])) {
            // remove relations from options
            foreach ($productParams['options'] as $key => $option) {
                $option = (array) $option;

                if (isset($option['product_id'])) {
                    unset($option['product_id']);
                }

                if (isset($option['option_id'])) {
                    unset($option['option_id']);
                }

                $productParams['options'][$key] = $option;
            }
        }

        return ['json' => ['product' => $productParams]];
    }
}
