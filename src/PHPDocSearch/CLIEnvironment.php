<?php

namespace PHPDocSearch;

class CLIEnvironment extends Environment
{
    /**
     * @var string[]
     */
    private $args = [];

    /**
     * @var \DateTime
     */
    private $startTime;

    /**
     * Constructor
     *
     * @param string $baseDir
     * @param Config $config
     * @param string[] $argv
     * @throws \RuntimeException
     */
    public function __construct($baseDir, Config $config, array $argv)
    {
        parent::__construct($baseDir, $config);
        $this->parseArgv($argv);

        $this->startTime = new \DateTime('now');
    }

    /**
     * Parse an argv array into a key-value store
     *
     * @param string[] $argv
     * @throws \RuntimeException
     */
    private function parseArgv(array $argv)
    {
        $current = $name = null;

        for ($i = 1, $l = count($argv); $i < $l; $i++) {
            if (substr($argv[$i], 0, 2) === '--') {
                $name = strtolower(substr($argv[$i], 2));

                if (strpos($name, '=') !== false) {
                    list($name, $value) = explode('=', $name, 2);
                    $this->args[$name] = $value;
                } else {
                    $this->args[$name] = true;
                }

                $current = &$this->args[$name];
            } else if ($current === true) {
                $this->args[$name] = $argv[$i];
            } else {
                throw new \RuntimeException('Invalid option: ' . $argv[$i]);
            }
        }
    }

    /**
     * Determine whether the named argument was passed
     *
     * @param string $name
     * @return bool
     */
    public function hasArg($name)
    {
        return isset($this->args[strtolower($name)]);
    }

    /**
     * Get the value of the named argument
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getArg($name, $defaultValue = null)
    {
        if (isset($this->args[$name = strtolower($name)])) {
            return $this->args[$name];
        }

        return $defaultValue;
    }

    /**
     * Get the time at which this environment was created
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }
}
