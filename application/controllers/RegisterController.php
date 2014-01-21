<?php

class RegisterController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->layout->setLayout('register');
    }

    public function indexAction() {
        if ($this->_request->isXmlHttpRequest() && $this->_request->isPost()) {
            try {
                /* Create user */
                $response = Application_Model_Users::create($_POST);
                /* Send email */
                /* Send json output */
                $this->_helper->json->sendJson($response);
            } catch (Exception $e) {
                
            }
        }
    }

}
