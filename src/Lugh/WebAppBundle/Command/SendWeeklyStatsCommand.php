<?php

namespace Lugh\WebAppBundle\Command;

use DateTime;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

use Lugh\WebAppBundle\Lib;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\TitlePageWeekly;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\UsoPageWeekly;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\HeaderWeekly;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\Footer;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\EstadisticasPageWeekly;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\DocumentoPDF;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\Site;


class SendWeeklyStatsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('send:stats')
            ->setDescription('Sends mails to admins with weekly stats')
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
        
        $now = strtotime('now');
        $hour = 60*60;

        $sDate = new DateTime('6 days ago');
        $sDate->setTime(0,0);
        $eDate = new DateTime();
        $eDate->setTime(23,59);
        
        
        foreach ($platforms as $platform) {

            $name = 'lugh_' . $platform->getDbname();
            $host = "<a href='http://".$platform->getHost()."'>".$platform->getHost()."</a>";
            $siteId = $platform->getId();
            
            $apps = array(
                'voto'      => $platform->getVoto(),
                'foro'      => $platform->getForo(),
                'derecho'   => $platform->getDerecho()
            );
            
            $params = $em->getConnection()->getParams();
            $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);

            $helperSet = $application->getHelperSet();
            $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
            $helperSet->set(new EntityManagerHelper($em), 'em');
            
            
            $platformTimes =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'Platform.time.activate']);
            $statsId =  $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(['key_param' => 'stats.api.site_id']);
            
            
            $servicio = "la plataforma electrónica";
            
            $pages = array(
                new TitlePageWeekly($servicio),
                new UsoPageWeekly(
                    $platform,
                    $statsId->getValueParam()
                )
             );

            $doc = new DocumentoPDF();

            $doc->OutputToFile(
                $siteId.$eDate->format('ymd'),
                new HeaderWeekly($servicio),
                new Footer(),
                $pages
            );

            
            //enviar los mails

            $admins = $this->getAdmins($em);
            
            $to = $admins;
            $subject ="Estadísticas semanales de la plataforma ".$platform->getHost();
            $body = 'Administrador, <br/><br/>Se adjuntan las estadísticas semanales para la plataforma '.$host.
                    '<br/><br/>Saludos';
            $attachment[$siteId.$eDate->format('ymd').'.pdf'] = $siteId.$eDate->format('ymd').'.pdf';
            Lib\SendMailClass::sendMail($to, $subject, $body, null, $attachment);
            
            
            //borrar pdf del servidor
            unlink($siteId.$eDate->format('ymd').'.pdf');
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
    
}
