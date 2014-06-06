<?php

namespace PHPDocSearch\Web\ContentNegotiation;

class MIMEType
{
    /**
     * @var string
     */
    private $superType;

    /**
     * @var string
     */
    private $subType;

    /**
     * @var string[]
     */
    private $params;

    /**
     * @var float
     */
    private $qValue;

    /**
     * Constructor
     *
     * @param string $superType
     * @param string $subType
     * @param string[] $params
     * @param float $qValue
     */
    public function __construct($superType, $subType, array $params = [], $qValue = 1.0)
    {
        $this->superType = $superType;
        $this->subType = $subType;
        $this->params = $params;
        $this->qValue = $qValue;
    }

    /**
     * Get a value indicating how well another MIMEType instance matches this one
     *
     * A return value of 1 indicates a 100% match, 0 indicates no match
     *
     * @param MIMEType $matchType
     * @return float
     */
    public function match(MIMEType $matchType)
    {
        $score = 0;
        $divisor = 2;

        if ($this->superType === '*' || $matchType->getSuperType() === $this->superType) {
            $score++;
        }

        if ($this->subType === '*' || $matchType->getSubType() === $this->subType) {
            $score++;
        }

        $params = $matchType->getParams();

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

    /**
     * Get the string representation of this type
     *
     * @return string
     */
    public function __toString()
    {
        $params = [];
        foreach ($this->params as $key => $val) {
            $params[] = $key . '=' . $val;
        }

        return $this->getType() . ($params ? '; ' . implode('; ', $params) : '');
    }

    /**
     * Get the super type and subtype with no parameters
     *
     * @return string
     */
    public function getType()
    {
        return $this->superType . '/' . $this->subType;
    }

    /**
     * Get the super type
     *
     * @return string
     */
    public function getSuperType()
    {
        return $this->superType;
    }

    /**
     * Get the sub type
     *
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * Get the q-value
     *
     * @return string
     */
    public function getQValue()
    {
        return $this->qValue;
    }

    /**
     * Get the value of a named parameter
     *
     * @param string $name
     * @return string|null
     */
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * Get an array of all parameters
     *
     * @return string[]
     */
    public function getParams()
    {
        return $this->params;
    }
}
