<?php

namespace PHPDocSearch;

use \PHPDocSearch\Config,
    \PHPDocSearch\Environment;

class Request extends Environment
{
    private $urlParams;

    private $serverParams;

    private $cookies;

    private $headers = [];

    private $path;

    public function __construct($baseDir, Config $config, array $urlParams, array $cookieParams, array $serverParams)
    {
        $this->setBaseDir($baseDir);
        $this->config = $config;

        $this->urlParams = $urlParams;
        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;

        $this->extractHeadersFromServerParams();
        $this->setPath();
    }

    private function extractHeadersFromServerParams()
    {
        foreach ($this->serverParams as $key => $value) {
            $key = strtolower($key);

            if (substr($key, 0, 5) === 'http_') {
                $this->headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
    }

    private function setPath()
    {
        $this->path = trim(explode('?', $this->serverParams['REQUEST_URI'], 2)[0], '/');
    }

    public function getPath()
    {
        return $this->path;
    }

    public function hasArg($name)
    {
        return isset($this->urlParams[strtolower($name)]);
    }

    public function getArg($name, $defaultValue = null)
    {
        if (isset($this->urlParams[$name = strtolower($name)])) {
            return $this->urlParams[$name];
        }

        return $defaultValue;
    }

    public function hasServerParam($name)
    {
        return isset($this->serverParams[strtolower($name)]);
    }

    public function getServerParam($name, $defaultValue = null)
    {
        if (isset($this->serverParams[$name = strtolower($name)])) {
            return $this->serverParams[$name];
        }

        return $defaultValue;
    }

    public function hasCookie($name)
    {
        return isset($this->cookies[strtolower($name)]);
    }

    public function getCookie($name, $defaultValue = null)
    {
        if (isset($this->cookies[$name = strtolower($name)])) {
            return $this->cookies[$name];
        }

        return $defaultValue;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name, $defaultValue = null)
    {
        if (isset($this->headers[$name = strtolower($name)])) {
            return $this->headers[$name];
        }

        return $defaultValue;
    }
}
