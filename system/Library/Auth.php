<?php
namespace Sys\Library;

class Auth extends \Sys\Core\Component {

    protected $_config;
    protected $_db;
    protected $_error;

    public function __construct(\Sys\Core\IApplication $app) {
        parent::__construct($app);

        $this->_config = $this->getAppConfig()->application->auth;
        $this->_db = $this->getApp()->getReadDb();
        $this->_error = NULL;
    }

    public function login($email, $pass) {
        $tabuser = $this->_config->user_table;

        $security = $this->getService('security');
        $session  = $this->getService('session');

        // check session
        if ($session->has('CURRENT_USER')) {
            return TRUE;
        }
        
        $user = $this->_db->fetchOne(
            "SELECT * FROM {$tabuser} WHERE email = ?", 
            array($email)
        );

        if ($user) {
            // verify password
            $hashed = $security->generateHash($pass, $user->passwd_salt);

            if ($hashed == $user->passwd) {

                $token = $security->generateToken($user);

                // save to session
                $this->save($user);

                // update table
                
                $this->_db->update(
                    $tabuser, 
                    array(
                        'access_token' => $token,
                        'last_login' => date('Y-m-d H:i:s'),
                        'last_ip' => $this->getService('request')->getClientAddress()
                    ),
                    array(
                        'email' => $email
                    )
                ); 

                $this->getService('role')->refresh();
                return TRUE;
            }
        }

        $this->_error = "Email atau password tidak valid";
        return FALSE;
    }

    public function logout() {
        $session = $this->getService('session');

        if ($session->has('CURRENT_USER')) {
            $user = $session->get('CURRENT_USER');
            $session->remove('CURRENT_USER');

            $this->getService('role')->refresh();
        }
    }

    public function register($spec) {
        $tabuser  = $this->_config->user_table;
        $security = $this->getService('security');

        $user = $this->_db->fetchOne("SELECT * FROM {$tabuser} WHERE email = ?", array($spec['email']));
        if ($user) {
            $this->_error = 'Alamat email tidak tersedia';
            return FALSE;
        } else {
            // create password
            $salt = $security->generateSalt();
            $hash = $security->generateHash($spec['passwd'], $salt);

            $spec['passwd'] = $hash;
            $spec['passwd_salt'] = $salt;

            if ($this->_db->insert('user', $spec)) {
                $user = $this->_db->fetchOne("SELECT * FROM user WHERE email = ?", array($spec['email']));
                return $user->toArray();
            } else {
                $this->_error = 'Terjadi kegagalan dalam pembuatan akun';
                return FALSE;
            }
        }
    }

    public function getUser() {
        $session = $this->getService('session');
        return $session->get('CURRENT_USER');
    }

    /**
     * Save user into session
     */
    public function save($user) {
        $session = $this->getService('session');
        $data = new \stdClass();

        // remove sensitive data
        foreach($user as $key => $val) {
            if ( ! in_array($key, array('passwd', 'passwd_salt', 'id'))) {
                $data->{$key} = $val;
            }
        }

        $session->set('CURRENT_USER', $data);
    }

    public function getError() {
        return $this->_error;
    }
}