<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {

    //var $scaffold;

    public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->User->delete()) {
            $this->User->recover();
			$this->Session->setFlash(__('User deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}


/*
    public function index(){
        $this->User->contain();
        $token_check = $this->TokenAuth->check_if_valid($this); 

        if(!$token_check){
            return;
        }

        $this->TokenAcl->check_if_can($token_check['id']);

        $this->set(array(
             'success' => true,
            '_serialize' => array('success')
        ));

    }


/*
    public function dirk(){

        $i =1;
        while($i < 50){
            $this->User->create();
            $d['username'] = "oot_$i";
            $d['password'] = "oot_$i";
            $d['group_id'] = 3;
            $d['realm_id'] = 1;
            $i++;
            $this->User->save($d);
        }

      //  print($this->request->query['filter']);
        print_r(json_decode($this->request->query['filter']));
        exit;


    }


    public function index(){

        $limit  = $this->request->query['limit'];
        $page   = $this->request->query['page'];
        $offset = $this->request->query['start'];

        $conditions = array();

        if(isset($this->request->query['filter'])){
            $filter = json_decode($this->request->query['filter']);
            foreach($filter as $f){
                $col = 'User.'.$f->field;
                array_push($conditions,array("$col LIKE" => '%'.$f->value.'%'));
            }
        }

        if(isset($this->request->query['realm_id'])){
            array_push($conditions,array('User.realm_id' => $this->request->query['realm_id']));
        }

       // print_r($conditions);
       // exit;

        $this->{$this->modelClass}->contain();//Only bare minimum for naauw!

        //Since the users can be quite alot we will not display any by default
        $q = $this->{$this->modelClass}->find('all',array(
            'conditions'=>  $conditions,
            'limit'     =>  $limit,
            'page'      =>  $page,
            'offset'    =>  $offset
        ));

        $total = $this->{$this->modelClass}->find('count',array(
            'conditions'=>  $conditions
        ));

        $items = array();
        foreach($q as $i){
            array_push($items,$i[$this->modelClass]);
        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }

/*
	public function index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

	public function view($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		}
		$groups = $this->User->Group->find('list');
		$this->set(compact('groups'));
	}

	public function edit($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->User->read(null, $id);
		}
		$groups = $this->User->Group->find('list');
		$this->set(compact('groups'));
	}

	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->User->delete()) {
			$this->Session->setFlash(__('User deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
*/
}
