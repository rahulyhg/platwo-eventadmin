<?php

namespace Mini\Model;

/**
 * Description of Permission
 *
 * @author robocon
 */
class Permission extends Model {
    
    public function gets() {
        $items = $this->db->permission->find()->sort(['id' => 1]);
        $item_list = [];
        
        foreach ($items as $item) {
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $item_list[] = $item;
        }
        
        return $item_list;
    }
}
