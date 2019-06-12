<?php
namespace GameX\Core\Mail\Helpers;

use \GameX\Core\Mail\Email;
use \GameX\Core\Mail\Helper;
use \GameX\Core\Configuration\Node;
use \Swift_Message;
use \Swift_SmtpTransport;
use \Swift_MailTransport;

class SwiftMailer extends Helper {
    
    /**
     * @param Email $to
     * @param string $subject
     * @param string $body
     * @return Swift_Message
     */
    protected function message(Email $to, $subject, $body) {
        $message = new Swift_Message($subject);
        $message
            ->setFrom([$this->from->getEmail() => $this->from->getName()])
            ->setTo([$to->getEmail() => $to->getName()])
	        ->setBody($body, 'text/html', 'utf-8');
        
        return $message;
    }

	/**
	 * @param Node $config
	 * @throws \GameX\Core\Configuration\Exceptions\NotFoundException
	 */
    protected function configure(Node $config) {
	    $sender = $config->getNode('sender');
	    $this->from = new Email($sender->get('email'), $sender->get('name'));
        switch ($config->get('type')) {
            case 'smtp': {
                $this->sender = new Swift_SmtpTransport($config->get('host'), $config->get('port'));
                $username = $config->get('username');
                $password = $config->get('password');
    
                if (!empty($username) && !empty($password)) {
                    $this->sender
                        ->setUsername($username)
                        ->setPassword($password);
                }
    
                $secure = $config->get('secure');
                if (!empty($secure) && in_array($secure, ['tls', 'ssl'], true)) {
                    $this->sender->setEncryption($secure);
                }
            } break;
            
            default: {
                $this->sender = new Swift_MailTransport();
            }
        }
    }
}
