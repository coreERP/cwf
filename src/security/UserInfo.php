<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\security;

/**
 * UserInfo: User's Post authentication information
 * This class is serialized and stored in table/file store based on 
 * UserAuth::persistToStore
 * UserAuth::retreiveFromStore
 * 
 * @author girish
 */
class UserInfo {
    
    private $auth_id;
    private $session_id;
    private $full_user_name = '';
    private $user_id = -1;
    private $user_name = '';
    private $sess_vars = [];
    
    public function __construct() {
        $this->auth_id = uniqid();
        $this->session_id = uniqid();
    }
    
    public function setInfo(string $uname, int $uid, string $funame) {
        $this->user_name = $uname;
        $this->user_id = $uid;
        $this->full_user_name = $funame;
    }
    
    /**
     * Creates/Resets the User Session ID. Call only when you are sure
     * about what you are doing
     */
    public function createNewSession() {
        $this->session_id = uniqid();
    }
    
    /**
     * Returns the unique session id
     * @return stringGUID
     */
    public function getSession_ID() {
        return $this->session_id;
    }
    
    /**
     * Returns the unique auth id
     * @return stringGUID
     */
    public function getAuth_ID() {
        return $this->auth_id;
    }
    
    /**
     * Returns the Full User Name
     * @return string
     */
    public function getFullUserName() {
        return $this->full_user_name;
    }
    
    /**
     * Returns the authentication status
     * @return bool
     */
    public function getAuthStatus() {
        return $this->auth_id != null;
    }
    
    /**
     * Returns the UserName
     * @return string
     */
    public function getUserName() {
        return $this->user_name;
    }
    
    /**
     * Returns the User ID
     * @return Int
     */
    public function getUser_ID() {
        return $this->user_id;
    }
    
    /**
     * Returns if user is Admin
     * @return boolean
     */
    public function isAdmin(){
        return $this->getAuthStatus() && (bool)$this->sess_vars['is_admin'];
    }
    
    /**
     * Returns if user is Owner
     * @return boolean
     */
    public function isOwner(){
        return (bool)$this->sess_vars['is_owner'];
    }
       
    /**
     * Returns a session variable value based on key
     * Will generate index error if key is not found
     * @return mixed
     */
    public function getSessionVariable($key) {
        return $this->sess_vars[$key];
    }
    
    /**
     * Returns if session contains the variable
     * @return mixed
     */
    public function hasSessionVariable($key){
        return array_key_exists($key, $this->sess_vars);
    }
    
    /**
     * Sets a session variable value based on key.
     * If the key already exists, the value is replaced
     * @return mixed
     */
    public function setSessionVariable($key, $value) {
        $this->sess_vars[$key] = $value;
    }
}
