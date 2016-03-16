<?php

namespace Shopify\Exporter;

use Shopify\Shopify;

/**
 * Recursively exports an entire shopify store.
 */
class ShopifyExporter extends AbstractExporter
{
    private $exporters;

    /**
     * @param Shopify $from
     * @param array   $exporters ordered Exporter classes to export
     */
    public function __construct(Shopify $from, $exporters = [
        BlogExporter::class,
        ArticleExporter::class,
        PageExporter::class,
        CustomerExporter::class,
        OrderExporter::class,
    ]) {
        parent::__construct($from);

        $this->exporters = $exporters;
    }

    public function export(Shopify $to)
    {
        foreach ($this->exporters as $exporterClass) {
            if (is_object($exporterClass)) {
                $exporter = $exporterClass;
            } else {
                $exporter = new $exporterClass($this->from);
            }

            /** @var Exporter $exporter */
            $exporter->export($to);

            gc_collect_cycles();
        }
    }
}
