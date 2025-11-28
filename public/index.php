<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// 1. On charge l'autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. On instancie l'application
$app = AppFactory::create();

// 3. Routing
require_once '../config/web-routes.php';

// 4. On lance l'application
$app->run();