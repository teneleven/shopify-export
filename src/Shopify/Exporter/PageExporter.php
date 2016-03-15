<?php

namespace Shopify\Exporter;

use Shopify\Shopify;

/**
 * Exports pages from shopify store -> shopify store.
 */
class PageExporter extends AbstractExporter
{
    public function export(Shopify $to)
    {
        $paginator = $this->from->getPages();

        while ($paginator->hasResults()) {
            $pages = $paginator->getResponse()->getResults();
            foreach ($pages as $page) {
                $to->createOrUpdatePage($page);
            }

            $paginator->nextPage();
        }
    }
}
