<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\base;

/**
 * ICwfSqlCommandType: This interface is implemented for SqlCommand parsing
 * 
 * @author girish
 */
interface ICwfSqlCommandType {
    static function parseXml(\SimpleXMLElement $xel): \cwf\data\SqlCommand;
}
