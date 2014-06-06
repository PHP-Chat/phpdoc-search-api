<?php

namespace PHPDocSearch;

class GitRepository
{
    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var string
     */
    private $name;

    /**
     * Constructor
     *
     * @param string $baseDir
     * @throws \RuntimeException
     */
    public function __construct($baseDir)
    {
        $this->baseDir = realpath($baseDir);
        $this->name = basename($this->baseDir); // todo: do this properly from .git

        if (!is_dir($this->baseDir . '/.git')) {
            throw new \RuntimeException('The supplied path is not a valid git repository');
        }
    }

    /**
     * Build a command line string
     *
     * @param string $action
     * @param string $flags
     * @param array $args
     * @return string
     */
    private function buildCommand($action, $flags, $args)
    {
        $cmdParts = ['git', $action];

        if ('' !== (string) $flags) {
            $cmdParts[] = '-' . ltrim($flags, '-');
        }

        foreach ($args as $arg) {
            $arg = trim($arg);

            if (preg_match('/\s+/', $arg)) {
                $arg = '"' . $arg . '"';
            }

            $cmdParts[] = $arg;
        }

        return implode(' ', $cmdParts);
    }

    /**
     * Execute a command
     *
     * I have no idea why I chose to use proc_open instead of exec, but I will assume there was a good
     * reason and ignore it for now.
     *
     * @param string $action
     * @param string $flags
     * @param mixed ...$args
     * @return bool
     */
    private function execCommand($action, $flags /*, ...$args */)
    {
        $cmd = $this->buildCommand($action, $flags, array_slice(func_get_args(), 2));

        $descriptors = [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ];
        $opts = ['bypass_shell' => true];

        if (!$proc = proc_open($cmd, $descriptors, $pipes, $this->baseDir, null, $opts)) {
            return false;
        }

        fclose($pipes[0]);
        unset($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while ($pipes) {
            $r = $pipes;
            $w = $e = null;
            stream_select($r, $w, $e, null);
            
            foreach ($r as $pipe) {
                $pipeId = array_search($pipe, $pipes);

                while ('' !== $chunk = fread($pipe, 1024)) continue;

                if (feof($pipe)) {
                    fclose($pipe);
                    unset($pipes[$pipeId]);
                }
            }
        }

        $errCode = proc_get_status($proc)['exitcode'];
        proc_close($proc);

        return $errCode === 0;
    }

    /**
     * Get the name of this repo
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the hash of the last commit to the current branch
     *
     * @return string
     */
    public function getLastCommit()
    {
        return trim(file_get_contents($this->baseDir . '/.git/refs/heads/master'));
    }

    /**
     * Pull data from the specified remote and branch
     *
     * @param string $remote
     * @param string $branch
     * @param string $flags
     * @return bool
     */
    public function pull($remote = 'origin', $branch = 'master', $flags = '')
    {
        return $this->execCommand('pull', $flags, $remote, $branch);
    }

    /**
     * Checkout the specified branch
     *
     * @param string $branch
     * @param string $flags
     * @return bool
     */
    public function checkout($branch = '.', $flags = '')
    {
        return $this->execCommand('checkout', $flags, $branch);
    }

    /**
     * Clean the current branch
     *
     * @param string $flags
     * @return bool
     */
    public function clean($flags = '')
    {
        return $this->execCommand('clean', 'f' . $flags);
    }
}
