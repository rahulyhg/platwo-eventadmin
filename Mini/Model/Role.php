<?php

namespace Mini\Model;
use Valitron\Validator;
/**
 * Description of Role
 *
 * @author robocon
 */
class Role extends Model {
    
    public function gets() {
        $items = $this->db->level->find()->sort(['level' => 1]);
        $item_list = [];
        
        foreach ($items as $item) {
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $item_list[] = $item;
        }
        
        return $item_list;
    }
    
    public function get($id) {
        if(empty($id)){
            throw new \Exception('Invalid id');
        }
        
        $item = $this->db->role_perm->findOne([
            '_id' => new \MongoId($id),
        ]);
        $item['id'] = $item['_id']->{'$id'};
        unset($item['_id']);
        
        $level = $this->db->level->findOne(['_id' => new \MongoId($item['level_id'])], ['name']);
        $item['name'] = $level['name'];
        return $item;
    }
    
    /**
     * Get all role_perm record
     * 
     * @return type
     */
    public function getRolePerms() {
        $items = $this->db->role_perm->find()->sort(['level' => 1]);
        $item_lists = [];
        foreach ($items as $item) {
            
            // get level name
            $level = $this->db->level->findOne([
                '_id' => new \MongoId($item['level_id']),
            ],['name']);
            
            $item['name'] = $level['name'];
            $item_lists[] = $item;
        }
        
        return $item_lists;
    }
    
    public function save(){
        
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
        $params = [
            'level' => filter_input(INPUT_POST, 'level', FILTER_SANITIZE_STRING),
            'perms' => filter_input(INPUT_POST, 'perms', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY),
        ];
        
        $v = new Validator($params);
        $v->rule('required', ['level', 'perms']);
        if(!$v->validate()){
            
//            $v->errors()
            throw new \Exception('Parameter error');
        }
        
        $level_data = $this->db->level->findOne([
            '_id' => new \MongoId($params['level']),
        ]);
        
        if(empty($id)){
            $check_role_perm = $this->db->role_perm->findOne(['level_id' => $params['level']]);
            if ($check_role_perm !== null) {
                throw new \Exception('Level already set');
            }

            if ($level_data !== null) {
                $data = [
                    'level_id' => $params['level'],
                    'level' => $level_data['level'],
                    'perms' => $params['perms']
                ];

                $this->db->role_perm->insert($data);
            }
        }else{
            $data = [
                'level_id' => $params['level'],
                'level' => $level_data['level'],
                'perms' => $params['perms']
            ];
            
            $this->db->role_perm->update(['_id'=> new \MongoId($id)], $data);
        }
        
    }
}
