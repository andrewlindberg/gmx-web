<?php

namespace GameX\Forms\Admin;

use \GameX\Core\BaseForm;
use GameX\Core\Forms\Elements\Checkbox;
use \GameX\Models\Server;
use \GameX\Models\Punishment;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Select;
use \GameX\Core\Forms\Elements\BitMask as BitMaskElement;
use \GameX\Core\Forms\Elements\Date as DateElement;
use \GameX\Core\Validate\Rules\InArray;
use \GameX\Core\Validate\Rules\BitMask as BitMaskRule;
use \GameX\Core\Validate\Rules\Boolean;
use \GameX\Core\Validate\Rules\Number;
use \GameX\Core\Validate\Rules\Date as DateRule;
use \GameX\Core\Exceptions\PunishmentsFormException;

class PunishmentsForm extends BaseForm
{

    /**
     * @var string
     */
    protected $name = 'admin_punishments';

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Punishment
     */
    protected $punishment;

    /**
     * @param Server $server
     * @param Punishment $punishment
     */
    public function __construct(Server $server, Punishment $punishment)
    {
        $this->server = $server;
        $this->punishment = $punishment;
    }

    /**
     * @noreturn
     */
    protected function createForm()
    {
        $reasons = $this->getReasons();
        if (!count($reasons)) {
            throw new PunishmentsFormException();
        }

        $this->form->add(new Select('reason', $this->punishment->reason_id, $reasons, [
                'title' => $this->getTranslate($this->name, 'reason'),
                'required' => true,
                'empty_option' => $this->getTranslate($this->name, 'reason_empty'),
            ]))->add(new Text('details', $this->punishment->details, [
                'title' => 'Details',
                'required' => false,
            ]))->add(new BitMaskElement('type', $this->punishment->type, [
                Punishment::TYPE_BANNED => 'Ban',
                Punishment::TYPE_GAGED => 'Gag',
                Punishment::TYPE_MUTED => 'Mute',
            ]))->add(new Checkbox('forever', $this->punishment->expired_at === null, [
                'title' => $this->getTranslate($this->name, 'forever'),
            ]))->add(new DateElement('expired', $this->punishment->expired_at, [
                'title' => $this->getTranslate($this->name, 'expired'),
                'required' => false,
            ]));

        $this->form->getValidator()->set('reason', true, [
                new Number(1),
                new InArray(array_keys($reasons)),
            ])->set('details', false)->set('type', true, [
                new BitMaskRule([
                    Punishment::TYPE_BANNED,
                    Punishment::TYPE_GAGED,
                    Punishment::TYPE_MUTED,
                ])
            ], [
                'check' => Validator::CHECK_ARRAY,
                'trim' => false,
                'default' => 0
            ])->set('forever', false, [
                new Boolean()
            ], [
                'default' => false
            ])->set('expired', false, [
                new DateRule()
            ]);
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    protected function processForm()
    {
        $this->punishment->reason_id = $this->form->getValue('reason');
        $this->punishment->type = $this->form->getValue('type');
        $this->punishment->expired_at = !$this->form->getValue('forever') ? $this->form->getValue('expired') : null;
        $this->punishment->status = Punishment::STATUS_PUNISHED;
        return $this->punishment->save();
    }
    
    /**
     * @return array
     */
    protected function getReasons()
    {
        $reasons = [];
        foreach ($this->server->reasons as $reason) {
            $reasons[$reason->id] = $reason->title;
        }
        return $reasons;
    }
}
