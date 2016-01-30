<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 30/01/2016
 * Time: 1:15 PM
 */

namespace Connection;

use GuzzleHttp\Client as Client;
use GuzzleHttp\Promise as Promise;

class AsyncCurl extends Base
{
    protected $options;
    protected $timeout;
    /** @var  $promises \GuzzleHttp\Promise */
    protected $promises;
    /** @var $client Client */
    protected $client;

    /**
     * set the base client for the request pool.
     * @param $url - base url for destination
     */
    protected function setClient($url)
    {
        $this->client = new Client(['base_uri' => $url . '/']);
    }

    /**
     * Function makes and adds a new request to the request pool
     * @param $key - The key that the request is to be indexed on
     * @param $uri - destination url path
     * @param array $options - any additional options that may be required
     */
    public function addPromise($key, $uri, $options = [])
    {
        $promise = $this->client->postAsync($this->url . $uri, $options);
        $this->promises[$key] = $promise;
    }

    /**
     * Function unwinds the request pool waiting for all requests to be completed (or timeout)
     * returning an array of response objects indexed against the key values that were used when the
     * promises were added to the pool
     * @return  \GuzzleHttp\Psr7\Response []
     */
    public function unwrap()
    {
        $results = Promise\unwrap($this->promises);
        return $results;
    }

}