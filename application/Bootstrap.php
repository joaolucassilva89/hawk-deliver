<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initUserDatabase()
    {
        $frontController = Zend_Controller_Front::getInstance();

        // set custom request object
        $frontController->setRequest(new REST_Request);
        $frontController->setResponse(new REST_Response);

        // add the REST route for the API module only
        $restRoute = new Zend_Rest_Route($frontController, array(), array('api'));
        $frontController->getRouter()->addRoute('rest', $restRoute);
        
        $zendAuth = Zend_Auth::getInstance();
        if($zendAuth->hasIdentity()) {
            $user_db = Zend_Db::factory('Pdo_Mysql', array(
                        'host' => '127.0.0.1',
                        'username' => 'root',
                        'password' => '123456',
                        'dbname' => 'user_'.$zendAuth->getIdentity()->user_id,
                        'charset' => 'utf8'
                            )
            );
            Zend_Registry::set('user_db', $user_db);
        }
    }

}

