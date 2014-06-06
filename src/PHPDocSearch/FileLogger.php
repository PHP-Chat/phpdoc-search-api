<?php

namespace PHPDocSearch;

class FileLogger implements Logger
{
    /**
     * @var resource
     */
    private $fp;

    /**
     * Constructor
     *
     * @param string $filePath
     * @throws \RuntimeException
     */
    public function __construct($filePath)
    {
        if (!$this->fp = fopen($filePath, 'a')) {
            throw new \RuntimeException("Unable to open log file '$filePath' for writing");
        }
    }

    /**
     * Log a message
     *
     * @param string $message
     */
    public function log($message)
    {
        fwrite($this->fp, date('Y-m-d H:i:s') . ' | ' . $message . "\n");
    }

    /**
     * Log a warning
     *
     * @param string $message
     */
    public function warn($message)
    {
        fwrite($this->fp, date('Y-m-d H:i:s') . ' | WARN: ' . $message . "\n");
    }

    /**
     * Log an error
     *
     * @param string $message
     */
    public function error($message)
    {
        fwrite($this->fp, date('Y-m-d H:i:s') . ' | ERROR: ' . $message . "\n");
    }
}
