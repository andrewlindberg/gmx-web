<?php
namespace GameX\Controllers\Admin;


use \GameX\Core\BaseAdminController;
use \GameX\Forms\Admin\PermissionsForm;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use \Cartalyst\Sentinel\Roles\RoleInterface;
use \Slim\Exception\NotFoundException;

class PermissionsController extends BaseAdminController {
    
    /** @var  RoleRepositoryInterface */
    protected $roleRepository;
    
    /**
     * @return string
     */
    protected function getActiveMenu() {
        return 'admin_roles_list';
    }
    
    /**
     * Init
     */
    public function init() {
        $this->roleRepository = $this->getContainer('auth')->getRoleRepository();
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \GameX\Core\Exceptions\RedirectException
     */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args = []) {
        $role = $this->getRole($request, $response, $args);
        $form = new PermissionsForm($role);
        if ($this->processForm($request, $form)) {
            $this->addSuccessMessage($this->getTranslate('labels', 'saved'));
            return $this->redirect('admin_role_permissions', [
                'role' => $role->id,
            ]);
        }
        
        return $this->render('admin/roles/permissions/index.twig', [
            'form' => $form->getForm(),
            'list' => $form->getList(),
            'servers' => $form->getServers()
        ]);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return RoleInterface
     * @throws NotFoundException
     */
    protected function getRole(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        if (!array_key_exists('role', $args)) {
            throw new NotFoundException($request, $response);
        }
        
        $role = $this->roleRepository->findById($args['role']);
        if (!$role) {
            throw new NotFoundException($request, $response);
        }
        
        return $role;
    }
}
