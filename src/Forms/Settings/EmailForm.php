<?php
namespace GameX\Forms\Settings;

use \GameX\Core\BaseForm;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Forms\Elements\Email as EmailElement;
use \GameX\Core\Validate\Rules\Email as EmailRule;

class EmailForm extends BaseForm {

	/**
	 * @var string
	 */
	protected $name = 'user_settings_email';

	/**
	 * @var UserModel
	 */
	protected $user;

	/**
	 * @param UserModel $user
	 */
	public function __construct(UserModel $user) {
		$this->user = $user;
	}

	/**
	 * @noreturn
	 */
	protected function createForm() {
		$this->form
			->add(new EmailElement('email', $this->user->email, [
				'title' => 'Email',
				'required' => true
			]));

		$this->form->getValidator()
			->set('email', true, [
			    new EmailRule()
            ]);
	}

    /**
     * @return bool
     * @throws \Exception
     */
	protected function processForm() {
		$this->user->email = $this->form->getValue('email');
		return $this->user->save();
	}
}
