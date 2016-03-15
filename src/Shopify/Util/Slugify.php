<?php

namespace Shopify\Util;

class Slugify
{
    /**
     * Simple slugify method.
     */
    public static function slugify($text)
    {
        // white space
        $text = str_replace(' ', '-', $text);

        // replace non letter or digits
        $text = preg_replace('~[^\\pL\d-]+~u', '', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
