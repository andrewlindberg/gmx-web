<?php
namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use \GameX\Core\Forms\Form;
use \GameX\Models\Server;
use \GameX\Models\Privilege;
use \GameX\Models\Group;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\Date as DateElement;
use \GameX\Core\Forms\Elements\Checkbox;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\InArray;
use \GameX\Core\Forms\Rules\Boolean;
use \GameX\Core\Forms\Rules\Number;
use \GameX\Core\Forms\Rules\Date as DateRule;
use \GameX\Core\Forms\Rules\Callback;
use \GameX\Core\Exceptions\PrivilegeFormException;

class PrivilegesForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'admin_privileges';

	/**
	 * @var Privilege
	 */
	protected $privilege;

	/**
	 * @param Privilege $privilege
	 */
	public function __construct(Privilege $privilege) {
		$this->privilege = $privilege;
	}
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkGroupExists($value, array $values) {
        return Group::where('id', $value)->exists() ? $value : null;
    }
    
    
    /**
     * @param mixed $value
     * @param array $values
     * @return mixed|null
     */
    public function checkPrivilegeExists($value, array $values) {
        return !Privilege::where([
        	'player_id' => $this->privilege->player_id,
        	'group_id' => $value,
		])->exists() ? $value : null;
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
        $server = $this->privilege->exists
            ? $this->privilege->group->server
            : Server::first();

        if (!$server) {
        	throw new PrivilegeFormException('Add server before adding privilege', 'admin_servers_list');
		}
        
        $servers = $this->getServers();
        $groups = $this->getGroups($server);
		if (!count($groups)) {
			throw new PrivilegeFormException('Add privileges groups before adding privilege', 'admin_servers_groups_list', ['server' => $server->id]);
		}
		
		$this->form
            ->add(new Select('server', $server->id, $servers, [
                'id' => 'input_admin_server',
                'title' => 'Server',
                'required' => true,
                'empty_option' => 'Choose server',
            ]))
            ->add(new Select('group', $this->privilege->group_id, $groups, [
                'id' => 'input_player_group',
                'title' => 'Group',
                'required' => true,
                'empty_option' => 'Choose group',
            ]))
            ->add(new Text('prefix', $this->privilege->prefix, [
                'title' => 'Prefix',
            ]))
            ->add(new DateElement('expired', $this->privilege->expired_at, [
                'title' => 'Expired',
                'required' => true,
            ]))
            ->add(new Checkbox('active', !$this->privilege->exists || $this->privilege->active ? true : false, [
                'title' => 'Active',
            ]))
            ->addRule('server', new Required())
            ->addRule('server', new Number(1))
            ->addRule('server', new InArray(array_keys($servers)))
            ->addRule('group', new Required())
            ->addRule('group', new Number(1))
            ->addRule('group', new Callback([$this, 'checkGroupExists'], 'Group doesn\'t exists'))
            ->addRule('prefix', new Trim())
            ->addRule('expired', new Required())
            ->addRule('expired', new DateRule())
            ->addRule('active', new Boolean());
		
		if (!$this->privilege->exists) {
		    $this->form
                ->addRule('group', new Callback([$this, 'checkPrivilegeExists'], 'Privilege already exists'));
        }
	}
    
    /**
     * @return boolean
     */
    protected function processForm() {
        /** @var \DateTime $expired */
        $expired = $this->form->get('expired');
        $this->privilege->group_id = $this->form->getValue('group');
        $this->privilege->prefix = $this->form->getValue('prefix');
        $this->privilege->expired_at = $expired->format('Y-m-d H:i:s');
        $this->privilege->active = $this->form->getValue('active') ? 1 : 0;
        return $this->privilege->save();
    }
    
    /**
     * @return array
     */
    protected function getServers() {
        $servers = [];
        /** @var Server $server */
        foreach (Server::all() as $server) {
            $servers[$server->id] = $server->name;
        }
        return $servers;
    }
    
    /**
     * @param Server $server
     * @return array
     */
    protected function getGroups(Server $server) {
        $groups = [];
        foreach ($server->groups as $group) {
            $groups[$group->id] = $group->title;
        }
        return $groups;
    }
}
