<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Model;

use Bastien\TerrariaWikiCommunity\Core\Database;


/**
 * Class Message
 * 
 * Represents a message in the Terraria Wiki Community application.
 */
class Message
{
    /**
     * Retrieves all messages for a specific chat.
     * 
     * @param int $chatId The unique identifier of the chat.
     * @return array An array of messages associated with the chat.
     */
    public static function allByChat(int $chatId): array
    {
        $stmt = Database::connection()->prepare("
            SELECT m.*, u.UserName, u.pfp, t.TimeStamp
            FROM Message m 
            JOIN Users u ON m.idUser = u.idUser
            JOIN Time t ON m.idTime = t.idTime
            WHERE t.idChat = :idChat 
            ORDER BY t.TimeStamp ASC
        ");
        $stmt->execute([':idChat' => $chatId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new message in the database.
     * 
     * @param int $userId The unique identifier of the user sending the message.
     * @param int $chatId The unique identifier of the chat.
     * @param string $content The content of the message.
     * @return bool True on success, false on failure.
     */
    public static function create(int $userId, int $chatId, string $content): bool
    {
        Database::connection()->prepare("
            INSERT INTO Time (idChat, TimeStamp) VALUES (:idChat, NOW())
        ")->execute([':idChat' => $chatId]);

        $idTime = Database::connection()->lastInsertId();

        $stmt = Database::connection()->prepare("
            INSERT INTO Message (content, idUser, idTime) 
            VALUES (:content, :idUser, :idTime)
        ");
        return $stmt->execute([
            ':content' => $content,
            ':idUser' => $userId,
            ':idTime' => $idTime
        ]);
    }
}
