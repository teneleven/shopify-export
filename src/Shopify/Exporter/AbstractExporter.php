<?php

namespace Shopify\Exporter;

use Shopify\Shopify;

/**
 * Exports blogs from shopify store -> shopify store.
 */
abstract class AbstractExporter implements Exporter
{
    /**
     * @var Shopify
     */
    protected $from;

    public function __construct(Shopify $from)
    {
        $this->from = $from;
    }

    abstract public function export(Shopify $to);
}
