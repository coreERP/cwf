<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\helpers;

/**
 * CollectionViewHelper: This class encapsulates the logic required to 
 * render data for a collection view
 *
 * @author girish
 */
class CollectionViewHelper {

    /**
     * Generates JSON data for display in collection/list of Master/Document
     * @param \cwf\utils\GenericElement Design information of the view
     * @param array $filters An Array of filters
     * @return string Returns JSON encoded data extracted directly from db-server
     */
    public static function getCollectionData(\cwf\utils\GenericElement $gel, array $filters): string {
        if ($gel->bo_type == \cwf\base\CwfType::BO_DOCUMENT) {
            $fdefaults = ['docstatus' => 0,
                'from_date' => SessionManager::getSessionVariable('year_begin'),
                'to_date' => SessionManager::getSessionVariable('year_end')];
            // This would ensure that the default parameters are created and available
            $filters = array_merge($fdefaults, $filters);
            \yii::info($filters, 'filters');
            // Add Wf Columns
            static::setWfDisplayFields($design, $filters);
            $data = static::getDocData($gel->sqlCommand, $filters, $gel->connectionType, $keyField);
        } else {
            $data = static::getMastData($gel->sqlCommand, $filters, $gel->cn_dbType);
        }

        // api resultsets with minimum data
        if (array_key_exists('forapi', $filters)) {
            $cols = [];
            foreach ($design->collectionSection->displayFields as $df) {
                $cols[] = $df->columnName;
            }
            return '{"cols": ' . json_encode($cols) . ',"data": ' . $data . '}';
        }
        // Prepare result
        $jsonResult = '{ "cols": ' . json_encode($gel->displayFields);
        $jsonResult .= ', "def": ' . json_encode(['al' => $gel->al_allowed, 'keyField' => $gel->keyField, 'afterLoad' => $gel->afterLoadEvent]);
        $jsonResult .= ', "data": ' . $data . '}';
        return $jsonResult;
    }

    public static function getData(\cwf\utils\GenericElement $gel, $filters) {
        $cmm = $gel->sqlCommand;
        if ($design->ovrrideClass != '' && $design->ovrrideMethod != '') {
            $ovrClass = $design->ovrrideClass;
            $ovrMethod = $design->ovrrideMethod;
            $cmtext = $cmm->getCommandText();
            $ovrrideClass = new $ovrClass();
            $ovrrideClass->$ovrMethod($cmtext, $filters);
            $cmm->setCommandText($cmtext);
        }
        if ($gel->type == \cwf\base\CwfType::BO_DOCUMENT) {
            $cmm->setCommandText(self::buildDocCollectionQuery($cmm->getCommandText(), $filters, $gel->keyField));
        } else {
            // This is not a document, hence ignore filters
            $cmm->setCommandText(self::buildMastCollectionQuery($cmm->getCommandText(), $filters, $design->keyField));
        }
        $collection = \app\cwf\vsla\data\DataConnect::getData($cmm, $gel->connectionType);
        return $collection;
    }

