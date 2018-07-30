<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\ServersController;
use \GameX\Controllers\Admin\GroupsController;
use \GameX\Controllers\Admin\ReasonsController;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(ServersController::class, 'index'))
        ->setName('admin_servers_list')
        ->setArgument('permission', 'admin.servers');

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(ServersController::class, 'create'))
        ->setName('admin_servers_create')
        ->setArgument('permission', 'admin.servers');

    $this
        ->map(['GET', 'POST'], '/{server}/edit', BaseController::action(ServersController::class, 'edit'))
        ->setName('admin_servers_edit')
        ->setArgument('permission', 'admin.servers');

    $this
        ->post('/{server}/delete', BaseController::action(ServersController::class, 'delete'))
        ->setName('admin_servers_delete')
        ->setArgument('permission', 'admin.servers');

    $this->group('/{server}/groups', function () {
        /** @var \Slim\App $this */
        $this
            ->get('', BaseController::action(GroupsController::class, 'index'))
            ->setName('admin_servers_groups_list')
            ->setArgument('permission', 'admin.servers.groups');

        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(GroupsController::class, 'create'))
            ->setName('admin_servers_groups_create')
			->setArgument('permission', 'admin.servers.groups');

        $this
            ->map(['GET', 'POST'], '/{group}/edit', BaseController::action(GroupsController::class, 'edit'))
            ->setName('admin_servers_groups_edit')
			->setArgument('permission', 'admin.servers.groups');

        $this
            ->post('/{group}/delete', BaseController::action(GroupsController::class, 'delete'))
            ->setName('admin_servers_groups_delete')
			->setArgument('permission', 'admin.servers.groups');
    });
    
    $this->group('/{server}/reasons', function () {
        /** @var \Slim\App $this */
        $this
            ->get('', BaseController::action(ReasonsController::class, 'index'))
            ->setName('admin_servers_reasons_list')
            ->setArgument('permission', 'admin.servers.reasons');
        
        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(ReasonsController::class, 'create'))
            ->setName('admin_servers_reasons_create')
            ->setArgument('permission', 'admin.servers.reasons');
        
        $this
            ->map(['GET', 'POST'], '/{reason}/edit', BaseController::action(ReasonsController::class, 'edit'))
            ->setName('admin_servers_reasons_edit')
            ->setArgument('permission', 'admin.servers.reasons');
        
        $this
            ->post('/{reason}/delete', BaseController::action(ReasonsController::class, 'delete'))
            ->setName('admin_servers_reasons_delete')
            ->setArgument('permission', 'admin.servers.reasons');
    });
};
