<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\security;

/**
 * AuthInfo: User's Pre-authentication information. Load this class with whatever information available
 * and the UserAuth will try to authenticate the user based on this info
 * @author girish
 */
class UserAuthInfo {

    public $auth_id = '';
    public $session_id = '';
    public $username = '';
    public $userpass = '';
    public $person_id = '';
    public $token = '';
    public $is_mobile = false;

}
