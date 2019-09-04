<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\security;

/**
 * AccessLevels: Defines various access levels
 *
 * @author devadatta
 */
class AccessLevels {

    const NOACCESS = 0;
    const READONLY = 1;
    const DATAENTRY = 2;
    const AUTHORIZE = 3;
    const CONSOLIDATED = 4;
    const ALLOW_DELETE = TRUE;
    const ALLOW_UNPOST = TRUE;

    public static function getLevel($accessLevel_int) {
        switch ($accessLevel_int) {
            case 1:
                return self::READONLY;
            case 2:
                return self::DATAENTRY;
            case 3:
                return self::AUTHORIZE;
            case 4:
                return self::CONSOLIDATED;
            default :
                return self::NOACCESS;
        }
    }

}
