<?php

class Hawk_Models_Database_User_Senders extends Zend_Db_Table {

    protected $_name = 'senders';
    protected $_primary = 'sender_id';

    public static function create($args = array())
    {
        /* Filters */
        $filterStringTrim = new Zend_Filter_StringTrim();
        /* Filter email|name */
        $args['email'] = $filterStringTrim->filter($args['email']);
        $args['name'] = $filterStringTrim->filter($args['name']);
        /* Validators */
        $nameStringLengthValidator = new Zend_Validate_StringLength(array('min' => 2, 'max' => 50));
        $emailStringLengthValidator = new Zend_Validate_StringLength(array('max' => 255));
        $emailEmailValidator = new Zend_Validate_EmailAddress();
        $emailDbNoRecordExistsValidator = new Zend_Validate_Db_NoRecordExists(array('table' => 'senders', 'field' => 'email'));
        /* Validate name */
        if(!$nameStringLengthValidator->isValid($args['name'])) {
            $errors['name'] = 'INVALID_LENGTH';
        }
        /* Validate email */
        if(!$emailStringLengthValidator) {
            $errors['email'] = 'INVALID_LENGTH';
        } else if(!$emailEmailValidator->isValid($args['email'])) {
            $errors['email'] = 'INVALID_FORMAT';
        } else if(!$emailDbNoRecordExistsValidator->isValid($args['email'])) {
            $errors['email'] = 'ALREADY_TAKEN';
        }
        /**/
        if(!empty($errors)) {
            return array('success' => false, 'errors' => $errors);
        }
        /**/
        $instance = new self;
        $instance->insert(array('email' => $args['email'], 'name' => $args['name']));
        return array('success' => true);
    }

    public static function getAll($args = array())
    {
        $args = (array) $args;
        /**/
        $args['dir'] = empty($args['dir']) ? '' : strtolower((string) $args['dir']);
        $args['limit'] = empty($args['limit']) ? 1 : (int) $args['limit'];
        $args['page'] = empty($args['page']) ? 1 : (int) $args['page'];
        $args['query'] = empty($args['query']) ? '' : (string) $args['query'];
        $args['sort'] = empty($args['sort']) ? '' : (string) $args['sort'];
        /**/
        $instance = new self;
        /**/
        $select = $instance->select();
        /**/
        if(!empty($args['sort']) &&
                !empty($args['dir']) &&
                in_array($args['sort'], array('email', 'name')) &&
                in_array($args['dir'], array('asc', 'desc'))) {
            $select->order($args['sort'].' '.$args['dir']);
        }
        /* Query */
        if(!empty($args['query'])) {
            $select->where('email like "?"', new Zend_Db_Expr('%'.$args['query'].'%'));
        }
        /**/
        $adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
        /**/
        $paginator = new Zend_Paginator($adapter);
        /**/
        $paginator->setCurrentPageNumber($args['page'])->setItemCountPerPage(20);
        /**/
        return array('totalItems' => $paginator->getTotalItemCount(), 'items' => $paginator->getCurrentItems()->toArray());
    }

}
