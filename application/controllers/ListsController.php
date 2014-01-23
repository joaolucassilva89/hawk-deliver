<?php

class ListsController extends Zend_Controller_Action {

    public function init()
    {
        $this->auth = Zend_Auth::getInstance();
        if(!$this->auth->hasIdentity()) {
            $this->_redirect('/');
        }
    }

    public function indexAction()
    {
        
    }

    public function createAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            Application_Model_Senders::setDefaultAdapter(Zend_Registry::get('user_db'));
            $response = Application_Model_Lists::create($this->getAllParams());
            $this->_helper->json->sendJson($response);
        }
    }
    
    public function listAction() {
        if($this->_request->isXmlHttpRequest()) {
            Application_Model_Senders::setDefaultAdapter(Zend_Registry::get('user_db'));
            $this->_helper->json->sendJson(Application_Model_Lists::getAll($this->getAllParams()));
        }
    }

    public function readAction()
    {
        
    }

    public function updateAction()
    {
        
    }

}
