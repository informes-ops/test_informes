<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\SchemaHelper;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Instala o verifica la base de datos a partir del dump de producción.
 */
final class DatabaseInstaller
{
    private const TABLAS_ESPERADAS = [
        'borradores_servicio', 'clientes_catalogo', 'contenedores_catalogo',
        'cotizaciones_catalogo', 'generadores_catalogo', 'informes',
        'inspecciones_preliminares', 'maquinas_catalogo', 'modelos_genset_catalogo',
        'modelos_reefer_catalogo', 'odoo_servicios_catalogo',
        'opciones_tecnicas_personalizadas', 'opciones_tecnicas_por_trabajo',
        'panel_eventos', 'repuestos_catalogo', 'repuestos_genset_catalogo',
        'repuestos_reefer_catalogo', 'salidas_tecnicas', 'salidas_tecnicas_materiales',
        'tecnicos', 'tg_items', 'tg_sesion', 'trabajos_realizados', 'zgroup_config',
    ];

    /** @param array<string, mixed> $config */
    public function __construct(private array $config) {}

    public static function fromConfigFile(): self
    {
        return new self(require APP_ROOT . '/app/Config/database.php');
    }

    /** @return array{ok: bool, message: string, details?: array<string, mixed>} */
    public function instalarSiVacia(?string $dumpPath = null, bool $forzar = false): array
    {
        $dumpPath = $dumpPath ?? APP_ROOT . '/zgroupin_zgroupinformes.sql';
        if (!is_file($dumpPath)) {
            throw new RuntimeException("No se encontró el dump SQL: $dumpPath");
        }

        $this->asegurarBaseDeDatos();

        $pdo = $this->conectar();
        $estado = $this->estadoBaseDeDatos($pdo);

        if (!$forzar && !$estado['vacia']) {
            SchemaHelper::asegurarEsquemaCompleto($pdo);
            return [
                'ok' => true,
                'message' => 'La base de datos ya contiene datos; no se importó el dump.',
                'details' => $estado,
            ];
        }

        if ($forzar && !$estado['vacia']) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            foreach ($estado['tablas'] as $tabla) {
                $pdo->exec("DROP TABLE IF EXISTS `$tabla`");
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->importarDump($dumpPath);
        $pdo = $this->conectar();
        SchemaHelper::asegurarEsquemaCompleto($pdo);

        $estadoFinal = $this->estadoBaseDeDatos($pdo);
        return [
            'ok' => true,
            'message' => 'Base de datos importada correctamente.',
            'details' => $estadoFinal,
        ];
    }

    /** @return array{ok: bool, conectado: bool, vacia: bool, tablas: list<string>, conteos: array<string, int>} */
    public function verificar(): array
    {
        try {
            $pdo = $this->conectar();
            $estado = $this->estadoBaseDeDatos($pdo);
            SchemaHelper::asegurarEsquemaCompleto($pdo);
            return ['ok' => true, 'conectado' => true] + $estado;
        } catch (PDOException $e) {
            return [
                'ok' => false,
                'conectado' => false,
                'vacia' => true,
                'tablas' => [],
                'conteos' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    private function asegurarBaseDeDatos(): void
    {
        $pdo = $this->conectarSinBase();
        $db = $this->config['dbname'];
        $charset = $this->config['charset'] ?? 'utf8mb4';
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset COLLATE {$charset}_unicode_ci");
    }

    private function conectarSinBase(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;charset=%s',
            $this->config['host'],
            $this->config['charset'] ?? 'utf8mb4'
        );
        return new PDO(
            $dsn,
            $this->config['user'],
            $this->config['pass'],
            $this->config['options'] ?? []
        );
    }

    private function conectar(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['dbname'],
            $this->config['charset'] ?? 'utf8mb4'
        );
        return new PDO(
            $dsn,
            $this->config['user'],
            $this->config['pass'],
            $this->config['options'] ?? []
        );
    }

    /** @return array{vacia: bool, tablas: list<string>, conteos: array<string, int>} */
    private function estadoBaseDeDatos(PDO $pdo): array
    {
        $tablas = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $conteos = [];
        $totalFilas = 0;

        foreach ($tablas as $tabla) {
            $count = (int)$pdo->query("SELECT COUNT(*) FROM `$tabla`")->fetchColumn();
            $conteos[$tabla] = $count;
            $totalFilas += $count;
        }

        $faltantes = array_values(array_diff(self::TABLAS_ESPERADAS, $tablas));

        return [
            'vacia' => $tablas === [] || $totalFilas === 0,
            'tablas' => array_values($tablas),
            'tablas_faltantes' => $faltantes,
            'conteos' => $conteos,
            'total_filas' => $totalFilas,
        ];
    }

    private function importarDump(string $dumpPath): void
    {
        $host = escapeshellarg((string)$this->config['host']);
        $user = escapeshellarg((string)$this->config['user']);
        $pass = escapeshellarg((string)$this->config['pass']);
        $db = escapeshellarg((string)$this->config['dbname']);
        $file = escapeshellarg($dumpPath);

        foreach (['mariadb', 'mysql'] as $bin) {
            $path = trim((string)shell_exec("command -v $bin 2>/dev/null"));
            if ($path === '') {
                continue;
            }
            $cmd = "$path -h$host -u$user -p$pass $db < $file 2>&1";
            exec($cmd, $output, $code);
            if ($code === 0) {
                return;
            }
            throw new RuntimeException("Error importando con $bin: " . implode("\n", $output));
        }

        throw new RuntimeException(
            'No se encontró el cliente mysql/mariadb. Instale MariaDB: sudo apt install mariadb-client mariadb-server'
        );
    }
}
