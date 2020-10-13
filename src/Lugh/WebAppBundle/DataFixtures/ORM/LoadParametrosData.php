<?php

namespace Lugh\WebAppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Lugh\WebAppBundle\Entity\Parametros;

class LoadParametrosData implements FixtureInterface, ContainerAwareInterface
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
        $parametros = array(
            //clave, valor, observaciones
            //'VotoPunto.time.create1;{"from": "01-10-2014 16:00:00","to": "01-10-2014 17:00:00"};',
            'Accionista.time.create;{"from": "01-10-2014 16:00:00"};',
            //'Delegation.time.vote1;{"from": "01-10-2014 16:00:00","to": "01-10-2014 17:00:00"};',
            'Platform.time.activate;{"from": "01-10-2014 16:00:00"};',
            'Voto.time.activate;{"from": "01-10-2014 16:00:00"};',
            'Foro.time.activate;{"from": "01-10-2014 16:00:00"};',
            'Derecho.time.activate;{"from": "01-10-2014 16:00:00"};',
            'Av.time.activate;{"from": "01-10-2014 16:00:00"};',
            'Proposal.time.create;{"to": "01-10-2016 16:00:00"};',
            'Proposal.time.pendiente;{"to": "01-10-2016 16:00:00"};',
            'Proposal.time.publica;{"to": "01-10-2016 16:00:00"};',
            'Proposal.time.retorna;{"to": "01-10-2016 16:00:00"};',
            'Proposal.time.rechaza;{"to": "01-10-2016 16:00:00"};',
            'AdhesionProposal.time.create;{"to": "01-10-2016 16:00:00"};',
            'AdhesionProposal.time.pendiente;{"to": "01-10-2016 16:00:00"};',
            'AdhesionProposal.time.publica;{"to": "01-10-2016 16:00:00"};',
            'AdhesionProposal.time.retorna;{"to": "01-10-2016 16:00:00"};',
            'AdhesionProposal.time.rechaza;{"to": "01-10-2016 16:00:00"};',
            'Thread.time.create;[];',
            'Accionista.default.apps;{"voto" : 1, "foro" : 1, "derecho" : 1, "av" : 1};',
            'Accionista.default.state;1;',
            'AdhesionOffer.default.state;1;',
            'AdhesionRequest.default.state;1;',
            'AdhesionProposal.default.state;2;',
            'AdhesionInitiative.default.state;2;',
            'AppVoto.default.state;2;',
            'AppForo.default.state;2;',
            'AppDerecho.default.state;2;',
            'AppAv.default.state;3;',
            'Config.mail.workFlow;1;',
            'Config.factory.class;prod;',
            'Config.mail.transport;0;',
            'Config.mail.template;default;',
            'Config.mail.bcc;["lugh_bcc@juntadeaccionistas.es"];',
            'Config.mail.from;solicitud@header.net;',
            'Config.mail.user;lev002c;',
            'Config.mail.password;UsuarioA443;',
            'Config.mail.port;25;',
            'Config.mail.server;smtp.header.net;',
            'Config.accionista.accionesMin;1;',
            'Config.accionista.check.fichero;0',
            'Config.register.alertsTop;1;',
            'Config.register.alertsField;1;',
            'Config.certificate.enable;1;',
            'Config.userpass.enable;1;',
            'Options.ratificar.delegacion;1;',
            'Config.require.LOPD;1;',
            'Config.require.username;1;',
            'Config.require.email;1;',
            'Config.require.tipo-persona;1;',
            'Config.require.name;1;',
            'Config.require.doca;0;',
            'Config.require.docb;0;',
            'Config.require.doca-user;1;',
            'Config.require.doca-certificate;1;',
            'Config.require.docb-user;1;',
            'Config.require.docb-certificate;1;',
            'Config.require.tipo-doc;1;',
            'Config.require.numero-doc;1;',
            'Config.require.telephone;0;',
            'Config.platform.title;;',
            'Config.customer.title;;',
            'Config.cookies.message;1;',
            'Config.vote.minShares;1;',
            'Config.derecho.maxquestions;-1;',
            
            
            'Config.delegation.require.nombre;1;',
            'Config.delegation.require.tipodoc;1;',
            'Config.delegation.require.numdoc;1;',
            'Config.delegation.hide.nombre;0;',
            'Config.delegation.hide.tipodoc;0;',
            'Config.delegation.hide.numdoc;0;',
            'Config.delegation.hide.presidente;0;',
            'Config.delegation.hide.secretary;1;',
            'Config.delegation.hide.listado;1;',
            'Config.delegation.hide.persona;0;',
            'Config.delegation.hide.comments;0;',
            'Config.shares.minSharesBlock;1;',
            'Config.shares.sharesBlock;0;',
            //'Config.shares.vote;1;',
            //'Config.shares.instructions;1;',
            'Config.vote.minVotesBlock;1;',
            'Config.instructions.minVotesBlock;1;',
            'Config.vote.maxVotesBlock;1;',
            'Config.instructions.maxVotesBlock;1;',
            'Config.vote.loadPreviousVote;1;',
            'Config.vote.show.substitution;0;',
            'Config.vote.show.absAdicional;0;',
            'Config.vote.showGlobalVote;1;',
            'Condig.vote.show.virtualAssistance;0;',
            'Config.foro.allowproposals;1;',
            
            'Config.service.time;60000;',
            
            'Config.junta.workFlow;1;',
            
            'Voto.pieces.config;[{"id": "1","type": "arbitrary-view","title": "id00401_app:voto:arbitrarywelcome:title","data": {"title_body": "id00291_app:voto:welcome:title", "subtitle_body":"id00292_app:voto:welcome:subtitle", "body": "id00293_app:voto:welcome:body"}},{"id": "2","type": "shares","title": "","data": {"body": "lorem shares"}},{"id": "3","type": "DCICV","title": "","data": {}}];',
            'AvVoto.pieces.config;[{"id": "1","type": "arbitrary-av-view","title": "id00444_app:av:arbitrarywelcome:title","data": {"title_body": "id00445_app:av:welcome:title", "subtitle_body":"id00446_app:av:welcome:subtitle", "body": "id00447_app:av:welcome:body"}},{"id": "2","type": "vote-av","title": "","data": {"body": "id00443_app:av:body:vote"}}];',
            
            
            'Config.platform.logo;0;',
            'Config.langs.active;{"es":1,"en":1,"ca":1,"gl":0};',
            
            'stats.api.address;https://analytics.juntadeaccionistas.es/;',
            'stats.api.site_id;1;',
            'stats.api.key;d1bc048cdcb2457f12beb40adb2702be;',
            
            'Directory.fileupload.storages;{"r":["/lugh"],"w":"/lugh"};',
            
            'Av.live.appkey;JqbBOXMHhcqSmnww;',
            'Av.live.account;392;',
            'Av.live.psk;snjkmx6r;',
            'Av.live.secretkey;ab4bb57bbd7583c84752f8ebb4c9ad8139929ad2;',
            'Av.live.allLives;1;',
            'Av.live.address;https:\\www.header.net;',
            'Config.Av.opentargetmode;0;',
            'Config.Av.maxquestions;-1;',
            'Config.Av.showquestions;1;',
            'Config.Av.showdesertion;1;',
            

            'juntas.api.addrres;https://api.juntadeaccionistas.es/api/;',
            'juntas.api.user;api;',
            'juntas.api.key;SGVhZGVySGVhZGVyQDIyMjI=;',
            'juntas.api.sharesNum;0;'
        );

        foreach ($parametros as $parametro) {

            list($clave, $valor, $observaciones) = explode(';', $parametro);
            
            $parametroObject = new Parametros();
            $parametroObject->setKeyParam($clave);
            $parametroObject->setValueParam(str_replace('#', ';', $valor));
            $parametroObject->setObservaciones($observaciones);
            $manager->persist($parametroObject);
            $manager->flush();

        }
    }
}