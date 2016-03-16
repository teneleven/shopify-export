<?php
namespace Shopify;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;

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

    /**
     * @var bool
     */
    private $debug;

    public function __construct(ClientInterface $client, $debug = false)
    {
        $this->client = $client;
        $this->debug = $debug ?: getenv('SHOPIFY_DEBUG');
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
        $request = new Request('POST', 'blogs.json', ['blog' => ['title' => $title]]);

        return $this->submitRequest($request);
    }

    public function getArticles($blogID, $page = 1)
    {
        $request = new Request('GET', 'blogs/'.$blogID.'/articles.json', [
            'limit' => 250,
            'page' => $page,
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
        if ($result = $this->walk($this->getArticles($blogID))->getResult('title', $article->title)) {
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

        $request = new Request('POST', 'blogs/'.$blogID.'/articles.json', $params);

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

        $request = new Request('PUT', 'blogs/'.$blogID.'/articles/'.$articleID.'.json', $params);

        return $this->submitRequest($request);
    }

    public function createOrUpdatePage($page)
    {
        if ($result = $this->walk($this->getPages())->getResult('handle', $page->handle)) {
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
            'limit' => 250,
            'page' => $page,
        ]);

        return $this->paginate($request);
    }

    public function createPage($page)
    {
        $request = new Request('POST', 'pages.json', $this->toParams('page', $page));

        return $this->submitRequest($request);
    }

    public function updatePage($pageID, $page)
    {
        $request = new Request('PUT', 'pages/'.$pageID.'.json', $this->toParams('page', $page));

        return $this->submitRequest($request);
    }

    public function getProducts($page = 1)
    {
        $request = new Api\ProductRequest('GET', 'products.json', [
            'limit' => 250,
            'page' => $page,
        ]);

        return $this->paginate($request);
    }

    public function createOrUpdateProduct($product)
    {
        if (isset($product->variants)) {
            // remove variant IDs (triggers creation of new variant)
            foreach ($product->variants as $key => $variant) {
                if (isset($variant->id)) {
                    unset($variant->id);
                    $product->variants[$key] = $variant;
                }
            }
        }

        if ($result = $this->walk($this->getProducts())->getResult('handle', $product->handle)) {
            // update
            echo "Resource found - {$result->title} {$result->id}\n";

            // update variant IDs - this triggers an update of existing variants
            if (isset($result->variants)) {
                foreach ($result->variants as $key => $variant) {
                    if (isset($variant->id) && isset($product->variants[$key])) {
                        $product->variants[$key]->id = $variant->id;
                    }
                }
            }

            return $this->updateProduct($result->id, $product);
        }

        // create
        echo "Resource not found, creating - {$product->title}\n";

        return $this->createProduct($product);
    }

    public function createProduct($product)
    {
        $request = new Api\ProductRequest('POST', 'products.json', (array) $product);

        return $this->submitRequest($request);
    }

    public function updateProduct($productID, $product)
    {
        $request = new Api\ProductRequest('PUT', 'products/'.$productID.'.json', (array) $product);

        return $this->submitRequest($request);
    }

    public function getCollections($page = 1)
    {
        $request = new Request('GET', 'custom_collections.json', [
            'limit' => 250,
            'page' => $page,
        ]);

        return $this->paginate($request);
    }

    public function createOrUpdateCollection($collection)
    {
        if ($result = $this->walk($this->getCollections())->getResult('handle', $collection->handle)) {
            // update
            echo "Resource found - {$result->title} {$result->id}\n";

            return $this->updateCollection($result->id, $collection);
        }

        // create
        echo "Resource not found, creating - {$collection->title}\n";

        return $this->createCollection($collection);
    }

    public function createCollection($collection)
    {
        $request = new Request('POST', 'custom_collections.json', ['custom_collection' => (array) $collection]);

        return $this->submitRequest($request);
    }

    public function updateCollection($collectionID, $collection)
    {
        $request = new Request('PUT', 'custom_collections/'.$collectionID.'.json', ['custom_collection' => (array) $collection]);

        return $this->submitRequest($request);
    }

    public function getSmartCollections($page = 1)
    {
        $request = new Request('GET', 'smart_collections.json', [
            'limit' => 250,
            'page' => $page,
        ]);

        return $this->paginate($request);
    }

    public function createOrUpdateSmartCollection($collection)
    {
        if ($result = $this->walk($this->getSmartCollections())->getResult('handle', $collection->handle)) {
            // update
            echo "Resource found - {$result->title} {$result->id}\n";

            return $this->updateSmartCollection($result->id, $collection);
        }

        // create
        echo "Resource not found, creating - {$collection->title}\n";

        return $this->createSmartCollection($collection);
    }

    public function createSmartCollection($collection)
    {
        $request = new Api\SmartCollectionRequest('POST', 'smart_collections.json', ['smart_collection' => (array) $collection]);

        return $this->submitRequest($request);
    }

    public function updateSmartCollection($collectionID, $collection)
    {
        $request = new Api\SmartCollectionRequest('PUT', 'smart_collections/'.$collectionID.'.json', ['smart_collection' => (array) $collection]);

        return $this->submitRequest($request);
    }

    public function getCollects($page = 1)
    {
        $request = new Request('GET', 'collects.json', [
            'limit' => 250,
            'page' => $page,
        ]);

        return $this->paginate($request);
    }

    public function createCollect($collect)
    {
        $request = new Request('POST', 'collects.json', ['collect' => (array) $collect]);

        return $this->submitRequest($request);
    }

    public function updateCollect($collectID, $collect)
    {
        $request = new Request('PUT', 'collects/'.$collectID.'.json', ['collect' => (array) $collect]);

        return $this->submitRequest($request);
    }

    public function getCustomers($page = 1)
    {
        $request = new Request('GET', 'customers.json', [
            'limit' => 250,
            'page' => $page,
        ]);

        return $this->paginate($request);
    }

    public function createOrUpdateCustomer($customer)
    {
        if ($result = $this->walk($this->getCustomers())->getResult('email', $customer->email)) {
            // update
            echo "Resource found - {$result->first_name} {$result->last_name} {$result->id}\n";

            return $this->updateCustomer($result->id, $customer);
        }

        // create
        echo "Resource not found, creating - {$customer->first_name} {$customer->last_name}\n";

        return $this->createCustomer($customer);
    }

    public function createCustomer($customer)
    {
        $request = new Api\CustomerRequest('POST', 'customers.json', (array) $customer);

        return $this->submitRequest($request);
    }

    public function updateCustomer($customerID, $customer)
    {
        $request = new Api\CustomerRequest('PUT', 'customers/'.$customerID.'.json', (array) $customer);

        return $this->submitRequest($request);
    }

    public function redirect($fromUrl, $toUrl)
    {
        $request = new Request('POST', 'redirects.json', [
            'redirect' => [
                'path' => $fromUrl,
                'target' => $toUrl,
            ],
        ]);

        return $this->submitRequest($request);
    }

    public function walk(Paginator\Paginator $paginator)
    {
        if (!$this->cachedResponse
            || ($this->cachedRequest->getEndpoint() !== $paginator->getRequest()->getEndpoint())
        ) {
            $this->cacheResults($paginator);
        }

        return $this->cachedResponse;
    }

    /**
     * Only way to search for a resource efficiently is to cache the result set.
     */
    private function cacheResults(Paginator\Paginator $paginator)
    {
        $walker = new Paginator\PaginationWalker($paginator);
        $walker->fetchResults();
        $this->cachedResponse = $walker->getResponse();
        $this->cachedRequest = $walker->getRequest();
    }

    private function submitRequest(Request $request)
    {
        try {
            $response = Response::fromApiResponse($this->client->request(
                $request->getMethod(),
                $request->getEndpoint(),
                $request->getParams()
            ));

            sleep(0.5); // cheap way to prevent API rate limit

            return $response->getResults();
        } catch (ClientException $e) {
            if ($this->debug) {
                echo "API Error: ".$e->getResponse()->getBody()."\n";
            } else {
                throw $e;
            }
        }
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
}
