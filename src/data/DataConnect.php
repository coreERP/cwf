<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\data;

/**
 * DataConnect can be used to connect to database and 
 * execute queries
 * @author girish
 */
class DataConnect {

    /**
     * Stores the singleton instance of the builder
     * created via di->container
     * @var ConnectionBuilder
     */
    static $connectionBuilder;

    private static function init() {
        if (self::$connectionBuilder == NULL) {
            self::$connectionBuilder = \yii::$container->get('ConnectionBuilder');
        }
    }

    /**
     * Executes the command and gets the resultset. You would use this for Select query that returns a table result.
     * @param app\vsla\data\SqlCommand $cmm     The SqlCommand instance
     * @param string $dbType                    Optional, ConnectionBuilder::DB_TYPE constants 
     * @param \PDO $cn                          Optional, open connection if required to execute command in a transaction.
     *                                          If specified, the dbType would be ignored and the existing connection used.
     * @return app\vsla\data\DataTable
     */
    public static function getData(SqlCommand $cmm, $dbType = ConnectionBuilder::DB_DEFAULT, \PDO $cn = null): DataTable {
        self::init();
        $selfCn = false;
        if ($cn == null) {
            $selfCn = true;
            $cn = self::$connectionBuilder->getCn($dbType);
        }
        $query = $cn->prepare($cmm->getCommandText());
        $query->execute($cmm->getParamsForBind());
        $dt = new DataTable();
        DataAdapter::Fill($dt, $query);
        $query = null;
        if ($selfCn) {
            $cn = null;
        }
        return $dt;
    }

    /**
     * Executes the command as an Update/Delete/Procedure/Function Call. 
     * You would use this for Queries that do not return results, but may map output parameters.
     * @param app\vsla\data\SqlCommand $cmm     The SqlCommand instance
     * @param \PDO $cn                          Optional, open connection if required to execute command in a transaction.
     *                                          If specified, the dbType would be ignored and the existing connection used.
     * @param string $dbType                    Optional, ConnectionBuilder::DB_TYPE constants
     */
    public static function exeCmm(SqlCommand $cmm, \PDO $cn = null, string $dbType = ConnectionBuilder::DB_DEFAULT) {
        $selfCn = false;
        if ($cn == null) {
            $selfCn = true;
            $cn = self::$connectionBuilder->getCn($dbType);
        }
        $query = $cn->prepare($cmm->getCommandText());
        $query->execute($cmm->getParamsForBind());
        $result = $query->fetchAll();
        $cmm->setOutput($result);
        $query = null;
        if ($selfCn) {
            $cn = null;
        }
    }

    /**
     * Gets the requested open connection to the database
     * @param string $dbType    One of the ConnectionBuilder::DB_TYPE constants
     * 
     * @return \PDO
     */
    public static function getCn(string $dbType): \PDO {
        return self::$connectionBuilder->getCn($dbType);
    }

    /**
     * Gets data in SplFixedArray. This is a memory efficient array. 
     * Use this method if you are trying to get read-only large resultsets
     * 
     * @param \cwf\data\SqlCommand $cmm    The command object
     * @param const $dbType                The DB to connect (Main/Company/Audit)
     * @param \PDO $cn                     The Connection Object (if already open)
     * 
     * @return \stdClass                   Returns the a stdClass with cols and rows (Index only rows)
     */
    public static function getDataSplArray(SqlCommand $cmm, $dbType = ConnectionBuilder::DB_DEFAULT, \PDO $cn = null): \stdClass {
        self::init();
        
        $selfCn = false;
        if ($cn == null) {
            $selfCn = true;
            $cn = self::$connectionBuilder->getCn($dbType);
        }
        $query = $cn->prepare($cmm->getCommandText());
        $query->execute($cmm->getParamsForBind());
        $resultData = new \SplFixedArray($query->rowCount());
        $i = 0;
        while ($row = $query->fetch(\PDO::FETCH_NUM)) {
            $resultData[$i] = \SplFixedArray::fromArray($row);
            $i++;
        }
        $resultCols = [];
        for ($ci = 0; $ci < $query->columnCount(); $ci++) {
            $colMeta = $query->getColumnMeta($ci);
            // Create column index
            $colMeta['colindex'] = $ci;
            $resultCols[$colMeta['name']] = $colMeta;
        }
        $query = null;
        $result = new \stdClass();
        $result->cols = $resultCols;
        $result->rows = $resultData;
        if ($selfCn) {
            $cn = null;
        }
        return $result;
    }

    /**
     * Directly writes the results into a csv file. 
     * @param \cwf\data\SqlCommand $cmm    The command object
     * @param const $dbType                The DB to connect (Main/Company/Audit)
     * @param \PDO $cn                     The Connection Object (if already open)
     * @param resource $fhandle            The writable file handle
     * 
     * @return int                         Returns the number of rows affected by the query
     */
    public static function getDataInCsvFile(SqlCommand $cmm, $dbType = ConnectionBuilder::DB_TYPE_DEFAULT, \PDO $cn = null, $fhandle = null): int {
        $selfCn = false;
        if ($cn == null) {
            $selfCn = true;
            $cn = self::$connectionBuilder->getCn($dbType);
        }
        $query = $cn->prepare($cmm->getCommandText());
        $query->execute($cmm->getParamsForBind());
        $rowCount = $query->rowCount();
        // put columns as header row in file
        $cols = [];
        for ($ci = 0; $ci < $query->columnCount(); $ci++) {
            $colMeta = $query->getColumnMeta($ci);
            $cols[] = $colMeta['name'];
        }
        fputcsv($fhandle, $cols, ',', '"');
        // put row data into file
        while ($row = $query->fetch(\PDO::FETCH_NUM)) {
            fputcsv($fhandle, $row, ',', '"');
        }
        $query = null;
        if ($selfCn) {
            $cn = null;
        }
        return $rowCount;
    }

}
