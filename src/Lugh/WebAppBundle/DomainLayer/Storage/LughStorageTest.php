<?php
namespace Lugh\WebAppBundle\DomainLayer\Storage;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Storage
 *
 * @author a.navarro
 */
class LughStorageTest extends LughStorageProd {
    
    public function save($item, $restrictionUser = true)
    {
        try {
            $itemSave = $restrictionUser ? $this->restrictionItem($item,StateClass::actionStore) : null;
            $this->preSave($item);
            $this->setDateTime($item);
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }
    
    public function saveStack()
    {
        try {
            foreach ($this->stack as $stackElement) {
                $this->preSave($stackElement);
                $this->setDateTime($stackElement);
                $this->restrictionItem($stackElement,StateClass::actionStore);
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

}