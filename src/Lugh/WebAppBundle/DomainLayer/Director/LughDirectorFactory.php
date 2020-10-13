<?php
namespace Lugh\WebAppBundle\DomainLayer\Director;
use Lugh\WebAppBundle\DomainLayer\LughBuilder\LughBuilderProd;
use Lugh\WebAppBundle\DomainLayer\LughBuilder\LughBuilderTest;

use Lugh\WebAppBundle\DomainLayer\State\StateBuilderProd;
use Lugh\WebAppBundle\DomainLayer\State\StateBuilderTest;

use Lugh\WebAppBundle\DomainLayer\Storage\LughStorageProd;
use Lugh\WebAppBundle\DomainLayer\Storage\LughStorageTest;

use Lugh\WebAppBundle\DomainLayer\Behavior\BehaviorProd;
use Lugh\WebAppBundle\DomainLayer\Behavior\BehaviorTest;

use Lugh\WebAppBundle\DomainLayer\Cipher\LughCipherSerialize;
use Lugh\WebAppBundle\DomainLayer\Cipher\LughCipherJson;
use Lugh\WebAppBundle\DomainLayer\Cipher\LughCipherAES;

use Lugh\WebAppBundle\DomainLayer\Mail\LughMailWF;
use Lugh\WebAppBundle\DomainLayer\Mail\LughMailNoWF;
use Lugh\WebAppBundle\DomainLayer\Mail\Mailer;
use Lugh\WebAppBundle\DomainLayer\LughService;
use Symfony\Component\DependencyInjection\ContainerInterface as container;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DirectorFactory
 *
 * @author a.navarro
 */
class LughDirectorFactory extends LughService {
    
    
    public function getBuidler(container $container = null)
    {
        $builderClass = $this->getParameterClass();
        switch (strtolower($builderClass)) {
            case 'prod':
                $builder = new LughBuilderProd($container);
                break;
            case 'test':
                $builder = new LughBuilderTest($container);
                break;
            default:
                $builder = new LughBuilderProd($container);
                break;
        }
        $director = new LughDirectorBuilder();
        $director->setBuilder($builder);
        return $director;
    }
    
    public function getState(container $container = null)
    {
        $builderClass = $this->getParameterClass();
        switch (strtolower($builderClass)) {
            case 'prod':
                $builder = new StateBuilderProd($container);
                break;
            case 'test':
                $builder = new StateBuilderTest($container);
                break;
            default:
                $builder = new StateBuilderProd($container);
                break;
        }
        $director = new LughDirectorState();
        $director->setBuilder($builder);
        return $director;
    }
    public function getStateTest(container $container = null)
    {
        $builder = new StateBuilderTest($container);
        $director = new LughDirectorState();
        $director->setBuilder($builder);
        return $director;
    }
    
    public function getStorage(container $container = null)
    {
        $builderClass = $this->getParameterClass();
        switch (strtolower($builderClass)) {
            case 'prod':
                $builder = new LughStorageProd($container);
                break;
            case 'test':
                $builder = new LughStorageTest($container);
                break;
            default:
                $builder = new LughStorageProd($container);
                break;
        }
        $director = new LughDirectorStorage();
        $director->setBuilder($builder);
        return $director;
    }
    
    public function getStorageTest(container $container = null)
    {
        $builder = new LughStorageTest($container);
        $director = new LughDirectorStorage();
        $director->setBuilder($builder);
        return $director;
    }
    
    public function getBehavior(container $container = null)
    {
        $builderClass = $this->getParameterClass();
        switch (strtolower($builderClass)) {
            case 'prod':
                $builder = new BehaviorProd($container);
                break;
            case 'test':
                $builder = new BehaviorTest($container);
                break;
            default:
                $builder = new BehaviorProd($container);
                break;
        }
        $director = new LughDirectorBehavior($container);
        $director->setBuilder($builder);
        return $director;
    }
    
    public function getBehaviorTest(container $container = null)
    {
        $builder = new BehaviorTest($container);
        $director = new LughDirectorBehavior($container);
        $director->setBuilder($builder);
        return $director;
    }
    
    public function getMailer(container $container = null)
    {
        $builderClass = $this->getParameterMail();
        switch (strtolower($builderClass)) {
            case Mailer::workFlowOn:
                $builder = new LughMailWF($container);
                break;
            case Mailer::workFlowOff:
                $builder = new LughMailNoWF($container);
                break;
            default:
                $builder = new LughMailNoWF($container);
                break;
        }
        $director = new LughDirectorMailer();
        $director->setBuilder($builder);
        return $director;
    }
    
    public function getCipher(container $container = null)
    {
        $builderClass = $this->getParameterCipher();
        switch (strtolower($builderClass)) {
            case 'serialize':
                $builder = new LughCipherSerialize($container);
                break;
            case 'json':
                $builder = new LughCipherJson($container);
                break;
            case 'aes':
                $builder = new LughCipherAES($container);
                break;
            default:
                $builder = new LughCipherSerialize($container);
                break;
        }
        $director = new LughDirectorCipher();
        $director->setBuilder($builder);
        return $director;
    }
    
    private function getParameterClass()
    {
        $builderClass = $this->get('lugh.parameters')->getByKey('Config.factory.class', 'prod');
        return $builderClass;
    }
    
    private function getParameterMail()
    {
        $builderClass = $this->get('lugh.parameters')->getByKey('Config.mail.workFlow', Mailer::workFlowOff);
        return $builderClass;
    }
    
    private function getParameterCipher()
    {
        $builderClass = $this->get('lugh.parameters')->getByKey('Config.cipher.class', 'serialize');
        return $builderClass;
    }
}

?>
