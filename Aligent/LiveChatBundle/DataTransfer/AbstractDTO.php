<?php

namespace Aligent\LiveChatBundle\DataTransfer;


/**
 * Abstract Data Transfer Object
 *
 * A simple, generic data transfer object (DTO), heavily inspired by Magento's
 * Varien_Object.
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */
abstract class AbstractDTO {

    protected $data = [];

    /**
     * A simple magic call method which implements getFoo(), setFoo() and hasFoo()
     * getters and setters.
     *
     * @param string $method Method called
     * @param array $params Parameters
     * @return $this|bool|mixed|null
     */
    public function __call($method, $params) {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->underscore(substr($method, 3));
                $data = $this->getData($key, isset($params[0]) ? $params[0] : null);
                return $data;
            case 'set' :
                $key = $this->underscore(substr($method, 3));
                $this->setData($key, isset($params[0]) ? $params[0] : null);
                return $this;
            case 'has' :
                $key = $this->underscore(substr($method, 3));
                return isset($this->data[$key]);
        }
        throw new Varien_Exception("Invalid method ".get_class($this)."::".$method."(".print_r($params,1).")");
    }


    /**
     * Generic getter.  Use of getFoo() magic methods is preferred.
     *
     * @param string $key Key to fetch
     * @param null|mixed $default Default value to return if key is not set
     * @return mixed|null Key if set, default if not
     */
    public function getData($key, $default = null) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return $default;
        }
    }


    /**
     * Generic setter.  Use of setFoo() magic methods is preferred.
     *
     * @param string $key Key to assign
     * @param mixed $value Value to assign
     * @return $this For chaining
     */
    public function setData($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }


    /**
     * Generic method to test if key has been assigned a value.  Use of hasFoo()
     * magic methods is preferred.
     *
     * @param string $key Key to check
     * @return bool True if key is set
     */
    public function hasData($key) {
        return isset($this->data[$key]);
    }


    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     *
     * @param string $name
     * @return string
     */
    protected function underscore($name) {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        return $result;
    }
}