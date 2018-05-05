<?php
$app->group('', function() {
    /** @var \Slim\App $this */
    $controller = new \GameX\Controllers\IndexController($this);

    $this->get('/', $controller->action('index'))->setName('index');
    $this->map(['GET', 'POST'], '/register', $controller->action('register'))->setName('register');
    $this->get('/login', $controller->action('login'))->setName('login');
});
