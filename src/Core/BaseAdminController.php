<?php
namespace GameX\Core;

use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;

abstract class BaseAdminController extends BaseMainController {

	protected function initMenu() {
		/** @var \GameX\Core\Lang\Language $lang */
		$lang = $this->getContainer('lang');

		$menu = new Menu();

		$menu
			->setActiveRoute($this->getActiveMenu())
			->add(new MenuItem(
			    $lang->format('admin_menu','preferences'),
                'admin_preferences_index',
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','users'),
                'admin_users_list',
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','roles'),
                'admin_roles_list',
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','servers'),
                'admin_servers_list',
                []
            ))
			->add(new MenuItem(
			    $lang->format('admin_menu','players'),
                'admin_players_list',
                []
            ));

		// TODO: Make refactoring for this
//        /** @var Twig $view */
//        $view = $this->getContainer('view');
//		$modules = $this->getContainer('modules');
//		/** @var \GameX\Core\Module\ModuleInterface $module */
//		foreach ($modules as $module) {
//			$items = $module->getAdminMenuItems();
//			foreach ($items as $item) {
//				$menu->add($item);
//			}
//		}
//
//		$view->getEnvironment()->addGlobal('menu', $menu);
	}
}
