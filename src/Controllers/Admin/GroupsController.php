<?php

namespace GameX\Controllers\Admin;

use \GameX\Models\Server;
use \GameX\Models\Group;
use \GameX\Core\BaseAdminController;
use \GameX\Constants\Admin\GroupsConstants;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Core\Pagination\Pagination;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Forms\Admin\GroupsForm;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Exceptions\RedirectException;
use \Exception;

class GroupsController extends BaseAdminController
{
    
    /**
     * @return string
     */
    protected function getActiveMenu()
    {
        return ServersConstants::ROUTE_LIST;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);
        $pagination = new Pagination($server->groups()->get(), $request);
        return $this->render('admin/servers/groups/index.twig', [
            'server' => $server,
            'groups' => $pagination->getCollection(),
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);
        $group = $this->getGroup($request, $response, $args, $server);
        
        $form = new GroupsForm($group);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(GroupsConstants::ROUTE_EDIT, [
                'server' => $server->id,
                'group' => $group->id
            ]);
        }
        
        return $this->render('admin/servers/groups/form.twig', [
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
     * @throws NotFoundException
     * @throws RedirectException
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);
        $group = $this->getGroup($request, $response, $args, $server);
        
        $form = new GroupsForm($group);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect(GroupsConstants::ROUTE_EDIT, [
                'server' => $server->id,
                'group' => $group->id
            ]);
        }
        
        return $this->render('admin/servers/groups/form.twig', [
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
     * @throws NotFoundException
     */
    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $server = $this->getServer($request, $response, $args);
        $group = $this->getGroup($request, $response, $args, $server);
        
        try {
            $group->delete();
            $this->addSuccessMessage($this->getTranslate('labels', 'removed'));
        } catch (Exception $e) {
            $this->addErrorMessage($this->getTranslate('labels', 'exception'));
            $this->getLogger()->exception($e);
        }
        
        return $this->redirect(GroupsConstants::ROUTE_LIST, ['server' => $server->id]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return Server
     * @throws NotFoundException
     */
    protected function getServer(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
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
     * @return Group
     * @throws NotFoundException
     */
    protected function getGroup(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
        Server $server
    ) {
        if (!array_key_exists('group', $args)) {
            $group = new Group();
            $group->server_id = $server->id;
        } else {
            $group = Group::find($args['group']);
        }
        
        if (!$group) {
            throw new NotFoundException($request, $response);
        }
        
        
        return $group;
    }
}
