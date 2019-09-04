<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\utils;

/**
 * XmlParser: Reads the xml and converts it into an array
 * with linked nodes
 * 
 * @author girish
 */
class XmlParser {
    
    /**
     * Contains a list of item definitions with default values in following format
     * ['field' => [
     *      '' => '',
     *      
     * @var array 
     */
    public $itemDefs = [];
    
    public function init() {
        $this->itemDefs = require(__DIR__ . '/XmlItems.php');
    }


    public function parseXml(string $fileName) {
        $xml = simplexml_load_file($fileName);
        $result = [];
        foreach($xml->children() as $xel) {
            $result = $this->praseElement($xel);
        }
    }
    
    private function parseAttrs(\SimpleXMLElement $xel): array {
        
    }
    
    private function parseElement(\SimpleXMLElement $xel): array {
        $xa = $itemDefs[$xel->getName()];
        
    }
}
