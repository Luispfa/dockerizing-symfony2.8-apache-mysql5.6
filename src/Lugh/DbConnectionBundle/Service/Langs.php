<?php

    namespace Lugh\DbConnectionBundle\Service;
    use Symfony\Component\DependencyInjection\ContainerInterface as container;
    use Lugh\DbConnectionBundle\Lib\PlatformsManager;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class Langs
    {
        private $platformLangs;
        private $container;
        private $langCodes = array(
            'es' => 'es_es',
            'ca' => 'ca_es',
            'gl' => 'gl_es',
            'en' => 'en_gb'
        );
        private $langNames = array(
            'es' => 'Castellano',
            'ca' => 'Catalán',
            'gl' => 'Gallego',
            'en' => 'Inglés'
        );

        public function __construct(container $container = null) {
            $this->container = $container;
        }


        public function from($platform_id){
            $default = '{"es":1, "en":1, "ca":1, "gl":1}';

            if($platform_id === -1)
                return json_decode($default, true);

            $this->getEM($platform_id);
            $langs = $this->get('lugh.parameters')->getByKey('Config.langs.active', $default);
            $langs = json_decode($langs,true);

            $this->platformLangs = $langs;

            return $langs;
        }

        public function isActive($lang){
            try{
                if(array_key_exists($lang,$this->platformLangs)){
                    return intval($this->platformLangs[$lang]);
                }
                return false;
            }catch(\Exception $exc){
                return false;
            }
        }

        public function langCode($lang){
            try{
                if(isset($lang,$this->langCodes)){
                    return $this->langCodes[$lang];
                }
                return false;
            }catch(\Exception $exc){
                return new Response(json_encode(array('error'=> $exc->getMessage())));
            }
        }

        public function langName($lang){
            try{
                if(isset($lang,$this->langNames)){
                    return $this->langNames[$lang];
                }
                return false;
            }catch(\Exception $exc){
                return new Response(json_encode(array('error'=> $exc->getMessage())));
            }
        }

        private function getEM( $platform_id ){
            $em = $this->get('doctrine')->getManager('db_connection');
            $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find( $platform_id );
            PlatformsManager::switchDb($platform->getDbname());

            return $platform;
        }

        private function get($id)
        {
            return $this->container->get($id);
        }
    }


