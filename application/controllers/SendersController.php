<?php

class SendersController extends Zend_Controller_Action {

    public function init()
    {
        $this->auth = Zend_Auth::getInstance();
    }

    public function createAction()
    {
        if($this->_request->isXmlHttpRequest() && $this->_request->isPost()) {
            try {
                /* Create user */
                $response = Application_Model_Senders::create($this->getParam('email', ''), $this->getParam('name', ''));
                /* Send json output */
                $this->_helper->json->sendJson($response);
            } catch(Exception $e) {
                var_dump($e->getMessage());
                exit;
            }
        }
    }

    public function indexAction()
    {
        
    }

    public function listAction()
    {
        Application_Model_Senders::setDefaultAdapter(Zend_Registry::get('user_db'));
        $this->_helper->json->sendJson(Application_Model_Senders::getAll($this->getAllParams()));
    }

}
