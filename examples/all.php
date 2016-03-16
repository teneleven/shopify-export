<?php

/**
 * Example showing how to export shopify store articles -> another shopify store articles.
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/config.php';

$from = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => FROM_SHOPIFY_API_URL]));
$to = new Shopify\Shopify(new GuzzleHttp\Client(['base_uri' => TO_SHOPIFY_API_URL]));

$exporter = new Shopify\Exporter\ShopifyExporter($from, [
    Shopify\Exporter\BlogExporter::class,
    Shopify\Exporter\ArticleExporter::class,
    Shopify\Exporter\PageExporter::class,
    Shopify\Exporter\CustomerExporter::class,
    Shopify\Exporter\OrderExporter::class,
]);

$exporter->export($to);

echo "Done!";
