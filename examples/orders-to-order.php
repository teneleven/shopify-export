<?php

/**
 * Example showing how to export shopify store articles -> another shopify store articles.
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/config.php';

$from = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => FROM_SHOPIFY_API_URL]));
$to = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => TO_SHOPIFY_API_URL]));

$exporter = new Shopify\Exporter\OrderExporter($from);
$exporter->export($to);

echo "Done!";
