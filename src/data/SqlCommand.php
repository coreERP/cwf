<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\data;

/**
 * SqlCommand: Can be used to execute/call any SQL Query or SQL Function
 * with a list of named parameters
 * @author girish
 */
class SqlCommand implements \cwf\base\ICwfSqlCommandType {
    use \cwf\base\CwfObjectType;

    /**
     * Stores the SQL Query
     * @var string 
     */
    private $cmmText = '';
    
    /**
     * Stores the parameters in key/value pairs
     * @var array()
     */
    private $cmmParams = [];
    public $returnValue = null;
    
    /**
     * Constructs an instance of SqlCommand with 
     * provided CommandText
     * @param string $sql
     */
    public function __construct(string $sql = '') {
        $this->cmmText = $sql;
    }

    /**
     * Sets the Command Text. Usually the SQL statement to be executed
     * Named Parameters if any, should be in the format :param1, :param2
     * @param string $sql
     */
    public function setCommandText(string $sql) {
        $this->cmmText = $sql;
    }

    /**
     * Add parameters with name/value. The parameter name should correspond to the 
     * parameter mentioned as part of CommandText.
     * @param string $paramName
     * @param mixed $paramValue
     * @param int $paramDirection
     */
    public function addParam($paramName, $paramValue, $paramDirection = SqlParamType::PARAM_IN, $dataType = DataAdapter::PHPDATA_TYPE_UNKNOWN) {
        $this->cmmParams[$paramName] = new SqlParamType($paramName, $paramValue, $paramDirection, $dataType);
    }

    public function getCommandText() {
        $ct = $this->parseConstants();
        return $ct;
    }

    public function getParams() {
        return $this->cmmParams;
    }

    public function getParamsForBind() {
        $result = null;
        if ($this->cmmParams !== null) {
            foreach ($this->cmmParams as $key => $param) {
                if ($param->ParamDirection == SqlParamType::PARAM_IN || $param->ParamDirection == SqlParamType::PARAM_INOUT) {
                    $result[$param->ParamName] = $this->parseValue($param);
                }
            }
        }
        return $result;
    }

    public function setParamValue($paramName, $paramValue) {
        $this->cmmParams[$paramName]->ParamValue = $paramValue;
    }

    public function getParamValue($paramName) {
        return $this->cmmParams[$paramName]->ParamValue;
    }

    public function setOutput($result) {
        if ($result === null || $this->cmmParams === null) {
            return;
        }
        if (!is_array($result) && $result . length() === 0) {
            return;
        }
        foreach ($this->cmmParams as $key => $param) {
            if ($param->ParamDirection == SqlParamType::PARAM_INOUT || $param->ParamDirection == SqlParamType::PARAM_OUT) {
                $param->ParamValue = $result[0][str_replace(':', '', $param->ParamName)]; //substr_replace($param->ParamName, ':', 0, 1)];
            }
        }
        if (count($result) > 0) {
            $this->returnValue = $result[0];
        } else {
            $this->returnValue = $result;
        }
    }

    private function parseConstants() {
        return $this->cmmText;
        $ct = $this->cmmText;
        if (strstr($ct, '{company_id}')) {
            $companyid = \app\cwf\vsla\security\SessionManager::getInstance()->getUserInfo()->getCompany_ID();
            $ct = str_replace('{company_id}', $companyid, $ct);
        }
        if (strstr($ct, '{branch_id}')) {
            $branchid = \app\cwf\vsla\security\SessionManager::getSessionVariable('branch_id');
            $ct = str_replace('{branch_id}', $branchid, $ct);
        }
        if (strstr($ct, '{finyear}')) {
            $finyear = \app\cwf\vsla\security\SessionManager::getSessionVariable('finyear');
            $ct = str_replace('{finyear}', $finyear, $ct);
        }
        if (strstr($ct, '{user_id}')) {
            $user_id = \app\cwf\vsla\security\SessionManager::getInstance()->getUserInfo()->getUser_ID();
            $ct = str_replace('{user_id}', $user_id, $ct);
        }
        if (strstr($ct, '{http_host}')) {
            $http_host = $_SERVER['HTTP_HOST'];
            $ct = str_replace('{http_host}', $http_host, $ct);
        }
        return $ct;
    }

    private function parseValue(SqlParamType $param) {
        $result = null;
        if ($param->DataType == DataAdapter::PHPDATA_TYPE_UNKNOWN) {
            if (gettype($param->ParamValue) == "boolean") {
                // boolean needs to be passed as 0 = false and 1 = true for postgres PDO
                $result = $param->ParamValue ? 1 : 0;
            } else {
                $result = $param->ParamValue;
            }
        } else {
            switch ($param->DataType) {
                case DataAdapter::PHPDATA_TYPE_BOOL:
                    $result = $param->ParamValue ? 1 : 0;
                    break;
                case DataAdapter::PHPDATA_TYPE_ARRAY:
                    if ($param->ParamValue instanceof ArrayField) {
                        $result = $param->ParamValue->get_dbvalue();
                    } else {
                        $result = '{' . ArrayField::str_putcsv($param->ParamValue, ',', '"') . '}';
                    }
                    break;
                case DataAdapter::PHPDATA_TYPE_JSON:
                    $result = json_encode($param->ParamValue);
                    break;
                default :
                    $result = $param->ParamValue;
                    break;
            }
        }
        return $result;
    }

    public static function parseXml(\SimpleXMLElement $xel): SqlCommand {
        $cmm = new SqlCommand((string)$xel->command);
        if (isset($xel->params)) {
            foreach($xel->params->childrem() as $nName => $nDef) {
                if (isset($nodeDef->session)) {
                    $cmm->addParam((string)$nodeDef->attributes()->id, \cwf\security\SessionManager::getSessionVariable((string)$nodeDef->session));
                } elseif (isset($nDef->text)) {
                    $cmm->addParam((string)$nodeDef->attributes()->id, (string)$nDef->text);
                } elseif (isset($ndef->currentDate)) {
                    $cmm->addParam((string)$nodeDef->attributes()->id, (new \DateTime())->format('Y-m-d'));
                }
            }
        }
        return $cmm;
    }

}
