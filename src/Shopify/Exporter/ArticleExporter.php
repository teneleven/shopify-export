<?php

namespace Shopify\Exporter;

use Shopify\Shopify;
use GuzzleHttp\Exception\ClientException;

/**
 * Exports articles from shopify store -> shopify store.
 */
class ArticleExporter extends AbstractExporter
{
    public function export(Shopify $to)
    {
        $blogs = $this->from->getBlogs();

        foreach ($blogs as $blog) {
            if (!isset($blog->id)) {
                throw new \RuntimeException('No blog ID index found');
            }

            $this->exportBlog($blog, $to);
        }
    }

    public function exportBlog($blog, Shopify $to)
    {
        // lookup blog ID
        $toBlogs = $to->getBlogs();
        $toBlogId = null;
        foreach ($toBlogs as $toBlog) {
            if ($toBlog->title === $blog->title) {
                $toBlogId = $toBlog->id;
                break;
            }
        }

        if (null === $toBlogId) {
            throw new \RuntimeException('Could not find blog with title "'.$blog->title.'"');
        }

        echo "Exporting blog {$blog->title}, from ID: {$blog->id}, to ID: {$toBlogId}\n";

        $paginator = $this->from->getArticles($blog->id);
        while ($paginator->hasResults()) {
            $articles = $paginator->getResults();
            foreach ($articles as $article) {
                $to->createOrUpdateArticle($toBlogId, $article);
            }

            $paginator->nextPage();
        }
    }
}
