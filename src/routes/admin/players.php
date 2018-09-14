<?php
use \GameX\Core\BaseController;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\PrivilegesConstants;
use \GameX\Controllers\Admin\PlayersController;
use \GameX\Controllers\Admin\PrivilegesController;
use \GameX\Core\Auth\Permissions;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(PlayersController::class, 'index'))
        ->setName(PlayersConstants::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_LIST));

	$this
		->map(['GET', 'POST'], '/create', BaseController::action(PlayersController::class, 'create'))
		->setName(PlayersConstants::ROUTE_CREATE)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_CREATE));

    $this
        ->map(['GET', 'POST'], '/{player}/edit', BaseController::action(PlayersController::class, 'edit'))
        ->setName(PlayersConstants::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_EDIT));

	$this
		->post('/{player}/delete', BaseController::action(PlayersController::class, 'delete'))
		->setName(PlayersConstants::ROUTE_DELETE)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'player', Permissions::ACCESS_DELETE));

	// TODO: Check permissions
    $this->group('/{player}/privileges', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', BaseController::action(PrivilegesController::class, 'index'))
            ->setName(PrivilegesConstants::ROUTE_LIST)
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'privilege', Permissions::ACCESS_CREATE));

        $this
            ->map(['GET', 'POST'], '/create/{server}', BaseController::action(PrivilegesController::class, 'create'))
            ->setName(PrivilegesConstants::ROUTE_CREATE);

        $this
            ->map(['GET', 'POST'], '/{privilege}/edit', BaseController::action(PrivilegesController::class, 'edit'))
            ->setName(PrivilegesConstants::ROUTE_EDIT);

        $this
            ->post('/{privilege}/delete', BaseController::action(PrivilegesController::class, 'delete'))
            ->setName(PrivilegesConstants::ROUTE_DELETE);
    });
};
