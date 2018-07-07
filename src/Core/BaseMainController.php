<?php
namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Slim\Views\Twig;
use \GameX\Core\Menu\Menu;
use \GameX\Core\Menu\MenuItem;
use \GameX\Core\Forms\Form;
use \GameX\Core\Exceptions\ValidationException;
use \GameX\Core\Exceptions\FormException;
use \Exception;

abstract class BaseMainController extends BaseController {
	/**
	 * @return string
	 */
	abstract protected function getActiveMenu();

	/**
	 * BaseController constructor.
	 * @param ContainerInterface $container
	 */
    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
		$this->initMenu();
    }

    /**
     * @param string $template
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function render($template, array $data = []) {
        /** @var Twig $view */
        $view = $this->getContainer('view');
        return $view->render($this->getContainer('response'), $template, $data);
    }

    /**
     * @param string $type
     * @param string $message
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function addFlashMessage($type, $message) {
        $this->getContainer('flash')->addMessage($type, $message);
    }

    protected function failRedirect(Exception $e, Form $form) {
        if ($e instanceof FormException) {
            $form->setError($e->getField(), $e->getMessage());
        } elseif ($e instanceof ValidationException) {
            $this->addFlashMessage('error', $e->getMessage());
        } else {
            $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
        }

        $form->saveValues();

        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer('log');
        $logger->error((string) $e);

        return $this->redirectTo($form->getAction());
    }

	protected function initMenu() {
		/** @var Twig $view */
		$view = $this->getContainer('view');

		$menu = new Menu();

		$menu
			->setActiveRoute($this->getActiveMenu())
			->add(new MenuItem('Index', 'index', [], null))
			->add(new MenuItem('Punishments', 'punishments', [], null));

		$modules = $this->getContainer('modules');
		/** @var \GameX\Core\Module\ModuleInterface $module */
		foreach ($modules as $module) {
			$items = $module->getMenuItems();
			foreach ($items as $item) {
				$menu->add($item);
			}
		}

		$view->getEnvironment()->addGlobal('menu', $menu);
	}
}
