<?php

namespace PHPDocSearch;

interface Logger
{
    public function log($message);

    public function warn($message);

    public function error($message);
}
