<?php

class Application_Model_Users {

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
        } else {
            $response['success'] = false;
        }
        return $response;
    }

}
