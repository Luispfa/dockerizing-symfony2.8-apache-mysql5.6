<?php
namespace Lugh\WebAppBundle\DomainLayer\LughBuilder;
use Lugh\WebAppBundle\Entity\Offer;
use Lugh\WebAppBundle\Entity\Initiative;
use Lugh\WebAppBundle\Entity\AdhesionInitiative;
use Lugh\WebAppBundle\Entity\AdhesionOffer;
use Lugh\WebAppBundle\Entity\AdhesionProposal;
use Lugh\WebAppBundle\Entity\AdhesionRequest;
use Lugh\WebAppBundle\Entity\ItemAccionista;
use Lugh\WebAppBundle\Entity\Proposal;
use Lugh\WebAppBundle\Entity\Request;
use Lugh\WebAppBundle\Entity\Thread;
use Lugh\WebAppBundle\Entity\Question;
use Lugh\WebAppBundle\Entity\Desertion;
use Lugh\WebAppBundle\Entity\Accionista;
use Lugh\WebAppBundle\Entity\Message;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use Lugh\WebAppBundle\Entity\PuntoDia;
use Lugh\WebAppBundle\Entity\OpcionesVoto;
use Lugh\WebAppBundle\Entity\VotoPunto;
use Lugh\WebAppBundle\Entity\Voto;
use Lugh\WebAppBundle\Entity\Delegacion;
use Lugh\WebAppBundle\Entity\Delegado;
use Lugh\WebAppBundle\Entity\Anulacion;
use Lugh\WebAppBundle\Entity\User;
use Lugh\WebAppBundle\Entity\Document;
use Lugh\WebAppBundle\Entity\LogMail;
use Lugh\WebAppBundle\Entity\Parametros;
use Lugh\WebAppBundle\Entity\Communique;
use Lugh\WebAppBundle\Entity\VotoSerie;
use Lugh\WebAppBundle\Entity\TipoVoto;
use Lugh\WebAppBundle\Entity\AccionRechazada;
use Lugh\WebAppBundle\Entity\AppVoto;
use Lugh\WebAppBundle\Entity\AppForo;
use Lugh\WebAppBundle\Entity\AppDerecho;
use Lugh\WebAppBundle\Entity\AppAV;
use Lugh\WebAppBundle\Entity\VotoAbsAdicional;
use Lugh\WebAppBundle\Entity\Av;
use Lugh\WebAppBundle\Entity\AnulacionAv;
use Lugh\WebAppBundle\Entity\Live;
use Lugh\WebAppBundle\Entity\Registro;
use Lugh\WebAppBundle\Entity\AccesoVoto;
use Lugh\WebAppBundle\Entity\AccesoForo;
use Lugh\WebAppBundle\Entity\AccesoDerecho;
use Lugh\WebAppBundle\Entity\AccesoAV;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughBuilderProd
 *
 * @author a.navarro
 */
class LughBuilderProd extends LughBuilder{
    
    
    private function setState($item)
    {
        //$state = $this->get('lugh.parameters')->getByKey($item::nameClass . '.default.state', StateClass::statePending);
        $state = StateClass::stateNew;
        return $item->setState($state);
    }
    private function setLocked($item)
    {
        $locked = $this->get('lugh.parameters')->getByKey($item::nameClass . '.default.locked', StateClass::unLocked); 
        return $item->setLocked($locked); // @TODO: Arreglar para que se pueda crear un item cerrado.
    }
    private function setEnabled($item)
    {
        $enabled = $this->get('lugh.parameters')->getByKey($item::nameClass . '.default.enabled', StateClass::disable);
        return $item->setEnabled($enabled);
    }
    private function setDelegationState($item)
    {
        $option = $this->get('lugh.parameters')->getByKey('Options.ratificar.delegacion', 0);
        
        switch ($option) {
            case 0:
                $item->setState(StateClass::statePublic);
                break;
            case 1:
                $item->setState(StateClass::statePublic);
                break;
            case 2:
                $item->setState(StateClass::statePending);
                break;
            default:
                break;
        }
        return $item;
    }
    public function buildOffer() {
        return $this->exceptionTime($this->setState(new Offer()));
    }

    public function buildInitiative() {
        return $this->exceptionTime($this->setState(new Initiative()));
    }

    public function buildAdhesionInitiative() {
        return $this->exceptionTime($this->setState(new AdhesionInitiative()));
    }

    public function buildAdhesionOffer() {
        return $this->exceptionTime($this->setState(new AdhesionOffer()));
    }

