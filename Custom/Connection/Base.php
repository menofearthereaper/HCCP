<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 30/01/2016
 * Time: 1:17 PM
 */

namespace Connection;


use GuzzleHttp\Psr7\Response;

abstract class Base
{
    protected $client;
    protected $url;
    /** @var  $response Response */
    protected $response;

    public function __construct($baseUrl)
    {
        if ($baseUrl) {
            // set URL
            $this->url = $baseUrl;
            // set Connection
            $this->setClient($this->url);
        } else {
            throw new \LogicException('Attempting to establish a connection without a base url');
        }

        $this->response = new Response();
    }

    protected abstract function setClient($args);

    public function getClient()
    {
        return $this->client;
    }

}