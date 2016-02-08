<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 29/01/2016
 * Time: 10:18 PM
 */

namespace Model;


use Utils\Logger;

class Base
{

    /**
     * constructor.
     * @param array $params - assoc array of properties
     * @throws \Exception
     */
    public function __construct($params)
    {
        try {
            foreach (get_object_vars($this) as $varName => $val) {
                // sanity check that all the params exist for the model class obj
                if (array_key_exists($varName, $params)) {
                    $this->__set($varName, $params[$varName]);
                } else {
                    throw new \LogicException("Required parameter [$varName] missing.");
                }
            }
        } catch (\Exception $e) {
            // Dump the params to log file so we can see what went BOOM! then rethrow exception
            Logger::getInstance()->write(ERROR, 'Failed to create instance of ' . get_class($this) . '. ' . $e->getMessage(), $params);
            throw $e;
        }

    }

    /**
     * Magic set method - While I generally avoid magic methods like the plague, the business logic is simple enough to
     * allow them, and the data all needs the same sanity checks on the way in.
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new \LogicException("Property name [$name] does not exist in class" . get_class($this));
        }
    }

    /**
     * magic get method
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            throw new \LogicException("Attempting to fetch property [$name] does not exist in class" . get_class($this));
        }
    }

    /**
     * Function to convert object to array - useful while in dev, unlikely to be required long term
     * @return array
     */
    public function toArray()
    {
        $json = [];
        foreach ($this as $key => $val) {
            $json[$key] = $val;
        }
        return $json;
    }

    public function hash()
    {
        return md5(json_encode($this->toArray()));
    }
}