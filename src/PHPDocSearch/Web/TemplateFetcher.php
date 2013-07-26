<?php

namespace PHPDocSearch\Web;

class TemplateFetcher
{
    public function fetch($name, $type = 'html')
    {
        $baseDir = realpath(__DIR__ . '/../../../templates/');
        $name = strtolower($name);

        switch ($type) {
            case 'html':
                if (file_exists($baseDir . $name . '.phtml')) {
                    return $baseDir . $name . '.phtml';
                }
                break;

            case 'text':
                if (file_exists($baseDir . $name . '.txt')) {
                    return $baseDir . $name . '.txt';
                }
                break;
        }

        return null;
    }
}
