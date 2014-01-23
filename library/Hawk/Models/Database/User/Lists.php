<?php

class Hawk_Models_Database_User_Lists extends Zend_Db_Table {

    protected $_name = 'lists';
    protected $_primary = 'list_id';

    public static function create($args)
    {
        /**/
        if(!is_array($args)) {
            throw new Exception('$args must be an array');
        }
        /* Exchange args */
        $args['address'] = !empty($args['address']) ? (string) $args['address'] : ' ';
        $args['city'] = !empty($args['city']) ? (string) $args['city'] : '';
//        $args['country'] = !empty($args['country']) ? (int) $args['country'] : 0;
        $args['location'] = !empty($args['location']) ? (string) $args['location'] : '';
        $args['postal_code'] = !empty($args['postal_code']) ? (string) $args['postal_code'] : '';
        $args['phone'] = !empty($args['phone']) ? (string) $args['phone'] : '';
        $args['sender'] = !empty($args['sender']) ? (string) $args['sender'] : ' ';
        $args['title'] = !empty($args['title']) ? (string) $args['title'] : '';
        /* Filters */
        $args = array_map(array(new Zend_Filter_StringTrim(), 'filter'), $args);
        /* Validators */
        $notEmptyValidator = new Zend_Validate_NotEmpty();
        $stringLengthValidator = new Zend_Validate_StringLength(array('max' => 100, 'min' => 1));
        /* Validate address */
        if($notEmptyValidator->isValid($args['address']) && !$stringLengthValidator->isValid($args['address'])) {
            $errors['address'] = 'INVALID_LENGTH';
        }
        /* Validate city */
        if($notEmptyValidator->isValid($args['city']) && !$stringLengthValidator->isValid($args['city'])) {
            $errors['city'] = 'INVALID_LENGTH';
        }
        /* Validate country */
        if($notEmptyValidator->isValid($args['location']) && !$stringLengthValidator->isValid($args['location'])) {
            $errors['location'] = 'INVALID_LENGTH';
        }
        /* Validate postal_code */
        if($notEmptyValidator->isValid($args['postal_code']) && !$stringLengthValidator->isValid($args['postal_code'])) {
            $errors['postal_code'] = 'INVALID_LENGTH';
        }
        /* Validate phone */
        if($notEmptyValidator->isValid($args['phone']) && !$stringLengthValidator->isValid($args['phone'])) {
            $errors['phone'] = 'INVALID_LENGTH';
        }
        /* Validate title */
        if(!$notEmptyValidator->isValid($args['sender'])) {
            $errors['sender'] = 'EMPTY';
        } else if(!$stringLengthValidator->isValid($args['sender'])) {
            $errors['sender'] = 'INVALID_LENGTH';
        } else {
            $senderInstance = new Application_Model_Senders();
            $senderInstanceRow = $senderInstance->fetchRow(array('email = ?' => $args['sender']));
            if(!$senderInstanceRow) {
                $errors['sender'] = 'INVALID_SENDER';
            } else if(!$senderInstanceRow->confirmed) {
                $errors['sender'] = 'SENDER_NOT_CONFIRMED';
            }
        }
        /* Validate title */
        if(!$notEmptyValidator->isValid($args['title'])) {
            $errors['title'] = 'EMPTY';
        } else if(!$stringLengthValidator->isValid($args['title'])) {
            $errors['title'] = 'INVALID_LENGTH';
        }
        /**/
        if(!empty($errors)) {
            return array('success' => false, 'errors' => $errors);
        }
        $instance = new self;
        $insert = array();
        $insert['address'] = $args['address'];
//        $insert['country'] = $args['country'];
        $insert['city'] = $args['city'];
        $insert['location'] = $args['location'];
        $insert['postal_code'] = $args['postal_code'];
        $insert['phone'] = $args['phone'];
        $insert['sender_id'] = $senderInstanceRow->sender_id;
        $insert['title'] = $args['title'];
        $instance->insert($insert);
        return array('success' => true);
    }

    public static function getAll($args = array())
    {
        /**/
        if(!is_array($args)) {
            throw new Exception('$args must be an array');
        }
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
            #$select->where('email like "?"', new Zend_Db_Expr('%'.$args['query'].'%'));
        }
        /**/
        $adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
        /**/
        $paginator = new Zend_Paginator($adapter);
        /**/
        $paginator->setCurrentPageNumber($args['page'])->setItemCountPerPage(20);
        /**/
        return array('total' => $paginator->getTotalItemCount(), 'rowset' => $paginator->getCurrentItems()->toArray());
    }

}
