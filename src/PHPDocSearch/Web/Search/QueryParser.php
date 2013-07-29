<?php

namespace PHPDocSearch\Web\Search;

class QueryParser
{
    private $queryFactory;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function parse($queryString)
    {
        $queryString = str_replace('-', '_', strtolower(trim($queryString)));
        if (substr($queryString, -2) === '()') {
            $isFunction = true;
            $queryString = rtrim(substr($queryString, 0, -2));
        } else {
            $isFunction = false;
        }

        $expr = '/\s*+(?:\.|::|_>)\s*/';
        $parts = preg_split($expr, $queryString, -1, PREG_SPLIT_NO_EMPTY);
        $numParts = count($parts);

        $flags = 0;
        if ($numParts === 1) {
            if ($isFunction) {
                $flags = Query::ENTITY_FUNCTION | Query::ENTITY_MAGICMETHOD;
                $signature = 'function.' . $parts[0];
            } else {
                $flags = Query::ENTITY_SINGLE_KEYWORD;
                $signature = $parts[0];
            }
        } else if ($numParts === 2) {
            switch (true) {
                case $parts[0] === 'book':
                    array_shift($parts);
                    $flags = Query::ENTITY_BOOK;
                    $signature = 'book.' . $parts[0];
                    break;

                case $parts[0] === 'class':
                    array_shift($parts);
                    $flags = Query::ENTITY_CLASS;
                    $signature = 'class.' . $parts[0];
                    break;

                case $parts[0] === 'function':
                    array_shift($parts);
                    $flags = Query::ENTITY_FUNCTION;
                    $signature = 'function.' . $parts[0];
                    break;

                case $isFunction:
                    $flags = Query::ENTITY_CLASSMETHOD;
                    $signature = $parts[0] . '.' . $parts[1] . '%';
                    break;

                case $parts[1][0] === '$':
                    $flags = Query::ENTITY_CLASSPROPERTY;
                    $signature = $parts[0] . '.%' . substr($parts[1], 1);
                    break;

                default:
                    $flags = Query::ENTITY_DOUBLE_KEYWORD;
                    $signature = $parts[0] . '.' . $parts[1];
                    break;
            }
        } else if ($numParts > 0) {
            $flags = Query::ENTITY_CONFIGOPTION;
            $signature = implode('.', $parts);
        }

        return $this->queryFactory->create($parts, $signature, $flags);
    }
}
