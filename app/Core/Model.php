<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Modelo base con acceso a PDO.
 */
abstract class Model
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }
}
