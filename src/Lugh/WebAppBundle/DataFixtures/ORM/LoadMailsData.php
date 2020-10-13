<?php

namespace Lugh\WebAppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Lugh\WebAppBundle\Entity\Mails;

class LoadMailsData implements FixtureInterface, ContainerAwareInterface
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
            
            'User.mail.resetPassword;[{"To":["USER"],"Subject":"subject-user-password-new_password","TranslateTag":"body-user-password-new_password","Template":"","Hide":1,"Activate":1}];',
            'User.mail.forgotPassword;[{"To":["USER"],"Subject":"subject-user-password-regenerate","TranslateTag":"body-user-password-regenerate","Template":"","Hide":1,"Activate":1}];',

            'ItemAccionista_ROLE_USER_FULL.mail.create;[{"To":["USER"],"Subject":"subject-user-password-accionista","TranslateTag":"body-user-password-accionista","Template":"","Hide":1,"Activate":1}];',
            'ItemAccionista.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-pdte_aprovacion-accionista","TranslateTag":"body-user-pdte_aprovacion-accionista","Template":"","Hide":1,"Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-pdte_aprovacion-accionista","TranslateTag":"body-moderator-pdte_aprovacion-accionista","Template":"","Hide":1,"Activate":1}];',
            'ItemAccionista.mail.retorna;[{"To":["USER"],"Subject":"subject-user-pdte_revision-accionista","TranslateTag":"body-user-pdte_revision-accionista","Template":"","Hide":1,"Activate":1}];',
            'ItemAccionista.mail.publica;[{"To":["USER"],"Subject":"subject-user-publicacion-accionista","TranslateTag":"body-user-publicacion-accionista","Template":"","Hide":1,"Activate":1}];',
            'ItemAccionista.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-descarte-accionista","TranslateTag":"body-user-descarte-accionista","Template":"","Hide":1,"Activate":1}];',

            'Offer.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-pdte_aprovacion-offer","TranslateTag":"body-user-pdte_aprovacion-offer","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-pdte_aprovacion-offer","TranslateTag":"body-moderator-pdte_aprovacion-offer","Template":"","Activate":1}];',
            'Offer.mail.retorna;[{"To":["USER"],"Subject":"subject-user-pdte_revision-offer","TranslateTag":"body-user-pdte_revision-offer","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-pdte_revision-adhered-offer","TranslateTag":"body-user-descarte-pdte_revision-offer","Template":"","Activate":1}];',
            'Offer.mail.publica;[{"To":["USER"],"Subject":"subject-user-publicacion-offer","TranslateTag":"body-user-publicacion-offer","Template":"","Activate":1}];',
            'Offer.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-descarte-offer","TranslateTag":"body-user-descarte-offer","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-descarte-owner-offer","TranslateTag":"body-moderator-descarte-owner-offer","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-descarte-adhered-offer","TranslateTag":"body-user-descarte-adhered-offer","Template":"","Activate":1}];',

            'Request.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-pdte_aprovacion-request","TranslateTag":"body-user-pdte_aprovacion-request","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-pdte_aprovacion-request","TranslateTag":"body-moderator-pdte_aprovacion-request","Template":"","Activate":1}];',
            'Request.mail.retorna;[{"To":["USER"],"Subject":"subject-user-pdte_revision-request","TranslateTag":"body-user-pdte_revision-request","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-pdte_revision-adhered-request","TranslateTag":"body-user-pdte_revision-adhered-request","Template":"","Activate":1}];',
            'Request.mail.publica;[{"To":["USER"],"Subject":"subject-user-publicacion-request","TranslateTag":"body-user-publicacion-request","Template":"","Activate":1}];',
            'Request.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-descarte-request","TranslateTag":"body-user-descarte-request","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-descarte-owner-request","TranslateTag":"body-moderator-descarte-owner-request","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-descarte-adhered-request","TranslateTag":"body-user-descarte-adhered-request","Template":"","Activate":1}];',

            'Initiative.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-pdte_aprovacion-initiative","TranslateTag":"body-user-pdte_aprovacion-initiative","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-pdte_aprovacion-initiative","TranslateTag":"body-moderator-pdte_aprovacion-initiative","Template":"","Activate":1}];',
            'Initiative.mail.retorna;[{"To":["USER"],"Subject":"subject-user-pdte_revision-initiative","TranslateTag":"body-user-pdte_revision-initiative","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-pdte_revision-adhered-initiative","TranslateTag":"body-user-pdte_revision-adhered-initiative","Template":"","Activate":1}];',
            'Initiative.mail.publica;[{"To":["USER"],"Subject":"subject-user-publicacion-initiative","TranslateTag":"body-user-publicacion-initiative","Template":"","Activate":1}];',
            'Initiative.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-descarte-initiative","TranslateTag":"body-user-descarte-initiative","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-descarte-owner-initiative","TranslateTag":"body-moderator-descarte-owner-initiative","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-descarte-adhered-initiative","TranslateTag":"body-user-descarte-adhered-initiative","Template":"","Activate":1}];',

            'Proposal.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-pdte_aprovacion-proposal","TranslateTag":"body-user-pdte_aprovacion-proposal","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-pdte_aprovacion-proposal","TranslateTag":"body-moderator-pdte_aprovacion-proposal","Template":"","Activate":1}];',
            'Proposal.mail.retorna;[{"To":["USER"],"Subject":"subject-user-pdte_revision-proposal","TranslateTag":"body-user-pdte_revision-proposal","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-pdte_revision-adhered-proposal","TranslateTag":"body-user-pdte_revision-adhered-proposal","Template":"","Activate":1}];',
            'Proposal.mail.publica;[{"To":["USER"],"Subject":"subject-user-publicacion-proposal","TranslateTag":"body-user-publicacion-proposal","Template":"","Activate":1}];',
            'Proposal.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-descarte-proposal","TranslateTag":"body-user-descarte-proposal","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-descarte-owner-proposal","TranslateTag":"body-moderator-descarte-owner-proposal","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-descarte-adhered-proposal","TranslateTag":"body-user-descarte-adhered-proposal","Template":"","Activate":1}];',

            'AdhesionOffer.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-adhesion-offer-adhered","TranslateTag":"body-user-adhesion-offer-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-offer","TranslateTag":"body-moderator-adhesion-offer","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-offer-owner","TranslateTag":"body-user-adhesion-offer-owner","Template":"","Activate":1}];',      
            'AdhesionOffer.mail.publica;[{"To":["USER"],"Subject":"subject-user-adhesion-offer-adhered-basic-accept","TranslateTag":"body-user-adhesion-offer-adhered-basic-accept","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-offer-owner-basic-accept","TranslateTag":"body-user-adhesion-offer-owner-basic-accept","Template":"","Activate":1}];',
            'AdhesionOffer.mail.retorna;[{"To":["USER"],"Subject":"subject-user-adhesion-offer-pdte_revision-adhered","TranslateTag":"body-user-adhesion-offer-pdte_revision-adhered","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-offer-pdte_revision-owner","TranslateTag":"body-user-adhesion-offer-pdte_revision-owner","Template":"","Activate":1}];',
            'AdhesionOffer.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-adhesion-offer-delete-adhered","TranslateTag":"body-user-adhesion-offer-delete-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-offer-delete","TranslateTag":"body-moderator-adhesion-offer-delete","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-offer-delete-owner","TranslateTag":"body-user-adhesion-offer-delete-owner","Template":"","Activate":1}];',

            'AdhesionRequest.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-adhesion-request-adhered","TranslateTag":"body-user-adhesion-request-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-request","TranslateTag":"body-moderator-adhesion-request","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-request-owner","TranslateTag":"body-user-adhesion-request-owner","Template":"","Activate":1}];',
            'AdhesionRequest.mail.publica;[{"To":["USER"],"Subject":"subject-user-adhesion-request-adhered-basic-accept","TranslateTag":"body-user-adhesion-request-adhered-basic-accept","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-request-owner-basic-accept","TranslateTag":"body-user-adhesion-request-owner-basic-accept","Template":"","Activate":1}];',
            'AdhesionRequest.mail.retorna;[{"To":["USER"],"Subject":"subject-user-adhesion-request-pdte_revision-adhered","TranslateTag":"body-user-adhesion-request-pdte_revision-adhered","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-request-pdte_revision-owner","TranslateTag":"body-user-adhesion-request-pdte_revision-owner","Template":"","Activate":1}];',
            'AdhesionRequest.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-adhesion-request-delete-adhered","TranslateTag":"body-user-adhesion-request-delete-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-request-delete","TranslateTag":"body-moderator-adhesion-request-delete","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-request-delete-owner","TranslateTag":"body-user-adhesion-request-delete-owner","Template":"","Activate":1}];',

            'AdhesionInitiative.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-adhesion-initiative-adhered","TranslateTag":"body-user-adhesion-initiative-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-initiative","TranslateTag":"body-moderator-adhesion-initiative","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-initiative-owner","TranslateTag":"body-user-adhesion-initiative-owner","Template":"","Activate":1}];',            
            'AdhesionInitiative.mail.publica;[{"To":["USER"],"Subject":"subject-user-adhesion-initiative-basic-adhered","TranslateTag":"body-user-adhesion-initiative-basic-adhered","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-initiative-basic-owner","TranslateTag":"body-user-adhesion-initiative-basic-owner","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-initiative-basic","TranslateTag":"body-moderator-adhesion-initiative-basic","Template":"","Activate":1}];',
            'AdhesionInitiative.mail.retorna;[{"To":["USER"],"Subject":"subject-user-adhesion-initiative-pdte_revision-adhered","TranslateTag":"body-user-adhesion-initiative-pdte_revision-adhered","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-initiative-pdte_revision-owner","TranslateTag":"body-user-adhesion-initiative-pdte_revision-owner","Template":"","Activate":1}];',
            'AdhesionInitiative.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-adhesion-initiative-delete-adhered","TranslateTag":"body-user-adhesion-initiative-delete-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-initiative-delete","TranslateTag":"body-moderator-adhesion-initiative-delete","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-initiative-delete-owner","TranslateTag":"body-user-adhesion-initiative-delete-owner","Template":"","Activate":1}];',

            'AdhesionProposal.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-adhesion-proposal-adhered","TranslateTag":"body-user-adhesion-proposal-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-proposal","TranslateTag":"body-moderator-adhesion-proposal","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-proposal-owner","TranslateTag":"body-user-adhesion-proposal-owner","Template":"","Activate":1}];',            
            'AdhesionProposal.mail.publica;[{"To":["USER"],"Subject":"subject-user-adhesion-proposal-basic-adhered","TranslateTag":"body-user-adhesion-proposal-basic-adhered","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-proposal-basic-owner","TranslateTag":"body-user-adhesion-proposal-basic-owner","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-proposal-basic","TranslateTag":"body-moderator-adhesion-proposal-basic","Template":"","Activate":"activate"}];',
            'AdhesionProposal.mail.retorna;[{"To":["USER"],"Subject":"subject-user-adhesion-proposal-pdte_revision-adhered","TranslateTag":"body-user-adhesion-proposal-pdte_revision-adhered","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-proposal-pdte_revision-owner","TranslateTag":"body-user-adhesion-proposal-pdte_revision-owner","Template":"","Activate":1}];',
            'AdhesionProposal.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-adhesion-proposal-delete-adhered","TranslateTag":"body-user-adhesion-proposal-delete-adhered","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-adhesion-proposal-delete","TranslateTag":"body-moderator-adhesion-proposal-delete","Template":"","Activate":1},{"To":["OWNER"],"Subject":"subject-user-adhesion-proposal-delete-owner","TranslateTag":"body-user-adhesion-proposal-delete-owner","Template":"","Activate":1}];',

            'Thread.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-pdte_aprovacion-thread","TranslateTag":"body-user-pdte_aprovacion-thread","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-pdte_aprovacion-thread","TranslateTag":"body-moderator-pdte_aprovacion-thread","Template":"","Activate":1}];',
            'Thread.mail.retorna;[{"To":["USER"],"Subject":"subject-user-pdte_revision-thread","TranslateTag":"body-user-pdte_revision-thread","Template":"","Activate":1}];',
            'Thread.mail.publica;[{"To":["USER"],"Subject":"subject-user-publicacion-thread","TranslateTag":"body-user-publicacion-thread","Template":"","Activate":1}];',
            'Thread.mail.rechaza;[{"To":["USER"],"Subject":"subject-user-descarte-thread","TranslateTag":"body-user-descarte-thread","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-descarte-owner-thread","TranslateTag":"body-moderator-descarte-owner-thread","Template":"","Activate":1},{"To":["ADHESIONS"],"Subject":"subject-user-descarte-adhered-offer","TranslateTag":"body-user-descarte-adhered-offer","Template":"","Activate":1}];',
            
            'Voto.mail.create;[{"To":["USER"],"Subject":"subject-user-voto-create","TranslateTag":"user-voto-create","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subjetct-admin-voto-create","TranslateTag":"admin-voto-create","Template":"","Activate":1}];',
            
            'Message.mail.add;[{"To":["USER"],"Subject":"subject-message-add","TranslateTag":"user-message-add","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-message-add","TranslateTag":"user-message-add","Template":"","Activate":1}];',
            'Message_Proposal.mail.add;[{"To":["USER"],"Subject":"subject-message-proposal-add","TranslateTag":"user-message-proposal-add","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-message-proposal-add","TranslateTag":"user-message-proposal-add","Template":"","Activate":1}];',
            'Message_Initiative.mail.add;[{"To":["USER"],"Subject":"subject-message-initiative-add","TranslateTag":"user-message-initiative-add","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-message-initiative-add","TranslateTag":"user-message-initiative-add","Template":"","Activate":1}];',
            'Message_Request.mail.add;[{"To":["USER"],"Subject":"subject-message-request-add","TranslateTag":"user-message-request-add","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-message-request-add","TranslateTag":"user-message-request-add","Template":"","Activate":1}];',
            'Message_Offer.mail.add;[{"To":["USER"],"Subject":"subject-message-offer-add","TranslateTag":"user-message-offer-add","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-message-offer-add","TranslateTag":"user-message-offer-add","Template":"","Activate":1}];',

            'Delegacion.mail.publica;[{"To":["USER"],"Subject":"subject-user-delegacion-publica","TranslateTag":"user-delegacion-publica","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-admin-delegacion-publica","TranslateTag":"admin-delegacion-publica","Template":"","Activate":1}];',
            'Delegacion.mail.pendiente;[{"To":["USER"],"Subject":"Delegación pendiente","TranslateTag":"accionistaDelegacionPendiente","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"Delegación pendiente","TranslateTag":"adminDelegacionPendiente","Template":"","Activate":1},{"To":["DELEGADO"],"Subject":"Delegación pendiente","TranslateTag":"delegadoDelegacionPendiente","Template":"","Activate":1}];',
            'Delegacion.mail.rechaza;[{"To":["USER"],"Subject":"Delegación rechzada","TranslateTag":"accionistaDelegacionRechaza","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"Delegación rechzada","TranslateTag":"adminDelegacionRechaza","Template":"","Activate":1}];',
            
            'Anulacion.mail.create;[{"To":["USER"],"Subject":"subject-user-anulacion-create","TranslateTag":"user-anulacion-create","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-admin-anulacion-create","TranslateTag":"admin-anulacion-create","Template":"","Activate":1}];',
            'AppAV.mail.pendiente;[{"To":["USER"],"Subject":"subject-user-av-pendiente","TranslateTag":"user-av-pendiente","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-av-pendiente","TranslateTag":"moderator-av-pendiente","Template":"","Activate":1}];',
            'Accionista.mail.acreditado;[{"To":["USER"],"Subject":"subject-accionista-acreditado","TranslateTag":"user-accionista-acreditado","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-moderator-accionista-acreditado","TranslateTag":"moderator-accionista-acreditado","Template":"","Activate":1}];',
            'Av.mail.create;[{"To":["USER"],"Subject":"subject-user-votoav-create","TranslateTag":"user-votoav-create","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-admin-votoav-create","TranslateTag":"admin-votoav-create","Template":"","Activate":1}];',
            'Question.mail.create;[{"To":["USER"],"Subject":"subject-question-create","TranslateTag":"user-question-create","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-admin-question-create","TranslateTag":"admin-question-create","Template":"","Activate":1}];',
            'Desertion.mail.create;[{"To":["USER"],"Subject":"subject-desertion-create","TranslateTag":"user-desertion-create","Template":"","Activate":1},{"To":["CUSTOMER"],"Subject":"subject-admin-desertion-create","TranslateTag":"admin-desertion-create","Template":"","Activate":1}];'
        );

        foreach ($parametros as $parametro) {

            list($clave, $valor, $observaciones) = explode(';', $parametro);
            
            $parametroObject = new Mails();
            $parametroObject->setKeyParam($clave);
            $parametroObject->setValueParam(str_replace('#', ';', $valor));
            $parametroObject->setObservaciones($observaciones);
            $manager->persist($parametroObject);
            $manager->flush();

        }
    }
}