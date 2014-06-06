<?php

namespace PHPDocSearch\Web;

class TemplateFetcher
{
    /**
     * Find the path of the named template for the request type
     *
     * @param string $name
     * @param string $type
     * @return string|null
     */
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
