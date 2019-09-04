<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\base;

/**
 * ICwfHtmlType: This interface is required if direct Html can be emitted
 * 
 * @author girish
 */
interface ICwfHtmlType {
    function emitHtml(\SimpleXMLElement $xel): string;
}
