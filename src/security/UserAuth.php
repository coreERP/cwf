<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\security;

/**
 * UserAuth: This can be used to authenticate the user
 * 
 * @author girish
 */
class UserAuth implements IUserAuth {

    /**
     * Contains the authentication fail reason/message
     * @var string
     */
    public $authFailMsg = '';

    /**
     * Authenticates the user in the database
     * 
     * $param UserAuthInfo $authInfo    Should contain either auth_id/session_id/username+pass
     */
    public function login(UserAuthInfo $authInfo): ?UserInfo {
        $uinfo = null;
        if ($authInfo->auth_id != '' && $authInfo->session_id != '') {
            // complete session. Retreive from db
            $uinfo = $this->retrieveFromStore($authInfo);
        } elseif ($authInfo->auth_id != '' && $authInfo->session_id == '') {
            // Auth exits, new session requested.
            $cmm = new SqlCommand();
            $cmm->setCommandText("Select a.user_id, a.full_user_name, a.user_name, b.session_info
                    From sys.user a 
                    Inner Join sys.user_session b On a.user_id=b.user_id 
                    Where b.auth_id = :pauth_id Limit 1;");
            $cmm->addParam("pauth_id", $authInfo->auth_id);
            $dtSess = \cwf\data\DataConnect::getData($cmm, \cwf\data\ConnectionBuilder::DB_DEFAULT);
            if (count($dtResult->Rows()) == 1) {
                $uinfo = unserialize($dtSess->Rows()[0]['session_info']);
                $uinfo->createNewSession();
                $this->persistToStore($uinfo);
            }
        } elseif ($authInfo->username !== '' && ($authInfo->userpass !== '' || $authInfo->person_id !== '' || $authInfo->token !== '')) {
            // First time authentication
            $cmm = new \cwf\data\SqlCommand();
            $cmm->setCommandText("Select user_id, user_name, user_pass, full_user_name, is_owner, is_admin
                    From sys.user 
                    Where user_name=:puser_name And is_active");
            $cmm->addParam("puser_name", $authInfo->username);
            $dtResult = \cwf\data\DataConnect::getData($cmm, \cwf\data\ConnectionBuilder::DB_DEFAULT);
            if (count($dtResult->Rows()) == 1) {
                $validUser = false;
                $dr = $dtResult->Rows()[0];
                // First try with password
                if ($authInfo->userpass !== '') {
                    //$validUser = \yii::$app->getSecurity()->validatePassword($authInfo->userpass, $dtResult->Rows()[0]['user_pass']);
                    $validUser = $authInfo->userpass == $dtResult->Rows()[0]['user_pass'];
                } elseif ($authInfo->person_id !== '') {
                    // pre authenticated user from OAuth
                    $validUser = true;
                } elseif ($authInfo->token !== '') {
                    // pre authenticated user from external client
                    $validUser = true;
                }
                if (!$validUser) {
                    $this->authFailMsg = 'Username/password is incorrect. Login failed.';
                }
                // Finally, if the user is valid, then create a session
                if ($validUser) {
                    $uinfo = new UserInfo();
                    $uinfo->setInfo($dr['user_name'], $dr['user_id'], $dr['full_user_name']);
                    $uinfo->setSessionVariable('is_admin', $dr['is_admin']);
                    $uinfo->setSessionVariable('is_owner', $dr['is_owner']);
                    $uinfo->setSessionVariable('is_mobile', $authInfo->is_mobile);
                    $this->persistToStore($uinfo);
                }
            } else {
                $this->authFailMsg = 'Invalid user or user not active';
            }
        }
        if ($uinfo != null) {
            SessionManager::createInstance($uinfo);
        }
        return $uinfo;
    }

    /**
     * Retrieve the user session from store. Currently retrieves from table sys.user_session. 
     * Override to use apcCache/file/redis. Simultaneously, also override persitToStore
     * 
     * @param \cwf\security\UserAuthInfo $authInfo  The authInfo
     */
    public function retrieveFromStore(UserAuthInfo $authInfo): UserInfo {
        $cmm = new \cwf\data\SqlCommand();
        if ($authInfo->session_id != '') {
            // Session id always takes priority as it is created with auth_id
            $sql = "Select a.session_info
                    From sys.user_session a
                    Where a.user_session_id = :pus_id";
            $cmm->addParam('pus_id', $authInfo->session_id);
        } elseif ($authInfo->auth_id != '') {
            // fallback to auth id if session id not available
            $sql = "Select a.session_info
                    From sys.user_session a
                    Where a.auth_id = :pauth_id limit 1";
            $cmm->addParam('pauth_id', $authInfo->auth_id);
        } else {
            throw new \Exception('Either Session_id or Auth_id required. Both cannot be ignored');
        }
        $cmm->setCommandText($sql);
        $dt = \cwf\data\DataConnect::getData($cmm, \cwf\data\ConnectionBuilder::DB_DEFAULT);
        if (count($dt->Rows()) == 1) {
            $uinfo = \unserialize(base64_decode($dt->Rows()[0]['session_info']));
            return $uinfo;
        } else {
            throw new \Exception('Failed to retreive Session Info from Store');
        }
    }

    /**
     * Persist the user session to store. Currently stores to table sys.user_session. 
     * Override to use apcCache/file/redis. Simultaneously, also override persitToStore
     * 
     * @param UserInfo $uinfo  The UserInfo instance to be serialised
     */
    public function persistToStore(UserInfo $uinfo) {
        // Persist to Database
        $cmm = new \cwf\data\SqlCommand();
        $cmmText = "Insert Into sys.user_session(user_session_id, auth_id, user_id, login_time, last_refresh_time, session_info)
                    Values (:pus_id, :pauth_id, :puser_id, current_timestamp(0), current_timestamp(0), :psession_info)
                    On Conflict (user_session_id)
                    Do Update set session_info = :psession_info, last_refresh_time = current_timestamp(0)";
        $cmm->setCommandText($cmmText);
        $cmm->addParam("pus_id", $uinfo->getSession_ID());
        $cmm->addParam("pauth_id", $uinfo->getAuth_ID());
        $cmm->addParam("puser_id", $uinfo->getUser_ID());
        $data = \serialize($uinfo);
        $cmm->addParam("psession_info", base64_encode($data));
        \cwf\data\DataConnect::exeCmm($cmm, null, \cwf\data\ConnectionBuilder::DB_DEFAULT);
    }

    public function logout(UserInfo $uinfo) {
        // logout from database
        $cmm = new \cwf\data\SqlCommand('Delete from sys.user_session where user_session_id = :puss_id');
        $cmm->addParam("puss_id", $uinfo->getSession_ID());
        \cwf\data\DataConnect::exeCmm($cmm, null, \cwf\data\ConnectionBuilder::DB_DEFAULT);
        SessionManager::disposeInstance();
    }

    /**
     * This method is called \yii\web\Application::EVENT_BEFORE_REQUEST
     * It ensures that the request is originating from an already
     * authenticated user
     * 
     * @param \yii\web\Request $req     Request instance
     */
    public function preProcessAuth(\yii\web\Request $req) {
        if (\yii::$app->session->has('auth_id')) {
            $ai = new \cwf\security\UserAuthInfo();
            $ai->auth_id = \yii::$app->session['auth_id'];
            $ai->session_id = \yii::$app->session['sess_id'];
            $ua = new \cwf\security\UserAuth();
            $uinfo = $ua->login($ai);
        }
    }
}
