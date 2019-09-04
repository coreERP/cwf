<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\data;

/**
 * DataConnectBuilder class can be inherited and customised
 * to create different types of connections.
 * 
 * Override method getCn() to achieve custom implementation
 * 
 * @author girish
 */
class ConnectionBuilder {

    const DB_DEFAULT = 'db';
    const DB_MAIN = 'DB_MAIN';

    /**
     * Stores the DB connections
     */
    protected static $dbConfs = [];

    /**
     * Prase the dbOptions from cwfconfig and 
     * construct connection strings
     * Each Database connection should be in following format
     * ['connect_1' => [ 'server' => 'hostname', 'dbname' => 'database', 
     *                      'dbuser' => 'Postgres User' 'dbpass' => 'User Password',
     *                      'port' => 'Optional custom port'],
     *  'connect_2' => [ 'server' => 'hostname', 'dbname' => 'database', 
     *                      'dbuser' => 'Postgres User' 'dbpass' => 'User Password',
     *                      'port' => 'Optional custom port']
     * ]
     * You need to provide a unique name for each connection. This is the dbType passed
     * to DataConnect::getData(). If the port is not mentioned, default 5432 will be used
     * 
     * @var array $config   Database connections
     */
    public function __construct(array $config) {
        foreach($config as $k => $v) {
            self::$dbConfs[$k] = array_merge($v, ['port' => 5432]);
        }
    }

    /**
     * Gets the Open Database Connection based on the DB_TYPE constants. If DB_TYPE is left blank, it will return 
     * the first connection from the collection. 
     * 
     * Warning:
     * Always ensure that the returned connection is set to null after usage. This would avoid open connections
     * floating around.
     * 
     * @param string $dbType    One of the DB_TYPE constants
     * @return \PDO             An open connection to the database.
     */
    public function getCn(string $dbType) {
        $dbCn = [];
        if (array_key_exists($dbType, self::$dbConfs)) {
            $dbCn = self::$dbConfs[$dbType];
        } elseif (count(self::$dbConfs) > 0 && $dbType == '') {
            $dbCn = self::$dbConfs[\array_key_first(self::$dbConfs)];
        } else {
            throw new \Exception("Requested dbType[$dbType] not found in collection. Failed to connect to database");
        }
        $cn = new \PDO('pgsql:host=' . $dbCn['server'] . ';port=' . $dbCn['port'] . ';dbname=' . $dbCn['database']
                , $dbCn['dbuser'], $dbCn['dbpass']);
        $cn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $cn;
    }

    /**
     * Override this method to parse any SQL and replace the constants within.
     * This method is always called before the sql is executed.
     * It would be a good practice to create and pass parameter values. However, 
     * in certain cases, you need to pass predefined constants and replace 
     * them in the SQL.
     * 
     * Create constant definitions that do not interfere with normal SQL or query syntax.
     * e.g: Using flower brackets and defining the constant within. {branch_id}, {user_id}
     * 
     * @param string $sql   Sql String containing variables embraced in {} to be replaced
     * @param array $vals   An associative array with key, values that would be replaced in sql.
     *                      Do not embrace the keys in {}.
     */
    public function parseSql(string $sql, array $vals): string {
        foreach($vals as $k => $v) {
            $sql = \str_replace("{$k}", $v, $sql);
        }
        return $sql;
    }
}
