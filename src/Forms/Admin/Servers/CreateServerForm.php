<?php
namespace GameX\Forms\Admin\Servers;

use \GameX\Forms\Admin\ServerForm;
use \Firebase\JWT\JWT;
use \GameX\Core\Forms\Rules\Callback;
use \GameX\Core\Forms\Form;
use \GameX\Models\Server;

class CreateServerForm extends ServerForm {

	/**
	 * @var string
	 */
	protected $secret;
    
    /**
     * @param Server $server
     * @param string $secret
     */
	public function __construct(Server $server, $secret){
        parent::__construct($server);
        $this->secret = (string) $secret;
    }
    
    /**
     * @param Form $form
     * @return bool
     */
    public function checkExists(Form $form) {
        $ip = $form->get('ip');
        $port = $form->get('port');
        
        if (!$ip || !$port || $ip->getHasError() || $port->getHasError()) {
            return true;
        }
        
        return !Server::where([
            'ip' => $ip->getValue(),
            'port' => $port->getValue()
        ])->exists();
    }
	
    /**
     * @noreturn
     */
	protected function createForm() {
        parent::createForm();
        $this->form->addRule('port', new Callback(
            [$this, 'checkExists'], $this->getTranslate('admin_servers', 'already_exists')
        ));
    }
    
    /**
	 * @return boolean
	 */
	protected function processForm() {
		$this->server->fill($this->form->getValues());
		$this->server->token = JWT::encode([
			'server_id' => $this->server->id
		], $this->secret, 'HS512');
		return $this->server->save();
	}
}
