<?php

namespace PHPDocSearch\Web\ContentNegotiation;

class ContentType
{
    private $superType;

    private $subType;

    private $params;

    private $qValue;

    public function __construct($superType, $subType, array $params = [], $qValue = 1)
    {
        $this->superType = $superType;
        $this->subType = $subType;
        $this->params = $params;
        $this->qValue = $qValue;
    }

    public function match(ContentType $contentType)
    {
        $score = 0;
        $divisor = 2;

        if ($this->superType === '*' || $contentType->getSuperType() === $this->superType) {
            $score++;
        }

        if ($this->subType === '*' || $contentType->getSubType() === $this->subType) {
            $score++;
        }

        $params = $contentType->getParams();

        foreach ($this->params as $key => $value) {
            $divisor++;

            if (isset($params[$key]) && $params[$key] === $value) {
                $score++;
            }

            unset($params[$key], $this->params[$key]);
        }

        $divisor += count($params);

        return $score / $divisor;
    }

    public function __toString()
    {
        $params = [];
        foreach ($this->params as $key => $val) {
            $params[] = $key . '=' . $val;
        }

        return $this->getType() . ($params ? ';' . implode(';', $params) : '');
    }

    public function getType()
    {
        return $this->superType . '/' . $this->subType;
    }

    public function getSuperType()
    {
        return $this->superType;
    }

    public function getSubType()
    {
        return $this->subType;
    }

    public function getQValue()
    {
        return $this->qValue;
    }

    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    public function getParams()
    {
        return $this->params;
    }
}
