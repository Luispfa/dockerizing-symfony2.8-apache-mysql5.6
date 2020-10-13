<?php

namespace Lugh\WebAppBundle\Command;

use DateTime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

use Lugh\WebAppBundle\Lib;


class CronCobsaCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('cron:cobsa')
                ->setDescription('Send mails in COBSA format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $application = $this->getApplication();
        $emName = 'db_connection';
        $name = 'lugh_' . 'db_connection';


        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager($emName);
        $params = $em->getConnection()->getParams();
        $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);

        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');


        //buscamos plataforma voto caixa
        //CAMBIAR ID POR LA QUE TOQUE EN PRODUCCIÓN!!!!!!!!!!!!
        $platform = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findOneBy(['id' => 42]);

        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager('default');


        $name = 'lugh_' . $platform->getDbname();
        //$host = "<a href='http://".$platform->getHost()."'>".$platform->getHost()."</a>";
        //$siteId = $platform->getId();
        //cambiamos a la base de datos de caixa
        $params = $em->getConnection()->getParams();
        $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);

        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');

        //emails a los que enviar
        //CAMBIAR SEGÚN CONVENGA
        $to = ['mridorsa@header.net'];

        $d = date("YmdHis");
        $filename = 'Voto' . $d . '.txt';

        $targetDir = 'txts';
        $path = $targetDir . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($targetDir)){
            @mkdir($targetDir);
        }

        $content = $this->COBSA($filename, $em);

        $file = fopen($path, "w");
        fputs($file, $content);
        fclose($file);


        //zip y password
        $fileout = /*$targetDir . DIRECTORY_SEPARATOR  . */'Voto'.$d.'.zip';
        $password=substr(md5(time()),0,8);
        shell_exec('zip -j -P '. $password. ' ' . $fileout .' '. $path);

        //enviar los mails



        $subject1 = "Movimientos Voto Electronico " . $d;
        $subject2 = "Movimientos Voto Electronico Password".$d;
        $attachment[$fileout] = $fileout;
        Lib\SendMailClass::sendMailCaixa($to, $subject1, '', null, $attachment);
        Lib\SendMailClass::sendMailCaixa($to, $subject2, $password, null, null);
        
        unlink($path);

    }

    public function COBSA($filename, $em) {
        
        //$storage = $this->get('lugh.server')->getStorage();
        //$response = new Response();

        $content = '';
        $content = $this->setHeader($content);
        $puntos = $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->findAll();
        $actions = $em->getRepository('Lugh\WebAppBundle\Entity\Accion')->findBy(array('movFileTagged' => null));

        $registros1 = 0;
        $registros2 = 0;
        $totalAcciones = 0;
        foreach ($actions as $action) {
            $registros1++;
            $accionista = $action->getAccionista();
            $totalAcciones += $accionista->getSharesNum();

            $content .= chr(13) . chr(10) . $this->RegistroDatos($action);
            if (count($votacion = $action->getVotacion()) > 0) {
                $cont = 1;
                foreach ($puntos as $punto) {
                    $registros2++;
                    if ($voto = $this->getVotacion($action, $punto)) {
                        $content .= chr(13) . chr(10) . $this->RegistroDetalleVotaciones($punto, $voto, $cont, $accionista);
                    } else {
                        if (count($punto->getSubpuntos()) == 0) {
                            //Si es informativo, ahora queda en blanco
                            $content .= chr(13) . chr(10) . $this->RegistroDetallePuntoEnBlanco($punto, $cont, $accionista);
                        }
                    }
                    $cont++;
                }
            }
            $action->setMovFileTagged($filename);
            $em->persist($action);
            $em->flush();
        }

        $content .= chr(13) . chr(10) . $this->RegistroFinal($registros1, $registros2, $totalAcciones);

        try {
            $this->StoreMovimientosFile($filename, $content);
        } catch (Exception $exc) {
            return $exc->getMessage();
        }


        /*$disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, iconv("UTF-8", 'ASCII//TRANSLIT', $filename));
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContent($content);

        return $response;*/
        
        return $content;
    }


    private function RegistroFinal($r1, $r2, $titulos) {
        $rf = array();
        $rf[] = $this->csprintf("%02.2s", '99');       //TipoRegistro
        $rf[] = $this->csprintf("%015.15s", $r1);       //Total Registros 01
        $rf[] = $this->csprintf("%015.15s", $r2);       //Total Registros 02
        $rf[] = $this->csprintf("%015.15s", $r1 + $r2);   //Total Registros 
        $rf[] = $this->csprintf("%015.15s", $titulos);  //Total Titulos
        $rf[] = $this->csprintf("%-293.293s", '');      //Filler
        return implode($rf);
    }

    private function RegistroDetallePuntoEnBlanco($punto, $cont, $accionista) {
        $id_file = $punto->getIdFile();
        $id = $punto->getExtra() > 0 ? '00' : $id_file == null ? $cont : $id_file;
        $num_shares = $accionista->getSharesNum();

        $rd = array();
        $rd[] = $this->csprintf("%02.2s", '02');            //TipoRegistro
        $rd[] = $this->csprintf("%036.36s", '0');            //CodigodeBarras
        $rd[] = $this->csprintf("%02.2s", $id);     //Punto
        $rd[] = $this->csprintf("%015.15s", doubleval($num_shares));  //Numero Titulos
        $rd[] = $this->csprintf("%01.1s", 'B');             //Sentido Voto
        $rd[] = $this->csprintf("%-299.299s", '');      //Filler
        return implode($rd);
    }

    private function RegistroDetalleVotaciones($punto, $voto, $cont, $accionista) {
        $id_file = $punto->getIdFile();
        $id = $punto->getExtra() > 0 ? '00' : $id_file == null ? $cont : $id_file;
        $sentido = $voto->getOpcionVoto()->getSymbol();
        $num_shares = $accionista->getSharesNum();

        $rd = array();
        $rd[] = $this->csprintf("%02.2s", '02');                 //TipoRegistro
        $rd[] = $this->csprintf("%036.36s", '0');                 //CodigodeBarras
        $rd[] = $this->csprintf("%02.2s", $id);                  //Punto
        $rd[] = $this->csprintf("%015.15s", doubleval($num_shares));   //Numero Titulos
        $rd[] = $this->csprintf("%01.1s", $sentido);             //Sentido Voto
        $rd[] = $this->csprintf("%-299.299s", '');                //Filler

        return implode($rd);
    }

    private function getVotacion($accion, $punto) {
        foreach ($accion->getVotacion() as $votacion) {
            if ($punto == $votacion->getPunto()) {
                return $votacion;
            }
        }
        return false;
    }

    function printPuntos($lista, $count, $tipo, $accions) {
        if (count($lista) == 0) {
            return $count;
        }
        $punto = array_shift($lista);
        if ($tipo->getTipo() != $punto->getTipoVoto()->getTipo()) {
            return $this->printPuntos($lista, $count, $tipo, $accions);
        }

        $row = 2;
        foreach ($accions as $accion) {
            $votacion = $this->getVotacion($accion, $punto);
            $nombre = $votacion != false ? $votacion->getOpcionVoto()->getNombre() : '';
        }

        $count++;
        if (count($subpuntos = $punto->getSubpuntos()->toArray()) > 0) {
            $count = $this->printPuntos($subpuntos, $count, $tipo, $accions);
        }

        return $this->printPuntos($lista, $count, $tipo, $accions);
    }
    

    private function RegistroDatos($action) {
        $accionista = $action->getAccionista();
        $tipo_documento = "2"; //0 delegación, 1 voto
        if ($action::nameClass == 'Voto') {
            $tipo_documento = "1";
        } else if ($action::nameClass == 'Delegacion') {
            $tipo_documento = "0";
        }

        $num_shares = $accionista->getSharesNum();

        if ($action::nameClass == 'Anulacion') {
            $actionAnulada = $action->getAccionAnterior();
            if ($actionAnulada::nameClass == 'Voto') {
                $tipo_documento = "1";
            } else if ($actionAnulada::nameClass == 'Delegacion') {
                $tipo_documento = "0";
            }
            $num_shares = 0;
        }

        $tipo_persona = $accionista->getDocumentType() == "cif" ? "J" : "F";
        $persona = $tipo_persona == "F" ? $accionista->getName() : $accionista->getRepresentedBy();
        $fecha = $action->getDateTime()->format('Ymd');
        $hora = $action->getDateTime()->format('H:i:s');
        //die(var_dump($action->getDateTime()->format('H:i:s')));
        $actuacion = '';

        $delegado_name = '';
        $delegado_num = '';
        if ($action::nameClass == 'Delegacion') {
            $delegado = $action->getDelegado();
            if ($delegado->getIsDirector()) {
                $actuacion = 'presidente';
            } else if ($delegado->getIsConseller()) {
                $actuacion = 'consejo';
            } else {
                $actuacion = 'persona';
            }

            $delegado_name = $delegado->getNombre();
            $delegado_num = $delegado->getDocumentNum();
        }


        $rd = array();

        $rd[] = $this->csprintf("%02.2s", '01');                                  //TipoRegistro
        $rd[] = $this->csprintf("%036.36s", '0');                                   //CodigodeBarras
        $rd[] = $this->csprintf("%015.15s", $num_shares);                           //Numero Títulos
        $rd[] = $this->csprintf("%01.1s", $tipo_documento);                       //Tipo Documento
        $rd[] = $this->csprintf("%-10.10s", $actuacion);                            //Delegación
        $rd[] = $this->csprintf("%-18.18s", $accionista->getDocumentNum());         //NIF
        $rd[] = $this->csprintf("%-100.100s", $delegado_name);                        //Representante
        $rd[] = $this->csprintf("%018.18s", $delegado_num);                         //Nif Representante
        $rd[] = $this->csprintf("%08.8s", $fecha);                                //Fecha
        $rd[] = $this->csprintf("%08.8s", $hora);                                 //Hora
        $rd[] = $this->csprintf("%02.2s", 'VE');                                  //Canal
        $rd[] = $this->csprintf("%036.36s", '0');                                   //CodigodeBrrasPadre
        $rd[] = $this->csprintf("%01.1s", $tipo_persona);                         //Tipo de Persona
        $rd[] = $this->csprintf("%-100.100s", $accionista->getName());                //Persona

        return implode($rd);
    }

    private function setHeader($content) {
        $h = array();
        $d = date("Ymd");
        $t = 'T';

        $h[] = $this->csprintf("%02.2s", '00');   //TipoRegistro
        $h[] = $this->csprintf("%01.1s", $t);     //Tipo
        $h[] = $this->csprintf("%08.8s", $d);     //Fecha
        $h[] = $this->csprintf("%-344.344s", ''); //Filler
        return implode($h);
    }

    private function csprintf($format) {
        $args = func_get_args();

        for ($i = 1; $i < count($args); $i++) {
            $args [$i] = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $args [$i]);
        }

        return call_user_func_array('sprintf', $args);
    }
    
     public function StoreMovimientosFile($filename, $content)
    {
        $path = 'movimientosCaixa/';
        if (!file_exists($path)) {
            mkdir($path);
        }
        $destinationfile = file_put_contents($path . $filename, $content);
        return $destinationfile;
    }

}
