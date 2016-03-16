<?php

/**
 * Example showing how to export shopify store collections -> another shopify store collections.
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/config.php';

$from = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => FROM_SHOPIFY_API_URL]));
$to = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => TO_SHOPIFY_API_URL]));

$collectionExporter = new Shopify\Exporter\ProductCollectionExporter($from);
$collectionExporter->export($to);

echo "Done!";
