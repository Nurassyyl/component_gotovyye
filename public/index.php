<?php 

if( !session_id() ) @session_start();
require "../vendor/autoload.php";

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/users', ['App\QueryBuilder','getAll']);
    $r->addRoute('GET', '/insert', ['App\QueryBuilder','insert']);
    $r->addRoute('POST', '/insertHandler', ['App\Controller\HomeController','insertHandler']);
    $r->addRoute('GET', '/register', ['App\Controller\HomeController','register']);
    $r->addRoute('POST', '/handlerRegister', ['App\Controller\HomeController','handlerRegister']);
    $r->addRoute('GET', '/login', ['App\Controller\HomeController','login']);
    $r->addRoute('POST', '/handlerLogin', ['App\Controller\HomeController','handlerLogin']);
    $r->addRoute('GET', '/data', ['App\Controller\HomeController','data']);
    $r->addRoute('GET', '/logOut', ['App\Controller\HomeController','logOut']);
    $r->addRoute('GET', '/update', ['App\QueryBuilder','update']);
    $r->addRoute('POST', '/handlerUpdate', ['App\QueryBuilder','handlerUpdate']);
    $r->addRoute('GET', '/isLogged', ['App\Controller\HomeController','isLogged']);
    $r->addRoute('GET', '/security', ['App\QueryBuilder','security']);
    $r->addRoute('GET', '/status', ['App\QueryBuilder','status']);
    $r->addRoute('GET', '/avatar', ['App\QueryBuilder','avatar']);
    $r->addRoute('POST', '/avatar_handler', ['App\QueryBuilder','avatar_handler']);
    $r->addRoute('GET', '/delete', ['App\QueryBuilder','delete']);
    $r->addRoute('POST', '/security_handler', ['App\Controller\HomeController','security_handler']);
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo 404;
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo 405;
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $controller = new $handler[0];
        call_user_func([$controller, $handler[1]], $vars);
        break;
}


?>