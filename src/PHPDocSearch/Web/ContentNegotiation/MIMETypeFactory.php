<?php

namespace PHPDocSearch\Web\ContentNegotiation;

class MIMETypeFactory
{
    /**
     * Create a new MIMEType instance
     *
     * @param string $superType
     * @param string $subType
     * @param string[] $params
     * @param float $qValue
     * @return MIMEType
     */
    public function create($superType, $subType, array $params = [], $qValue = 1.0)
    {
        return new MIMEType($superType, $subType, $params, $qValue);
    }
}
