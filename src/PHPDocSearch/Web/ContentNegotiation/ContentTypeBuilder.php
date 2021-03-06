<?php

namespace PHPDocSearch\Web\ContentNegotiation;

class ContentTypeBuilder
{
    private $contentTypeFactory;

    public function __construct(ContentTypeFactory $contentTypeFactory)
    {
        $this->contentTypeFactory = $contentTypeFactory;
    }

    public function build($typeDef)
    {
        $parts = preg_split('#\s*;\s*#', trim($typeDef), -1, PREG_SPLIT_NO_EMPTY);

        $typeParts = preg_split('#\s*/\s*#', strtolower(array_shift($parts)), 2);
        if (!isset($typeParts[1])) {
            return null;
        }

        list($superType, $subType) = $typeParts;
        if ($superType === '*' && $subType !== '*') {
            return null;
        }

        $params = [];
        $qValue = 1;
        foreach ($parts as $param) {
            $paramParts = preg_split('#\s*=\s*#', $param, 2);

            if (isset($paramParts[1])) {
                if ($paramParts[0] === 'q') {
                    $qValue = (float) $paramParts[1];
                    break; // TODO: we don't account for accept-extensions
                }

                $params[$paramParts[0]] = $paramParts[1];
            }
        }

        return $this->contentTypeFactory->create($superType, $subType, $params, $qValue);
    }
}
