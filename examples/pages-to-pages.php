<?php

/**
 * Example showing how to export shopify store pages -> another shopify store pages.
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/config.php';

$from = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => FROM_SHOPIFY_API_URL]));
$to = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => TO_SHOPIFY_API_URL]));

$pageExporter = new Shopify\Exporter\PageExporter($from);
$pageExporter->export($to);

echo "Done!";
