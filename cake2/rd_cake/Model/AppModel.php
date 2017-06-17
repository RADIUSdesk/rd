<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {


    public $actsAs = array('Containable');

        /**
    * checks a record, if it is unique - depending on other fields in this table (transfered as array)
    * example in model: 'rule' => array ('validateUnique',array('belongs_to_table_id','some_id','user_id')),
    * if all keys (of the array transferred) match a record, return false, otherwise true
    * @param ARRAY other fields
    * TODO: add possibity of deep nested validation (User -> Comment -> CommentCategory: UNIQUE comment_id,         Comment.user_id)
    * 2010-01-30 ms
    */

     public function validateUnique($data, $fields = array(), $options = array()) {
        $id = (!empty($this->data[$this->alias]['id']) ? $this->data[$this->alias]['id'] : 0);
        if (!$id && $this->id) {
            $id = $this->id;
        }

        foreach ($data as $key => $value) {
            $fieldName = $key;
            $fieldValue = $value; // equals: $this->data[$this->alias][$fieldName]
        }

        $conditions = array($this->alias . '.' . $fieldName => $fieldValue, // Model.field => $this->data['Model']['field']
        $this->alias . '.id !=' => $id, );

        # careful, if fields is not manually filled, the options will be the second param!!! big problem...
        foreach ((array)$fields as $dependingField) {
            if (isset($this->data[$this->alias][$dependingField])) { // add ONLY if some content is transfered (check on that first!)
            $conditions[$this->alias . '.' . $dependingField] = $this->data[$this->alias][$dependingField];

            } elseif (isset($this->data['Validation'][$dependingField])) { 
            // add ONLY if some content is transfered        (check on that first!
                $conditions[$this->alias . '.' . $dependingField] = $this->data['Validation'][$dependingField];

            } elseif (!empty($id)) {
                # manual query! (only possible on edit)
                $res = $this->find('first', array('fields' => array($this->alias.'.'.$dependingField), 'conditions' => array($this->alias.'.id' => $this->data[$this->alias]['id'])));
                if (!empty($res)) {
                    $conditions[$this->alias . '.' . $dependingField] = $res[$this->alias][$dependingField];
                }
            }
        }

        $this->recursive = -1;
        if (count($conditions) > 2) {
            $this->recursive = 0;
        }
        $res = $this->find('first', array('fields' => array($this->alias . '.id'), 'conditions' => $conditions));
        if (!empty($res)) {
            return false;
        }
        return true;
    }
}
