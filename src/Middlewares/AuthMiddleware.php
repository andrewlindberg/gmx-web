<?php

namespace GameX\Middlewares;

use \Psr\Container\ContainerInterface;
use \Cartalyst\Sentinel\Sentinel;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Auth\Models\UserModel;
use \GameX\Core\Exceptions\NotAllowedException;

class AuthMiddleware
{
    
    /**
     * @var Sentinel
     */
    public $auth;
    
    /**
     * AuthMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->auth = $container->get('auth');
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return mixed
     * @throws NotAllowedException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /** @var UserModel $user */
        $user = $this->auth->getUser();
        
        if ($user && $user->status === UserModel::STATUS_BANNED) {
            return $response->withStatus(403);
        }
        return $next($request->withAttribute('user', $user), $response);
    }
}
