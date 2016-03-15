<?php

namespace Shopify\Exporter;

use Shopify\Shopify;
use GuzzleHttp\Exception\ClientException;

/**
 * Exports pages from shopify store -> shopify store.
 */
class ProductExporter extends AbstractExporter
{
    public function export(Shopify $to)
    {
        $paginator = $this->from->getProducts();

        while ($paginator->hasResults()) {
            $products = $paginator->getResponse()->getResults();
            foreach ($products as $product) {
                $to->createOrUpdateProduct($product);
            }

            $paginator->nextPage();
        }
    }
}
