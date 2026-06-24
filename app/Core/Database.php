<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton de conexión PDO a MySQL.
 */
final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require APP_ROOT . '/app/Config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['dbname'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], $config['options']);
        } catch (PDOException $e) {
            throw new RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /** Reinicia la instancia (útil en tests). */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
