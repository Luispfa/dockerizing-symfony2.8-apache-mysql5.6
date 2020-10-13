<?php

namespace Lugh\WebAppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Lugh\WebAppBundle\Entity\Junta;

class LoadJuntaData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $juntaObject = new Junta();
        $juntaObject->setAcreditacionEnabled(false);
        $juntaObject->setLiveEnabled(false);
        $juntaObject->setPreguntasEnabled(false);
        $juntaObject->setVotacionEnabled(false);
        $juntaObject->setAbandonoEnabled(false);
        $juntaObject->setState(1);
        $manager->persist($juntaObject);
        $manager->flush();
    }
}