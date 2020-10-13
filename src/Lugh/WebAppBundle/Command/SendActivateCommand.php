<?php

namespace Lugh\WebAppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

use Lugh\WebAppBundle\Lib;


class SendActivateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('send:activate')
            ->setDescription('Sends mails to admins notifying of activation/deactivation of platforms and services')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $emName = 'db_connection';
        $name = 'lugh_' . 'db_connection';
        
        
        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager($emName);
        $params = $em->getConnection()->getParams();
        $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);
        
        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');
        
        
        $platforms =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findBy(['onProductionDates' => 1]);
        
        //por cada plataforma en producción
        
        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager('default');
        
        
        foreach ($platforms as $platform) {

            $name = 'lugh_' . $platform->getDbname();
            $host = "<a href='http://".$platform->getHost()."'>".$platform->getHost()."</a>";
            //$template = $platform->getTemplate();
            
            $isForo = $platform->getForo();
            $isDerecho = $platform->getDerecho();
            $isVoto = $platform->getVoto();
            $isAv = $platform->getAv();
            
            $params = $em->getConnection()->getParams();
            $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);

            $helperSet = $application->getHelperSet();
            $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
            $helperSet->set(new EntityManagerHelper($em), 'em');
            
            
            $platformTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Platform.time.activate']);
            $foroTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Foro.time.activate']);
            $votoTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Voto.time.activate']);
            $derechoTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Derecho.time.activate']);
            $proposalTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Proposal.time.create']);
            $threadTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Thread.time.create']);
            $avTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Av.time.activate']);
            $now = strtotime('now');
            $hour = 60*60;
            
            $mailInicio = false;
            $mailFin = false;
            $mailFinVoto = false;
            $mailFinForo = false;
            $mailFinDerecho = false;
            $mailFinPropuestas = false;
            $mailFinHilos = false;
            
            if($platformTimes != null && $platformTimes != ''){
                $value = json_decode($platformTimes->getValueParam(), true);
                if(array_key_exists('from', $value) && $value['from'] != ''){
                    $fromDate = strtotime($value['from']);
                    if($now - $fromDate > 0 && $now - $fromDate <= $hour){
                        //mail inicio plataformas
                        $mailInicio = true;
                        var_dump("inicio ".$platform->getHost());
                    }
                }
                if(array_key_exists('to', $value) && $value['to'] != ''){
                    $toDate = strtotime($value['to']);
                    if($now - $toDate > 0 && $now - $toDate <= $hour){
                        //mail fin plataformas
                        $mailFin = true;
                        $this->deactivate($platform, $em);
                        var_dump("fin ".$platform->getHost());
                    }
                    
                    if(!$mailFin){
                    
                        if($isVoto == 1){
                            $valueVoto = json_decode($votoTimes->getValueParam(), true);
                            if(array_key_exists('to', $valueVoto) && $valueVoto['to'] != ''){
                                $toVotoDate = strtotime($valueVoto['to']);
                                if($now - $toVotoDate > 0 && $now - $toVotoDate <= $hour){
                                    //mail fin voto
                                    $mailFinVoto = true;
                                    var_dump("fin voto ".$platform->getHost());
                                }
                            }
                        }

                        if($isForo == 1){
                            $valueForo = json_decode($foroTimes->getValueParam(), true);
                            if(array_key_exists('to', $valueForo) && $valueForo['to'] != ''){
                                $toForoDate = strtotime($valueForo['to']);
                                if($now - $toForoDate > 0 && $now - $toForoDate <= $hour){
                                    //mail fin foro
                                    $mailFinForo = true;
                                    var_dump("fin foro ".$platform->getHost());
                                }
                            }

                            $valuePropuestas = json_decode($proposalTimes->getValueParam(), true);
                            if(array_key_exists('to', $valuePropuestas) && $valuePropuestas['to'] != ''){
                                $toPropuestasDate = strtotime($valuePropuestas['to']);
                                if($now - $toPropuestasDate > 0 && $now - $toPropuestasDate <= $hour){
                                    //mail fin propuestas
                                    $mailFinPropuestas = true;
                                    var_dump("fin propuestas ".$platform->getHost());
                                }
                            }
                        }

                        if($isDerecho == 1){
                            $valueDerecho = json_decode($derechoTimes->getValueParam(), true);
                            if(array_key_exists('to', $valueDerecho) && $valueDerecho['to'] != ''){
                                $toDerechoDate = strtotime($valueDerecho['to']);
                                if($now - $toDerechoDate > 0 && $now - $toDerechoDate <= $hour){
                                    //mail fin derecho
                                    $mailFinDerecho = true;
                                    var_dump("fin derecho ".$platform->getHost());
                                }
                            }

                            if(!$mailFinDerecho){
                                if($threadTimes != null && $threadTimes != ''){
                                    $valueThread = json_decode($threadTimes->getValueParam(), true);
                                    if(array_key_exists('to', $valueThread) && $valueThread['to'] != ''){
                                        $toThreadDate = strtotime($valueThread['to']);
                                        if($now - $toThreadDate > 0 && $now - $toThreadDate <= $hour){
                                            //mail fin hilo
                                            $mailFinHilos = true;
                                            var_dump("fin hilo ".$platform->getHost());
                                        }
                                    }
                                }
                            }
                        }
                        
                        if($isAv == 1){
                            $valueAv = json_decode($avTimes->getValueParam(), true);
                            if(array_key_exists('to', $valueAv) && $valueAv['to'] != ''){
                                $toAvDate = strtotime($valueAv['to']);
                                if($now - $toAvDate > 0 && $now - $toAvDate <= $hour){
                                    //mail fin voto
                                    $mailFinAv = true;
                                    var_dump("fin av ".$platform->getHost());
                                }
                            }
                        }
                    }
                }
            }
            
            
            
            //enviar los mails

            $admins = $this->getAdmins($em);
            
            if($mailInicio){
                $subject = "Activación plataformas electrónicas";
                $body = "Administrador,<br><br>"
                        . "La plataforma electrónica ".$host.
                        " se ha activado con fecha ".$value['from']."<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
            if($mailFin){
                $subject = "Desactivación plataformas electrónicas";
                $body = "Administrador,<br><br>"
                        . "La plataforma electrónica ".$host.
                        " se ha desactivado con fecha ".$value['to']."<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
            if($mailFinVoto){
                $subject = "Desactivación voto electrónico";
                $body = "Administrador,<br><br>"
                        . "El voto/delegación de la plataforma ".$host.
                        " se ha desactivado con fecha ".$valueVoto['to']."<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
            if($mailFinAv){
                $subject = "Desactivación asitencia virtual";
                $body = "Administrador,<br><br>"
                        . "La asitencia virtual de la plataforma ".$host.
                        " se ha desactivado con fecha ".$valueAv['to']."<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
            if($mailFinDerecho){
                $subject = "Desactivación derecho de la información";
                $body = "Administrador,<br><br>"
                        . "El derecho de información de la plataforma ".$host.
                        " se ha desactivado con fecha ".$valueDerecho['to']."<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
            if($mailFinForo){
                $subject = "Desactivación foro electrónico";
                $body = "Administrador,<br><br>"
                        . "El foro electrónico de la plataforma ".$host.
                        " se ha desactivado con fecha ".$valueForo['to']."<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
            if($mailFinPropuestas){
                $subject = "Desactivación propuestas complementarias en foro electrónico";
                $body = "Administrador,<br><br>"
                        . "A partir de ".$valuePropuestas['to']." los accionistas no podran presentar propuestas complementarias a la orden del día en el foro electrónico de la plataforma ".$host.
                        " El foro seguirá abierto hasta ".$valueForo['to']." para la presentación de iniciativas y ofertas/peticiones de representación."
                        . "<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
            if($mailFinHilos){
                $subject = "Desactivación de preguntas nuevas en derecho de información";
                $body = "Administrador,<br><br>"
                        . "Las preguntas nuevas del derecho de información de la plataforma ".$host.
                        " se han desactivado con fecha ".$valueThread['to']."<br><br>"
                        . "Saludos,";
                
                foreach ($admins as $admin) {
                
                    Lib\SendMailClass::sendMail($admin, $subject, $body);

                }
                
            }
            
        }
        
    }
    
    
    private function getAdmins($em)
    {
        $allowedUsers = array();

        $entities = $em->getRepository('Lugh\WebAppBundle\Entity\\User')->findAll();


        foreach($entities as $user){

            foreach($user->getRoles() as $role){
                if($role == 'ROLE_CUSTOMER'){

                       $allowedUsers[] = $user->getEmail();
                }

            }
        }

        return $allowedUsers;
    }
    
    private function deactivate($platform, $oldEm){
        
        $application = $this->getApplication();
        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager('db_connection');
        
        $platform->setOnProductionDates(0);
        $em->persist($platform);
        $em->flush();
        
        
    }
    
}
