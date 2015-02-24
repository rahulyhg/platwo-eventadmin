<?php

namespace Mini\Model;

use Valitron\Validator;

/**
 * Description of Users
 *
 * @author robocon
 */
class Users extends Model {
    
    public function gets() {
        
        $items = $this->db->users->find()
                ->fields(['username' => 1, 'email' => 1, 'display_name' => 1])
                ->sort(['_id' => -1]);
        $item_lists = [];
        foreach ($items as $item) {
            $item_lists[] = $item;
        }
        
        return $item_lists;
    }
    
    public function get($id) {
        
        $user = $this->db->users->findOne(['_id' => new \MongoId($id)]);
        unset($user['password']);
        unset($user['access_token']);
        unset($user['private_key']);
        
        return $user;
    }
    
    public function getGroups() {
        $items = $this->db->groups->find();
        $item_lists = [];
        foreach ($items as $item) {
            $item_lists[] = $item;
        }
        return $item_lists;
    }
    
    public function save() {
        
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
        $params = [
            'group' => filter_input(INPUT_POST, 'group', FILTER_SANITIZE_STRING),
            'role_perm' => filter_input(INPUT_POST, 'role_perm', FILTER_SANITIZE_STRING),
        ];
        
        $v = new Validator($params);
        $v->rule('required', ['group', 'role_perm']);
        if(!$v->validate()){
            throw new \Exception('Parameter error');
        }
        
        $user = $this->db->users->findOne(['_id' => new \MongoId($id)]);
        if($user === null){
            throw new \Exception('Invalid id');
        }
        
        $data['group_role'] = [
            'group_id' => $params['group'],
            'role_perm_id' => $params['role_perm']
        ];
        
        $this->db->users->update(['_id' => new \MongoId($id)], ['$set' => $data]);
        
    }
    
    public function save_pass($salt) {
        
        $params = [
            'user' => filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING),
            'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING),
            'confirm_password' => filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING),
        ];
        
        $v = new Validator($params);
        $v->rule('required', ['user', 'password']);
        $rules = [
            'lengthMin' => [
                ['password', 6],
                ['confirm_password', 6]
            ]
        ];
        $v->rules($rules);
        $v->rule('equals', 'password', 'confirm_password');
        
        if(!$v->validate()){
            throw new \Exception('Parameter error');
        }
        
        $user = $this->db->users->findOne([
            '_id' => new \MongoId($params['user'])
        ]);
        if($user !== null){
            $password = hash('sha256', $params['password'].$salt.$user['private_key']);
            $this->db->users->update(['_id' => new \MongoId($params['user'])], ['$set' => ['password' => $password]]);
        
        }
    }
}
