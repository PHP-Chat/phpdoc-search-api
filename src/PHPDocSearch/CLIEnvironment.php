<?php

namespace PHPDocSearch;

class CLIEnvironment extends Environment
{
    private $argv;

    private $args;

    private $startTime;

    public function __construct($applicationBaseDir, array $argv)
    {
        $this->baseDir = $applicationBaseDir;
        $this->argv = $argv;
        $this->startTime = new \DateTime('now');
    }

    private function parseArgv()
    {
        $this->args = [];
        $current = null;

        for ($i = 1, $l = count($this->argv); $i < $l; $i++) {
            if (substr($this->argv[$i], 0, 2) === '--') {
                $name = strtolower(substr($this->argv[$i], 2));
                $this->args[$name] = true;
                $current = &$this->args[$name];
            } else if ($current === true) {
                $current = $this->argv[$i];
            }
        }
    }

    public function hasArg($name)
    {
        if (!isset($this->args)) {
            $this->parseArgv();
        }

        return isset($this->args[strtolower($name)]);
    }

    public function getArg($name, $defaultValue = null)
    {
        if (!isset($this->args)) {
            $this->parseArgv();
        }

        if (isset($this->args[$name = strtolower($name)])) {
            return $this->args[$name];
        }

        return $defaultValue;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }
}
