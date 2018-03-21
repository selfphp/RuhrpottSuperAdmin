<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class AuthorizePlugin extends AbstractPlugin
{
    
    /**
     * Get the user information (name, email, id and role)
     * @param int $userId
     * @return array
     */
    public function getUser(){
    
       // $rowsetArray = $this->getAuthorizeTable()->getUser($this->userId);
        	
        return "Damir";
    
    }
    
}

