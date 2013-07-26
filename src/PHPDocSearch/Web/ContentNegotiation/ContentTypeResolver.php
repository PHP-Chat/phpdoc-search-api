<?php

namespace PHPDocSearch\Web\ContentNegotiation;

class ContentTypeResolver
{
    private $contentTypeBuilder;

    public function __construct(ContentTypeBuilder $contentTypeBuilder)
    {
        $this->contentTypeBuilder = $contentTypeBuilder;
    }

    private function parseAcceptedTypes($acceptedTypes)
    {
        $result = [];

        foreach (explode(',', $acceptedTypes) as $typeSpec) {
            if ($contentType = $this->contentTypeBuilder->build($typeSpec)) {
                $result[$contentType->getSuperType()][$contentType->getSubType()][] = $contentType;
            }
        }

        return $result;
    }

    public function getResponseType($acceptedTypes, array $availableTypes)
    {
        if (!$acceptedTypes = $this->parseAcceptedTypes($acceptedTypes)) {
            return current($availableTypes);
        }

        $weightMap = [];

        foreach ($availableTypes as $typeSpec) {
            $availableType = $this->contentTypeBuilder->build($typeSpec);
            $superType = $availableType->getSuperType();
            $subType = $availableType->getSubType();

            if (isset($acceptedTypes[$superType][$subType])) {
                $candidates = $acceptedTypes[$superType][$subType];
            } else if (isset($acceptedTypes[$superType]['*'])) {
                $candidates = $acceptedTypes[$superType]['*'];
            } else if (isset($acceptedTypes['*']['*'])) {
                $candidates = $acceptedTypes['*']['*'];
            } else {
                continue;
            }

            $bestMatchFactor = 0;
            $bestMatch = null;
            foreach ($candidates as $acceptedType) {
                $factor = $acceptedType->match($availableType);
                if ($factor > $bestMatchFactor) {
                    $bestMatchFactor = $factor;
                    $bestMatch = $acceptedType;

                    if ($bestMatchFactor >= 1) {
                        break;
                    }
                }
            }

            $weightMap[(string) $bestMatch->getQValue()][] = $availableType;
        }

        if (!$weightMap) {
            return null;
        }

        ksort($weightMap, SORT_NUMERIC);

        return end($weightMap)[0];
    }
}
