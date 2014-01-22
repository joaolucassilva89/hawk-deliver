<?php

class LoginController extends Zend_Controller_Action {

    public function init() {
        $this->auth = Zend_Auth::getInstance();
        if ($this->auth->hasIdentity()) {
            $this->_redirect('/');
        }
        $this->_helper->layout->setLayout('login');
    }

    public function indexAction() {
        if ($this->_request->isXmlHttpRequest() && $this->_request->isPost()) {
            try {
                $password = $this->_request->getParam('password', '');
                $remember = $this->_request->getparam('remember', '');
                $username = $this->_request->getParam('username', '');
                $response = Application_Model_Users::login($username, $password);
                if ($response['response']['success']) {
                    $resultObject = $response['auth_adapter']->getResultRowObject(null, array('password', 'password_salt'));
                    /**/
                    if (!$resultObject->active) {
                        $json = array();
                        $json['success'] = false;
                        $json['awaiting_activation'] = true;
                        $json['errors'] = array('Cenas que são feitas assim');
                        $this->_helper->json->sendJson($json);
                    }
                    if ($remember) {
                        Zend_Session::rememberMe(31536000);
                    }
                    $zendAuthStorage = Zend_Auth::getInstance()->getStorage();
                    $zendAuthStorage->write($resultObject);
                }
                $this->_helper->json->sendJson($response['response']);
            } catch (Exception $e) {
                var_dump($e->getMessage());
                exit;
            }
        }
    }

}
