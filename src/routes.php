<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\UserController;
use \GameX\Controllers\Admin\UsersController;
use \GameX\Controllers\Admin\RolesController;

//$app
//    ->get('/', BaseController::action(IndexController::class, 'index'))
//    ->setName('index');

$app
    ->map(['GET', 'POST'], '/', BaseController::action(IndexController::class, 'index'))
    ->setName('index')
    ->setArgument('permission', 'index.view');

$app
    ->map(['GET', 'POST'], '/register', BaseController::action(UserController::class, 'register'))
    ->setName('register');

$app
	->map(['GET', 'POST'], '/activation/{code}', BaseController::action(UserController::class, 'activate'))
    ->setName('activation');

$app
	->map(['GET', 'POST'], '/login', BaseController::action(UserController::class, 'login'))
    ->setName('login');

$app
	->map(['GET', 'POST'], '/reset_password', BaseController::action(UserController::class, 'resetPassword'))
    ->setName('reset_password');

$app
    ->map(['GET', 'POST'], '/reset_password/{code}', BaseController::action(UserController::class, 'resetPasswordComplete'))
    ->setName('reset_password_complete');

$app
	->get('/logout', BaseController::action(UserController::class, 'logout'))
    ->setName('logout');


$app->group('/admin', function () {
    $this->group('/users', function () {
        /** @var \Slim\App $this */
        $this
            ->get('/', BaseController::action(UsersController::class, 'index'))
            ->setName('admin_users_list');

        /** @var \Slim\App $this */
        $this
			->map(['GET', 'POST'], '/edit/{user}', BaseController::action(UsersController::class, 'edit'))
            ->setName('admin_users_edit');
    });


    $this->group('/roles', function () {
        /** @var \Slim\App $this */
        $this
            ->get('/', BaseController::action(RolesController::class, 'index'))
            ->setName('admin_roles_list');

        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(RolesController::class, 'create'))
            ->setName('admin_roles_create');

        $this
            ->map(['GET', 'POST'], '/edit/{role}', BaseController::action(RolesController::class, 'edit'))
            ->setName('admin_roles_edit');

        $this
            ->post('/delete/{role}', BaseController::action(RolesController::class, 'delete'))
            ->setName('admin_roles_delete');

        $this
            ->get('/users/{role}', BaseController::action(RolesController::class, 'users'))
            ->setName('admin_roles_users');
    });
});
