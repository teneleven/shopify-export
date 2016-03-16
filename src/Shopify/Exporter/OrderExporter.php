<?php

namespace Shopify\Exporter;

use Shopify\Shopify;

/**
 * Exports order from shopify store -> shopify store.
 */
class OrderExporter extends AbstractExporter
{
    public function export(Shopify $to)
    {
        $paginator = $this->from->getOrders();

        while ($paginator->hasResults()) {
            $orders = $paginator->getResponse()->getResults();
            foreach ($orders as $order) {
                $to->createOrUpdateOrder($order);
            }

            $paginator->nextPage();
        }
    }
}
