<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\security;

/**
 * SessionManager: This singleton class manages the UserSession
 * 
 * @author girish
 */
class SessionManager {

    /**
     * Contains a reference to self
     * @var SessionManager 
     */
    private static $instance = null;
    
    /**
     * Holds UserInfo after successful login
     * @var UserInfo    The UserInfo Instance 
     */
    private $userInfo = null;

    /**
     * @param AuthInfo $authInfo
     */
    private function __construct(UserInfo $uinfo) {
        $this->userInfo = $uinfo;
        \Yii::$app->cache->cachePath = '@runtime/cache/sid' . (string) $this->userInfo->getSession_ID();
        \yii::$app->cache->init();
    }
    
    public static function createInstance(UserInfo $uinfo) {
        self::$instance = new SessionManager($uinfo);
    }
    
    public static function disposeInstance() {
        self::$instance = null;
    }

    /**
     * 
     * @param AuthInfo $authInfo
     * @return SessionManager
     */
    public static function getInstance() {
        return self::$instance;
    }

    public static function hasInstance() {
        if (self::$instance == null) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    /**
     * Returns if Auth instance is created or not
     * @return bool     True if created, else False
     */
    public static function getAuthStatus(): bool {
        return self::$instance != null;
    }
    
    /**
     * Returns a session variable from UserInfo.
     * Friendly method wrapper for UserInfo->getSessionVariable
     * 
     * @param string $key     Variable Key
     * @return mixed          Variable Value
     * @throws \Exception
     */
    public static function getSessionVariable(string $key) {
        if (self::$instance == null) {
            throw new \Exception('Session Variable requests are invalid without user login');
        } else {
            return self::$instance->userInfo->getSessionVariable($key);
        }
    }

    /**
     * Gets the Authenticated user info
     * @return UserInfo
     */
    public function getUserInfo(): UserInfo {
        return $this->userInfo;
    }
    
    /**
     * Handles the specified request.
     * @param Request $request the request to be handled 
     * This method should be called in index.php as yii application EVENT_BEFORE_REQUEST
     * 
     * \yii\base\Event::on(\yii\web\Application::className(), \yii\web\Application::EVENT_BEFORE_REQUEST, function($event) {
     *      $request = $event->sender->getRequest();
     *      
     * }
     *      */
    public static function createUserSessionForCoreAPI($request) {
        // create a session with whatever information is available is available
        // An auth_id is compulsory. This comes from the header        
        $authInfo = new \app\cwf\vsla\security\AuthInfo();
        // First get auth_id (This is in the php session)
        if ($request->getHeaders()->has('auth-id')) {
            $authInfo->auth_id = $request->getHeaders()->get('auth-id');
        }
        // See if the header has session id (This is used by ajax calls)
        if ($request->getHeaders()->has('core-sessionid')) {
            $authInfo->session_id = $request->getHeaders()->get('core-sessionid');
        } else {
            // See if query param has session id (This is used by browswe requests)
            $qp = $request->queryParams;
            if (array_key_exists('core-sessionid', $qp)) {
                $authInfo->session_id = $request->queryParams['core-sessionid'];
            }
        }
        if ($authInfo->auth_id != '') {
            // Auth info would contain core-session id if it is available
            self::getInstance($authInfo);
        }
    }

    public static function getTitle() {
        $branch_id = self::getSessionVariable('branch_id');
        $title = 'coreERP';
        if ($branch_id != null && $branch_id != -1) {
            $cmm = new \app\cwf\vsla\data\SqlCommand();
            $cmm->setCommandText('Select company_code || branch_code as cc_code From sys.branch Where branch_id=:pbranch_id');
            $cmm->addParam('pbranch_id', $branch_id);
            $dt = \app\cwf\vsla\data\DataConnect::getData($cmm);
            if (count($dt->Rows()) == 1) {
                $title .= '-' . $dt->Rows()[0]['cc_code'];
            }
            $finyear = self::getSessionVariable('finyear');
            if ($finyear != null && $finyear != '') {
                $title .= "[" . $finyear . "]";
            }
        }
        return $title;
    }

    private static $branch_gst_state_info = null;

    public static function getBranchGstInfo() {
        if (self::$branch_gst_state_info == null) {
            $branch_id = self::getSessionVariable('branch_id');
            if ($branch_id != -1) {
                $cmm = new \app\cwf\vsla\data\SqlCommand();
                $cmm->setCommandText("Select a.gstin, a.gst_state_id, b.gst_state_code, b.state_name, 
                                        b.gst_state_code || '-' || b.state_name as gst_state,
                                        coalesce((a.annex_info->>'gst_sez_wop')::Boolean, false) gst_sez_wop,
                                        coalesce((a.annex_info->>'gst_exp_wop')::Boolean, false) gst_exp_wop
                                    From sys.branch a
                                    Inner Join tx.gst_state b On a.gst_state_id = b.gst_state_id
                                    Where a.branch_id=:pbranch_id");
                $cmm->addParam('pbranch_id', $branch_id);
                $dt = \app\cwf\vsla\data\DataConnect::getData($cmm);
                if (count($dt->Rows()) == 1) {
                    self::$branch_gst_state_info = $dt->Rows()[0];
                }
            }
        }
        return self::$branch_gst_state_info;
    }

}
