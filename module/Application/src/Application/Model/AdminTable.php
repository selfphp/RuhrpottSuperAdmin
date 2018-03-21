<?php

namespace Application\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;


class AdminTable extends AbstractTableGateway
{
    
    protected $db;
    
    public function __construct(Adapter $adapter)
    {
    
       // $this->db = $adapter->getAdapter()->getDriver()->getConnection();
        
        $this->adapter = $adapter;
        
        $this->adapter->query('SET NAMES UTF8');
                
    }
    
    public function createProject($data){
        
        try {
            $connection = $this->adapter->getDriver()->getConnection();
            $connection->beginTransaction();
            
            $stmt = $connection->prepare("INSERT INTO `projects` (`name`, `scrum`, `kanban`) VALUES(:name, :scrum, :kanban)");
        
            $stmt->bindParam(':name', $data['project_name']);
            $stmt->bindParam(':scrum', $data['scrum']);
            $stmt->bindParam(':kanban', $data['kanban']);
            
            $sth = $connection->execute();
                    
            $connection->commit();
        }
        
        catch (\Exception $e) {
            print "Error!: " . $e->getMessage() . "</br>";
            if ($connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface) {
                $connection->rollback();
            }
        
            /* Other error handling */
        }
        /*
        $con = $this->getAdapter()->getDriver()->getConnection();
        
        //$con->beginTransaction();
        //$this->beginTransaction();
        try {
            //$stmt =  $this->adapter->query("INSERT INTO `projects` (`name`, `scrum`, `kanban`) VALUES(?,?,?)");
            
            //$stmt = $con->prepare("INSERT INTO test (name, email) VALUES(?,?)");
            
            try {
                $con->beginTransaction();
                $con->insert('projects', $data);
                $con->commit();
                return $this->adapter->lastInsertId();
            } catch(\Exception $e) {
                $con->rollback();
                print "Error!: " . $e->getMessage() . "</br>";
            }
        
            //$rowset = $stmt->execute(array($data['project_name'],$data['scrum'],$data['kanban']));
        
           // $this->insert('projects', $data);
            
           // $lastId = $this->lastInsertId();
            
            return 0;
        } catch (\Exception $e) {
            print "Error!: " . $e->getMessage() . "</br>";
        }
        */
    }
   
    
}
