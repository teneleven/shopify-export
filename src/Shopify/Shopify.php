<?php

namespace Shopify;

use GuzzleHttp\ClientInterface;

/**
 * Shopify api wrapper.
 */
class Shopify
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Response
     */
    private $cachedResponse;

    /**
     * @var Request
     */
    private $cachedRequest;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getBlogs()
    {
        $request = new Request('GET', 'blogs.json');

        return $this->submitRequest($request);
    }

    public function getBlog($blogID)
    {
        $request = new Request('GET', 'blogs/'.$blogID.'.json');

        return $this->submitRequest($request);
    }

    public function createBlog($title)
    {
        $request = new Request('POST', 'blogs.json', [
            'form_params' => ['blog' => ['title' => $title]],
        ]);

        return $this->submitRequest($request);
    }

    public function getArticles($blogID, $page = 1)
    {
        $request = new Request('GET', 'blogs/'.$blogID.'/articles.json', [
            'form_params' => [
                'limit' => 250,
                'page' => $page,
            ],
        ]);

        return $this->paginate($request);
    }

    public function getArticle($blogID, $articleID)
    {
        $request = new Request('GET', 'blogs/'.$blogID.'/articles/'.$articleID.'.json');

        return $this->submitRequest($request);
    }

    public function createOrUpdateArticle($blogID, $article)
    {
        if ($result = $this->getArticlesResponse($blogID)->getResult('title', $article->title)) {
            // update
            echo "Resource found - {$result->title} {$result->id}\n";

            return $this->updateArticle($blogID, $result->id, $article);
        }

        // create
        echo "Resource not found, creating - {$article->title}\n";

        return $this->createArticle($blogID, $article);
    }

    public function createArticle($blogID, $article)
    {
        $params = $this->toParams('article', $article);

        if (isset($params['article']['blog_id'])) {
            unset($params['article']['blog_id']);
        }

        $request = new Request('POST', 'blogs/'.$blogID.'/articles.json', [
            'json' => $params,
        ]);

        return $this->submitRequest($request);
    }

    public function updateArticle($blogID, $articleID, $article)
    {
        $params = $this->toParams('article', $article);

        if (isset($params['article']['blog_id'])) {
            unset($params['article']['blog_id']);
        }

        $params['article']['id'] = $articleID;
        $params['article']['blog_id'] = $blogID;

        $request = new Request('PUT', 'blogs/'.$blogID.'/articles/'.$articleID.'.json', [
            'json' => $params,
        ]);

        return $this->submitRequest($request);
    }

    public function createOrUpdatePage($page)
    {
        if ($result = $this->getPagesResponse()->getResult('handle', $page->handle)) {
            // update
            echo "Resource found - {$result->title} {$result->id}\n";

            return $this->updatePage($result->id, $page);
        }

        // create
        echo "Resource not found, creating - {$page->title}\n";

        return $this->createPage($page);
    }

    public function getPages($page = 1)
    {
        $request = new Request('GET', 'pages.json', [
            'form_params' => [
                'limit' => 250,
                'page' => $page,
            ],
        ]);

        return $this->paginate($request);
    }

    public function createPage($page)
    {
        $request = new Request('POST', 'pages.json', [
            'form_params' => $this->toParams('page', $page),
        ]);

        return $this->submitRequest($request);
    }

    public function updatePage($pageID, $page)
    {
        $request = new Request('PUT', 'pages/'.$pageID.'.json', [
            'form_params' => $this->toParams('page', $page),
        ]);

        return $this->submitRequest($request);
    }

    public function getProducts($page = 1)
    {
        $request = new Request('GET', 'products.json', [
            'form_params' => [
                'limit' => 250,
                'page' => $page,
            ],
        ]);

        return $this->paginate($request);
    }

    public function createProduct($product)
    {
        $request = new Request('POST', 'customers.json', [
            'form_params' => $this->toParams('product', $product),
        ]);

        return $this->submitRequest($request);
    }

    public function updateProduct($productID, $product)
    {
        $request = new Request('PUT', 'products/'.$productID.'.json', [
            'form_params' => $this->toParams('product', $product),
        ]);

        return $this->submitRequest($request);
    }

    public function getCustomers($page = 1)
    {
        $request = new Request('GET', 'customers.json', [
            'form_params' => [
                'limit' => 250,
                'page' => $page,
            ],
        ]);

        return $this->paginate($request);
    }

    public function createCustomer($customer)
    {
        $request = new Request('POST', 'customers.json', [
            'form_params' => $this->toParams('customer', $customer),
        ]);

        return $this->submitRequest($request);
    }

    public function updateCustomer($customerID, $customer)
    {
        $request = new Request('PUT', 'customers/'.$customerID.'.json', [
            'form_params' => $this->toParams('customer', $customer),
        ]);

        return $this->submitRequest($request);
    }

    public function redirect($fromUrl, $toUrl)
    {
        $request = new Request('POST', 'redirects.json', [
            'form_params' => [
                'redirect' => [
                    'path' => $fromUrl,
                    'target' => $toUrl,
                ],
            ],
        ]);

        return $this->submitRequest($request);
    }

    private function submitRequest(Request $request)
    {
        $response = Response::fromApiResponse($this->client->request(
            $request->getMethod(),
            $request->getEndpoint(),
            $request->getParams()
        ));

        sleep(0.5); // cheap way to prevent API rate limit

        return $response->getResults();
    }

    private function paginate(Request $request)
    {
        return new Paginator\Paginator($this->client, $request);
    }

    private function toParams($param, $valueObject)
    {
        if (array_key_exists($param, (array) $valueObject)) {
            $params = (array) $valueObject;
        } else {
            $params = [$param => (array) $valueObject];
        }

        return $params;
    }

    private function getArticlesResponse($blogID)
    {
        // only way to search for a resource efficiently is to cache the result set
        if (!$this->cachedResponse) {
            $this->cacheResults($this->getArticles($blogID));
        } else {
            // check that the blog ID is the same
            $matches = [];
            preg_match('#^blogs/([^/]*)/articles.*\.json$#', $this->cachedRequest->getEndpoint(), $matches);

            // update new response
            if (count($matches) > 1 && $matches[1] != $blogID) {
                $this->cacheResults($this->getArticles($blogID));
            }
        }

        return $this->cachedResponse;
    }

    private function getPagesResponse()
    {
        // only way to search for a resource efficiently is to cache the result set
        if (!$this->cachedResponse) {
            $this->cacheResults($this->getPages());
        } else {
            // check that the endpoint is the same
            if ($this->cachedRequest->getEndpoint() !== 'pages.json') {
                $this->cacheResults($this->getPages());
            }
        }

        return $this->cachedResponse;
    }

    private function cacheResults(Paginator\Paginator $paginator)
    {
        $walker = new Paginator\PaginationWalker($paginator);
        $this->cachedResponse = $walker->getResponse();
        $this->cachedRequest = $walker->getRequest();
    }
}
