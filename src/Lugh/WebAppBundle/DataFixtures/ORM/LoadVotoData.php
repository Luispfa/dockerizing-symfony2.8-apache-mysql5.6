<?php

namespace Lugh\WebAppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Lugh\WebAppBundle\Entity\GrupoOpcionesVoto;
use Lugh\WebAppBundle\Entity\OpcionesVoto;
use Lugh\WebAppBundle\Entity\TipoVoto;

class LoadOpcionesVotoData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        $langs = array(
            'A Favor'   =>array('en_gb' => 'In Favor', 'ca_es'=>'A Favor',   'gl_es' => 'A Favor', 'es_es'=>'A Favor'),
            'En Contra' =>array('en_gb' => 'Against',   'ca_es'=>'En Contra', 'gl_es' => 'En Contra', 'es_es'=>'En Contra'),
            'Abstención'=>array('en_gb' => 'Abstention','ca_es'=>'Abstenció', 'gl_es' => 'Abstención', 'es_es'=>'Abstención'),
            'En Blanco' =>array('en_gb' => 'Blank',     'ca_es'=>'Blanc',     'gl_es' => 'En Blanco', 'es_es'=>'En Blanco')
        );
        $parametros = array(
            'A Favor;S;10',
            'En Contra;N;20',
            'Abstención;A;30',
            'En Blanco;B;40'
        );

        foreach ($parametros as $parametro) {

            list($nombre, $symbol, $orden) = explode(';', $parametro);
            
            $parametroObject = new OpcionesVoto();

            $parametroObject->setNombre($nombre);
            $parametroObject->setSymbol($symbol);
            $parametroObject->setOrden($orden);

            $manager->persist($parametroObject);
            $manager->flush();

            if(isset($langs[$nombre])){
                foreach($langs[$nombre] as $lang => $text){
                    $punto = $manager->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->find($parametroObject->getId());
                    $repository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
                    $repository->translate($punto, 'nombre', $lang, $text);
                    $manager->persist($punto);
                    $manager->flush();
                }
            }


            $this->addReference( $symbol, $parametroObject);

        }
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}

class LoadGrupoOpcionesVotoData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $S = $this->getReference('S');
        $N = $this->getReference('N');
        $A = $this->getReference('A');
        $B = $this->getReference('B');


        $TodasObject = new GrupoOpcionesVoto();
        $TodasObject->setName('Todas las opciones');
        $TodasObject->addOpcionesVoto($S);
        $TodasObject->addOpcionesVoto($N);
        $TodasObject->addOpcionesVoto($A);
        $TodasObject->addOpcionesVoto($B);
        $manager->persist($TodasObject);
        $manager->flush();

        $ExceptoObject = new GrupoOpcionesVoto();
        $ExceptoObject->setNAme('Todas las opciones excepto "En Blanco"');
        $ExceptoObject->addOpcionesVoto($S);
        $ExceptoObject->addOpcionesVoto($N);
        $ExceptoObject->addOpcionesVoto($A);
        $manager->persist($ExceptoObject);
        $manager->flush();


    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}

class LoadTipoVotoData extends AbstractFixture
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
           
        $parametroObject = new TipoVoto();

        $parametroObject->setTipo('1');
        $parametroObject->setName('Votación');
        $parametroObject->setTag('id00324_app:voto:voto');
        $parametroObject->setMinVotos(1);
        $parametroObject->setMaxVotos(999999999);
        $parametroObject->setIsSerie(0);

        $manager->persist($parametroObject);

        $manager->flush();

    }
}

