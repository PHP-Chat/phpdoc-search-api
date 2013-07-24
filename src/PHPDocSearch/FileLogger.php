<?php

namespace PHPDocSearch;

class FileLogger implements Logger
{
    private $fp;

    public function __construct($filePath)
    {
        if (!$this->fp = fopen($filePath, 'a')) {
            throw new \RuntimeException("Unable to open log file '$filePath' for writing");
        }
    }

    public function log($message)
    {
        fwrite($this->fp, date('Y-m-d H:i:s') . ' | ' . $message . "\n");
    }

    public function warn($message)
    {
        fwrite($this->fp, date('Y-m-d H:i:s') . ' | WARN: ' . $message . "\n");
    }

    public function error($message)
    {
        fwrite($this->fp, date('Y-m-d H:i:s') . ' | ERROR: ' . $message . "\n");
    }
}
