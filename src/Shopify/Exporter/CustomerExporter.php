<?php

namespace Shopify\Exporter;

use Shopify\Shopify;

/**
 * Exports customer from shopify store -> shopify store.
 */
class CustomerExporter extends AbstractExporter
{
    public function export(Shopify $to)
    {
        $paginator = $this->from->getCustomers();

        while ($paginator->hasResults()) {
            $customers = $paginator->getResponse()->getResults();
            foreach ($customers as $customer) {
                $to->createOrUpdateCustomer($customer);
            }

            $paginator->nextPage();
        }
    }
}
