<?php
namespace Lugh\WebAppBundle\DomainLayer\Director;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughDirectorBuilder
 *
 * @author a.navarro
 */
class LughDirectorMailer extends LughDirector {
    
    public function sendMailByAddress($to, $body, $subject, $from=null) {
         return $this->builder->sendMailByAddress($to, $body, $subject, $from);
    }
    public function sendMailByRole($roles, $body, $subject, $from=null) {
        return $this->builder->sendMailByRole($roles, $body, $subject, $from);
    }
    public function workflow($item, $state, $extra = '', $attributs = array(), $external = array(), $attachments = array()) {
        return $this->builder->workflow($item, $state, $extra, $attributs, $external, $attachments);
    }
    public function formatandsend($item, $state, $extra = '', $attributs = array(), $external = array()) {
        return $this->builder->formatandsend($item, $state, $extra, $attributs, $external);
    }
    public function sendMailByUsername($usernames, $body, $subject, $from=null) {
        return $this->builder->sendMailByUsername($usernames, $body, $subject, $from);
    }
    public function setWorkflowOff($switch = true) {
        return $this->builder->setWorkflowOff($switch);
    }
}

?>
