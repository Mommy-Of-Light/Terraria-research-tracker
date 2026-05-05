<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Controllers;

use Bastien\TerrariaWikiCommunity\Model\Chat;
use Bastien\TerrariaWikiCommunity\Model\Message;
use Bastien\TerrariaWikiCommunity\Core\Database;
use Bastien\TerrariaWikiCommunity\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ChatController
 * 
 * Handles chat-related functionalities in the Terraria Wiki Community application.
 */
class ChatController extends BaseController
{
    /**
     * Displays the list of chats.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The rendered chat list response.
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        return $this->view->render($response, 'chat/list.php', [
            'chats' => Chat::all(),
            'users' => User::all()
        ]);
    }

    /**
     * Displays a specific chat and its messages.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments, including 'id' for the chat ID.
     * @return ResponseInterface The rendered chat response.
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }
        
        $id = $args['id'];
        $chat = Chat::find((int) $id);
        if (!$chat) {
            return $response->withHeader('Location', "/chats")->withStatus(302);
        }

        $messages = Message::allByChat($chat->idChat);
        return $this->view->render($response, 'chat/chat.php', [
            'chat' => $chat,
            'messages' => $messages
        ]);
    }

    /**
     * Handles sending a new message in a chat.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments, including 'id' for the chat ID.
     * @return ResponseInterface The response after processing the new message.
     */
    public function sendMessage(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!isset($_SESSION['user'])) {
            return $response->withStatus(403);
        }

        $chatId = (int) $args['id'];
        $data = $request->getParsedBody();
        $content = $data['message'] ?? '';
        if ($content) {
            Message::create((int) $_SESSION['user']['idUser'], $chatId, $content);
        }

        return $response->withHeader("Location", "/chat/$chatId")->withStatus(302);
    }

    /**
     * Fetches messages for a specific chat.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments, including 'id' for the chat ID.
     * @return ResponseInterface The JSON response containing the chat messages.
     */
    public function fetchMessages(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!isset($_SESSION['user'])) {
            return $response->withStatus(403);
        }

        $chatId = (int) $args['id'];

        $messages = Message::allByChat($chatId);

        $response->getBody()->write(json_encode($messages, JSON_PRETTY_PRINT));
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
    }

    /**
     * Displays the form to create a new chat.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The rendered new chat form response.
     */
    public function showNewChatForm($request, $response)
    {
        if (empty($_SESSION['user'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        return $this->view->render($response, 'chat/new.php');
    }

    /**
     * Handles the creation of a new chat.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The response after processing the new chat creation.
     */
    public function createChat($request, $response)
    {
        if (empty($_SESSION['user'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $data = $request->getParsedBody();
        $chatName = trim($data['chatName'] ?? '');
        if ($chatName === '') {
            return $response->withHeader('Location', '/chats/new')->withStatus(302);
        }

        $chat = new Chat(0, $chatName, $_SESSION['user']['idUser']);
        $chat->save();

        return $response->withHeader('Location', '/chats')->withStatus(302);
    }

    /**
     * Handles the deletion of a chat.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments, including 'id' for the chat ID.
     * @return ResponseInterface The response after processing the chat deletion.
     */
    public function deleteChat(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            return $response->withStatus(403);
        }

        $chatId = (int) $args['id'];

        $chat = Chat::find($chatId);
        if (!$chat || $chat->idUser !== $_SESSION['user']['idUser']) {
            return $response->withStatus(403);
        }

        $stmt = Database::connection()->prepare("
        DELETE FROM Chats WHERE idChat = :idChat
    ");
        $stmt->execute([':idChat' => $chatId]);

        return $response->withHeader('Location', '/chats')->withStatus(302);
    }

    /**
     * Fetches all chats.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The JSON response containing all chats.
     */
    public function fetchChats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!isset($_SESSION['user'])) {
            return $response->withStatus(403);
        }

        $chats = Chat::all();
        $users = User::all();

        $data = array_map(fn($chat) => [
            'idChat' => $chat->idChat,
            'chatName' => $chat->chatName,
            'idUser' => $chat->idUser,
            'UserName' => ($user = array_filter($users, fn($u) => $u->idUser === $chat->idUser)) ? reset($user)->userName : 'Unknown',
        ], $chats);

        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withStatus(200)->withHeader('Content-type', 'application/json');
    }

    /**
     * Checks if a chat exists by its ID.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments, including 'id' for the chat ID.
     * @return ResponseInterface The JSON response indicating if the chat exists.
     */
    public function chatExists(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $chatId = (int) $args['id'];

        $chat = Chat::find($chatId);

        $exists = $chat !== null;

        $response->getBody()->write(json_encode(['exists' => $exists]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Deletes a specific message in a chat.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments, including 'id' for the chat ID and 'msgId' for the message ID.
     * @return ResponseInterface The response after processing the message deletion.
     */
    public function deleteMessage(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!isset($_SESSION['user'])) {
            return $response->withStatus(403);
        }

        $messageId = (int) $args['msgId'];
        $chatId = (int) $args['id'];

        $stmt = Database::connection()->prepare("
        DELETE FROM Messages 
        WHERE idMessage = :idMessage
    ");
        $stmt->execute([
            ':idMessage' => $messageId
        ]);

        return $response->withHeader('Location', "/chat/$chatId")->withStatus(302);
    }

    /**
     * Edits a specific message in a chat.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments, including 'id' for the chat ID and 'msgId' for the message ID.
     * @return ResponseInterface The response after processing the message edit.
     */
    public function editMessage(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!isset($_SESSION['user'])) {
            return $response->withStatus(403);
        }

        $messageId = (int) $args['msgId'];
        $chatId = (int) $args['id'];
        $data = $request->getParsedBody();
        $newContent = trim($data['content'] ?? '');
        $idUser = (int) trim($data['msgOwner']);
        $dt = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
        $timestamp = $dt->format('Y-m-d H:i:s');

        if ($newContent === "") {
            return $response
                ->withHeader('Location', "/chat/$chatId")
                ->withStatus(302);
        }

        $stmt = Database::connection()->prepare("
            UPDATE Message 
            SET content = :content
            WHERE idMessage = :idMessage 
            AND (idUser = :user)
        ");

        $stmt->execute([
            ':idMessage' => $messageId,
            ':content' => $newContent,
            ':user' => $idUser
        ]);

        return $response->withHeader('Location', "/chat/$chatId")->withStatus(302);
    }
}
