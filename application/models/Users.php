<?php

class Application_Model_Users extends Zend_Db_Table {

    protected $_name = 'users';
    protected $_primary = 'user_id';

    public static function confirm($username, $tokenVerification) {
        if (!is_string($username)) {
            throw new Exception('$username must be a type of string');
        }
        if (!is_string($tokenVerification)) {
            throw new Exception('$tokenVerification must be a type of string');
        }
        $instance = new self;
        $instanceRow = $instance->fetchRow(array('username = ?' => $username));
        if (!$instanceRow) {
            return array('success' => false, 'error' => 'INVALID_USERNAME');
        } else {
            if ($instanceRow->active) {
                return array('success' => false, 'error' => 'ACCOUNT_ALREADY_ACTIVATED');
            } else if ($instanceRow->token_verification != $tokenVerification) {
                return array('success' => false, 'error' => 'INVALID_TOKEN_VERIFICATION');
            } else {
                $instanceRow->active = 1;
                $instanceRow->save();
                return array('success' => true);
            }
        }
    }

    public static function create($args) {
        /**/
        if (!is_array($args)) {
            throw new Exception('$args must be an array');
        }
        /**/
        if (empty($args)) {
            throw new Exception('$args is empty');
        }
        /* Response array */
        $response = array();
        /* Exchange args */
        $args['email'] = !empty($args['email']) ? (string) $args['email'] : '';
        $args['password'] = !empty($args['password']) ? (string) $args['password'] : '';
        $args['username'] = !empty($args['username']) ? (string) $args['username'] : '';
        /* Validators */
        $emailValidator = new Zend_Validate_EmailAddress();
        $emailDbRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(array('table' => 'users', 'field' => 'email'));
        $notEmptyValidator = new Zend_Validate_NotEmpty();
        $stringLengthValidator = new Zend_Validate_StringLength();
        $usernameDbRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(array('table' => 'users', 'field' => 'username'));
        /* Validate e-mail */
        if (!$notEmptyValidator->isValid($args['email'])) {
            $response['errors']['email'][] = 'Empty email';
        } else if (!$emailValidator->isValid($args['email'])) {
            $response['errors']['email'][] = 'Invalid email';
        } else if (!$emailDbRecordExistsValidator->isValid($args['email'])) {
            $response['errors']['email'][] = 'email taken';
        }
        /* Validate password */
        $stringLengthValidator->setMax(10);
        $stringLengthValidator->setMin(6);
        if (!$notEmptyValidator->isValid($args['password'])) {
            $response['errors']['password'][] = 'Empty password';
        } else if (!$stringLengthValidator->isValid($args['password'])) {
            $response['errors']['password'][] = 'Min 6 Max 10';
        }
        /* Validate username */
        $stringLengthValidator->setMax(15);
        $stringLengthValidator->setMin(5);
        if (!$notEmptyValidator->isValid($args['username'])) {
            $response['errors']['username'][] = 'Empty username';
        } else if (!$stringLengthValidator->isValid($args['username'])) {
            $response['errors']['username'][] = 'Min 5 Max 15';
        } else if (!$usernameDbRecordExistsValidator->isValid($args['username'])) {
            $response['errors']['username'][] = 'username taken';
        }
        /**/
        if (empty($response['errors'])) {
            $response['success'] = true;
            $instance = new self;
            $insert = array();
            $insert['created_at'] = date('Y-m-d H:i:s');
            $insert['email'] = $args['email'];
            $passwordSalt = sha1(microtime());
            $insert['password'] = sha1($args['password'] . $passwordSalt);
            $insert['password_salt'] = $passwordSalt;
            $insert['token_verification'] = sha1(microtime() . microtime());
            $insert['username'] = $args['username'];
            $instance->insert($insert);
        } else {
            $response['success'] = false;
        }
        return $response;
    }

    public static function login($username = '', $password = '') {
        /* Verify if username or password is empty */
        $usernameIsEmpty = empty($username);
        $passwordIsEmpty = empty($password);
        if ($usernameIsEmpty || $passwordIsEmpty) {
            $errors = array();
            if ($usernameIsEmpty) {
                $errors['username'] = 'Username empty';
            }
            if ($passwordIsEmpty) {
                $errors['password'] = 'Password empty';
            }
            return array('response' => array('success' => false, 'errors' => $errors));
        }
        /* Auth database adapter */
        $authAdapter = new Zend_Auth_Adapter_DbTable();
        $authAdapter->setTableName('users');
        $authAdapter->setIdentityColumn('username');
        $authAdapter->setCredentialColumn('password');
        $authAdapter->setCredentialTreatment('SHA1(CONCAT(?,password_salt))');
        $authAdapter->setIdentity((string) $username)->setCredential((string) $password);
        $authenticateResponse = $authAdapter->authenticate();
        switch ($authenticateResponse->getCode()) {
            case Zend_Auth_Result::FAILURE:
                $response = array('success' => false, 'errors' => array());
                break;
            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                $response = array('success' => false, 'errors' => array('password' => 'Invalid password'));
                break;
            case Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS:
                $response = array('success' => false, 'errors' => array());
                break;
            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                $response = array('success' => false, 'errors' => array('username' => 'Invalid username'));
                break;
            case Zend_Auth_Result::FAILURE_UNCATEGORIZED:
                $response = array('success' => false, 'errors' => array());
                break;
            case Zend_Auth_Result::SUCCESS:
                $response = array('success' => true);
                break;
        }
        return array('auth_adapter' => $authAdapter, 'response' => $response);
    }

}
