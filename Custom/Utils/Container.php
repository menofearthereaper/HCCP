<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 29/01/2016
 * Time: 8:21 PM
 */

namespace Utils;

use Connection\AsyncCurl;

/**
 * Class Container - A simple DI container, typically would use Symfony Service container or Pimple
 * @package Utils
 */
class Container
{
    /**
     *  typically I would drop in Doctrine DBAL here as most applications I have worked on need to work in a variety
     *  of environments. For sake of simplicity and portability I will use sqlite for persistent storage.
     *
     *  Would typically have a user, and site or state objects here as well.
     *
     */
    public function getDb()
    {
        // return a simple sqlite conn, lets not worry about encryption its just a prototype.
        return new \SQLite3('Custom\Resources\hccp.sqlite', SQLITE3_OPEN_READWRITE);
    }

    /**
     * @param $url - base url for the curl connection
     * @return AsyncCurl
     */
    public function getAsyncCurl($url)
    {
        return new AsyncCurl($url);
    }


}