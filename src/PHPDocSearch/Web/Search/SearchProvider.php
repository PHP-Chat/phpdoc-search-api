<?php

namespace PHPDocSearch\Web\Search;

use PHPDocSearch\Web\Request;

class SearchProvider
{
    private $queryParser;

    private $queryCache;

    private $queryResolver;

    private $request;

    public function __construct(
        QueryParser $queryParser,
        QueryResolver $queryResolver,
        Request $request
    ) {
        $this->queryParser = $queryParser;
        $this->queryResolver = $queryResolver;
        $this->request = $request;
    }

    public function getResult()
    {
        $query = $this->queryParser->parse($this->request->getArg('q'));

        $result = $this->queryResolver->resolve($query);

        return $result;
    }
}
