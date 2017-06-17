<?php
/*---- This is a nice override from the following URL-------:

    http://php.refulz.com/cakephp-saving-alias-in-aco-and-aro/
    It is used to add the value of the name field of model instance (not the name) as the alias
    // app\Model\Behavior\MyAclBehavior.php
*/

App::uses('AclBehavior', 'Model/Behavior');

class MyAclBehavior extends AclBehavior {

    public function afterSave(Model $model, $created,$options = array()) {
        $types = $this->_typeMaps[$this->settings[$model->name]['type']];
        if (!is_array($types)) {
            $types = array($types);
        }

        foreach ($types as $type) {
            $parent = $model->parentNode();
            if (!empty($parent)) {
                $parent = $this->node($model, $parent, $type);
            }

            $data = array(
                'parent_id'     => isset($parent[0][$type]['id']) ? $parent[0][$type]['id'] : null,
                'model'         => $model->name,
                'foreign_key'   => $model->id,
                //'alias' => $model->name ."::". $model->id,
                'alias'         => $model->field('name'),
            );

            if (!$created) {
                $node = $this->node($model, null, $type);
                $data['id'] = isset($node[0][$type]['id']) ? $node[0][$type]['id'] : null;
            }
            $model->{$type}->create();
            $model->{$type}->save($data);
        }
    }
}
