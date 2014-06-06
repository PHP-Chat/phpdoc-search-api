<?php

namespace PHPDocSearch;

class BlackHoleLogger implements Logger
{
    /**
     * Log a message
     *
     * @param string $message
     */
    public function log($message) {}

    /**
     * Log a warning
     *
     * @param string $message
     */
    public function warn($message) {}

    /**
     * Log an error
     *
     * @param string $message
     */
    public function error($message) {}
}
