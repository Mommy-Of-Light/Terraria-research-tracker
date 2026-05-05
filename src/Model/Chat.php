<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Model;

use Bastien\TerrariaWikiCommunity\Core\Database;
use PDO;

/**
 * Class Chat
 * 
 * Represents a chat in the Terraria Wiki Community application.
 */
class Chat
{
    /**
     * @var int The unique identifier for the chat.
     */
    public int $idChat;

    /**
     * @var string The name of the chat.
     */
    public string $chatName;

    /**
     * @var int The unique identifier of the user who created the chat.
     */
    public int $idUser;

    /**
     * @var array An array of messages associated with the chat.
     */
    public array $messages = [];

    /** 
     * Chat constructor.
     * 
     * @param int|null $idChat The unique identifier for the chat.
     * @param string $chatName The name of the chat.
     * @param int $idUser The unique identifier of the user who created the chat.
     */
    public function __construct(?int $idChat, string $chatName, int $idUser)
    {
        $this->idChat = $idChat ?? 0;
        $this->chatName = $chatName;
        $this->idUser = $idUser;
    }

    /**
     * Saves the chat to the database.
     * 
     * @return bool True on success, false on failure.
     */
    public function save(): bool
    {
        $db = Database::connection();

        if ($this->idChat > 0) {
            $stmt = $db->prepare("UPDATE Chat SET ChatName = :chatName, idCreator = :idUser WHERE idChat = :idChat");
            return $stmt->execute([
                ':chatName' => $this->chatName,
                ':idUser' => $this->idUser,
                ':idChat' => $this->idChat,
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO Chat (ChatName, idCreator) VALUES (:chatName, :idUser)");
            $success = $stmt->execute([
                ':chatName' => $this->chatName,
                ':idUser' => $this->idUser,
            ]);

            if ($success) {
                $this->idChat = (int) $db->lastInsertId();
            }

            return $success;
        }
    }

    /**
     * Retrieves all chats from the database.
     * 
     * @return array An array of Chat objects.
     */
    public static function all(): array
    {
        $stmt = Database::connection()->query("
        SELECT c.idChat, c.ChatName, c.idCreator, u.UserName
        FROM Chat c
        JOIN Users u ON c.idCreator = u.idUser
        ORDER BY c.idChat DESC
    ");
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($c) => new Chat((int) $c['idChat'], $c['ChatName'], (int) $c['idCreator']), $chats);
    }

    /**
     * Finds a chat by its unique identifier.
     * 
     * @param int $idChat The unique identifier of the chat.
     * @return Chat|null The Chat object if found, null otherwise.
     */
    public static function find(int $idChat): ?Chat
    {
        $stmt = Database::connection()->prepare("SELECT * FROM Chat WHERE idChat = :idChat");
        $stmt->execute([':idChat' => $idChat]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data)
            return null;

        return new Chat((int) $data['idChat'], $data['ChatName'], (int) $data['idCreator']);
    }
}
