<?php

namespace Shopify;

/**
 * Simple shopify product exporter class, meant to used to export from SQL databse -> Shopify store.
 *
 * To use, initialize using constructor to set DB config, then call the export method with the SQL & params to export to
 * shopify as args.
 */
class ShopifyProductExporter
{
    /**
     * Standard shopify header.
     *
     * @see: https://docs.shopify.com/manual/your-store/products/product-csv
     *
     * @var array
     */
    protected $header = array(
        'Handle',
        'Title',
        'Body (HTML)',
        'Vendor',
        'Type',
        'Collection',
        'Tags',
        'Published',
        'Option1 Name',
        'Option1 Value',
        'Option2 Name',
        'Option2 Value',
        'Option3 Name',
        'Option3 Value',
        'Variant SKU',
        'Variant Grams',
        'Variant Inventory Tracker',
        'Variant Inventory Qty',
        'Variant Inventory Policy',
        'Variant Fulfillment Service',
        'Variant Price',
        'Variant Compare At Price',
        'Variant Requires Shipping',
        'Variant Taxable',
        'Variant Barcode',
        'Image Src',
        'Image Alt Text',
        'Gift Card',
        'Google Shopping / MPN',
        'Google Shopping / Age Group',
        'Google Shopping / Gender',
        'Google Shopping / Google Product Category',
        'SEO Title',
        'SEO Description',
        'Google Shopping / AdWords Grouping',
        'Google Shopping / AdWords Labels',
        'Google Shopping / Condition',
        'Google Shopping / Custom Product',
        'Google Shopping / Custom Label 0',
        'Google Shopping / Custom Label 1',
        'Google Shopping / Custom Label 2',
        'Google Shopping / Custom Label 3',
        'Google Shopping / Custom Label 4',
        'Variant Image',
        'Variant Weight Unit',
    );

    protected $db;
    protected $callback;

    /**
     * @param string $filename csv filename to export to
     * @param array  $config   database configuration setup with keys:
     *                         host => database host
     *                         database => database name
     *                         username => database user
     *                         password => database password
     */
    public function __construct($filename, array $config)
    {
        $this->db = new PDO(
            "mysql:host={$config['host']};dbname={$config['database']}",
            $config['username'],
            $config['password'],
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );

        $this->writer = \League\Csv\Writer::createFromPath($filename, 'w');
    }

    /**
     * @param mixed $sql     SQL to execute and use for import. Use column aliases as value in param below to properly
     *                       map values to the spreadsheet.
     * @param array $mapping Mappings of Shopify column header to SQL alias. Use Option# for options.
     *
     *                       e.g. [
     *                           'Handle' => 'slug',
     *                           'Title' => 'title',
     *                           'Option1' => 'width',
     *                           'Option2' => 'height',
     *                       ]
     *
     * @see self::parseRow()
     */
    public function export($sql, array $mapping)
    {
        $stmt = $this->db->query($sql);

        $this->writer->insertOne($this->header);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $parsedRow = $this->parseRow($row, $mapping);

            // handle multiple images
            if (is_array($parsedRow['Image Src'])) {
                foreach ($parsedRow['Image Src'] as $i => $imageSrc) {
                    if ($i === 0) {
                        $this->writer->insertOne(array_merge(
                            $parsedRow,
                            array(
                                'Image Src' => $imageSrc,
                                'Variant Image' => $imageSrc,
                            )
                        ));

                        continue;
                    }

                    $row = array();
                    foreach ($parsedRow as $key => $value) {
                        $row[$key] = null;

                        if ($key === 'Handle') {
                            $row[$key] = $value;
                        } elseif (in_array($key, array('Image Src', 'Variant Image'))) {
                            $row[$key] = $imageSrc;
                        }
                    }

                    $this->writer->insertOne($row);
                }
            } else {
                $this->writer->insertOne($parsedRow);
            }
        }
    }

    /**
     * Add row callback. Callback has 2 parameters: $parsedRow and $row (DB results). The callback
     * should return an array to insert into the CSV.
     *
     * @param $callback
     */
    public function addRowCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return PDO
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param array $row     database row
     * @param array $mapping column header => SQL alias
     *
     * @throws \Exception
     * @return array
     *
     */
    protected function parseRow(array $row, array $mapping)
    {
        $parsed = array_combine(array_values($this->header), array_fill(0, count($this->header), null));

        foreach ($mapping as $header => $column) {
            // expand Option fields
            if (preg_match('/^Option ?(\d*)$/', $header, $matches)) {
                $parsed['Option'.$matches[1].' Name'] = $column;
                $parsed['Option'.$matches[1].' Value'] = trim($row[$column]);

                continue;
            }

            if (!array_key_exists($header, $parsed)) {
                continue;
            }

            $parsed[$header] = trim($row[$column]);
        }

        // manipulate row with callback
        if ($callback = $this->callback) {
            return $callback($parsed, $row);
        }

        return $parsed;
    }
}
