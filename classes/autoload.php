<?php
/**
 * Fonction d'autoload pour charger automatiquement les classes
 */
spl_autoload_register(function($class_name) {
    $file = __DIR__ . '/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});
