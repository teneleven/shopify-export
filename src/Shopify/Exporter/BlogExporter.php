<?php

namespace Shopify\Exporter;

use Shopify\Shopify;

/**
 * Exports blogs from shopify store -> shopify store.
 */
class BlogExporter extends AbstractExporter
{
    public function export(Shopify $to)
    {
        $fromBlogs = $this->format($this->from->getBlogs());
        $toBlogs = $this->format($to->getBlogs());

        $diff = array_diff($fromBlogs, $toBlogs);

        foreach ($diff as $id => $title) {
            $to->createBlog($title);
        }
    }

    private function format(array $blogs)
    {
        $formattedBlogs = [];
        foreach ($blogs as $blog) {
            if (!isset($blog->id)) {
                throw new \RuntimeException('No blog ID index found');
            }

            if (!isset($blog->title)) {
                throw new \RuntimeException('No blog ID index found');
            }

            $formattedBlogs[$blog->id] = $blog->title;
        }

        return $formattedBlogs;
    }
}
