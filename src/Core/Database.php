<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Core;

use PDO;

require_once __DIR__ . '/../../config/database.php';

/**
 * Class Database
 * 
 * Provides a singleton PDO database connection.
 */
class Database
{
    /**
     * Establishes and returns a singleton PDO database connection.
     * 
     * @return PDO The PDO database connection.
     */
    public static function connection(): PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';port=3306;dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            } catch (\Throwable $th) {
                die("Can't connect to database: " . $th->getMessage());
            }
        }
        return $pdo;
    }
}
