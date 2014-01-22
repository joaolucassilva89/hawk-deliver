<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initUserDatabase()
    {
        $zendAuth = Zend_Auth::getInstance();
        if($zendAuth->hasIdentity()) {
            $user_db = Zend_Db::factory('Pdo_Mysql', array(
                        'host' => '127.0.0.1',
                        'username' => 'root',
                        'password' => '123456',
                        'dbname' => 'user_'.$zendAuth->getIdentity()->user_id
                            )
            );
            Zend_Registry::set('user_db', $user_db);
        }
    }

}

