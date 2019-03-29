<?php
namespace GameX\Forms\Settings;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Validate\Rules\Length;
use \GameX\Core\Validate\Rules\PasswordRepeat;
use \GameX\Core\Exceptions\FormException;

class PasswordForm extends BaseForm {
    
    /**
     * @var string
     */
    protected $name = 'user_settings_password';
    
    /**
     * @var UserModel
     */
    protected $user;
    
    /**
     * @var AuthHelper
     */
    protected $authHelper;
    
    /**
     * @param UserModel $user
     * @param AuthHelper $authHelper
     */
    public function __construct(UserModel $user, AuthHelper $authHelper) {
        $this->user = $user;
        $this->authHelper = $authHelper;
    }
    
    /**
     * @noreturn
     */
    public function createForm() {
        $this->form
            ->add(new Password('old_password', '', [
                'title' => $this->getTranslate('inputs', 'current_password'),
                'required' => true,
            ]))
            ->add(new Password('new_password', '', [
                'title' => $this->getTranslate('inputs', 'new_password'),
                'required' => false,
            ]))
            ->add(new Password('repeat_password', '', [
                'title' => $this->getTranslate('inputs', 'password_repeat'),
                'required' => true,
            ]));

        $this->form->getValidator()
            ->set('old_password', true, [
                new Length(AuthHelper::MIN_PASSWORD_LENGTH)
            ])
            ->set('new_password', true, [
                new Length(AuthHelper::MIN_PASSWORD_LENGTH)
            ])
            ->set('repeat_password', true, [
                new PasswordRepeat('new_password')
            ]);
    }
    
    /**
     * @return boolean
     * @throws FormException
     */
    protected function processForm() {
        if (!$this->authHelper->validatePassword($this->user, $this->form->getValue('old_password'))) {
            throw new FormException('old_password', $this->getTranslate('forms', 'bad_password'));
        }
        $this->authHelper->changePassword($this->user, $this->form->getValue('new_password'));
        return true;
    }
}
