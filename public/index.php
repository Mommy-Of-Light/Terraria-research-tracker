<?php
date_default_timezone_set('Europe/Paris');

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

define('ROOT_PATH', __DIR__ . '/../');

$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

session_start();

$app = AppFactory::create();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(
    Slim\Exception\HttpNotFoundException::class,
    function ($request, $exception, $displayErrorDetails) {
            $response = new \Slim\Psr7\Response();
            // Correct views directory (was misspelled 'scr/Views')
            $view = new PhpRenderer(ROOT_PATH . 'views/');
            $view->setLayout("layout.php");
        return $view->render($response->withStatus(404), 'errors/404.php', [
            'withMenu' => false,
            'title' => 'Page non trouvée',
            'message' => $exception->getMessage(),
        ]);
    }
);

$errorMiddleware->setErrorHandler(
    Slim\Exception\HttpInternalServerErrorException::class,
    function ($request, $exception, $displayErrorDetails) {
        $response = new \Slim\Psr7\Response();
            $view = new PhpRenderer(ROOT_PATH . 'views/');
            $view->setLayout("layout.php");
        return $view->render($response->withStatus(500), 'errors/500.php', [
            'withMenu' => false,
            'title' => 'Erreur interne du serveur',
            'message' => $exception->getMessage(),
        ]);
    }
);

$errorMiddleware->setErrorHandler(
    Slim\Exception\HttpInternalServerErrorException::class,
    function ($request, $exception, $displayErrorDetails) {
        $response = new \Slim\Psr7\Response();
            $view = new PhpRenderer(ROOT_PATH . 'views/');
            $view->setLayout("layout.php");
        return $view->render($response->withStatus(500), 'errors/500.php', [
            'withMenu' => false,
            'title' => 'Erreur interne du serveur',
            'message' => $exception->getMessage(),
        ]);
    }
);

require __DIR__ . '/../routes/web.php';

$app->run();
