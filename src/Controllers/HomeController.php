<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Bastien\TerrariaWikiCommunity\Model\User;

/**
 * Class HomeController
 * 
 * Handles the home page display for logged-in users.
 */
class HomeController extends BaseController
{
    /**
     * Displays the home page for logged-in users.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The rendered home page response.
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $userFiles = User::getAllPlayerFiles($_SESSION['user']['idUser']) ?? [];

        return $this->view->render($response, 'home/home.php', [
            'playerFiles' => $userFiles
        ]);
    }
}
