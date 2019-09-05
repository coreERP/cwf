<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\base;

/**
 * CwfObjectType: This trait is required to be implemented by all
 * representative object types defined in xml schema.
 * If you have created custom elements, and would like to implement them, then your class handler must use 
 * this trait.
 * 
 * @author girish
 */
trait CwfObjectType {
    
    private static $registeredTypes = [];


    public function registerType(string $cwfType) {
        self::$registeredTypes[$cwfType] = $this;
    }
    
    /**
     * Calls the emitHtml method based on the XmlElement Name from one of the registered types and
     * returns the result html
     * 
     * @param \SimpleXMLElement $xel
     * @return string
     * @throws \Exception
     */
    public function runHtmlType(\SimpleXMLElement $xel): string {
        // First We try registered types
        if (array_key_exists($xel->getName(), self::$registeredTypes)) {
            $ct = self::$registeredTypes[$cwfType];
            if ($ct instanceof ICwfHtmlType) {
                return $ct->emitHtml($xel);
            }
        }
        // Finally we throw exception for unknown type
        throw new \Exception("Xml element ".$xel->getName()." is an unknown type and does not return html");
    }
    
    /**
     * Calls the emitArray method based on the XmlElement Name from one of the registered types and
     * returns the result array
     *  
     * @param \SimpleXMLElement $xel
     * @return array
     * @throws \Exception
     */
    public function runArrayType(\SimpleXMLElement $xel): array {
        // First We try registered types
        if (array_key_exists($xel->getName(), self::$registeredTypes)) {
            $ct = self::$registeredTypes[$cwfType];
            if ($ct instanceof ICwfArrayType) {
                return $ct->emitArray($xel);
            }
        }
        // Finally we throw exception for unknown type
        throw new \Exception("Xml element ".$xel->getName()." is an unknown type and does not return array");
    }
    
    
    public function runSqlCommandType(\SimpleXMLElement $xel): \cwf\data\SqlCommand {
        // First We try registered types
        if (array_key_exists($xel->getName(), self::$registeredTypes)) {
            $ct = self::$registeredTypes[$cwfType];
            if ($ct instanceof ICwfSqlCommandType) {
                return $ct::parseXml($xel);
            }
        } else {
            switch ($xel->getName()) {
                case 'sql':
                    return \cwf\data\SqlCommand::parseXml($xel);
            }
        }
        // Finally we throw exception for unknown type
        throw new \Exception("Xml element ".$xel->getName()." is an unknown type and does not return SqlCommand");
    }
}
