<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Slim\Http\Request;
use \Slim\Http\Response;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Constants\Routes\Admin\Servers as ServersRoutes;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Server;
use \GameX\Forms\Admin\ServersForm;
use \Slim\Exception\NotFoundException;
use \Exception;

class ServersController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return ServersRoutes::ROUTE_LIST;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(Request $request, Response $response, array $args = []) {
		$pagination = new Pagination(Server::get(), $request);
		return $this->render('admin/servers/index.twig', [
			'servers' => $pagination->getCollection(),
			'pagination' => $pagination,
		]);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return ResponseInterface
     * @throws NotFoundException
	 */
    public function viewAction(Request $request, Response $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
		return $this->render('admin/servers/view.twig', [
			'server' => $server,
		]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
	public function createAction(Request $request, Response $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);

		$form = new ServersForm($server);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ServersRoutes::ROUTE_VIEW, [
                'server' => $server->id,
            ]);
        }

		return $this->render('admin/servers/form.twig', [
			'form' => $form->getForm(),
            'create' => true,
		]);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
	public function editAction(Request $request, Response $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        
        $form = new ServersForm($server);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(ServersRoutes::ROUTE_VIEW, [
                'server' => $server->id,
            ]);
        }

		return $this->render('admin/servers/form.twig', [
			'form' => $form->getForm(),
            'create' => false,
		]);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
	public function deleteAction(Request $request, Response $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);

		try {
			$server->delete();
            $this->addSuccessMessage($this->getTranslate('admins_servers', 'removed'));
		} catch (Exception $e) {
			$this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
		}

		return $this->redirect(ServersRoutes::ROUTE_LIST);
	}
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
	public function tokenAction(Request $request, Response $response, array $args = []) {
        try {
            $server = $this->getServer($request, $response, $args);
            $server->token = $server->generateNewToken();
            $server->save();
            return $response->withJson([
                'success' => true,
                'token' => $server->token
            ]);
        } catch (Exception $e) {
            $this->getLogger()->exception($e);
            return $response->withJson([
                'success' => false,
                'error' => $this->getTranslate('labels', 'exception')
            ]);
        }
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Server
	 * @throws NotFoundException
	 */
	protected function getServer(Request $request, Response $response, array $args) {
	    if (array_key_exists('server', $args)) {
            $serverId = $args['server'];
        } else {
	        $serverId = $request->getParam('server');
        }
        
	    if (!$serverId) {
	        return new Server();
        }

		$server = Server::find($serverId);
		if (!$server) {
			throw new NotFoundException($request, $response);
		}

		return $server;
	}
}
