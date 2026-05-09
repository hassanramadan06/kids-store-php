<?php
/**
 * PDO database wrapper.
 *
 *  - Singleton connection
 *  - Always uses prepared statements (defense vs. SQL injection)
 *  - utf8mb4 encoding to safely store Arabic text and emojis
 *  - Throws exceptions on errors so we can surface them in dev mode
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                if (ini_get('display_errors')) {
                    die('DB connection failed: ' . htmlspecialchars($e->getMessage()));
                }
                die('Service temporarily unavailable. Please try again later.');
            }
        }
        return self::$pdo;
    }

    /** Run a prepared query and return the statement. */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    /** Fetch one row (or null). */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row ?: null;
    }

    /** Fetch all rows. */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** Scalar value of the first column of the first row. */
    public static function scalar(string $sql, array $params = [])
    {
        $st = self::run($sql, $params);
        $val = $st->fetchColumn();
        return $val === false ? null : $val;
    }

    public static function lastId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }
}
