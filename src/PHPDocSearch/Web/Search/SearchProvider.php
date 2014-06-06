<?php

namespace PHPDocSearch\Web\Search;

use PHPDocSearch\Web\Request;

class SearchProvider
{
    /**
     * @var QueryParser
     */
    private $queryParser;

    /**
     * @var QueryResolver
     */
    private $queryResolver;

    /**
     * @var \PHPDocSearch\Web\Request
     */
    private $request;

    /**
     * Constructor
     *
     * @param QueryParser $queryParser
     * @param QueryResolver $queryResolver
     * @param Request $request
     */
    public function __construct(
        QueryParser $queryParser,
        QueryResolver $queryResolver,
        Request $request
    ) {
        $this->queryParser = $queryParser;
        $this->queryResolver = $queryResolver;
        $this->request = $request;
    }

    /**
     * Get the result set for a query string
     *
     * @param string $queryStr
     * @return array
     */
    public function getResult($queryStr)
    {
        $query = $this->queryParser->parse($queryStr);
        $result = $this->queryResolver->resolve($query);

        return $result;
    }
}
