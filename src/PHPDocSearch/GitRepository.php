<?php

namespace PHPDocSearch;

class GitRepository
{
    private $baseDir;

    private $name;

    public function __construct($baseDir)
    {
        $this->baseDir = realpath($baseDir);
        $this->name = basename($this->baseDir);

        if (!is_dir($this->baseDir . '/.git')) {
            throw new \RuntimeException('The supplied path is not a valid git repository');
        }
    }

    private function execCommand()
    {
        $cmdParts = ['git'];

        $args = func_get_args();
        $cmdParts[] = array_shift($args);

        $flags = array_shift($args);
        if (((string) $flags) !== '') {
            $cmdParts[] = '-' . $flags;
        }

        foreach ($args as $arg) {
            $arg = trim($arg);

            if (preg_match('/\s+/', $arg)) {
                $arg = '"' . $arg . '"';
            }

            $cmdParts[] = $arg;
        }

        $cmd = implode(' ', $cmdParts);
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

    public function getName()
    {
        return $this->name;
    }

    public function getLastCommit()
    {
        return trim(file_get_contents($this->baseDir . '/.git/refs/heads/master'));
    }

    public function pull($remote = 'origin', $branch = 'master', $flags = '')
    {
        return $this->execCommand('pull', $flags, $remote, $branch);
    }

    public function checkout($branch = '.', $flags = '')
    {
        return $this->execCommand('checkout', $flags, $branch);
    }

    public function clean($flags = '')
    {
        return $this->execCommand('clean', 'f' . $flags);
    }
}
