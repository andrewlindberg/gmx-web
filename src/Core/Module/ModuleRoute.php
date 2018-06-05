<?php
namespace GameX\Core\Module;

class ModuleRoute {

	/**
	 * @var string
	 */
	protected $route;

	/**
	 * @var string
	 */
	protected $controller;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string|null
	 */
	protected $permission;

	/**
	 * ModuleRoute constructor.
	 * @param string $route
	 * @param string $controller
	 * @param string $action
	 * @param string $name
	 * @param string|null $permission
	 */
	public function __construct($route, $controller, $action, $name, $permission = null) {
		$this->route = $route;
		$this->controller = $controller;
		$this->action = $action;
		$this->name = $name;
		$this->permission = $permission;
	}

	/**
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * @return string
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPermission() {
		return $this->permission;
	}
}
