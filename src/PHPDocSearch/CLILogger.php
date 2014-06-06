<?php

namespace PHPDocSearch;

class CLILogger implements Logger
{
    /**
     * Log a message
     *
     * @param string $message
     */
    public function log($message)
    {
        echo date('H:i:s') . ' | ' . $message . "\n";
    }

    /**
     * Log a warning
     *
     * @param string $message
     */
    public function warn($message)
    {
        echo date('H:i:s') . ' | WARN: ' . $message . "\n";
    }

    /**
     * Log an error
     *
     * @param string $message
     */
    public function error($message)
    {
        echo date('H:i:s') . ' | ERROR: ' . $message . "\n";
    }
}
