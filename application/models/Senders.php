<?php

class Application_Model_Senders extends Zend_Db_Table {

    protected $_name = 'senders';
    protected $_primary = 'sender_id';

    public static function create($email = '', $name = '')
    {
        /**/
        if(!Zend_Registry::isRegistered('user_db')) {
            throw new Exception('user db is not registered');
        }
        /**/
        Application_Model_Senders::setDefaultAdapter(Zend_Registry::get('user_db'));
        /* Check if email is a string */
        if(!is_string($email)) {
            throw new Exception('$email must be string');
        }
        /* Check if name is a string */
        if(!is_string($name)) {
            throw new Exception('$name must be string');
        }
        /**/
        $errors = array();
        /* Filters */
        $filterStringTrim = new Zend_Filter_StringTrim();
        /* Filter email|name */
        $email = $filterStringTrim->filter($email);
        $name = $filterStringTrim->filter($name);
        /* Validators */
        $nameStringLengthValidator = new Zend_Validate_StringLength(array('min' => 2, 'max' => 50));
        $emailStringLengthValidator = new Zend_Validate_StringLength(array('max' => 255));
        $emailEmailValidator = new Zend_Validate_EmailAddress();
        $emailDbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(array('table' => 'senders', 'field' => 'email'));
        /* Validate name */
        if(!$nameStringLengthValidator->isValid($name)) {
            $errors['name'] = 'SENDER_ERROR_NAME_INVALID_LENGTH';
        }
        /* Validate email */
        if(!$emailStringLengthValidator) {
            $errors['email'] = 'SENDER_ERROR_EMAIL_INVALID_LENGTH';
        } else if(!$emailEmailValidator->isValid($email)) {
            $errors['email'] = 'SENDER_ERROR_EMAIL_INVALID_FORMAT';
        } else if(!$emailDbNoRecordExistsValidator->isValid($email)) {
            $errors['email'] = 'SENDER_ERROR_EMAIL_ALREADY_TAKEN';
        }
        /**/
        if(!empty($errors)) {
            return array('success' => false, 'errors' => $errors);
        }
        /**/
        $instance = new self;
        $instance->insert(array('email' => $email, 'name' => $name));
        return array('success' => true);
    }

    public static function list_()
    {
       
    }

}
