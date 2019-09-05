<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\data;

/**
 * Defines a column used in DataTable
 * @author girish
 */
class DataColumn {

    public $columnName = '';
    public $phpDataType = DataAdapter::PHPDATA_TYPE_UNKNOWN;
    public $default = null;
    public $length = 0;
    public $scale = 0;
    public $isUnique = false;
    public $ntName = '';

    public function __construct(string $columnName, $phpDataType, $default, $length = 0, $scale = 0, $isUnique = false, $ntName = '') {
        $this->columnName = $columnName;
        $this->phpDataType = $phpDataType;
        $this->default = $default;
        $this->length = $length;
        $this->scale = $scale;
        $this->isUnique = $isUnique;
        if ($phpDataType == DataAdapter::PHPDATA_TYPE_DATATABLE) {
            $this->ntName = $ntName;
        }
    }

}
