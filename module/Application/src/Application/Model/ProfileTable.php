<?php

namespace Application\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;


class ProfileTable extends AbstractTableGateway
{
    
    
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->adapter->query('SET NAMES UTF8');
    }
    
    public function setNavbar($id){
        
        try {
            // active = IF(active=1, 0, 1)
            $stmt =  $this->adapter->query("UPDATE `user` SET navbar = IF(navbar=1, 0, 1) WHERE `user_id`=?");
        
            $rowset = $stmt->execute(array( $id));
        
            return true;
             
        } catch (\Exception $e) {
            return false;
        }
                
    }
   
    
    public function getUser($id){
        try {
            $stmt =  $this->adapter->query("SELECT * FROM `user` WHERE `user_id` = ?");
    
            $rowset = $stmt->execute(array($id));
    
            if ( $rowset->count() > 0 ){
    
                $returnArray = array();
    
                foreach ($rowset as $result) {
                    $returnArray[] = $result;
                }
                return $returnArray;
            }else{
                return 0;
            }
             
        } catch (\Exception $e) {
            return false;
        }
    }
    
   
    
    
}
