<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\widgets;

/**
 * WidgetBase: This is that abstract base class that can be inherited by 
 * all widgets
 *
 * @author girish
 */
abstract class WidgetBase {
    
    /**
     * Override this method to render the output HTML
     */
    abstract function emitHtml(\SimpleXMLElement $xel): string;
    
}
