<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        $this->auth = Zend_Auth::getInstance();
        if (!$this->auth->hasIdentity()) {
            $this->_redirect('/login');
        }
    }

    public function indexAction() {
    }

}
