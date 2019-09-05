<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\security;

/**
 * IUserAuth: Required to be implemented by any Authentication Class
 * that would use SessionManager
 * @author girish
 */
interface IUserAuth {
    function login(UserAuthInfo $authInfo): ?UserInfo;
    function retrieveFromStore(UserAuthInfo $authInfo): UserInfo;
    function persistToStore(UserInfo $uinfo);
    function logout(UserInfo $uInfo);
}
