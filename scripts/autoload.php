<?php

// TODO: make this better

spl_autoload_register(function($className) {
    static $path;

    if (!isset($path)) {
        $path = realpath(__DIR__ . '/../src/');
    }

    if (strtolower(substr($className, 0, 12)) === 'phpdocsearch') {
        /** @noinspection PhpIncludeInspection */
        require $path . '/' . strtr($className, '\\', '/') . '.php';
    }
});
