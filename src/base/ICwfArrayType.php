<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\base;

/**
 * ICwfArrayType: This interface is required if the reader can return an array
 * 
 * @author girish
 */
interface ICwfArrayType {
    public function emitArray(\SimpleXMLElement $xel): array;
}
