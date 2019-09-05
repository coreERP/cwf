<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\data;

/**
 * SqlParamType: Used to define a SqlCommand Parameter
 *
 * @author girish
 */
class SqlParamType {

    const PARAM_IN = 0;
    const PARAM_INOUT = 1;
    const PARAM_OUT = 2;
    const PARAM_PREFIX = 'p';

    public $ParamName = '';
    public $ParamValue = null;
    public $ParamDirection = self::PARAM_IN;
    public $DataType = DataAdapter::PHPDATA_TYPE_UNKNOWN;

    public function __construct($paramName, $paramValue, $paramDirection = self::PARAM_IN, $dataType = DataAdapter::PHPDATA_TYPE_UNKNOWN) {
        $this->ParamName = $paramName;
        $this->ParamValue = $paramValue;
        $this->ParamDirection = $paramDirection;
        $this->DataType = $dataType;
    }

}
