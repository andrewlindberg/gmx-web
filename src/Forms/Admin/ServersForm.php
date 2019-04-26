<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Models\Server;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Elements\Number as NumberElement;
use \GameX\Core\Validate\Rules\Number as NumberRule;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Validate\Rules\IPv4;
use \GameX\Core\Validate\Rules\InArray;
use \GameX\Core\Validate\Rules\Callback;

class ServersForm extends BaseForm
{

    /**
     * @var string
     */
    protected $name = 'admin_servers';

    /**
     * @var Server
     */
    protected $server;

	/**
	 * @var bool
	 */
    protected $rconEnabled;

    /**
     * @param Server $server
     * @param bool $rconEnabled
     */
    public function __construct(Server $server, $rconEnabled = false)
    {
        $this->server = $server;
        $this->rconEnabled = $rconEnabled;
    }
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkExists($value, array $values)
    {
        return !Server::where([
            'ip' => $values['ip'],
            'port' => $values['port']
        ])->exists() ? $value : null;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->form
            ->add(new Select('type', $this->server->type, [
                'cstrike' => 'Counter-Strike 1.6'
            ], [
                'title' => $this->getTranslate($this->name, 'type'),
                'required' => true,
                'empty_option' => $this->getTranslate($this->name, 'choose_type')
            ]))
            ->add(new Text('name', $this->server->name, [
                'title' => $this->getTranslate($this->name, 'name'),
                'required' => true,
            ]))->add(new Text('ip', $this->server->ip, [
                'title' => $this->getTranslate($this->name, 'ip'),
                'required' => true,
                'attributes' => [
                    'pattern' => '((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4}$',
                ],
            ]))->add(new NumberElement('port', $this->server->port, [
                'title' => $this->getTranslate($this->name, 'port'),
                'required' => true,
            ]));
        
        $this->form->getValidator()
            ->set('type', true, [
                new InArray(['cstrike'])
            ])
            ->set('ip', true, [
                new IPv4()
            ])
            ->set('name', true)
            ->set('ip', true, [
                new IPv4()
            ])
            ->set('port', true, [
                new NumberRule(1024, 65535)
            ]);
        
        if (!$this->server->exists) {
            $this->form
	            ->getValidator()->add('port', new Callback(
	            	[$this, 'checkExists'],
		            $this->getTranslate($this->name, 'already_exists')
	            ));
        }

	    if ($this->rconEnabled) {
	    	$required = !$this->server->exists;
		    $this->form->add(new Password('rcon', '', [
			    'title' => $this->getTranslate($this->name, 'rcon'),
			    'required' => $required,
		    ]));
		    $this->form->getValidator()->set('rcon', $required);
	    }
    }
    
    /**
     * @return boolean
     */
    protected function processForm()
    {
        $this->server->fill($this->form->getValues());
        return $this->server->save();
    }
}
