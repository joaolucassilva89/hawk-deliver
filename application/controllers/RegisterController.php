<?php

class RegisterController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
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
                var_dump($e->getMessage());
                exit;
            }
        }
    }

}
