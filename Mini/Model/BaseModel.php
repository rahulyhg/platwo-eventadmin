<?php
namespace Mini\Model;

/**
 * Description of BaseModel
 *
 * @author robocon
 */
class BaseModel {
    
    private $db;
    
    function __construct($config)
    {
        
        $user_pass = null;
        if(!empty($config['db_user']) && !empty($config['db_pass'])){
            $user_pass = $config['db_user'].':'.$config['db_pass'].'@';
        }
        
        $connect = new \MongoClient('mongodb://'.$user_pass.$config['db_host'].':'.$config['db_port']);
        $this->db = $connect->eventsniff;
        
    }
    
}
