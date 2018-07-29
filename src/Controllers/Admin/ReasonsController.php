<?php
namespace GameX\Controllers\Admin;

use \GameX\Core\BaseAdminController;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Pagination\Pagination;
use \GameX\Models\Reason;
use \GameX\Models\Server;
use \GameX\Forms\Admin\ReasonsForm;
use \Slim\Exception\NotFoundException;
use \Exception;

class ReasonsController extends BaseAdminController {

	/**
	 * @return string
	 */
	protected function getActiveMenu() {
		return 'admin_servers_list';
	}
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $pagination = new Pagination($server->reasons()->get(), $request);
        return $this->render('admin/servers/reasons/index.twig', [
            'server' => $server,
            'reasons' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $group = $this->getReason($request, $response, $args, $server);
        
        $form = new ReasonsForm($group);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_servers_groups_edit', [
                'server' => $server->id,
                'group' => $group->id
            ]);
        }
        
        return $this->render('admin/servers/reasons/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => true,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $group = $this->getReason($request, $response, $args, $server);
        
        $form = new GroupForm($group);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_servers_groups_edit', [
                'server' => $server->id,
                'group' => $group->id
            ]);
        }
        
        return $this->render('admin/servers/reasons/form.twig', [
            'server' => $server,
            'form' => $form->getForm(),
            'create' => false,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $server = $this->getServer($request, $response, $args);
        $group = $this->getReason($request, $response, $args, $server);
        
        try {
            $group->delete();
            $this->addSuccessMessage($this->getTranslate('admins_players', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            /** @var \Monolog\Logger $logger */
            $logger = $this->getContainer('log');
            $logger->error((string) $e);
        }
        
        return $this->redirect('admin_servers_groups_list', ['server' => $server->id]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('server', $args)) {
            return new Server();
        }
        
        $server = Server::find($args['server']);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }
        
        return $server;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @param Server $server
     * @return Reason
     * @throws NotFoundException
     */
    protected function getReason(ServerRequestInterface $request, ResponseInterface $response, array $args, Server $server) {
        if (!array_key_exists('reason', $args)) {
            $reason = new Reason();
            $reason->server_id = $server->id;
        } else {
            $reason = Reason::find($args['reason']);
        }
    
        if (!$reason) {
            throw new NotFoundException($request, $response);
        }
    
    
        return $reason;
    }
}
