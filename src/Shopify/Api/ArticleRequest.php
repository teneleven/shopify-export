<?php

namespace Shopify\Api;

use Shopify\Request;

class ArticleRequest extends Request
{
    protected function filterParams(array $params)
    {
        if (isset($params['blog_id'])) {
            unset($params['blog_id']);
        }

        if (isset($params['id'])) {
            unset($params['id']);
        }

        return ['json' => ['article' => $params]];
    }
}
