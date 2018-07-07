<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \GameX\Core\Lang\I18n;
use \GameX\Core\Forms\Form;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use \Exception;

abstract class BaseController {
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var I18n|null
     */
    protected $translate = null;

	/**
	 * BaseController constructor.
	 * @param ContainerInterface $container
	 */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->init();
    }

    /**
     * Init method
     */
    protected function init() {}

    /**
     * @param $container
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getContainer($container) {
        return $this->container->get($container);
    }

	/**
	 * @param string $key
	 * @param string|null $element
	 * @param mixed $default
	 * @return mixed
	 */
    public function getConfig($key, $element = null, $default = null) {
    	$config = $this->getContainer('config');
    	if (!array_key_exists($key, $config)) {
    		return $default;
		}
		if ($element == null) {
    		return $config[$key];
		}

		return array_key_exists($element, $config[$key])
			? $config[$key][$element]
			: $default;
	}

	/**
	 * @param $section
	 * @param $key
	 * @param array $args
	 * @return string
	 */
	public function getTranslate($section, $key, ...$args) {
        if ($this->translate === null) {
            $this->translate = $this->getContainer('lang');
        }
        return $this->translate->format($section, $key, $args);
    }

	/**
	 * @param string $path
	 * @param array $data
	 * @param array $queryParams
	 * @param bool $external
	 * @return string
	 */
	public function pathFor($path, array $data = [], array $queryParams = [], $external = false) {
		$link = $this->getContainer('router')->pathFor($path, $data, $queryParams);
		if (!$external) {
			return $link;
		}

		return (string)$this->getContainer('request')
			->getUri()
			->withPath($link);
	}

	/**
	 * @param $path
	 * @param array $data
	 * @param array $queryParams
	 * @param null $status
	 * @return ResponseInterface
	 */
	protected function redirect($path, array $data = [], array $queryParams = [], $status = null) {
		return $this->redirectTo(
			$this->pathFor($path, $data, $queryParams),
			$status
		);
	}

	/**
	 * @param string $path
	 * @param null $status
	 * @return ResponseInterface
	 */
	protected function redirectTo($path, $status = null) {
		return $this->getContainer('response')->withRedirect($path, $status);
	}

	/**
	 * @param string $controller
	 * @param string $action
	 * @return string
	 */
	public static function action($controller, $action) {
		return $controller . ':' . $action . 'Action';
	}
}
