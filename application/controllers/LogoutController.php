<?php

class LogoutController extends Zend_Controller_Action {

    public function init() {
        $this->auth = Zend_Auth::getInstance();
    }

    public function indexAction() {
        if ($this->auth->hasIdentity()) {
            $this->auth->clearIdentity();
        }
        $this->redirect('/login');
    }

}