    private static function buildDocCollectionQuery($sql, $filters, $keyField) {
        $userInfo = SessionManager::getInstance()->getUserInfo();
        $year_begin = SessionManager::getSessionVariable('year_begin');
        $year_end = SessionManager::getSessionVariable('year_end');
        $qCond = [];
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'docstatus':
                    if ($value == self::STATUS_FILTER_AwaitMyAction) {
                        $qCond[] = 'a.status In (1, 3)';
                    } elseif ($value == self::STATUS_FILTER_ParticipatedIn) {
                        $qCond[] = 'a.status = 3 And Exists (Select b.doc_id From sys.doc_wf_history b Where a.' . $keyField . '=b.doc_id And (b.user_id_to=' . $userInfo->getUser_ID() . ' Or b.user_id_from=' . $userInfo->getUser_ID() . '))';
                    } elseif ($value == self::STATUS_FILTER_StartedByMe) {
                        $qCond[] = 'a.status In (1, 3)';
                    } elseif ($value == self::STATUS_FILTER_Pending) {
                        $qCond[] = 'a.status!=5';
                    } elseif ($value == self::STATUS_FILTER_Posted) {
                        $qCond[] = 'a.status=5';
                    } elseif ($value == self::STATUS_FILTER_All) {
                        // Do nothing
                    }
                    break;
                case 'from_date':
                    if (strtotime($value) >= strtotime($year_begin) && strtotime($value) <= strtotime($year_end)) {
                        $qCond[] = "a.doc_date >= '$value'";
                    } else {
                        $qCond[] = "a.doc_date >= '$year_begin'";
                    }
                    break;
                case 'to_date':
                    if (strtotime($value) >= strtotime($year_begin) && strtotime($value) <= strtotime($year_end)) {
                        $qCond[] = "a.doc_date <= '$value'";
                    } else {
                        $qCond[] = "a.doc_date <= '$year_end'";
                    }
                    break;
                case 'voucher_id':
                    $value = trim($value);
                    if (strlen($value) > 0) {
                        $qCond[] = "$keyField = '" . str_replace("'", "", $value) . "'";
                    }
                    break;
                default:
                    break;
            }
        }
        $finalSql = "With doc_data
                As
                (   Select a.* 
                    From ($sql) a \n" .
                (count($qCond) > 0 ? "Where " . implode(" and ", $qCond) . "\n" : "")
                . "),
                wf_data
                As
                ( " . self::getWfSql($filters['docstatus'], $keyField) . " )
                Select json_agg(wf_data) raw_data
                From wf_data";
        \yii::info($finalSql, 'finalSql');
        return $finalSql;
    }

    private static function getMastData(\cwf\data\SqlCommand $cmm, array $filters, string $dbType): string {
        // Build filters if any
        $sql_filters = [];
        $filterTempl = "md.{field} {operand} {param}";
        foreach ($filters as $fkey => $fval) {
            if ($fkey != 'forapi') {
                $sql_filters[] = strtr($filterTempl, [
                    '{field}' => $fkey,
                    '{operand}' => $fval['op'],
                    '{param}' => ":fp_$fkey"
                ]);
                $cmm->addParam("fp_$fkey", $fval['val']);
            }
        }
        $finalSqlTempl = "With mast_data
                As
                ( {sql} 
                )
                Select json_agg(md) raw_data
                From mast_data md
                {sql_filter}";
        $finalSql = strtr($finalSqlTempl, [
            '{sql}' => $cmm->getCommandText(),
            '{sql_filter}' => count($sql_filters) > 0 ? "Where " . implode(" And ", $sql_filters) : ""
        ]);
        $cmm->setCommandText($finalSql);
        $dt = \cwf\data\DataConnect::getData($cmm, $dbType);
        return $dt->Rows()[0]['raw_data'] == null ? json_encode([]) : $dt->Rows()[0]['raw_data'];
    }

    public static function getSql($sql) {
        $cmm = new \app\cwf\vsla\data\SqlCommand();
        $cmm->setCommandText((string) $sql->command);
        if (isset($sql->params)) {
            foreach ($sql->params as $param) {
                $paramval = ReportHelper::output_paramvalue_param($param);
                $cmm->addParam($param->id, $paramval);
            }
        }
        return $cmm;
    }

    /**
     * Returns second part of the query with Wf information
     * @param int $qStatus One of the STATUS_FILTER_*
     */
    private static function getWfSql(int $qStatus, $keyField) {
        $userInfo = SessionManager::getInstance()->getUserInfo();
        switch ($qStatus) {
            case self::STATUS_FILTER_AwaitMyAction:
                return "Select a.*, 
                        c.full_user_name from_user, 
                        b.doc_sent_on doc_sent_on
                    From doc_data a
                    Inner Join sys.doc_wf b On a.$keyField=b.doc_id
                    Inner Join sys.user c On b.user_id_from = c.user_id
                    Where b.user_id_to = " . $userInfo->getUser_ID() . "
                    Union All
                    Select a.*, 
                        entered_by from_user, 
                        entered_on doc_sent_on
                    From doc_data a
                    Inner Join sys.doc_es b On a.$keyField=b.voucher_id And a.status = 1 and b.entered_user='" . $userInfo->getUserName() . "'";
            case self::STATUS_FILTER_StartedByMe:
                return "Select a.*, 
                        c.full_user_name from_user, 
                        b.doc_sent_on doc_sent_on
                    From doc_data a
                    Inner Join sys.doc_wf b On a.$keyField=b.doc_id
                    Inner Join sys.user c On b.user_id_to = c.user_id
                    Inner Join sys.doc_es d On a.$keyField=d.voucher_id
                    Where d.entered_user='" . str_replace("'", "", $userInfo->getUserName()) . "'";
            case self::STATUS_FILTER_ParticipatedIn:
                return "Select a.*, 
                        Coalesce(c.full_user_name, 'Me') from_user, 
                        Coalesce(b.doc_sent_on, current_timestamp(0)) doc_sent_on
                    From doc_data a
                    Left Join sys.doc_wf b On a.$keyField=b.doc_id
                    Left Join sys.user c On b.user_id_from = c.user_id";
            case self::STATUS_FILTER_Pending:
                return "Select a.*, 
                        Case When c.full_user_name Is Null Then d.entered_by Else c.full_user_name End from_user, 
                        Case When b.doc_sent_on Is Null Then d.entered_on Else b.doc_sent_on End doc_sent_on
                    From doc_data a
                    Left Join sys.doc_wf b On a.$keyField = b.doc_id
                    Left Join sys.user c On b.user_id_from = c.user_id
                    Left Join sys.doc_es d On a.$keyField = d.voucher_id";
            case self::STATUS_FILTER_All:
                return "Select a.*
                    From doc_data a";
            default:
                return "Select a.* From doc_data a";
        }
    }

}
