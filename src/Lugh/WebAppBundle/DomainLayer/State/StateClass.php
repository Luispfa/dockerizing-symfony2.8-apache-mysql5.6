<?php
namespace Lugh\WebAppBundle\DomainLayer\State;
use Lugh\WebAppBundle\DomainLayer\LughService;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accionista
 *
 * @author a.navarro
 */
abstract class StateClass extends LughService {
    
    const stateNew              = 0;
    
    const statePending          = 1;
    const statePublic           = 2;
    const stateRetornate        = 3;
    const stateReject           = 4;
    
    const stateConfiguracion    = 1;
    const stateConvocatoria     = 2;
    const statePrejunta         = 3;
    const stateAsistencia       = 4;
    const stateQuorumCerrado    = 5;
    const stateVotacion         = 6;
    const stateFinalizado       = 7;
    
    const locked                = 1;
    const unLocked              = 0;
    
    const enable                = 1;
    const disable               = 0;
    
    const actionPending         = 'pendiente';
    const actionPublic          = 'publica';
    const actionRetornate       = 'retorna';
    const actionReject          = 'rechaza';
    const actionStore           = 'store';
    const actionCreate          = 'create';
    const actionDelete          = 'delete';
    const actionGet             = 'get';
    const actionAdd             = 'add';
    
    const actionLocked          = 'locked';
    const actionUnLocked        = 'unlocked';
    const actionEnable          = 'enable';
    const actionDisable         = 'disable';
    
    protected $mailer;
    
    public function __construct($container) {
        parent::__construct($container);
        $this->mailer = $this->get('mailer.builder');
    }
    
    protected function getExternal($comments, $postTag = 'Comment')
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $tag = 'user' . $postTag;
        if (is_object($user))
        {
            foreach($user->getRoles() as $role){
                if($role == "ROLE_ADMIN" || $role == "ROLE_SUPER_ADMIN" || $role == "ROLE_CUSTOMER"){
                    $tag = 'admin' . $postTag;
                }
            }
        }

       return $comments == null || $comments == '' ? array() : array('tag' => $tag, 'vars' => array('%comments%' => $comments));
    }
    
    protected function mailerState($item, $state, $external)
    {
        try {
            $this->mailer->workflow($item, $state, '', array(), $external);
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }
   
    abstract function pendiente($item, $comments = null);
    abstract function publica($item, $comments = null);
    abstract function retorna($item, $comments = null);
    abstract function rechaza($item, $comments = null);
    abstract function locked($item, $comments = null);
    abstract function unlocked($item, $comments = null);
    abstract function enable($item, $comments = null);
    abstract function disable($item, $comments = null);
    
    abstract function configuracion($junta);
    abstract function convocatoria($junta);
    abstract function prejunta($junta);
    abstract function asistencia($junta);
    abstract function quorumcerrado($junta);
    abstract function votacion($junta);
    abstract function finalizado($junta);
}

?>
