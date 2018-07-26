<?php
namespace GameX\Forms\User;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Helpers\AuthHelper;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Forms\Elements\Text;
use \GameX\Core\Forms\Elements\Password;
use \GameX\Core\Forms\Rules\Required;
use \GameX\Core\Forms\Rules\Trim;
use \GameX\Core\Forms\Rules\Email as EmailRule;
use \GameX\Core\Forms\Rules\Length;
use \GameX\Core\Forms\Rules\PasswordRepeat;
use \GameX\Core\Forms\Rules\Callback;

class RegisterForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'register';

	/**
	 * @var AuthHelper
	 */
	protected $authHelper;

	/**
	 * @var boolean
	 */
	protected $activate;
    
    /**
     * @var UserModel
     */
	protected $user;

	/**
	 * @param AuthHelper $authHelper
	 * @param boolean $activate
	 */
	public function __construct(AuthHelper $authHelper, $activate) {
		$this->authHelper = $authHelper;
		$this->activate = $activate;
	}
    
    /**
     * @return UserModel
     */
	public function getUser() {
	    return $this->user;
    }
    
    /**
     * @param Form $form
     * @return bool
     */
    public function checkExists(Form $form) {
        return !$this->authHelper->exists($form->getValue('login'), $form->getValue('email'));
    }

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new Text('login', '', [
				'title' => $this->getTranslate('inputs', 'login'),
				'required' => true,
			]))
			->add(new EmailElement('email', '', [
				'title' => $this->getTranslate('inputs', 'email'),
				'required' => true,
			]))
			->add(new Password('password', '', [
				'title' => $this->getTranslate('inputs', 'password'),
				'required' => true,
			]))
			->add(new Password('password_repeat', '', [
				'title' => $this->getTranslate('inputs', 'password_repeat'),
				'required' => true,
			]))
			->addRule('login', new Trim())
			->addRule('login', new Required())
			->addRule('email', new Trim())
			->addRule('email', new Required())
			->addRule('email', new EmailRule())
			->addRule('email', new Callback([$this, 'checkExists'], 'User already exists'))
			->addRule('password', new Trim())
			->addRule('password', new Length(AuthHelper::MIN_PASSWORD_LENGTH))
			->addRule('password_repeat', new Trim())
			->addRule('password_repeat', new PasswordRepeat('password'));
	}

	/**
	 * @return bool
	 */
	protected function processForm() {

		$this->user = $this->authHelper->registerUser(
			$this->form->getValue('login'),
			$this->form->getValue('email'),
			$this->form->getValue('password'),
			$this->activate
		);

		return (bool) $this->user;
	}
}
