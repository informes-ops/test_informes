<?php
/**
 * Carga scripts externos del formulario técnico en orden.
 */
declare(strict_types=1);

$scripts = require APP_ROOT . '/app/Config/formulario_scripts.php';
?>
<?php require APP_ROOT . '/app/Views/tecnicos/partials/formulario_config.php'; ?>
<?php foreach ($scripts as $src): ?>
<script src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>?v=20260626" defer></script>
<?php endforeach; ?>
