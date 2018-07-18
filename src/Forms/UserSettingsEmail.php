<?php
namespace GameX\Forms;

use \GameX\Core\BaseForm;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Email as EmailRule;

class UserSettingsEmail extends BaseForm {
    
    public static function init(array $data) {
        return static::create('user_settings_email')
            ->add(new Password('old_password', '', [
                'title' => 'Old password',
                'required' => true,
            ]))
            ->add(new Password('new_password', '', [
                'title' => 'New password',
                'required' => true,
            ]))
            ->add(new Password('repeat_password', '', [
                'title' => 'Repeat password',
                'required' => true,
            ]))
            ->addRule('old_password', new Required())
            ->addRule('old_password', new Length(['min' => 6]))
            ->addRule('new_password', new Required())
            ->addRule('new_password', new Length(['min' => 6]))
            ->addRule('repeat_password', new PasswordRepeat(['element' => 'new_password']));
    }
}
