<?php
date_default_timezone_set('Europe/Paris');

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$app = AppFactory::create();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function ($request, $exception, $displayErrorDetails) {
        $response = new \Slim\Psr7\Response();
        $view = new PhpRenderer(__DIR__ . '/../views');
        $view->setLayout("layout.php");
        return $view->render($response->withStatus(404), 'errors/404.php', [
            'withMenu' => true,
            'title' => 'Page non trouvée',
            'message' => $exception->getMessage(),
        ]);
    }
);

$errorMiddleware->setErrorHandler(
    Slim\Exception\HttpBadRequestException::class,
    function ($request, $exception, $displayErrorDetails) {
        $response = new \Slim\Psr7\Response();
        $view = new PhpRenderer(__DIR__ . '/../views');
        $view->setLayout("layout.php");
        return $view->render($response->withStatus(400), 'errors/400.php', [
            'withMenu' => false,
            'title' => 'Mauvaise requête',
            'message' => $exception->getMessage(),
        ]);
    }
);

$errorMiddleware->setErrorHandler(
    Slim\Exception\HttpInternalServerErrorException::class,
    function ($request, $exception, $displayErrorDetails) {
        $response = new \Slim\Psr7\Response();
        $view = new PhpRenderer(__DIR__ . '/../views');
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
