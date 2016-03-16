<?php

namespace Shopify\Exporter;

use Shopify\Shopify;

/**
 * Simple Shopify exporter interface.
 */
interface Exporter
{
    /**
     * @param Shopify $to shopify site to export into
     *
     * @return void
     */
    public function export(Shopify $to);
}
