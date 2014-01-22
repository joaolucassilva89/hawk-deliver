<?php

class RegisterController extends Zend_Controller_Action {

    public function init()
    {
        $this->_helper->layout->setLayout('register');
    }

    public function indexAction()
    {
        if($this->_request->isXmlHttpRequest() && $this->_request->isPost()) {
            try {
                /* Create user */
                $response = Application_Model_Users::create($_POST);
                /* Send json output */
                $this->_helper->json->sendJson($response);
            } catch(Exception $e) {
                
            }
        }
    }

    public function databaseAction()
    {
        ini_set('display_errors', true);
        error_reporting(E_ALL);
        require 'MysqlDatabaseXmlToSchema/MysqlDatabaseXmlToSchema.php';
        $file = APPLICATION_PATH.'/../data/schemas/database/default.xml';
        $mysqlDatabaseXmlToSchema = new MysqlDatabaseXmlToSchema($file, "", null, "localhost", "root", "123456");
        $log = $mysqlDatabaseXmlToSchema->XML2Schema();
        echo $log;
        $file = APPLICATION_PATH.'/../data/schemas/database/users.xml';
        $mysqlDatabaseXmlToSchema = new MysqlDatabaseXmlToSchema($file, "", null, "localhost", "root", "123456");
        $log = $mysqlDatabaseXmlToSchema->XML2Schema();
        echo $log;
        exit;
    }

}
