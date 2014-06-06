<?php

namespace PHPDocSearch\Web\Search;

class QueryFactory
{
    /**
     * Create a new Query instance
     *
     * @param string[] $parts
     * @param string $signature
     * @param int $flags
     * @return Query
     */
    public function create(array $parts, $signature, $flags)
    {
        return new Query($parts, $signature, $flags);
    }
}
