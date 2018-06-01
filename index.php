<?php
require __DIR__ . '/vendor/autoload.php';

$container = new \Slim\Container([
	'settings' => [
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true,
	],
	'root' => __DIR__ . DIRECTORY_SEPARATOR
]);

$container['errorHandler'] = function ($c) {
	return function ($request, $response, $exception) use ($c) {
		return $c['response']->withStatus(500)
			->withHeader('Content-Type', 'text/html')
			->write('Something went wrong!');
	};
};

$container['config'] = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

$app = new \Slim\App($container);

include __DIR__ . '/src/dependencies.php';
include __DIR__ . '/src/middlewares.php';
include __DIR__ . '/src/routes/index.php';

$app->run();
