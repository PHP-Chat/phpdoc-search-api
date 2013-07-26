<?php

namespace PHPDocSearch\Web\ContentNegotiation;

class ContentTypeFactory
{
    public function create($superType, $subType, array $params = [], $qValue = 1)
    {
        return new ContentType($superType, $subType, $params, $qValue);
    }
}
