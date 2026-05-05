<?php
declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Controllers;

use Bastien\TerrariaWikiCommunity\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthController
 * 
 * Handles user authentication including login, registration, and logout.
 */
class AuthController extends BaseController
{
    /** @var User The user model instance. */
    private User $user;

    /**  * AuthController constructor.
     * 
     * Initializes the user model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }

    /**  * Displays the login page.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The rendered login page response.
     */
    public function showLogin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->view->render($response, 'login/login.php');
    }

    /**  * Displays the registration page.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The rendered registration page response.
     */
    public function showRegister(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->view->render($response, 'login/register.php');
    }

    /**  * Handles user login.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The response after processing the login.
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        try {
            $user = $this->user->findByUserName($data['username'] ?? '');
        } catch(\Exception) {
            $user = null;
        }

        if (!$user || !password_verify($data['password'] ?? '', $user->password)) {
            return $response->withHeader("Location", "/login?error=1")->withStatus(302);
        }

        $_SESSION['user'] = [
            'idUser' => $user->idUser,
            'email' => $user->email,
            'userName' => $user->userName,
            'profile_pic' => $user->profilePic
        ];

        return $response->withHeader("Location", "/")->withStatus(302);
    }

    /**  * Handles user registration.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The response after processing the registration.
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->user->create($data['email'], $data['username'], $data['password'], 'https://ui-avatars.com/api/?name=' . $data['username']);

        return $response->withHeader("Location", "/login")->withStatus(302);
    }

    /**  * Handles user logout.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The response after processing the logout.
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        session_destroy();
        return $response->withHeader("Location", "/login")->withStatus(302);
    }
}