    public function buildAdhesionProposal() {
        return $this->exceptionTime($this->setState(new AdhesionProposal()));
    }

    public function buildAdhesionRequest() {
        return $this->exceptionTime($this->setState(new AdhesionRequest()));
    }

    public function buildItemAccionista() {
        return $this->exceptionTime($this->setState(new ItemAccionista()));
    }

    public function buildProposal() {
        return $this->exceptionTime($this->setState(new Proposal()));
    }

    public function buildRequest() {
        return $this->exceptionTime($this->setState(new Request()));
    }

    public function buildThread() {
        return $this->exceptionTime($this->setLocked($this->setState(new Thread())));
    }
    
    public function buildQuestion() {
        return $this->exceptionTime($this->setState(new Question()));
    }
    
    public function buildDesertion() {
        return $this->exceptionTime(new Desertion());
    }
    
    public function buildAccionista() {
        return $this->exceptionTime(new Accionista());
    }
    
    public function buildRegistro() {
        return $this->exceptionTime(new Registro());
    }
    
    public function buildMessage() {
        return $this->exceptionTime(new Message());
    }
    
    public function buildPunto() {
        return new PuntoDia();
    }
 
    
    private function exceptionTime($item, $action = StateClass::actionCreate)
    {
        
        if(self::getApp($item) == 'Foro'){
            $fullClassName = get_class($item);
            if(self::getClassName($fullClassName) == 'Proposal'){
                $allowed = Restrictions::proposalsAllowed();
                if(!$allowed){
                    throw new Exception("No se permite presentar propuestas para esta convocatoria");
                }
            }
        }
        
        if (!Restrictions::inTime($item, $action))
        {
            //throw new Exception("item not in time to " . $action);
            throw new Exception("Fuera de plazo");
        }
        
        if(self::getApp($item) == 'Derecho'){
            $item = $this->exceptionMaxThreads($item);
        }
        
        return $item;
    }
    
    private function exceptionMaxThreads($item){
        if (!Restrictions::derechoMaxThreads())
        {
            throw new Exception("Ha llegado al máximo de preguntas");
        }
        return $item;
    }
    
    public function buildOpcionesVoto() {
        return new OpcionesVoto(); 
    }

    public function buildVoto() {
        return $this->exceptionTime(new Voto());
    }
    
    public function buildAv() {
        return $this->exceptionTime(new Av());
    }

    public function buildVotoPunto() {
        return $this->exceptionTime(new VotoPunto());
    }

    public function buildDelegacion() {
        return $this->exceptionTime($this->setDelegationState(new Delegacion()));
    }

    public function buildDelegado() {
        return $this->exceptionTime(new Delegado());
    }

    public function buildAnulacion() {
        return $this->exceptionTime(new Anulacion());
    }
    
    public function buildAnulacionAv() {
        return $this->exceptionTime(new AnulacionAv());
    }
    
    public function buildAccionRechazada() {
        return $this->exceptionTime(new AccionRechazada());
    }

    public function buildUser() {
        return $this->exceptionTime(new User());
    }

    public function buildDocument() {
        return $this->exceptionTime(new Document());
    }

    public function buildLogMail() {
        return $this->exceptionTime(new LogMail());
    }

    public function buildParametro() {
        return new Parametros();
    }

    public function buildCommunique() {
        return $this->exceptionTime($this->setEnabled(new Communique()));
    }

    public function buildVotoSerie() {
        return $this->exceptionTime(new VotoSerie());
    }

    public function buildTipoVoto() {
        return new TipoVoto();
    }

    public function buildAppVoto() {
        return new AppVoto();
    }
    
    public function buildAppForo() {
        return new AppForo();
    }
    
    public function buildAppDerecho() {
        return new AppDerecho();
    }
    
    public function buildAppAV() {
        return new AppAV();
    }

    public function buildVotoAbsAdicional() {
        return new VotoAbsAdicional();
    }
    
    public function buildLive() {
        return new Live();
    }
    
    public function buildAccesoVoto() {
        return new AccesoVoto();
    }
    
    public function buildAccesoForo() {
        return new AccesoForo();
    }
    
    public function buildAccesoDerecho() {
        return new AccesoDerecho();
    }
    
    public function buildAccesoAV() {
        return new AccesoAV();
    }
    
    static private function getApp($item)
    {
        if (defined(get_class($item) . '::appClass'))
        {
            return $item::appClass;
        }
        return '';
    }
    
    static private function getClassName($classname)
    {
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    }
    

}

?>
