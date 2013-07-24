<?php

namespace PHPDocSearch;

class CLILogger implements Logger
{
    public function log($message)
    {
        echo date('H:i:s') . ' | ' . $message . "\n";
    }

    public function warn($message)
    {
        echo date('H:i:s') . ' | WARN: ' . $message . "\n";
    }

    public function error($message)
    {
        echo date('H:i:s') . ' | ERROR: ' . $message . "\n";
    }
}
