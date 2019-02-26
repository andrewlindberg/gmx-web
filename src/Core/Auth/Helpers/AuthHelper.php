<?php

namespace GameX\Core\Auth\Helpers;

use \Psr\Container\ContainerInterface;
use \GameX\Core\Utils;
use \GameX\Core\Auth\Models\UserModel;
use \Cartalyst\Sentinel\Users\UserInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Cartalyst\Sentinel\Reminders\EloquentReminder;
use \GameX\Core\Exceptions\FormException;
use \GameX\Core\Exceptions\ValidationException;

class AuthHelper
{

    const MIN_PASSWORD_LENGTH = 6;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * AuthHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $login
     * @return UserModel|null
     */
    public function findUser($login)
    {
        return $this->getAuth()->getUserRepository()->findByCredentials([
            'login' => $login
        ]);
    }

    /**
     * @param string $login
     * @param string $email
     * @return bool
     */
    public function exists($login, $email)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->getAuth()->getUserRepository()->createModel()->newQuery();
        $query
            ->where('login', $login)
            ->orWhere('email', $email);

        return $query->exists();
    }

    /**
     * @param $login
     * @param $email
     * @param $password
     * @param bool $activate
     * @return bool|\Cartalyst\Sentinel\Users\UserInteface
     * @throws \Exception
     */
    public function registerUser($login, $email, $password, $activate = false)
    {
        return $this->getAuth()->register([
            'login' => $login,
            'email' => $email,
            'password' => $password,
            'token' => Utils::generateToken(16),
        ], $activate ? true : null);
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    public function getActivationCode(UserInterface $user)
    {
        return $this->getAuth()->getActivationRepository()->create($user)->getCode();
    }

    /**
     * @param UserModel $user
     * @param string $code
     * @return bool
     */
    public function activateUser(UserModel $user, $code)
    {
        return $this->getAuth()->getActivationRepository()->complete($user, $code);
    }

    /**
     * @param string $login
     * @param string $password
     * @return bool|\Cartalyst\Sentinel\Users\UserInterface
     * @throws ValidationException
     */
    public function loginUser($login, $password, $remember)
    {
        return $this->getAuth()->authenticate([
            'login' => $login,
            'password' => $password
        ], (bool)$remember);
    }

    /**
     * @return bool
     */
    public function logoutUser()
    {
        return $this->getAuth()->logout();
    }

    /**
     * @param UserInterface $user
     * @return string
     * @throws FormException
     * @throws ValidationException
     */
    public function resetPassword(UserInterface $user)
    {
        $reminderRepository = $this->getAuth()->getReminderRepository();
        if ($reminderRepository->exists($user)) {
            throw new ValidationException('You already reset password');
        }

        /** @var EloquentReminder $reminder */
        $reminder = $reminderRepository->create($user);
        if (!$reminder) {
            throw new ValidationException('Something wrong. Please Try again later.');
        }

        return $reminder->code;
    }

    /**
     * @param UserInterface $user
     * @param $password
     * @param $code
     * @throws ValidationException
     */
    public function resetPasswordComplete(UserInterface $user, $password, $code)
    {
        $reminderRepository = $this->getAuth()->getReminderRepository();
        if (!$reminderRepository->exists($user)) {
            throw new ValidationException('Bad code');
        }

        /** @var EloquentReminder $reminder */
        if (!$reminderRepository->complete($user, $code, $password)) {
            throw new ValidationException('Something wrong. Please Try again later.');
        }
    }

    /**
     * @param UserInterface $user
     * @param string $password
     * @return bool
     */
    public function validatePassword(UserInterface $user, $password)
    {
        return $this->getAuth()->getUserRepository()->validateCredentials($user, ['password' => $password]);
    }

    /**
     * @param UserInterface $user
     * @param string $password
     */
    public function changePassword(UserInterface $user, $password)
    {
        $this->getAuth()->getUserRepository()->update($user, ['password' => $password]);
    }

    /**
     * @param UserInterface $user
     * @param $code
     * @return bool|\Cartalyst\Sentinel\Activations\ActivationInterface
     */
    public function checkActivationExists(UserInterface $user, $code)
    {
        return $this->getAuth()->getActivationRepository()->exists($user, $code);
    }

    /**
     * @param UserInterface $user
     * @return bool|\Cartalyst\Sentinel\Activations\ActivationInterface
     */
    public function checkActivationCompleted(UserInterface $user)
    {
        return $this->getAuth()->getActivationRepository()->completed($user);
    }

    /**
     * @return Sentinel
     */
    protected function getAuth()
    {
        return $this->container->get('auth');
    }

    /**
     * @param $name
     * @param array $data
     * @return string
     */
    protected function getLink($name, array $data = [])
    {
        /** @var \Slim\Router $router */
        $router = $this->container->get('router');

        /** @var \Slim\Http\Request $router */
        $request = $this->container->get('request');

        return (string)$request
            ->getUri()
            ->withPath($router->pathFor($name, $data));
    }
}
