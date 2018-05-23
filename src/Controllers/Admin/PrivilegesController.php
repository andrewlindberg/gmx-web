<?php
namespace GameX\Controllers\Admin;

use \GameX\Models\Player;
use \GameX\Models\Privilege;
use \GameX\Models\Group;
use \GameX\Core\BaseController;
use \GameX\Core\Pagination\Pagination;
use GameX\Models\Server;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Exception\NotFoundException;
use \GameX\Core\Forms\Form;
use \GameX\Core\AccessFlags\Helper;
use \Exception;

class PrivilegesController extends BaseController {

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return ResponseInterface
	 */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $player = $this->getPlayer($request, $response, $args);
		$pagination = new Pagination($player->privileges()->get(), $request);
		return $this->render('admin/players/privileges/index.twig', [
            'player' => $player,
			'privileges' => $pagination->getCollection(),
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
        $player = $this->getPlayer($request, $response, $args);
        $privilege = $this->getPrivilege($request, $response, $args);
        $form = $this
            ->getForm($privilege)
            ->setAction((string)$request->getUri())
            ->processRequest($request);
        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $privilege->player_id = $player->id;
                    $privilege->group_id = $form->getValue('group');
                    $privilege->prefix = $form->getValue('prefix');
                    $privilege->expired_at = \DateTime::createFromFormat('Y-m-d', $form->getValue('expired'));
                    $privilege->active = (bool)$form->getValue('active') ? 1 : 0;
                    $privilege->save();
                    return $this->redirect('admin_players_privileges_list', ['player' => $player->id]);
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/players/privileges/form.twig', [
            'player' => $player,
            'form' => $form,
            'create' => true,
            'servers' => $this->getServers()
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
        $group = $this->getGroup($request, $response, $args);
        $form = $this
            ->getForm($group)
            ->setAction((string)$request->getUri())
            ->processRequest($request);
        if ($form->getIsSubmitted()) {
            if (!$form->getIsValid()) {
                return $this->redirectTo($form->getAction());
            } else {
                try {
                    $group->title = $form->getValue('title');
                    $group->flags = Helper::readFlags($form->getValue('flags'));
                    $server->save();
                    return $this->redirect('admin_servers_groups_list', ['server' => $server->id]);
                } catch (Exception $e) {
                    return $this->failRedirect($e, $form);
                }
            }
        }

        return $this->render('admin/servers/groups/form.twig', [
            'server' => $server,
            'form' => $form,
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
        $group = $this->getGroup($request, $response, $args);

        try {
            $group->delete();
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Something wrong. Please Try again later.');
            /** @var \Monolog\Logger $logger */
            $logger = $this->getContainer('log');
            $logger->error((string) $e);
        }

        return $this->redirect('admin_servers_groups_list', ['server' => $server->id]);
    }

    public function groupsAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        if (!array_key_exists('server', $_GET)) {
            throw new NotFoundException($request, $response);
        }
        $server = Server::find($_GET['server']);
        if (!$server) {
            throw new NotFoundException($request, $response);
        }

        $groups = [];
        /** @var Group $group */
        foreach ($server->groups as $group) {
            $groups[$group->id] =$group->title;
        }

        return $response->withJson([
            'groups' => $groups,
        ]);
    }

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args
	 * @return Player
	 * @throws NotFoundException
	 */
	protected function getPlayer(ServerRequestInterface $request, ResponseInterface $response, array $args) {
	    if (!array_key_exists('player', $args)) {
	        return new Player();
        }

		$player = Player::find($args['player']);
		if (!$player) {
			throw new NotFoundException($request, $response);
		}

		return $player;
	}

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return Privilege
     * @throws NotFoundException
     */
	protected function getPrivilege(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('privilege', $args)) {
            return new Privilege();
        }

        $privilege = Privilege::find($args['privilege']);
        if (!$privilege) {
            throw new NotFoundException($request, $response);
        }

        return $privilege;
    }

    /**
     * @param Privilege $privilege
     * @return Form
     */
    protected function getForm(Privilege $privilege) {
        /** @var Form $form */
        $form = $this->getContainer('form')->createForm('admin_server_group');
        $form
            ->add('group', $privilege->group_id, [
                'type' => 'select',
                'id' => 'input_player_group',
                'title' => 'Group',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'integer'])
            ->add('prefix', $privilege->prefix, [
                'type' => 'text',
                'title' => 'Prefix',
                'error' => '',
                'required' => false,
                'attributes' => [],
            ], ['trim'])
            ->add('expired', $privilege->expired_at, [
                'type' => 'date',
                'title' => 'Expired',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'date'])
            ->add('active', $privilege->active, [
                'type' => 'checkbox',
                'title' => 'Active',
                'error' => 'Required',
                'required' => true,
                'attributes' => [],
            ], ['required', 'bool']);

        return $form;
    }

    protected function getServers() {
        return Server::all();
    }
}
