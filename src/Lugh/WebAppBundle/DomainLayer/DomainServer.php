<?php
namespace Lugh\WebAppBundle\DomainLayer;
use Symfony\Component\DependencyInjection\ContainerInterface as container;
use Lugh\WebAppBundle\DomainLayer\Director\LughDirectorBuilder;
use Lugh\WebAppBundle\DomainLayer\Director\LughDirectorState;
use Lugh\WebAppBundle\DomainLayer\Director\LughDirectorStorage;
use Lugh\WebAppBundle\DomainLayer\Director\LughDirectorBehavior;
use Lugh\WebAppBundle\DomainLayer\Director\LughDirectorMailer;
use Lugh\WebAppBundle\DomainLayer\Director\LughDirectorCipher;
use Lugh\WebAppBundle\DomainLayer\LughService;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DomainServer
 *
 * @author a.navarro
 */
class DomainServer extends LughService {
    
    private $builder;
    
    private $state;
    
    private $stateTest;
    
    private $storage;
    
    private $storageTest;
    
    private $behavior;
    
    private $behaviorTest;
    
    private $mailer;
    
    private $cipher;
    
    
    public function __construct
            (
            container $container = null,
            LughDirectorBuilder $director, 
            LughDirectorStorage $storage,
            LughDirectorStorage $storageTest,
            LughDirectorState $state,
            LughDirectorState $stateTest,
            LughDirectorBehavior $behavior,
            LughDirectorBehavior $behaviorTest,
            LughDirectorMailer $mailer,
            LughDirectorCipher $cipher
            ) 
    {
        parent::__construct($container);
        $this->builder      = $director;
        $this->storage      = $storage;
        $this->storageTest  = $storageTest;
        $this->state        = $state;
        $this->stateTest    = $stateTest;
        $this->behavior     = $behavior;
        $this->behaviorTest = $behaviorTest;
        $this->mailer       = $mailer;
        $this->cipher       = $cipher;
    }
    
    public function getBuilder()
    {
        return $this->builder;
    }
    
    public function getState()
    {
        return $this->modeService($this->state, $this->stateTest);
    }
    
    public function getStateTest()
    {
        return $this->stateTest;
    }
    
    public function getStorage()
    {
        $this->storage->resetStack();
        return $this->modeService($this->storage, $this->storageTest);
    }
    
    public function getStorageTest()
    {
        $this->storageTest->resetStack();
        return $this->storageTest;
    }
    
    public function getBehavior()
    {
        return $this->modeService($this->behavior, $this->behaviorTest);
    }
    
    public function getBehaviorTest()
    {
        return $this->behaviorTest;
    }
    
    public function getMailer()
    {
        return $this->mailer;
    }
    
    public function getCipher()
    {
        return $this->cipher;
    }
    
    private function modeService($prod, $test)
    {
        $service = null;
        switch ($this->get('lugh.mode')->getMode()) {
            case 'prod':
                $service = $prod;
                break;
            case 'test':
                $service = $test;
                break;
            default:
                $service = $prod;
                break;
        }
        return $service;
    }
   
 }

?>
