<?php

namespace Shopify\Exporter;

use Shopify\Shopify;
use GuzzleHttp\Exception\ClientException;

/**
 * Exports blogs from shopify store -> shopify store.
 */
class ProductCollectionExporter extends AbstractExporter
{
    public function export(Shopify $to)
    {
        // regular collections
        $collections = $this->from->getCollections()->getResponse()->getResults();
        foreach ($collections as $collection) {
            $to->createOrUpdateCollection($collection);
        }

        // smart collections
        $collections = $this->from->getSmartCollections()->getResponse()->getResults();
        foreach ($collections as $collection) {
            $to->createOrUpdateSmartCollection($collection);
        }

        // product -> collection resource
        $collects = $this->from->walk($this->from->getCollects())->getResults();
// need to lookup to product/collection ID
        $fromProducts = $this->from->walk($this->from->getProducts());
        $fromCollections = $this->from->walk($this->from->getCollections());
        $toProducts = $to->walk($to->getProducts());
        $toCollections = $to->walk($to->getCollections());

        // sync collects
        foreach ($collects as $collect) {
            $fromProduct = $fromProducts->getResult('id', $collect->product_id);
            $fromCollection = $fromCollections->getResult('id', $collect->collection_id);

            if (!$fromProduct || !$fromCollection) {
                // echo 'Unable to find product/collection in FROM result set. Collection ID: '.$collect->collection_id.', Product ID: '.$collect->product_id."\n";

                continue;
            }

            $product = $toProducts->getResult('handle', $fromProduct->handle);
            $collection = $toCollections->getResult('handle', $fromCollection->handle);

            if (!$product || !$collection) {
                // echo 'Unable to find product/collection in TO result set. Collection ID: '.$collect->collection_id.', Product ID: '.$collect->product_id."\n";

                continue;
            }

            echo "Syncing relationship, Product: {$product->title} {$product->id} -> Collection: {$collection->title} {$collection->id}\n";

            try {
                $to->createCollect([
                    'product_id' => $product->id,
                    'collection_id' => $collection->id,
                ]);
            } catch (ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 422) {
                    // duplicate collections throw error 422
                    // echo "Error: ".$e->getResponse()->getBody()."\n";

                    continue;
                } else {
                    throw $e;
                }
            }

        }
    }
}
