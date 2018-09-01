<?php
function redirectToInstall() {
    $url = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    $url = str_replace('\\', '/', $url);
    $baseUrl =  rtrim(dirname($url), '/');
    
    header('Location: ' . $baseUrl . '/install',true,302);
    die();
}

if (!is_file(__DIR__ . '/vendor/autoload.php')) {
    redirectToInstall();
}

require __DIR__ . '/vendor/autoload.php';

$container = new \Slim\Container([
	'settings' => [
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true,
	],
	'root' => __DIR__ . DIRECTORY_SEPARATOR
]);

$errorHandler = function (\Slim\Container $container) {
	return function (\Slim\Http\Request $request, \Slim\Http\Response $response, Exception $e) use ($container) {
        /** @var \Slim\Views\Twig $view */
        $view = $c->get('view');
	    if ($e instanceof \GameX\Core\Exceptions\NotAllowedException) {
            return $view->render($response->withStatus(403), 'errors/403.twig');
        } elseif ($e instanceof \Slim\Exception\NotFoundException) {
            return $view->render($response->withStatus(404), 'errors/404.twig');
        } elseif ($e instanceof \Slim\Exception\MethodNotAllowedException) {
            return $view->render($response->withStatus(405), 'errors/405.twig');
        } else {
            $container->get('log')->exception($e);
            return $view->render($response->withStatus(500), 'errors/500.twig');
        }
	};
};

$notFoundHandler = function (\Slim\Container $container) {
    return function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
        return $container['view']->render($response->withStatus(404), 'errors/404.twig');
    };
};

$container['errorHandler'] = $errorHandler;
$container['phpErrorHandler'] = $errorHandler;
$container['notFoundHandler'] = $notFoundHandler;

set_exception_handler(function (Exception $e) use ($container) {
    if ($e instanceof \GameX\Core\Configuration\Exceptions\ConfigNotFoundException) {
        redirectToInstall();
    } else {
        /** @var \Slim\Views\Twig $view */
        $view = $container->get('view');
        $container->get('log')->exception($e);
        return $view->render($container->get('response')->withStatus(500), 'errors/500.twig');
    }
});

$app = new \Slim\App($container);

include __DIR__ . '/src/dependencies.php';
include __DIR__ . '/src/middlewares.php';
include __DIR__ . '/src/routes/index.php';

//set_error_handler(function ($errno, $error, $file, $line) use ($container) {
//    $container->get('log')->error("#$errno: $error in $file:$line");
//}, E_ALL);

$app->run();
