<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\utils;

/**
 * XmlHelper: Contains methods used for friendly reading of xml nodes
 * 
 * @author girish
 */
class XmlHelper {
    
    /**
     * Gets the attribute from the XmlElement. If the attribute is not found, 
     * returns the default value specified
     * 
     * @param \SimpleXMLElement $xel    The XmlElement
     * @param string $attr              Name of the attribute
     * @param mixed $default            Default value to return when attribute is missing
     * @return mixed                    Returns the data found
     */
    public static function getAttr(\SimpleXMLElement $xel, string $attr, $default) {
        if (array_key_exists($attr, $xel->attributes())) {
            return $xel->attributes()[$attr];
        } else {
            return $default;
        }
    }
    
    public static function getAttrs(\SimpleXMLElement $xel, array $defaults): array {
        return array_merge($defaults, $xel->attributes());
    }
    
    public static function getAttrsGenericElement(\SimpleXMLElement $xel, array $defaults): \cwf\utils\GenericElement {
        return new \cwf\utils\GenericElement(array_merge($defaults, $xel->attributes()));
    }
}
