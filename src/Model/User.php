<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Model;

use Bastien\TerrariaWikiCommunity\Core\Database;
use stdClass;

/**
 * Class User
 * 
 * Represents a user in the Terraria Wiki Community application.
 */
class User
{
    /**
     * @var \PDO Database connection handle.
     */
    private $db;

    /**
     * @var int|null The unique identifier for the user.
     */
    public int|null $idUser;

    /**
     * @var string|null The user's email address.
     */
    public string|null $email;

    /**
     * @var string|null The user's display username.
     */
    public string|null $userName;

    /**
     * @var string|null The user's hashed password.
     */
    public string|null $password;

    /**
     * @var string|null URL or path to the user's profile picture.
     */
    public string|null $profilePic;

    /**
     * @var array|null The user's player files.
     */
    public array|null $playerFiles;

    /** 
     * User constructor.
     * 
     * @param int|null $idUser The unique identifier for the user.
     * @param string|null $email The email address of the user.
     * @param string|null $userName The username of the user.
     * @param string|null $password The hashed password of the user.
     * @param string|null $profilePic The URL of the user's profile picture.
     */
    public function __construct(int|null $idUser = null, string|null $email = null, string|null $userName = null, string|null $password = null, string|null $profilePic = null, array|null $playerFiles = null)
    {
        $this->db = Database::connection();
        $this->idUser = $idUser;
        $this->email = $email;
        $this->userName = $userName;
        $this->password = $password;
        $this->profilePic = $profilePic;
        $this->playerFiles = $playerFiles;
    }

    /**
     * Retrieves all users from the database.
     * 
     * @return array An array of User objects.
     */
    public static function All(): array
    {
        $stmt = Database::connection()->query("SELECT * FROM Users");
        $users = $stmt->fetchAll();
        return array_map(fn($u) => new User($u['idUser'], $u['Email'], $u['UserName'], $u['Password'], $u['pfp'], User::getAllPlayerFiles($u['idUser'])), $users);
    }

    /**
     * Retrieves all player files for a specific user.
     * 
     * @param int $idUser The unique identifier for the user.
     * @return array An array of player file names.
     */
    public static function getAllPlayerFiles(int $idUser): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT fileName FROM Player_file WHERE idUser = :id"
        );

        $stmt->execute([':id' => $idUser]);

        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $rows ?: [];
    }

    /**
     * Saves player files for a specific user.
     * 
     * @param int $idUser The unique identifier for the user.
     * @param array $playerFiles An array of player file names.
     * @return bool True on success, false on failure.
     */
    public static function savePlayerFiles(int $idUser, array $playerFiles): bool
    {
        $dbFiles = self::getAllPlayerFiles($idUser);

        $newFiles = array_diff($playerFiles, $dbFiles);

        if (empty($newFiles)) {
            return true;
        }

        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            "INSERT INTO Player_file (idUser, fileName) VALUES (:id, :file)"
        );

        foreach ($newFiles as $file) {
            $stmt->execute([
                ':id' => $idUser,
                ':file' => $file
            ]);
        }

        return true;
    }

    /**
     * Deletes player files for a specific user.
     * 
     * @param int $idUser The unique identifier for the user.
     * @param array $files An array of player file names to delete.
     * @return bool True on success, false on failure.
     */
    public static function deletePlayerFiles(int $idUser, array $files): bool
    {
        if (empty($files)) {
            return true;
        }

        $pdo = Database::connection();

        $placeholders = implode(',', array_fill(0, count($files), '?'));

        $sql = "DELETE FROM Player_file 
            WHERE idUser = ? AND fileName IN ($placeholders)";

        $stmt = $pdo->prepare($sql);

        $params = array_merge([$idUser], $files);

        return $stmt->execute($params);
    }

    /**
     * Creates a new user in the database.
     * 
     * @param string $email The email address of the new user.
     * @param string $username The username of the new user.
     * @param string $password The plaintext password of the new user.
     * @return bool True on success, false on failure.
     */
    public function create(string $email, string $username, string $password, string $profilePic): bool
    {
        $stmt = $this->db->prepare("INSERT INTO Users (Email, UserName, Password, pfp) VALUES (:email, :username, :password, :pfp)");
        return $stmt->execute([
            ':email' => $email,
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':pfp' => $profilePic
        ]);
    }

    /**
     * Finds a user by their email address.
     * 
     * @param string $email The email address to search for.
     * @return User The user object if found.
     * @throws \Exception If the user does not exist.
     */
    public function findByEmail(string $email): User
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE Email = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch();

        if ($data) {
            return new User($data['idUser'], $data['Email'], $data['UserName'], $data['Password'], $data['pfp']);
        }

        throw new \Exception("The user doesn't exist");
    }

    /**
     * Finds a user by their unique identifier.
     * 
     * @param int $id The unique identifier of the user.
     * @return User|null The user object if found, null otherwise.
     */
    public function findById(int $id): ?User
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM Users WHERE idUser = :id");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            if ($data) {
                return new User($data['idUser'], $data['Email'], $data['UserName'], $data['Password'], $data['pfp']);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Finds a user by their username.
     * 
     * @param string $username The username to search for.
     * @return User The user object if found.
     * @throws \Exception If the user does not exist.
     */
    public function findByUserName(string $username): User
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE UserName = :username");
        $stmt->execute([':username' => $username]);
        $data = $stmt->fetch();

        if ($data) {
            return new User($data['idUser'], $data['Email'], $data['UserName'], $data['Password'], $data['pfp']);
        }

        throw new \Exception("The user doesn't exist");
    }

    /**
     * Updates the profile picture URL of the user.
     * 
     * @param int $idUser The unique identifier of the user.
     * @param string $url The new profile picture URL.
     * @return bool True on success, false on failure.
     */
    public function updateProfilePic(int $idUser, string $url): bool
    {
        $stmt = $this->db->prepare("UPDATE Users SET pfp = :url WHERE idUser = :id");
        return $stmt->execute([
            ':url' => $url,
            ':id' => $idUser
        ]);
    }

    /**
     * Deletes a user from the database.
     * 
     * @param int $idUser The unique identifier of the user to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteUser(int $idUser): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Users WHERE idUser = :id");
        return $stmt->execute([':id' => $idUser]);
    }
}
