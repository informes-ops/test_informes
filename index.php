<?php
/**
 * Punto de entrada del área técnica — delega al controlador MVC.
 * @see app/Controllers/IndexController.php
 * @see funcionamiento.md §4.1
 */
require_once __DIR__ . '/app/bootstrap.php';

(new App\Controllers\IndexController())->dispatch();
