<?php

class AccountController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // action body
    }

    public function confirmAction() {
        $this->_helper->layout->disableLayout();
        $tokenVerification = $this->_request->getparam('token_verification', '');
        $username = $this->_request->getParam('username', '');
        $response = Application_Model_Users::confirm($username, $tokenVerification);
        if ($response['success']) {
            $this->_redirect('/login?activated=1');
        } else {
            $this->_redirect('/login');
        }
    }

}
