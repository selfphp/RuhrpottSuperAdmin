<?php

namespace Application\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;


class DashboardTable extends AbstractTableGateway
{
    
    
    
    public function __construct(Adapter $adapter)
    {
    
        $this->adapter = $adapter;
        
        $this->adapter->query('SET NAMES UTF8');
                
    }
    
   
    
}
