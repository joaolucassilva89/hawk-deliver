<?php

class ListsController extends Zend_Controller_Action {

    public function init()
    {
        $this->auth = Zend_Auth::getInstance();
        if($this->auth->hasIdentity()) {
            $this->_redirect('/');
        }
    }

    public function indexAction()
    {
        
    }

    public function createAction()
    {
        
    }

    public function readAction()
    {
        
    }

    public function updateAction()
    {
        
    }

}
