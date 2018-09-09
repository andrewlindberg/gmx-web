<?php
namespace GameX\Middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GameX\Core\Exceptions\ApiException;
use \GameX\Models\Server;

class ApiTokenMiddleware {
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        try {
            if (!preg_match('/Token\s+(?P<token>.+?)$/i', $request->getHeaderLine('Authorization'), $matches)) {
                throw new ApiException('Token required');
            }

            /** @var \GameX\Models\Server $server */
            $server = Server::where('token', $matches['token'])->first();
            if (!$server || !$server->active) {
                throw new ApiException('Invalid token');
            }
            return $next($request->withAttribute('server', $server), $response);
        } catch (ApiException $e) {
            return $response
                ->withStatus(403)
                ->withJson([
                    'success' => false,
                    'error' => [
                        'code' => ApiException::ERROR_INVALID_TOKEN,
                        'message' => $e->getMessage(),
                    ],
                ]);
        }
    }
}
