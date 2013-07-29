<?php

namespace PHPDocSearch\Web\Search;

class QueryFactory
{
    public function create(array $parts, $signature, $flags)
    {
        return new Query($parts, $signature, $flags);
    }
}
