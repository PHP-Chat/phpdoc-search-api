<?php

namespace PHPDocSearch\Web\ContentNegotiation;

class MIMETypeResolver
{
    /**
     * @var MIMETypeBuilder
     */
    private $mimeTypeBuilder;

    /**
     * Constructor
     *
     * @param MIMETypeBuilder $mimeTypeBuilder
     */
    public function __construct(MIMETypeBuilder $mimeTypeBuilder)
    {
        $this->mimeTypeBuilder = $mimeTypeBuilder;
    }

    /**
     * Parse the value of an Accept: header
     *
     * @param $acceptedTypes
     * @return array
     */
    private function parseAcceptedTypes($acceptedTypes)
    {
        $result = [];

        foreach (explode(',', $acceptedTypes) as $typeSpec) {
            if ($type = $this->mimeTypeBuilder->build($typeSpec)) {
                $result[$type->getSuperType()][$type->getSubType()][] = $type;
            }
        }

        return $result;
    }

    /**
     * Get the best matching available type based on an Accept: header value
     *
     * @param string $acceptedTypes
     * @param array $availableTypes
     * @return MIMEType|null
     */
    public function getResponseType($acceptedTypes, array $availableTypes)
    {
        if (!$acceptedTypes = $this->parseAcceptedTypes($acceptedTypes)) {
            return current($availableTypes);
        }

        $weightMap = [];

        foreach ($availableTypes as $typeSpec) {
            $availableType = $typeSpec instanceof MIMEType ? $typeSpec : $this->mimeTypeBuilder->build($typeSpec);

            $superType = $availableType->getSuperType();
            $subType = $availableType->getSubType();

            /** @var MIMEType[] $candidates */
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
