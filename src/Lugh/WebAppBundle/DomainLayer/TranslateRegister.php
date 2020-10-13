<?php

namespace Lugh\WebAppBundle\DomainLayer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface as container;

class TranslateRegister {

    protected $container;
    
    protected function get($id)
    {
        return $this->container->get($id);
    }
    
    protected function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
    
    public function __construct(container $container = null) {
        $this->container = $container;
    }

    
    public function register($file)
    {
        $locale = 'es_es';
        if (strpos($file, '_ca') != false) { $locale = 'ca_es'; }
        if (strpos($file, '_es') != false) { $locale = 'es_es'; }
        if (strpos($file, '_en') != false) { $locale = 'en_gb'; }
        if (strpos($file, '_gl') != false) { $locale = 'gl_es'; }
        
        $this->getRequest()->getSession()->set('_locale', $locale);

        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) 
        {
            $user = $this->get('security.context')->getToken()->getUser();
            $user->setLang($locale);
            $this->get('doctrine')->getManager()->persist($user);
            $this->get('doctrine')->getManager()->flush();
        }
    }
    
    public function getLocale()
    {
        if (is_object($this->getRequest()))
        {
            return $this->getRequest()->getSession()->get('_locale', 'es_es');
        }
        return 'es_es';
        
    }
    public function setLocale($locale)
    {
        if ($locale == 'ca') { $locale = 'ca_es'; }
        if ($locale == 'es') { $locale = 'es_es'; }
        if ($locale == 'en') { $locale = 'en_gb'; }
        if ($locale == 'gl') { $locale = 'gl_es'; }
        $this->getRequest()->getSession()->set('_locale', $locale);
    }
    public function getUserLocale($user)
    {
        return $user->getLang() == null ? 'es_es' : $user->getLang();
    }
    

}

