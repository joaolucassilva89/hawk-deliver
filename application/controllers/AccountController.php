<?php

class AccountController extends Zend_Controller_Action {

    public function init()
    {
        $this->auth = Zend_Auth::getInstance();
    }

    public function indexAction()
    {
        // action body
    }

    public function confirmAction()
    {
        $this->_helper->layout->disableLayout();
        $tokenVerification = $this->_request->getparam('token_verification', '');
        $username = $this->_request->getParam('username', '');
        $response = Application_Model_Users::confirm($username, $tokenVerification);
        if($response['success']) {
            $this->_redirect('/login?activated=1');
        } else {
            $this->_redirect('/login');
        }
    }

    public function passwordAction()
    {
        if($this->_request->isXmlHttpRequest() && $this->_request->isPost()) {
            try {
                /* Current session user id */
                $userId = $this->auth->getIdentity()->user_id;
                /* Current password */
                $password = $this->getParam('password', '');
                /* New password */
                $newPassword = $this->getParam('new-password', '');
                /* Confirm new password */
                $confirmNewPassword = $this->getParam('confirm-new-password', '');
                /* Change password */
                $response = Application_Model_Users::changePassword($userId, $password, $newPassword, $confirmNewPassword);
                /* Send json output */
                $this->_helper->json->sendJson($response);
            } catch(Exception $e) {
                var_dump($e);exit;
            }
        }
    }

}
