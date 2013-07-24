<?php

namespace PHPDocSearch;

class BlackHoleLogger implements Logger
{
    public function log($message) {}

    public function warn($message) {}

    public function error($message) {}
}
