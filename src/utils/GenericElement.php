<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\utils;

/**
 * GenericElement: Can be used to wrap around an array enabling Array to property implementation
 * 
 * @author girish
 */
class GenericElement implements \JsonSerializable {
    /**
     * contains the localised array
     * @var array 
     */
    private $data;
    
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    /**
     * Returns the raw array
     * @return array
     */
    public function getArray(): array {
        return $this->data;
    }
    
    //<editor-fold defaultstate="collapsed" desc="Array to Property Implementation">
    public function &__get($key) {
        return $this->data[$key];
    }
    
    public function __set($key, $value) {
        $this->data[$key] = $value;
    }
    
    public function __isset($key) {
        return isset($this->data[$key]);
    }
    
    public function __unset($key) {
        unset($this->data[$key]);
    }

    public function jsonSerialize() {
        return $this->data;
    }

    // </editor-fold>

}
