<?php

// TODO: make this better

spl_autoload_register(function($className) {
    if (strtolower(substr($className, 0, 12)) === 'phpdocsearch') {
        require __DIR__ . '/../src/' . $className . '.php';
    }
});
