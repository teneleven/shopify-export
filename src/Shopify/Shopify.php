<?php

namespace Shopify;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Shopify api wrapper.
 */
class Shopify
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getBlogs()
    {
        $response = $this->client->request('GET', 'blogs.json');

        $json = (array) json_decode((string) $response->getBody());

        return array_shift($json);
    }

    public function getArticles($blogID, $page = 1)
    {
        $response = $this->client->request('GET', 'blogs/'.$blogID.'/articles.json', [
            'form_params' => [
                'limit' => 250,
                'page' => $page,
            ],
        ]);

        $json = (array) json_decode((string) $response->getBody());

        return array_shift($json);
    }

    public function submitArticle($blogID, array $article)
    {
        $response = $this->client->request('POST', 'blogs/'.$blogID.'/articles.json', [
            'form_params' => $article,
        ]);

        return json_decode((string) $response->getBody());
    }

    public function updateArticle($blogID, $articleID, array $article)
    {
        $response = $this->client->request('PUT', 'blogs/'.$blogID.'/articles/'.$articleID.'.json', [
            'form_params' => $article,
        ]);

        return json_decode((string) $response->getBody());
    }

    public function redirect($fromUrl, $toUrl)
    {
        return $this->client->request('POST', 'redirects.json', [
            'form_params' => [
                'redirect' => [
                    'path' => $fromUrl,
                    'target' => $toUrl,
                ],
            ],
        ]);
    }

    /**
     * Simple slugify method
     */
    public function slugify($text)
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

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }
}
